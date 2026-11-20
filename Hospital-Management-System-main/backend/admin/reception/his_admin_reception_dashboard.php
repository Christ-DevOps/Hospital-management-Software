<?php
$nex_admin_base = '../';
require __DIR__ . '/../_init.php';
require_once __DIR__ . '/../../assets/inc/nex_reception.php';
require_once __DIR__ . '/../../assets/inc/nex_dashboard_ui.php';
check_login();
nex_reception_authorize('reception.dashboard');
if (function_exists('nex_set_active_sector')) {
    nex_set_active_sector('reception');
}

$q = trim((string) ($_GET['q'] ?? ''));
if (isset($_GET['ok'])) {
    $success = trim((string) $_GET['ok']);
}
if (isset($_GET['err'])) {
    $err = trim((string) $_GET['err']);
}

$kpis = nex_reception_kpis($mysqli);
$queue = nex_reception_queue($mysqli, $q);
$schedule = nex_reception_todays_schedule($mysqli, $q);
$alerts = nex_reception_alerts($mysqli);
$visit_types = nex_visit_types();
$visit_statuses = nex_visit_statuses();

$patients = nex_reception_patient_options($mysqli, $q);

$avg_label = $kpis['avg_registration_minutes'] !== null
    ? $kpis['avg_registration_minutes'] . ' min'
    : '—';

function nex_reception_action_btn($action, $extra = [], $label = 'Go', $btn = 'btn-primary btn-sm') {
    $html = '<form method="post" action="his_admin_reception_action.php" class="d-inline">';
    $html .= '<input type="hidden" name="csrf_token" value="' . escape_output(generate_csrf_token()) . '">';
    $html .= '<input type="hidden" name="reception_action" value="' . escape_output($action) . '">';
    if (!empty($_GET['q'])) {
        $html .= '<input type="hidden" name="q" value="' . escape_output($_GET['q']) . '">';
    }
    foreach ($extra as $k => $v) {
        $html .= '<input type="hidden" name="' . escape_output($k) . '" value="' . escape_output((string) $v) . '">';
    }
    $html .= '<button type="submit" class="btn ' . escape_output($btn) . '">' . escape_output($label) . '</button>';
    $html .= '</form>';
    return $html;
}
?>
<!DOCTYPE html>
<html lang="en">
<?php include __DIR__ . '/../assets/inc/head.php'; ?>
<body>
<div id="wrapper">
    <?php include __DIR__ . '/../assets/inc/nav.php'; ?>
    <?php include __DIR__ . '/../assets/inc/sidebar.php'; ?>
    <div class="content-page">
        <div class="content">
            <div class="container-fluid">
                <div class="row">
                    <div class="col-12">
                        <div class="page-title-box d-flex justify-content-between align-items-center flex-wrap">
                            <div>
                                <h4 class="page-title mb-0">Reception</h4>
                                <p class="text-muted mb-0 mt-1" style="font-size:13px;">Today’s front-desk control center</p>
                            </div>
                            <div class="d-flex gap-2 flex-wrap">
                                <a href="his_admin_register_patient.php" class="btn btn-primary btn-sm">
                                    <i class="fe-user-plus"></i> Quick Registration
                                </a>
                                <a href="his_admin_add_appointment.php" class="btn btn-outline-primary btn-sm">
                                    <i class="fe-plus"></i> Book appointment
                                </a>
                                <a href="his_admin_appointments.php" class="btn btn-outline-secondary btn-sm">Full schedule</a>
                            </div>
                        </div>
                    </div>
                </div>

                <?php if (!$kpis['visits_available']) { ?>
                    <div class="alert alert-warning">
                        Visit queue table is not installed. Run
                        <code>Database/migrations/2026_08_07_reception_visits.sql</code>
                        to enable waiting queue, walk-ins, emergencies, and registration timing.
                    </div>
                <?php } ?>

                <div class="row">
                    <?php
                    nex_kpi_card("Today's Appointments", $kpis['appointments_today'], 'hms-stat-blue', 'mdi-calendar-today', 'Excluding cancelled');
                    nex_kpi_card('Patients Waiting', $kpis['patients_waiting'], 'hms-stat-amber', 'mdi-account-clock', 'In queue now');
                    nex_kpi_card('Avg Registration Time', $avg_label, 'hms-stat-purple', 'mdi-timer-outline', 'Completed today');
                    nex_kpi_card('Missed Appointments', $kpis['missed_appointments'], 'hms-stat-amber', 'mdi-calendar-remove', 'No-show or overdue');
                    nex_kpi_card('Walk-in Patients', $kpis['walk_ins_today'], 'hms-stat-success', 'mdi-walk', 'Arrived today');
                    nex_kpi_card('Emergency Arrivals', $kpis['emergencies_today'], 'hms-stat-purple', 'mdi-ambulance', 'Priority queue');
                    ?>
                </div>

                <div class="row">
                    <div class="col-lg-4">
                        <?php
                        nex_alert_panel('Overdue — not checked in', $alerts['overdue'], 'danger');
                        nex_alert_panel('Long wait (≥ ' . nex_reception_long_wait_minutes() . ' min)', $alerts['long_wait'], 'warning');
                        if (!$alerts['overdue'] && !$alerts['long_wait']) {
                            echo '<div class="card-box mb-3"><p class="text-muted mb-0">No critical reception alerts right now.</p></div>';
                        }
                        ?>

                        <div class="card-box mb-3">
                            <h4 class="header-title mb-3">Quick arrival</h4>
                            <form method="post" action="his_admin_reception_action.php">
                                <input type="hidden" name="csrf_token" value="<?php echo escape_output(generate_csrf_token()); ?>">
                                <?php if ($q !== '') { ?>
                                    <input type="hidden" name="q" value="<?php echo escape_output($q); ?>">
                                <?php } ?>
                                <div class="form-group">
                                    <label>Patient</label>
                                    <select name="pat_id" class="form-control" required>
                                        <option value="">Select patient…</option>
                                        <?php foreach ($patients as $p) { ?>
                                            <option value="<?php echo (int) $p->pat_id; ?>">
                                                <?php echo escape_output($p->pat_fname . ' ' . $p->pat_lname . ' (' . $p->pat_number . ')'); ?>
                                            </option>
                                        <?php } ?>
                                    </select>
                                    <small class="text-muted">
                                        <?php if ($q !== '') { ?>
                                            Matching “<?php echo escape_output($q); ?>” — <a href="his_admin_reception_dashboard.php">clear</a>
                                        <?php } else { ?>
                                            Most recent patients. Use the search above to find any other patient.
                                        <?php } ?>
                                    </small>
                                </div>
                                <div class="form-group">
                                    <label>Notes <span class="text-muted">(optional)</span></label>
                                    <input type="text" name="notes" class="form-control" placeholder="Chief complaint / arrival note">
                                </div>
                                <div class="d-flex flex-wrap" style="gap:8px;">
                                    <button type="submit" name="reception_action" value="walk_in" class="btn btn-success btn-sm">
                                        Walk-in
                                    </button>
                                    <button type="submit" name="reception_action" value="emergency" class="btn btn-danger btn-sm">
                                        Emergency
                                    </button>
                                </div>
                            </form>
                            <p class="text-muted mt-2 mb-0" style="font-size:12px;">
                                New patient?
                                <a href="his_admin_register_patient.php">Quick Registration</a>
                                then add them as walk-in or emergency.
                            </p>
                        </div>
                    </div>

                    <div class="col-lg-8">
                        <div class="card-box mb-3">
                            <div class="d-flex justify-content-between align-items-center flex-wrap mb-3">
                                <h4 class="header-title mb-0">Current queue</h4>
                                <form method="get" class="form-inline">
                                    <input type="search" name="q" class="form-control form-control-sm mr-2" placeholder="Search patient…" value="<?php echo escape_output($q); ?>">
                                    <button type="submit" class="btn btn-outline-primary btn-sm">Filter</button>
                                    <?php if ($q !== '') { ?>
                                        <a href="his_admin_reception_dashboard.php" class="btn btn-light btn-sm ml-1">Clear</a>
                                    <?php } ?>
                                </form>
                            </div>
                            <?php if (!$queue) { ?>
                                <p class="text-muted mb-0">No patients waiting in queue.</p>
                            <?php } else { ?>
                                <div class="table-responsive">
                                    <table class="table table-sm table-centered mb-0">
                                        <thead>
                                        <tr>
                                            <th>Q#</th>
                                            <th>Patient</th>
                                            <th>Type</th>
                                            <th>Status</th>
                                            <th>Wait</th>
                                            <th>Actions</th>
                                        </tr>
                                        </thead>
                                        <tbody>
                                        <?php foreach ($queue as $v) {
                                            $typeLabel = $visit_types[$v->visit_type] ?? $v->visit_type;
                                            $statusLabel = $visit_statuses[$v->visit_status] ?? $v->visit_status;
                                            $badge = $v->visit_type === 'emergency' ? 'badge-danger' : ($v->visit_type === 'walk_in' ? 'badge-success' : 'badge-primary');
                                            ?>
                                            <tr>
                                                <td><strong><?php echo (int) $v->queue_number; ?></strong></td>
                                                <td>
                                                    <?php echo escape_output(trim($v->patient_name)); ?>
                                                    <br><small class="text-muted"><?php echo escape_output($v->pat_number); ?></small>
                                                </td>
                                                <td><span class="badge <?php echo escape_output($badge); ?>"><?php echo escape_output($typeLabel); ?></span></td>
                                                <td><?php echo escape_output($statusLabel); ?></td>
                                                <td><?php echo (int) $v->wait_minutes; ?> min</td>
                                                <td class="text-nowrap">
                                                    <?php
                                                    if ($v->visit_status === 'waiting') {
                                                        echo nex_reception_action_btn('start_registration', ['visit_id' => $v->visit_id], 'Start reg', 'btn-outline-primary btn-sm');
                                                        echo ' ';
                                                        echo nex_reception_action_btn('mark_ready', ['visit_id' => $v->visit_id], 'Ready', 'btn-outline-success btn-sm');
                                                    } elseif ($v->visit_status === 'registering') {
                                                        echo '<a class="btn btn-primary btn-sm" href="his_admin_update_single_patient.php?pat_id=' . (int) $v->pat_id . '">Update details</a> ';
                                                        echo nex_reception_action_btn('complete_registration', ['visit_id' => $v->visit_id], 'Complete', 'btn-success btn-sm');
                                                    } elseif ($v->visit_status === 'ready') {
                                                        echo nex_reception_action_btn('send_in', ['visit_id' => $v->visit_id], 'Send in', 'btn-primary btn-sm');
                                                        echo ' ';
                                                    } elseif ($v->visit_status === 'in_service') {
                                                        echo nex_reception_action_btn('complete_visit', ['visit_id' => $v->visit_id], 'Complete visit', 'btn-success btn-sm');
                                                        echo ' ';
                                                    }
                                                    echo nex_reception_action_btn('cancel_visit', ['visit_id' => $v->visit_id], 'Cancel', 'btn-light btn-sm');
                                                    ?>
                                                </td>
                                            </tr>
                                        <?php } ?>
                                        </tbody>
                                    </table>
                                </div>
                            <?php } ?>
                        </div>

                        <div class="card-box mb-3">
                            <h4 class="header-title mb-3">Today’s appointments</h4>
                            <?php if (!$schedule) { ?>
                                <p class="text-muted mb-0">No appointments scheduled for today.</p>
                            <?php } else { ?>
                                <div class="table-responsive">
                                    <table class="table table-sm table-centered mb-0">
                                        <thead>
                                        <tr>
                                            <th>Time</th>
                                            <th>Patient</th>
                                            <th>Doctor</th>
                                            <th>Status</th>
                                            <th>Action</th>
                                        </tr>
                                        </thead>
                                        <tbody>
                                        <?php foreach ($schedule as $a) {
                                            $checked = !empty($a->visit_id);
                                            $canCheckIn = !$checked && in_array($a->appt_status, ['booked', 'rescheduled'], true);
                                            ?>
                                            <tr>
                                                <td><?php echo escape_output(date('H:i', strtotime($a->appt_datetime))); ?></td>
                                                <td>
                                                    <?php echo escape_output(trim($a->patient_name)); ?>
                                                    <br><small class="text-muted"><?php echo escape_output($a->pat_number); ?></small>
                                                </td>
                                                <td><?php echo escape_output(trim($a->doctor_name)); ?></td>
                                                <td>
                                                    <?php
                                                    if ($checked) {
                                                        echo '<span class="badge badge-success">Checked in</span>';
                                                    } elseif ($a->appt_status === 'no_show') {
                                                        echo '<span class="badge badge-danger">No-show</span>';
                                                    } else {
                                                        echo '<span class="badge badge-secondary">' . escape_output(ucfirst(str_replace('_', ' ', $a->appt_status))) . '</span>';
                                                    }
                                                    ?>
                                                </td>
                                                <td class="text-nowrap">
                                                    <?php
                                                    if ($canCheckIn) {
                                                        echo nex_reception_action_btn('check_in', ['appt_id' => $a->appt_id], 'Check in', 'btn-primary btn-sm');
                                                        echo ' ';
                                                        echo nex_reception_action_btn('no_show', ['appt_id' => $a->appt_id], 'No-show', 'btn-outline-danger btn-sm');
                                                    } elseif ($checked) {
                                                        echo '<a class="btn btn-light btn-sm" href="his_admin_manage_appointment.php?appt_id=' . (int) $a->appt_id . '">Manage</a>';
                                                    } else {
                                                        echo '<a class="btn btn-light btn-sm" href="his_admin_manage_appointment.php?appt_id=' . (int) $a->appt_id . '">Manage</a>';
                                                    }
                                                    ?>
                                                </td>
                                            </tr>
                                        <?php } ?>
                                        </tbody>
                                    </table>
                                </div>
                            <?php } ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <?php include __DIR__ . '/../assets/inc/footer.php'; ?>
    </div>
</div>
<script src="<?php echo escape_output(nex_admin_url('assets/js/vendor.min.js')); ?>"></script>
<script src="<?php echo escape_output(nex_admin_url('assets/js/app.min.js')); ?>"></script>
</body>
</html>
