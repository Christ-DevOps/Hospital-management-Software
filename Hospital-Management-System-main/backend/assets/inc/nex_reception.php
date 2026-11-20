<?php
/**
 * NexClinic Reception — visit/queue helpers and KPI queries.
 */

require_once __DIR__ . '/nex_sectors.php';

/** Minutes before a waiting patient is flagged as long-wait (later: config). */
function nex_reception_long_wait_minutes() {
    return 30;
}

function nex_visit_types() {
    return [
        'scheduled' => 'Scheduled',
        'walk_in' => 'Walk-in',
        'emergency' => 'Emergency',
    ];
}

function nex_visit_statuses() {
    return [
        'waiting' => 'Waiting',
        'registering' => 'Registering',
        'ready' => 'Ready',
        'in_service' => 'In service',
        'completed' => 'Completed',
        'cancelled' => 'Cancelled',
    ];
}

function nex_visits_available($mysqli) {
    static $ok = null;
    if ($ok !== null) {
        return $ok;
    }
    if (!$mysqli) {
        $ok = false;
        return $ok;
    }
    $res = @$mysqli->query("SHOW TABLES LIKE 'his_visits'");
    $ok = ($res && $res->num_rows > 0);
    return $ok;
}

function nex_reception_scalar($mysqli, $sql) {
    $n = 0;
    if ($stmt = $mysqli->prepare($sql)) {
        $stmt->execute();
        $stmt->bind_result($n);
        $stmt->fetch();
        $stmt->close();
    }
    return $n;
}

function nex_reception_next_queue_number($mysqli, $day = null) {
    $day = $day ?: date('Y-m-d');
    $stmt = $mysqli->prepare(
        'SELECT COALESCE(MAX(queue_number), 0) + 1 FROM his_visits WHERE DATE(arrived_at) = ?'
    );
    if (!$stmt) {
        return 1;
    }
    $stmt->bind_param('s', $day);
    $stmt->execute();
    $stmt->bind_result($next);
    $stmt->fetch();
    $stmt->close();
    return max(1, (int) $next);
}

function nex_reception_priority_for_type($visit_type) {
    if ($visit_type === 'emergency') {
        return 100;
    }
    if ($visit_type === 'walk_in') {
        return 10;
    }
    return 0;
}

/**
 * @return array{ok:bool, visit_id?:int, err?:string}
 */
function nex_reception_create_visit($mysqli, array $opts) {
    if (!nex_visits_available($mysqli)) {
        return ['ok' => false, 'err' => 'Visit table not available. Run reception migration.'];
    }

    $pat_id = (int) ($opts['pat_id'] ?? 0);
    $appt_id = isset($opts['appt_id']) ? (int) $opts['appt_id'] : 0;
    if ($appt_id <= 0) {
        $appt_id = 0;
    }
    $visit_type = (string) ($opts['visit_type'] ?? 'scheduled');
    $types = nex_visit_types();
    if (!isset($types[$visit_type])) {
        return ['ok' => false, 'err' => 'Invalid visit type.'];
    }
    if ($pat_id <= 0) {
        return ['ok' => false, 'err' => 'Patient is required.'];
    }

    $chk = $mysqli->prepare(
        "SELECT visit_id FROM his_visits
         WHERE pat_id = ? AND DATE(arrived_at) = CURDATE()
           AND visit_status IN ('waiting','registering','ready','in_service')
         LIMIT 1"
    );
    if ($chk) {
        $chk->bind_param('i', $pat_id);
        $chk->execute();
        $existing = $chk->get_result()->fetch_object();
        $chk->close();
        if ($existing) {
            return ['ok' => false, 'err' => 'Patient already has an open visit today.', 'visit_id' => (int) $existing->visit_id];
        }
    }

    if ($appt_id > 0) {
        $alink = $mysqli->prepare(
            "SELECT visit_id FROM his_visits WHERE appt_id = ? AND visit_status <> 'cancelled' LIMIT 1"
        );
        if ($alink) {
            $alink->bind_param('i', $appt_id);
            $alink->execute();
            $linked = $alink->get_result()->fetch_object();
            $alink->close();
            if ($linked) {
                return ['ok' => false, 'err' => 'Appointment already checked in.', 'visit_id' => (int) $linked->visit_id];
            }
        }
    }

    $queue = nex_reception_next_queue_number($mysqli);
    $priority = isset($opts['priority']) ? (int) $opts['priority'] : nex_reception_priority_for_type($visit_type);
    $facility_id = isset($opts['facility_id']) ? (int) $opts['facility_id'] : (int) ($_SESSION['nex_facility_id'] ?? 0);
    $created_by = nex_actor_id();
    $notes = trim((string) ($opts['notes'] ?? ''));
    $arrived_at = date('Y-m-d H:i:s');
    $status = 'waiting';

    // Safe integer literals for nullable FK columns (values cast to int only).
    $fidSql = $facility_id > 0 ? (string) (int) $facility_id : 'NULL';
    $aidSql = $appt_id > 0 ? (string) (int) $appt_id : 'NULL';
    $cidSql = $created_by !== null ? (string) (int) $created_by : 'NULL';

    $sql = "INSERT INTO his_visits (
        facility_id, pat_id, appt_id, visit_type, visit_status, queue_number, priority,
        arrived_at, created_by, notes
    ) VALUES ({$fidSql}, ?, {$aidSql}, ?, ?, ?, ?, ?, {$cidSql}, ?)";
    $stmt = $mysqli->prepare($sql);
    if (!$stmt) {
        return ['ok' => false, 'err' => 'Could not create visit.'];
    }
    $stmt->bind_param('issiiss', $pat_id, $visit_type, $status, $queue, $priority, $arrived_at, $notes);
    if (!$stmt->execute()) {
        $stmt->close();
        return ['ok' => false, 'err' => 'Could not create visit.'];
    }
    $visit_id = (int) $mysqli->insert_id;
    $stmt->close();
    return ['ok' => true, 'visit_id' => $visit_id];
}

/**
 * @return array{ok:bool, visit_id?:int, err?:string}
 */
function nex_reception_check_in_appointment($mysqli, $appt_id) {
    $appt_id = (int) $appt_id;
    $stmt = $mysqli->prepare(
        "SELECT appt_id, pat_id, appt_status FROM his_appointments WHERE appt_id = ? LIMIT 1"
    );
    if (!$stmt) {
        return ['ok' => false, 'err' => 'Appointment not found.'];
    }
    $stmt->bind_param('i', $appt_id);
    $stmt->execute();
    $appt = $stmt->get_result()->fetch_object();
    $stmt->close();
    if (!$appt || !(int) $appt->pat_id) {
        return ['ok' => false, 'err' => 'Appointment not found.'];
    }
    if (!in_array($appt->appt_status, ['booked', 'rescheduled'], true)) {
        return ['ok' => false, 'err' => 'Only booked appointments can be checked in.'];
    }
    return nex_reception_create_visit($mysqli, [
        'pat_id' => (int) $appt->pat_id,
        'appt_id' => $appt_id,
        'visit_type' => 'scheduled',
    ]);
}

/**
 * @return array{ok:bool, err?:string}
 */
function nex_reception_mark_no_show($mysqli, $appt_id) {
    $appt_id = (int) $appt_id;
    $stmt = $mysqli->prepare(
        "UPDATE his_appointments SET appt_status = 'no_show' WHERE appt_id = ? AND appt_status IN ('booked','rescheduled')"
    );
    if (!$stmt) {
        return ['ok' => false, 'err' => 'Update failed.'];
    }
    $stmt->bind_param('i', $appt_id);
    $ok = $stmt->execute() && $stmt->affected_rows > 0;
    $stmt->close();
    return $ok ? ['ok' => true] : ['ok' => false, 'err' => 'Could not mark no-show.'];
}

/**
 * @return array{ok:bool, err?:string}
 */
function nex_reception_update_visit_status($mysqli, $visit_id, $new_status) {
    $visit_id = (int) $visit_id;
    $statuses = nex_visit_statuses();
    if (!isset($statuses[$new_status])) {
        return ['ok' => false, 'err' => 'Invalid status.'];
    }
    $now = date('Y-m-d H:i:s');
    $extra = '';
    if ($new_status === 'registering') {
        $extra = ', registration_started_at = COALESCE(registration_started_at, ?)';
    } elseif ($new_status === 'ready') {
        $extra = ', registration_completed_at = COALESCE(registration_completed_at, ?), called_at = COALESCE(called_at, ?)';
    } elseif ($new_status === 'completed') {
        $extra = ', completed_at = COALESCE(completed_at, ?)';
    } elseif ($new_status === 'in_service') {
        $extra = ', called_at = COALESCE(called_at, ?)';
    }

    if ($new_status === 'ready') {
        $sql = "UPDATE his_visits SET visit_status = ?{$extra} WHERE visit_id = ? AND visit_status IN ('waiting','registering','ready')";
        $stmt = $mysqli->prepare($sql);
        if (!$stmt) {
            return ['ok' => false, 'err' => 'Update failed.'];
        }
        $stmt->bind_param('sssi', $new_status, $now, $now, $visit_id);
    } elseif ($extra !== '') {
        $sql = "UPDATE his_visits SET visit_status = ?{$extra} WHERE visit_id = ? AND visit_status NOT IN ('completed','cancelled')";
        $stmt = $mysqli->prepare($sql);
        if (!$stmt) {
            return ['ok' => false, 'err' => 'Update failed.'];
        }
        $stmt->bind_param('ssi', $new_status, $now, $visit_id);
    } else {
        $sql = "UPDATE his_visits SET visit_status = ? WHERE visit_id = ? AND visit_status NOT IN ('completed','cancelled')";
        $stmt = $mysqli->prepare($sql);
        if (!$stmt) {
            return ['ok' => false, 'err' => 'Update failed.'];
        }
        $stmt->bind_param('si', $new_status, $visit_id);
    }
    $ok = $stmt->execute() && $stmt->affected_rows > 0;
    $stmt->close();
    return $ok ? ['ok' => true] : ['ok' => false, 'err' => 'Visit could not be updated.'];
}

/**
 * After patient registration succeeds, close registration timing on today's open visit.
 */
function nex_reception_complete_registration_for_patient($mysqli, $pat_id) {
    if (!nex_visits_available($mysqli)) {
        return false;
    }
    $pat_id = (int) $pat_id;
    $find = $mysqli->prepare(
        "SELECT visit_id FROM his_visits
         WHERE pat_id = ? AND DATE(arrived_at) = CURDATE()
           AND visit_status IN ('waiting','registering')
         ORDER BY visit_id DESC LIMIT 1"
    );
    if (!$find) {
        return false;
    }
    $find->bind_param('i', $pat_id);
    $find->execute();
    $row = $find->get_result()->fetch_object();
    $find->close();
    if (!$row) {
        return false;
    }
    $visit_id = (int) $row->visit_id;
    // Ensure registration_started_at is set before marking ready
    $boot = $mysqli->prepare(
        'UPDATE his_visits SET registration_started_at = COALESCE(registration_started_at, arrived_at) WHERE visit_id = ?'
    );
    if ($boot) {
        $boot->bind_param('i', $visit_id);
        $boot->execute();
        $boot->close();
    }
    return nex_reception_update_visit_status($mysqli, $visit_id, 'ready')['ok'];
}

function nex_reception_kpis($mysqli) {
    $kpis = [
        'appointments_today' => 0,
        'patients_waiting' => 0,
        'avg_registration_minutes' => null,
        'missed_appointments' => 0,
        'walk_ins_today' => 0,
        'emergencies_today' => 0,
        'visits_available' => nex_visits_available($mysqli),
    ];

    $kpis['appointments_today'] = (int) nex_reception_scalar(
        $mysqli,
        "SELECT COUNT(*) FROM his_appointments
         WHERE DATE(appt_datetime) = CURDATE() AND appt_status <> 'cancelled'"
    );

    if (!$kpis['visits_available']) {
        $kpis['missed_appointments'] = (int) nex_reception_scalar(
            $mysqli,
            "SELECT COUNT(*) FROM his_appointments
             WHERE DATE(appt_datetime) = CURDATE()
               AND (appt_status = 'no_show'
                    OR (appt_status = 'booked' AND appt_datetime < NOW()))"
        );
        return $kpis;
    }

    $kpis['missed_appointments'] = (int) nex_reception_scalar(
        $mysqli,
        "SELECT COUNT(*) FROM his_appointments a
         WHERE DATE(a.appt_datetime) = CURDATE()
           AND (
             a.appt_status = 'no_show'
             OR (
               a.appt_status = 'booked'
               AND a.appt_datetime < NOW()
               AND NOT EXISTS (
                 SELECT 1 FROM his_visits v
                 WHERE v.appt_id = a.appt_id AND v.visit_status <> 'cancelled'
               )
             )
           )"
    );

    $kpis['patients_waiting'] = (int) nex_reception_scalar(
        $mysqli,
        "SELECT COUNT(*) FROM his_visits
         WHERE DATE(arrived_at) = CURDATE()
           AND visit_status IN ('waiting','registering')"
    );
    $kpis['walk_ins_today'] = (int) nex_reception_scalar(
        $mysqli,
        "SELECT COUNT(*) FROM his_visits
         WHERE DATE(arrived_at) = CURDATE() AND visit_type = 'walk_in'"
    );
    $kpis['emergencies_today'] = (int) nex_reception_scalar(
        $mysqli,
        "SELECT COUNT(*) FROM his_visits
         WHERE DATE(arrived_at) = CURDATE() AND visit_type = 'emergency'"
    );

    $avg = nex_reception_scalar(
        $mysqli,
        "SELECT AVG(TIMESTAMPDIFF(MINUTE, registration_started_at, registration_completed_at))
         FROM his_visits
         WHERE DATE(arrived_at) = CURDATE()
           AND registration_started_at IS NOT NULL
           AND registration_completed_at IS NOT NULL"
    );
    $kpis['avg_registration_minutes'] = $avg !== null && $avg !== '' ? round((float) $avg, 1) : null;

    return $kpis;
}

/**
 * @return object[]
 */
function nex_reception_queue($mysqli, $q = '') {
    if (!nex_visits_available($mysqli)) {
        return [];
    }
    $sql = "SELECT v.*,
            CONCAT(COALESCE(p.pat_fname,''),' ',COALESCE(p.pat_lname,'')) AS patient_name,
            p.pat_number,
            TIMESTAMPDIFF(MINUTE, v.arrived_at, NOW()) AS wait_minutes
        FROM his_visits v
        INNER JOIN his_patients p ON p.pat_id = v.pat_id
        WHERE DATE(v.arrived_at) = CURDATE()
          AND v.visit_status IN ('waiting','registering','ready','in_service')";
    $params = [];
    $types = '';
    $q = trim($q);
    if ($q !== '') {
        $like = '%' . $q . '%';
        $sql .= ' AND (p.pat_fname LIKE ? OR p.pat_lname LIKE ? OR p.pat_number LIKE ? OR CONCAT(p.pat_fname,\' \',p.pat_lname) LIKE ?)';
        $params = [$like, $like, $like, $like];
        $types = 'ssss';
    }
    $sql .= ' ORDER BY v.priority DESC, v.queue_number ASC, v.arrived_at ASC';
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
    while ($row = $res->fetch_object()) {
        $rows[] = $row;
    }
    $stmt->close();
    return $rows;
}

/**
 * Today's schedule (appointments) with check-in state.
 * @return object[]
 */
function nex_reception_todays_schedule($mysqli, $q = '') {
    $sql = "SELECT a.*,
            CONCAT(COALESCE(p.pat_fname,''),' ',COALESCE(p.pat_lname,'')) AS patient_name,
            p.pat_number,
            CONCAT(COALESCE(d.doc_fname,''),' ',COALESCE(d.doc_lname,'')) AS doctor_name,
            (SELECT v.visit_id FROM his_visits v
             WHERE v.appt_id = a.appt_id AND v.visit_status <> 'cancelled'
             LIMIT 1) AS visit_id
        FROM his_appointments a
        LEFT JOIN his_patients p ON p.pat_id = a.pat_id
        LEFT JOIN his_docs d ON d.doc_id = a.doc_id
        WHERE DATE(a.appt_datetime) = CURDATE()
          AND a.appt_status <> 'cancelled'";
    $params = [];
    $types = '';
    $q = trim($q);
    if ($q !== '') {
        $like = '%' . $q . '%';
        $sql .= ' AND (p.pat_fname LIKE ? OR p.pat_lname LIKE ? OR p.pat_number LIKE ? OR CONCAT(p.pat_fname,\' \',p.pat_lname) LIKE ?)';
        $params = [$like, $like, $like, $like];
        $types = 'ssss';
    }
    $sql .= ' ORDER BY a.appt_datetime ASC';
    $stmt = $mysqli->prepare($sql);
    if (!$stmt) {
        // Fallback without visit subquery if table missing
        $sql = "SELECT a.*,
                CONCAT(COALESCE(p.pat_fname,''),' ',COALESCE(p.pat_lname,'')) AS patient_name,
                p.pat_number,
                CONCAT(COALESCE(d.doc_fname,''),' ',COALESCE(d.doc_lname,'')) AS doctor_name,
                NULL AS visit_id
            FROM his_appointments a
            LEFT JOIN his_patients p ON p.pat_id = a.pat_id
            LEFT JOIN his_docs d ON d.doc_id = a.doc_id
            WHERE DATE(a.appt_datetime) = CURDATE()
              AND a.appt_status <> 'cancelled'
            ORDER BY a.appt_datetime ASC";
        $stmt = $mysqli->prepare($sql);
        if (!$stmt) {
            return [];
        }
        $stmt->execute();
        $res = $stmt->get_result();
        $rows = [];
        while ($row = $res->fetch_object()) {
            $rows[] = $row;
        }
        $stmt->close();
        return $rows;
    }
    if ($types !== '') {
        $stmt->bind_param($types, ...$params);
    }
    $stmt->execute();
    $res = $stmt->get_result();
    $rows = [];
    while ($row = $res->fetch_object()) {
        $rows[] = $row;
    }
    $stmt->close();
    return $rows;
}

/**
 * Patients selectable for a walk-in / emergency arrival.
 * Filtered by the same ?q= the queue and schedule use, so a clerk can reach
 * any patient by name or number — the previous unfiltered LIMIT 500 made
 * patient 501 onwards unreachable from this screen.
 * @return object[]
 */
function nex_reception_patient_options($mysqli, $q = '', $limit = 200) {
    $limit = max(1, (int) $limit);
    $q = trim((string) $q);
    $sql = 'SELECT pat_id, pat_fname, pat_lname, pat_number FROM his_patients';
    if ($q !== '') {
        $sql .= " WHERE pat_fname LIKE ? OR pat_lname LIKE ? OR pat_number LIKE ?
                  OR CONCAT(COALESCE(pat_fname,''),' ',COALESCE(pat_lname,'')) LIKE ?";
    }
    $sql .= " ORDER BY pat_date_joined DESC LIMIT {$limit}";
    $stmt = $mysqli->prepare($sql);
    if (!$stmt) {
        return [];
    }
    if ($q !== '') {
        $like = '%' . $q . '%';
        $stmt->bind_param('ssss', $like, $like, $like, $like);
    }
    $stmt->execute();
    $res = $stmt->get_result();
    $rows = [];
    while ($row = $res->fetch_object()) {
        $rows[] = $row;
    }
    $stmt->close();
    return $rows;
}

function nex_reception_alerts($mysqli) {
    $alerts = ['overdue' => [], 'long_wait' => []];
    $schedule = nex_reception_todays_schedule($mysqli);
    foreach ($schedule as $a) {
        if ($a->appt_status === 'booked' && strtotime($a->appt_datetime) < time() && empty($a->visit_id)) {
            $alerts['overdue'][] = trim($a->patient_name) . ' · ' . date('H:i', strtotime($a->appt_datetime));
        }
    }
    $limit = nex_reception_long_wait_minutes();
    foreach (nex_reception_queue($mysqli) as $v) {
        if ((int) $v->wait_minutes >= $limit && in_array($v->visit_status, ['waiting', 'registering'], true)) {
            $alerts['long_wait'][] = trim($v->patient_name) . ' · Q' . (int) $v->queue_number . ' · ' . (int) $v->wait_minutes . ' min';
        }
    }
    return $alerts;
}

/** Authorize when nex session exists; otherwise rely on portal login. */
function nex_reception_authorize($action, array $ctx = []) {
    if (empty($_SESSION['nex_user_id'])) {
        return true;
    }
    require_once __DIR__ . '/nex_authz.php';
    return nex_authorize($action, $ctx);
}
