<?php
/**
 * NexClinic prescription → pharmacy dispense helpers.
 */

require_once __DIR__ . '/nex_sectors.php';

function nex_rx_statuses() {
    return [
        'pending' => 'Pending pharmacy',
        'partial' => 'Partially dispensed',
        'dispensed' => 'Dispensed',
        'cancelled' => 'Cancelled',
    ];
}

function nex_rx_schema_ready($mysqli) {
    static $ok = null;
    if ($ok !== null) {
        return $ok;
    }
    $res = @$mysqli->query("SHOW TABLES LIKE 'his_prescription_items'");
    $ok = ($res && $res->num_rows > 0);
    if ($ok) {
        $col = @$mysqli->query("SHOW COLUMNS FROM his_prescriptions LIKE 'pres_status'");
        $ok = ($col && $col->num_rows > 0);
    }
    return $ok;
}

function nex_rx_list_drugs($mysqli) {
    $rows = [];
    $res = @$mysqli->query(
        "SELECT phar_id, phar_name, phar_qty, phar_bcode
         FROM his_pharmaceuticals
         ORDER BY phar_name ASC
         LIMIT 1000"
    );
    if ($res) {
        while ($r = $res->fetch_object()) {
            $rows[] = $r;
        }
    }
    return $rows;
}

/**
 * Normalize POST drug lines: drug_phar_id[], drug_qty[], drug_dosage[], etc.
 * @return array{ok:bool, items?:array, err?:string}
 */
function nex_rx_collect_items_from_post() {
    $ids = $_POST['drug_phar_id'] ?? [];
    $qtys = $_POST['drug_qty'] ?? [];
    $dosages = $_POST['drug_dosage'] ?? [];
    $freqs = $_POST['drug_frequency'] ?? [];
    $durs = $_POST['drug_duration'] ?? [];
    $notes = $_POST['drug_notes'] ?? [];
    if (!is_array($ids)) {
        return ['ok' => false, 'err' => 'Invalid drug lines.'];
    }
    $items = [];
    $n = count($ids);
    for ($i = 0; $i < $n; $i++) {
        $phar_id = (int) ($ids[$i] ?? 0);
        $qty = (int) ($qtys[$i] ?? 0);
        if ($phar_id <= 0 || $qty <= 0) {
            continue;
        }
        $items[] = [
            'phar_id' => $phar_id,
            'qty' => $qty,
            'dosage' => trim((string) ($dosages[$i] ?? '')),
            'frequency' => trim((string) ($freqs[$i] ?? '')),
            'duration' => trim((string) ($durs[$i] ?? '')),
            'notes' => trim((string) ($notes[$i] ?? '')),
        ];
    }
    if (!$items) {
        return ['ok' => false, 'err' => 'Add at least one medicine with quantity.'];
    }
    return ['ok' => true, 'items' => $items];
}

function nex_rx_build_instructions_html(array $items, $extra = '') {
    $lis = '';
    foreach ($items as $it) {
        $name = htmlspecialchars((string) ($it['drug_name'] ?? ''), ENT_QUOTES, 'UTF-8');
        $qty = (int) ($it['qty'] ?? 0);
        $dose = htmlspecialchars((string) ($it['dosage'] ?? ''), ENT_QUOTES, 'UTF-8');
        $freq = htmlspecialchars((string) ($it['frequency'] ?? ''), ENT_QUOTES, 'UTF-8');
        $dur = htmlspecialchars((string) ($it['duration'] ?? ''), ENT_QUOTES, 'UTF-8');
        $bits = array_filter([$dose, $freq, $dur]);
        $detail = $bits ? ' — ' . implode(', ', $bits) : '';
        $lis .= '<li>' . $name . ' × ' . $qty . $detail . '</li>';
    }
    $html = '<ul>' . $lis . '</ul>';
    $extra = trim((string) $extra);
    if ($extra !== '') {
        $html .= '<p>' . htmlspecialchars($extra, ENT_QUOTES, 'UTF-8') . '</p>';
    }
    return $html;
}

/**
 * @param array $header patient + ailment fields
 * @param array $items collected items (phar_id, qty, ...)
 * @return array{ok:bool, pres_id?:int, err?:string}
 */
function nex_rx_create($mysqli, array $header, array $items) {
    if (!nex_rx_schema_ready($mysqli)) {
        return ['ok' => false, 'err' => 'Run migration 2026_08_07_prescription_dispense.sql first.'];
    }

    // Resolve drug names + validate stock presence (warn only at create)
    $resolved = [];
    foreach ($items as $it) {
        $phar_id = (int) $it['phar_id'];
        $stmt = $mysqli->prepare('SELECT phar_id, phar_name, phar_qty FROM his_pharmaceuticals WHERE phar_id=? LIMIT 1');
        if (!$stmt) {
            return ['ok' => false, 'err' => 'Drug catalogue unavailable.'];
        }
        $stmt->bind_param('i', $phar_id);
        $stmt->execute();
        $drug = $stmt->get_result()->fetch_object();
        $stmt->close();
        if (!$drug) {
            return ['ok' => false, 'err' => 'Selected medicine not found.'];
        }
        $it['drug_name'] = $drug->phar_name;
        $resolved[] = $it;
    }

    $pat_number = (string) ($header['pres_pat_number'] ?? '');
    $pat_id = nex_resolve_pat_id($mysqli, $pat_number);
    $prescribed_by = (int) (nex_actor_id() ?? 0);
    $pres_ins = nex_rx_build_instructions_html($resolved, $header['pres_notes'] ?? '');
    $status = 'pending';

    $pat_name = (string) ($header['pres_pat_name'] ?? '');
    $pat_type = (string) ($header['pres_pat_type'] ?? '');
    $pat_addr = (string) ($header['pres_pat_addr'] ?? '');
    $pat_age = (string) ($header['pres_pat_age'] ?? '');
    $pres_number = (string) ($header['pres_number'] ?? '');
    $ailment = (string) ($header['pres_pat_ailment'] ?? '');

    $has_pat_id = false;
    $col = @$mysqli->query("SHOW COLUMNS FROM his_prescriptions LIKE 'pat_id'");
    if ($col && $col->num_rows > 0) {
        $has_pat_id = true;
    }

    $mysqli->begin_transaction();
    try {
        if ($has_pat_id && $pat_id) {
            $sql = 'INSERT INTO his_prescriptions (
                pat_id, prescribed_by, pres_pat_name, pres_pat_number, pres_pat_type, pres_pat_addr,
                pres_pat_age, pres_number, pres_pat_ailment, pres_ins, pres_status
            ) VALUES (?,?,?,?,?,?,?,?,?,?,?)';
            $stmt = $mysqli->prepare($sql);
            $stmt->bind_param(
                'iisssssssss',
                $pat_id,
                $prescribed_by,
                $pat_name,
                $pat_number,
                $pat_type,
                $pat_addr,
                $pat_age,
                $pres_number,
                $ailment,
                $pres_ins,
                $status
            );
        } else {
            $sql = 'INSERT INTO his_prescriptions (
                prescribed_by, pres_pat_name, pres_pat_number, pres_pat_type, pres_pat_addr,
                pres_pat_age, pres_number, pres_pat_ailment, pres_ins, pres_status
            ) VALUES (?,?,?,?,?,?,?,?,?,?)';
            $stmt = $mysqli->prepare($sql);
            $stmt->bind_param(
                'isssssssss',
                $prescribed_by,
                $pat_name,
                $pat_number,
                $pat_type,
                $pat_addr,
                $pat_age,
                $pres_number,
                $ailment,
                $pres_ins,
                $status
            );
        }
        if (!$stmt->execute()) {
            throw new RuntimeException('Could not save prescription.');
        }
        $pres_id = (int) $mysqli->insert_id;
        $stmt->close();

        $isql = 'INSERT INTO his_prescription_items
            (pres_id, phar_id, drug_name, qty, dosage, frequency, duration, notes)
            VALUES (?,?,?,?,?,?,?,?)';
        $istmt = $mysqli->prepare($isql);
        foreach ($resolved as $it) {
            $phar_id = (int) $it['phar_id'];
            $drug_name = (string) $it['drug_name'];
            $qty = (int) $it['qty'];
            $dosage = (string) $it['dosage'];
            $frequency = (string) $it['frequency'];
            $duration = (string) $it['duration'];
            $notes = (string) $it['notes'];
            $istmt->bind_param(
                'iisissss',
                $pres_id,
                $phar_id,
                $drug_name,
                $qty,
                $dosage,
                $frequency,
                $duration,
                $notes
            );
            if (!$istmt->execute()) {
                throw new RuntimeException('Could not save prescription lines.');
            }
        }
        $istmt->close();
        $mysqli->commit();
        return ['ok' => true, 'pres_id' => $pres_id, 'pat_id' => $pat_id, 'pres_ins' => $pres_ins];
    } catch (Throwable $e) {
        $mysqli->rollback();
        return ['ok' => false, 'err' => $e->getMessage()];
    }
}

function nex_rx_pending_list($mysqli, $q = '') {
    if (!nex_rx_schema_ready($mysqli)) {
        return [];
    }
    $sql = "SELECT p.*,
            (SELECT COUNT(*) FROM his_prescription_items i WHERE i.pres_id = p.pres_id) AS item_count
        FROM his_prescriptions p
        WHERE p.pres_status IN ('pending','partial')";
    $params = [];
    $types = '';
    $q = trim($q);
    if ($q !== '') {
        $like = '%' . $q . '%';
        $sql .= ' AND (p.pres_pat_name LIKE ? OR p.pres_pat_number LIKE ? OR p.pres_number LIKE ?)';
        $params = [$like, $like, $like];
        $types = 'sss';
    }
    $sql .= ' ORDER BY p.pres_date DESC LIMIT 200';
    $stmt = $mysqli->prepare($sql);
    if (!$stmt) {
        return [];
    }
    if ($types !== '') {
        $stmt->bind_param($types, ...$params);
    }
    $stmt->execute();
    $res = $stmt->get_result();
    $rows = [];
    while ($r = $res->fetch_object()) {
        $rows[] = $r;
    }
    $stmt->close();
    return $rows;
}

function nex_rx_get($mysqli, $pres_id) {
    $pres_id = (int) $pres_id;
    $stmt = $mysqli->prepare('SELECT * FROM his_prescriptions WHERE pres_id=? LIMIT 1');
    if (!$stmt) {
        return null;
    }
    $stmt->bind_param('i', $pres_id);
    $stmt->execute();
    $row = $stmt->get_result()->fetch_object();
    $stmt->close();
    return $row;
}

function nex_rx_items($mysqli, $pres_id) {
    if (!nex_rx_schema_ready($mysqli)) {
        return [];
    }
    $pres_id = (int) $pres_id;
    $stmt = $mysqli->prepare(
        'SELECT i.*, ph.phar_qty AS stock_qty
         FROM his_prescription_items i
         LEFT JOIN his_pharmaceuticals ph ON ph.phar_id = i.phar_id
         WHERE i.pres_id=?
         ORDER BY i.item_id ASC'
    );
    if (!$stmt) {
        return [];
    }
    $stmt->bind_param('i', $pres_id);
    $stmt->execute();
    $res = $stmt->get_result();
    $rows = [];
    while ($r = $res->fetch_object()) {
        $rows[] = $r;
    }
    $stmt->close();
    return $rows;
}

/**
 * Dispense full remaining quantities for a pending/partial Rx.
 * @return array{ok:bool, err?:string}
 */
function nex_rx_dispense($mysqli, $pres_id, $notes = '') {
    if (!nex_rx_schema_ready($mysqli)) {
        return ['ok' => false, 'err' => 'Dispense schema not installed.'];
    }
    $pres_id = (int) $pres_id;
    $actor = nex_actor_id();
    $now = date('Y-m-d H:i:s');

    $mysqli->begin_transaction();
    try {
        $rx = nex_rx_get($mysqli, $pres_id);
        if (!$rx) {
            throw new RuntimeException('Prescription not found.');
        }
        if (!in_array($rx->pres_status, ['pending', 'partial'], true)) {
            throw new RuntimeException('Prescription is not awaiting dispense.');
        }
        $items = nex_rx_items($mysqli, $pres_id);
        if (!$items) {
            // Legacy free-text only — mark dispensed without stock movement
            $ustmt = $mysqli->prepare(
                "UPDATE his_prescriptions SET pres_status='dispensed', dispensed_by=?, dispensed_at=? WHERE pres_id=?"
            );
            $ustmt->bind_param('isi', $actor, $now, $pres_id);
            $ustmt->execute();
            $ustmt->close();
        } else {
            foreach ($items as $it) {
                $need = (int) $it->qty - (int) $it->qty_dispensed;
                if ($need <= 0) {
                    continue;
                }
                $phar_id = (int) $it->phar_id;
                if (!$phar_id) {
                    throw new RuntimeException('Line has no linked drug: ' . $it->drug_name);
                }
                $lock = $mysqli->prepare('SELECT phar_qty FROM his_pharmaceuticals WHERE phar_id=? FOR UPDATE');
                $lock->bind_param('i', $phar_id);
                $lock->execute();
                $stockRow = $lock->get_result()->fetch_object();
                $lock->close();
                if (!$stockRow) {
                    throw new RuntimeException('Drug not found in stock: ' . $it->drug_name);
                }
                $stock = (int) $stockRow->phar_qty;
                if ($stock < $need) {
                    throw new RuntimeException(
                        'Insufficient stock for ' . $it->drug_name . ' (need ' . $need . ', have ' . $stock . ').'
                    );
                }
                $newStock = $stock - $need;
                $dstmt = $mysqli->prepare('UPDATE his_pharmaceuticals SET phar_qty=? WHERE phar_id=?');
                $dstmt->bind_param('ii', $newStock, $phar_id);
                if (!$dstmt->execute()) {
                    throw new RuntimeException('Stock update failed.');
                }
                $dstmt->close();

                $newDisp = (int) $it->qty_dispensed + $need;
                $item_id = (int) $it->item_id;
                $istmt = $mysqli->prepare('UPDATE his_prescription_items SET qty_dispensed=? WHERE item_id=?');
                $istmt->bind_param('ii', $newDisp, $item_id);
                $istmt->execute();
                $istmt->close();
            }
            $ustmt = $mysqli->prepare(
                "UPDATE his_prescriptions SET pres_status='dispensed', dispensed_by=?, dispensed_at=? WHERE pres_id=?"
            );
            $ustmt->bind_param('isi', $actor, $now, $pres_id);
            $ustmt->execute();
            $ustmt->close();
        }

        $dstmt = $mysqli->prepare(
            'INSERT INTO his_prescription_dispenses (pres_id, dispensed_by, dispensed_at, notes) VALUES (?,?,?,?)'
        );
        $dstmt->bind_param('iiss', $pres_id, $actor, $now, $notes);
        $dstmt->execute();
        $dstmt->close();

        $mysqli->commit();
        return ['ok' => true];
    } catch (Throwable $e) {
        $mysqli->rollback();
        return ['ok' => false, 'err' => $e->getMessage()];
    }
}

function nex_rx_pending_count($mysqli) {
    if (!nex_rx_schema_ready($mysqli)) {
        return 0;
    }
    $n = 0;
    if ($stmt = $mysqli->prepare(
        "SELECT COUNT(*) FROM his_prescriptions WHERE pres_status IN ('pending','partial')"
    )) {
        $stmt->execute();
        $stmt->bind_result($n);
        $stmt->fetch();
        $stmt->close();
    }
    return (int) $n;
}

/** Soft authorize when nex session present. */
function nex_rx_authorize($action, array $ctx = []) {
    if (empty($_SESSION['nex_user_id'])) {
        return true;
    }
    require_once __DIR__ . '/nex_authz.php';
    return nex_authorize($action, $ctx);
}

function nex_rx_status_label($status) {
    $map = nex_rx_statuses();
    $status = (string) $status;
    return $map[$status] ?? ($status !== '' ? $status : '—');
}

/** HTML list of structured lines (escaped). Empty string if none. */
function nex_rx_items_html($mysqli, $pres_id) {
    $items = nex_rx_items($mysqli, $pres_id);
    if (!$items) {
        return '';
    }
    $html = '<ul class="mb-0 pl-3">';
    foreach ($items as $it) {
        $bits = array_filter([$it->dosage, $it->frequency, $it->duration]);
        $html .= '<li>' . htmlspecialchars((string) $it->drug_name, ENT_QUOTES, 'UTF-8')
            . ' × ' . (int) $it->qty;
        if ($bits) {
            $html .= ' — ' . htmlspecialchars(implode(', ', $bits), ENT_QUOTES, 'UTF-8');
        }
        $html .= ' <span class="text-muted">(dispensed ' . (int) $it->qty_dispensed . ')</span></li>';
    }
    $html .= '</ul>';
    return $html;
}

/**
 * Cancel a pending prescription (clinical). No stock impact.
 * @return array{ok:bool, err?:string}
 */
function nex_rx_cancel($mysqli, $pres_id) {
    if (!nex_rx_schema_ready($mysqli)) {
        return ['ok' => false, 'err' => 'Dispense schema not installed.'];
    }
    $pres_id = (int) $pres_id;
    $rx = nex_rx_get($mysqli, $pres_id);
    if (!$rx) {
        return ['ok' => false, 'err' => 'Prescription not found.'];
    }
    if ($rx->pres_status !== 'pending') {
        return ['ok' => false, 'err' => 'Only pending prescriptions can be cancelled.'];
    }
    $stmt = $mysqli->prepare("UPDATE his_prescriptions SET pres_status='cancelled' WHERE pres_id=? AND pres_status='pending'");
    if (!$stmt) {
        return ['ok' => false, 'err' => 'Update failed.'];
    }
    $stmt->bind_param('i', $pres_id);
    $ok = $stmt->execute() && $stmt->affected_rows > 0;
    $stmt->close();
    return $ok ? ['ok' => true] : ['ok' => false, 'err' => 'Could not cancel prescription.'];
}
