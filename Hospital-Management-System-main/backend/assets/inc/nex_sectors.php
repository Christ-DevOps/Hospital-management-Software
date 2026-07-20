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
        'reception' => ['label' => 'Reception', 'href' => 'his_admin_sector_dashboard.php?sector=reception', 'portal' => 'admin'],
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
        'his_admin_register_patient.php' => 'clinical',
        'his_admin_view_patients.php' => 'clinical',
        'his_admin_manage_patient.php' => 'clinical',
        'his_admin_appointments.php' => 'reception',
        'his_admin_add_appointment.php' => 'reception',
        'his_admin_manage_appointment.php' => 'reception',
        'his_admin_add_pharmaceuticals.php' => 'pharmacy',
        'his_admin_view_pharmaceuticals.php' => 'pharmacy',
        'his_admin_manage_pharmaceuticals.php' => 'pharmacy',
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
