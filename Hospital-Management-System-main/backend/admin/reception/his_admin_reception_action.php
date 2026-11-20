<?php
$nex_admin_base = '../';
require __DIR__ . '/../_init.php';
require_once __DIR__ . '/../../assets/inc/nex_reception.php';
check_login();
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: his_admin_reception_dashboard.php');
    exit;
}

$action = trim((string) ($_POST['reception_action'] ?? ''));
$redirect = 'his_admin_reception_dashboard.php';
$q = trim((string) ($_POST['q'] ?? ''));
if ($q !== '') {
    $redirect .= '?q=' . rawurlencode($q);
}

function nex_reception_flash_redirect($redirect, $ok, $msg) {
    $sep = strpos($redirect, '?') === false ? '?' : '&';
    $key = $ok ? 'ok' : 'err';
    header('Location: ' . $redirect . $sep . $key . '=' . rawurlencode($msg));
    exit;
}

switch ($action) {
    case 'check_in':
        nex_reception_authorize('visit.checkin', ['entity_type' => 'appointment', 'entity_id' => (int) ($_POST['appt_id'] ?? 0)]);
        $result = nex_reception_check_in_appointment($mysqli, (int) ($_POST['appt_id'] ?? 0));
        nex_reception_flash_redirect($redirect, $result['ok'], $result['ok'] ? 'Patient checked in.' : ($result['err'] ?? 'Check-in failed.'));
        break;

    case 'no_show':
        nex_reception_authorize('visit.manage', ['entity_type' => 'appointment', 'entity_id' => (int) ($_POST['appt_id'] ?? 0)]);
        $result = nex_reception_mark_no_show($mysqli, (int) ($_POST['appt_id'] ?? 0));
        nex_reception_flash_redirect($redirect, $result['ok'], $result['ok'] ? 'Marked as no-show.' : ($result['err'] ?? 'Update failed.'));
        break;

    case 'walk_in':
    case 'emergency':
        nex_reception_authorize('visit.checkin', ['entity_type' => 'patient', 'entity_id' => (int) ($_POST['pat_id'] ?? 0)]);
        $pat_id = (int) ($_POST['pat_id'] ?? 0);
        $notes = trim((string) ($_POST['notes'] ?? ''));
        $type = $action === 'emergency' ? 'emergency' : 'walk_in';
        $result = nex_reception_create_visit($mysqli, [
            'pat_id' => $pat_id,
            'visit_type' => $type,
            'notes' => $notes,
        ]);
        $label = $type === 'emergency' ? 'Emergency arrival recorded.' : 'Walk-in added to queue.';
        nex_reception_flash_redirect($redirect, $result['ok'], $result['ok'] ? $label : ($result['err'] ?? 'Could not create visit.'));
        break;

    case 'start_registration':
        nex_reception_authorize('visit.manage', ['entity_type' => 'visit', 'entity_id' => (int) ($_POST['visit_id'] ?? 0)]);
        $result = nex_reception_update_visit_status($mysqli, (int) ($_POST['visit_id'] ?? 0), 'registering');
        nex_reception_flash_redirect($redirect, $result['ok'], $result['ok'] ? 'Registration started.' : ($result['err'] ?? 'Update failed.'));
        break;

    case 'complete_registration':
        nex_reception_authorize('visit.manage', ['entity_type' => 'visit', 'entity_id' => (int) ($_POST['visit_id'] ?? 0)]);
        $result = nex_reception_update_visit_status($mysqli, (int) ($_POST['visit_id'] ?? 0), 'ready');
        nex_reception_flash_redirect($redirect, $result['ok'], $result['ok'] ? 'Registration completed — patient ready.' : ($result['err'] ?? 'Update failed.'));
        break;

    case 'mark_ready':
        nex_reception_authorize('visit.manage', ['entity_type' => 'visit', 'entity_id' => (int) ($_POST['visit_id'] ?? 0)]);
        $result = nex_reception_update_visit_status($mysqli, (int) ($_POST['visit_id'] ?? 0), 'ready');
        nex_reception_flash_redirect($redirect, $result['ok'], $result['ok'] ? 'Patient called / ready.' : ($result['err'] ?? 'Update failed.'));
        break;

    case 'send_in':
        nex_reception_authorize('visit.manage', ['entity_type' => 'visit', 'entity_id' => (int) ($_POST['visit_id'] ?? 0)]);
        $result = nex_reception_update_visit_status($mysqli, (int) ($_POST['visit_id'] ?? 0), 'in_service');
        nex_reception_flash_redirect($redirect, $result['ok'], $result['ok'] ? 'Patient sent in to consultation.' : ($result['err'] ?? 'Update failed.'));
        break;

    case 'complete_visit':
        nex_reception_authorize('visit.manage', ['entity_type' => 'visit', 'entity_id' => (int) ($_POST['visit_id'] ?? 0)]);
        $result = nex_reception_update_visit_status($mysqli, (int) ($_POST['visit_id'] ?? 0), 'completed');
        nex_reception_flash_redirect($redirect, $result['ok'], $result['ok'] ? 'Visit completed.' : ($result['err'] ?? 'Update failed.'));
        break;

    case 'cancel_visit':
        nex_reception_authorize('visit.manage', ['entity_type' => 'visit', 'entity_id' => (int) ($_POST['visit_id'] ?? 0)]);
        $result = nex_reception_update_visit_status($mysqli, (int) ($_POST['visit_id'] ?? 0), 'cancelled');
        nex_reception_flash_redirect($redirect, $result['ok'], $result['ok'] ? 'Visit cancelled.' : ($result['err'] ?? 'Update failed.'));
        break;

    default:
        nex_reception_flash_redirect($redirect, false, 'Unknown action.');
}
