<?php
/**
 * NexClinic Stage B — sector operating model helpers.
 * Legacy admin/doctor sessions remain valid; sector session is additive.
 * Set NEX_SECTORS_ENFORCE=1 in backend/.env to deny cross-sector URLs.
 */

function nex_sectors_available() {
    static $ok = null;
    if ($ok !== null) {
        return $ok;
    }
    global $mysqli;
    if (!$mysqli) {
        $ok = false;
        return $ok;
    }
    $res = @$mysqli->query("SHOW TABLES LIKE 'nex_users'");
    $ok = ($res && $res->num_rows > 0);
    return $ok;
}

function nex_map_dept_to_sector($dept) {
    $d = strtolower((string) $dept);
    if (strpos($d, 'lab') !== false) return 'laboratory';
    if (strpos($d, 'pharm') !== false) return 'pharmacy';
    if (strpos($d, 'surg') !== false || strpos($d, 'theatre') !== false) return 'theatre';
    if (strpos($d, 'account') !== false || strpos($d, 'financ') !== false) return 'accounting';
    if (strpos($d, 'hr') !== false || strpos($d, 'human') !== false) return 'hr';
    if (strpos($d, 'payroll') !== false) return 'payroll';
    if (strpos($d, 'invent') !== false) return 'inventory';
    if (strpos($d, 'recept') !== false) return 'reception';
    return 'clinical';
}

/**
 * After legacy login, attach nex_user + assignments into session.
 */
function nex_bootstrap_session_from_legacy($mysqli, $legacy_source, $legacy_id) {
    if (!nex_sectors_available()) {
        return false;
    }
    $stmt = $mysqli->prepare('SELECT * FROM nex_users WHERE legacy_source=? AND legacy_id=? AND active=1 LIMIT 1');
    if (!$stmt) {
        return false;
    }
    $stmt->bind_param('si', $legacy_source, $legacy_id);
    $stmt->execute();
    $user = $stmt->get_result()->fetch_assoc();
    $stmt->close();
    if (!$user) {
        return false;
    }
    $_SESSION['nex_user_id'] = (int) $user['user_id'];
    $_SESSION['nex_is_system_admin'] = (int) $user['is_system_admin'] === 1;

    $aq = $mysqli->prepare('SELECT a.facility_id, a.sector_code, a.role, f.facility_name, s.sector_name
        FROM nex_user_assignments a
        INNER JOIN nex_facilities f ON f.facility_id = a.facility_id
        INNER JOIN nex_sectors s ON s.sector_code = a.sector_code
        WHERE a.user_id=? AND a.active=1
        ORDER BY s.sector_sort');
    $aq->bind_param('i', $user['user_id']);
    $aq->execute();
    $res = $aq->get_result();
    $assignments = [];
    while ($row = $res->fetch_assoc()) {
        $assignments[] = $row;
    }
    $aq->close();
    $_SESSION['nex_assignments'] = $assignments;

    if (count($assignments) === 1) {
        $_SESSION['nex_sector'] = $assignments[0]['sector_code'];
        $_SESSION['nex_facility_id'] = (int) $assignments[0]['facility_id'];
    }
    return true;
}

function nex_user_sectors() {
    $list = [];
    foreach ($_SESSION['nex_assignments'] ?? [] as $a) {
        $list[$a['sector_code']] = $a['sector_name'];
    }
    return $list;
}

function nex_has_sector($sector_code) {
    if (!empty($_SESSION['nex_is_system_admin'])) {
        // Superadmin can reach any sector, but never silently: every override
        // is written to the audit log, not just granted and forgotten. This
        // used to be a blanket `return true` with no trace of who used it.
        global $mysqli;
        nex_audit_access($mysqli, 'sector.override', ['entity_type' => 'sector', 'entity_id' => $sector_code], 'granted:superadmin');
        return true;
    }
    return isset(nex_user_sectors()[$sector_code]);
}

function nex_active_sector() {
    return $_SESSION['nex_sector'] ?? null;
}

function nex_set_active_sector($sector_code, $facility_id = null) {
    if (!nex_has_sector($sector_code)) {
        return false;
    }
    $_SESSION['nex_sector'] = $sector_code;
    if ($facility_id !== null) {
        $_SESSION['nex_facility_id'] = (int) $facility_id;
    } else {
        foreach ($_SESSION['nex_assignments'] ?? [] as $a) {
            if ($a['sector_code'] === $sector_code) {
                $_SESSION['nex_facility_id'] = (int) $a['facility_id'];
                break;
            }
        }
    }
    return true;
}

function nex_sector_modules() {
    return [
        'system' => ['label' => 'System', 'href' => 'his_admin_sector_dashboard.php?sector=system', 'portal' => 'admin'],
        'clinical' => ['label' => 'Clinical', 'href' => 'his_admin_sector_dashboard.php?sector=clinical', 'portal' => 'both'],
        'reception' => ['label' => 'Reception', 'href' => 'reception/his_admin_reception_dashboard.php', 'portal' => 'admin'],
        'pharmacy' => ['label' => 'Pharmacy', 'href' => 'his_admin_sector_dashboard.php?sector=pharmacy', 'portal' => 'both'],
        'laboratory' => ['label' => 'Laboratory', 'href' => 'his_admin_sector_dashboard.php?sector=laboratory', 'portal' => 'both'],
        'theatre' => ['label' => 'Theatre', 'href' => 'his_admin_sector_dashboard.php?sector=theatre', 'portal' => 'admin'],
        'hr' => ['label' => 'HR', 'href' => 'his_admin_sector_dashboard.php?sector=hr', 'portal' => 'admin'],
        'payroll' => ['label' => 'Payroll', 'href' => 'his_admin_sector_dashboard.php?sector=payroll', 'portal' => 'both'],
        'accounting' => ['label' => 'Accounting', 'href' => 'his_admin_sector_dashboard.php?sector=accounting', 'portal' => 'admin'],
        'inventory' => ['label' => 'Inventory', 'href' => 'his_admin_sector_dashboard.php?sector=inventory', 'portal' => 'both'],
    ];
}

/** Script basename → required sector (enforcement map). */
function nex_script_sector_map() {
    return [
        'his_admin_register_patient.php' => 'reception',
        'his_admin_view_patients.php' => 'clinical',
        'his_admin_manage_patient.php' => 'clinical',
        'his_admin_appointments.php' => 'reception',
        'his_admin_add_appointment.php' => 'reception',
        'his_admin_manage_appointment.php' => 'reception',
        'his_admin_reception_dashboard.php' => 'reception',
        'his_admin_reception_action.php' => 'reception',
        'his_admin_register.php' => 'system',
        'his_admin_account.php' => 'system',
        'his_admin_pwd_reset.php' => 'system',

        'his_admin_add_pharmaceuticals.php' => 'pharmacy',
        'his_admin_view_pharmaceuticals.php' => 'pharmacy',
        'his_admin_manage_pharmaceuticals.php' => 'pharmacy',
        'his_admin_pharmacy_queue.php' => 'pharmacy',
        'his_admin_pharmacy_dispense.php' => 'pharmacy',
        'his_admin_add_presc.php' => 'clinical',
        'his_admin_view_presc.php' => 'clinical',
        'his_admin_manage_presc.php' => 'clinical',
        'his_admin_add_single_pres.php' => 'clinical',
        'his_admin_view_single_pres.php' => 'clinical',
        'his_admin_upate_single_pres.php' => 'clinical',
        'his_admin_patient_lab_test.php' => 'laboratory',
        'his_admin_patient_lab_result.php' => 'laboratory',
        'his_admin_lab_report.php' => 'laboratory',
        'his_admin_add_employee.php' => 'hr',
        'his_admin_view_employee.php' => 'hr',
        'his_admin_manage_employee.php' => 'hr',
        'his_admin_add_payroll.php' => 'payroll',
        'his_admin_manage_payrolls.php' => 'payroll',
        'his_admin_add_acc.payable.php' => 'accounting',
        'his_admin_manage_acc_payable.php' => 'accounting',
        'his_admin_add_acc_receivable.php' => 'accounting',
        'his_admin_manage_acc_receivable.php' => 'accounting',
        'his_admin_pharm_inventory.php' => 'inventory',
        'his_admin_equipments_inventory.php' => 'inventory',
        'his_admin_add_theatre_patient.php' => 'theatre',
        'his_admin_surgery_records.php' => 'theatre',
    ];
}

function nex_enforce_sector_access() {
    $enforce = getenv('NEX_SECTORS_ENFORCE');
    if ($enforce !== '1' && strtolower((string) $enforce) !== 'true') {
        return;
    }
    if (!nex_sectors_available() || empty($_SESSION['nex_user_id'])) {
        return;
    }
    if (!empty($_SESSION['nex_is_system_admin'])) {
        return;
    }
    $script = basename($_SERVER['SCRIPT_NAME'] ?? '');
    $map = nex_script_sector_map();
    if (!isset($map[$script])) {
        return;
    }
    $needed = $map[$script];
    $active = nex_active_sector();
    if ($active && $active !== $needed && !nex_has_sector($needed)) {
        http_response_code(403);
        die('Access denied for this sector module.');
    }
    if (!$active && !nex_has_sector($needed)) {
        http_response_code(403);
        die('Access denied for this sector module.');
    }
}

function nex_resolve_pat_id($mysqli, $pat_number) {
    $pat_number = (string) $pat_number;
    $stmt = $mysqli->prepare('SELECT pat_id FROM his_patients WHERE pat_number=? LIMIT 1');
    if (!$stmt) {
        return null;
    }
    $stmt->bind_param('s', $pat_number);
    $stmt->execute();
    $row = $stmt->get_result()->fetch_object();
    $stmt->close();
    return $row ? (int) $row->pat_id : null;
}

/** Canonical actor id for provenance columns. Null if session predates nex_users. */
function nex_actor_id() {
    return isset($_SESSION['nex_user_id']) ? (int) $_SESSION['nex_user_id'] : null;
}

/** Axis 1 — platform tier from session or legacy tables. */
function nex_platform_tier() {
    if (!empty($_SESSION['nex_platform_tier'])) {
        return $_SESSION['nex_platform_tier'];
    }
    if (!empty($_SESSION['nex_is_system_admin'])) {
        return 'superadmin';
    }
    if (!empty($_SESSION['ad_id'])) {
        return 'admin';
    }
    return 'standard';
}

function nex_is_superadmin() {
    return nex_platform_tier() === 'superadmin';
}

function nex_is_hospital_admin() {
    $t = nex_platform_tier();
    return $t === 'admin' || $t === 'superadmin';
}

/** Cached per-request (not per-call) check that the audit table exists. */
function nex_audit_available() {
    static $ok = null;
    if ($ok !== null) {
        return $ok;
    }
    global $mysqli;
    if (!$mysqli) {
        $ok = false;
        return $ok;
    }
    $r = @$mysqli->query("SHOW TABLES LIKE 'nex_audit_log'");
    $ok = ($r && $r->num_rows > 0);
    return $ok;
}

/** 'nex:12' / 'admin:1' / 'doc:6' — whichever session identity is active. */
function nex_current_actor_ref() {
    if (!empty($_SESSION['nex_user_id'])) {
        return 'nex:' . (int) $_SESSION['nex_user_id'];
    } elseif (!empty($_SESSION['ad_id'])) {
        return 'admin:' . (int) $_SESSION['ad_id'];
    } elseif (!empty($_SESSION['doc_id'])) {
        return 'doc:' . (int) $_SESSION['doc_id'];
    }
    return null;
}

/**
 * Write audit row if nex_audit_log exists. Never throws to caller.
 */
function nex_audit($mysqli, $action, $entity_type = null, $entity_id = null, $detail = null) {
    if (!$mysqli || !nex_audit_available()) {
        return false;
    }
    $actor = nex_current_actor_ref();
    $tier = nex_platform_tier();
    $ip = $_SERVER['REMOTE_ADDR'] ?? null;
    $stmt = $mysqli->prepare('INSERT INTO nex_audit_log (actor_user_ref, actor_tier, action, entity_type, entity_id, detail, ip_address) VALUES (?,?,?,?,?,?,?)');
    if (!$stmt) {
        return false;
    }
    $stmt->bind_param('sssssss', $actor, $tier, $action, $entity_type, $entity_id, $detail, $ip);
    $ok = $stmt->execute();
    $stmt->close();
    return $ok;
}

/**
 * Log an access decision (grant, denial, or break-glass) to nex_audit_log.
 * Used by both the break-glass override (nex_care.php) and the
 * authorization kernel (nex_authz.php) so every decision — not just writes —
 * lands in one place. Never throws.
 */
function nex_audit_access($mysqli, $action, array $ctx, $outcome, $break_glass = false) {
    if (!$mysqli || !nex_audit_available()) {
        return false;
    }
    $actor = nex_current_actor_ref();
    $tier = nex_platform_tier();
    $ip = $_SERVER['REMOTE_ADDR'] ?? null;
    $entity_type = $ctx['entity_type'] ?? null;
    $entity_id = isset($ctx['entity_id']) ? (string) $ctx['entity_id'] : null;
    $detail = !empty($ctx) ? json_encode($ctx) : null;
    $bg = $break_glass ? 1 : 0;
    $stmt = $mysqli->prepare('INSERT INTO nex_audit_log (actor_user_ref, actor_tier, action, outcome, entity_type, entity_id, detail, ip_address, break_glass) VALUES (?,?,?,?,?,?,?,?,?)');
    if (!$stmt) {
        return false;
    }
    $stmt->bind_param('ssssssssi', $actor, $tier, $action, $outcome, $entity_type, $entity_id, $detail, $ip, $bg);
    $ok = $stmt->execute();
    $stmt->close();
    return $ok;
}

