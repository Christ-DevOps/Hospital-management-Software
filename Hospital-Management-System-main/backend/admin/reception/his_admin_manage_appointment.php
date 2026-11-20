<?php
$nex_admin_base = '../';
require __DIR__ . '/../_init.php';
require_once __DIR__ . '/../../assets/inc/nex_reception.php';
check_login();
nex_reception_authorize('visit.manage');
require_once __DIR__ . '/../../assets/inc/notification_events.php';

$appt_id = (int) ($_GET['appt_id'] ?? 0);
$allowed = ['booked', 'completed', 'cancelled', 'rescheduled', 'no_show'];

if (isset($_POST['update_appointment']) && $appt_id > 0) {
    $status = trim((string) ($_POST['appt_status'] ?? ''));
    $reason = trim((string) ($_POST['appt_reason'] ?? ''));
    $appt_datetime = trim((string) ($_POST['appt_datetime'] ?? ''));
    if (!in_array($status, $allowed, true)) {
        $err = 'Invalid status.';
    } else {
        $dt = date_create($appt_datetime);
        if (!$dt) {
            $err = 'Invalid date/time.';
        } else {
            $appt_sql = $dt->format('Y-m-d H:i:s');

            $prev = $mysqli->prepare("SELECT a.pat_id, a.appt_datetime,
                    CONCAT(COALESCE(d.doc_fname,''),' ',COALESCE(d.doc_lname,'')) AS doctor_name
                FROM his_appointments a
                LEFT JOIN his_docs d ON d.doc_id = a.doc_id
                WHERE a.appt_id=?");
            $prev->bind_param('i', $appt_id);
            $prev->execute();
            $prevRow = $prev->get_result()->fetch_object();
            $prev->close();

            $q = 'UPDATE his_appointments SET appt_status=?, appt_reason=?, appt_datetime=? WHERE appt_id=?';
            $stmt = $mysqli->prepare($q);
            $stmt->bind_param('sssi', $status, $reason, $appt_sql, $appt_id);
            if ($stmt->execute()) {
                $success = 'Appointment updated.';
                if ($prevRow && $prevRow->appt_datetime !== $appt_sql) {
                    notify_appointment_rescheduled($mysqli, (int) $prevRow->pat_id, $appt_sql, trim($prevRow->doctor_name));
                }
            } else {
                $err = 'Update failed. Try again.';
            }
            $stmt->close();
        }
    }
}

$row = null;
if ($appt_id > 0) {
    $stmt = $mysqli->prepare("SELECT a.*,
            CONCAT(COALESCE(p.pat_fname,''),' ',COALESCE(p.pat_lname,'')) AS patient_name,
            CONCAT(COALESCE(d.doc_fname,''),' ',COALESCE(d.doc_lname,'')) AS doctor_name
        FROM his_appointments a
        LEFT JOIN his_patients p ON p.pat_id = a.pat_id
        LEFT JOIN his_docs d ON d.doc_id = a.doc_id
        WHERE a.appt_id=?");
    $stmt->bind_param('i', $appt_id);
    $stmt->execute();
    $row = $stmt->get_result()->fetch_object();
    $stmt->close();
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
                        <div class="page-title-box">
                            <div class="page-title-right">
                                <ol class="breadcrumb m-0">
                                    <li class="breadcrumb-item"><a href="his_admin_dashboard.php">Dashboard</a></li>
                                    <li class="breadcrumb-item"><a href="his_admin_appointments.php">Appointments</a></li>
                                    <li class="breadcrumb-item active">Manage</li>
                                </ol>
                            </div>
                            <h4 class="page-title">Manage Appointment</h4>
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-12">
                        <div class="card">
                            <div class="card-body">
                                <?php if (!$row) { ?>
                                    <p class="text-muted mb-0">Appointment not found. <a href="his_admin_appointments.php">Back to list</a></p>
                                <?php } else {
                                    $local = date('Y-m-d\TH:i', strtotime($row->appt_datetime));
                                ?>
                                <h4 class="header-title mb-3">
                                    <?php echo escape_output(trim($row->patient_name)); ?>
                                    <span class="text-muted">with</span>
                                    <?php echo escape_output(trim($row->doctor_name)); ?>
                                </h4>
                                <form method="post">
                                    <input type="hidden" name="csrf_token" value="<?php echo generate_csrf_token(); ?>">
                                    <div class="form-row">
                                        <div class="form-group col-md-4">
                                            <label class="col-form-label">Status</label>
                                            <select name="appt_status" class="form-control" required>
                                                <?php foreach ($allowed as $st) { ?>
                                                    <option value="<?php echo escape_output($st); ?>" <?php echo $row->appt_status === $st ? 'selected' : ''; ?>>
                                                        <?php echo escape_output(ucfirst($st)); ?>
                                                    </option>
                                                <?php } ?>
                                            </select>
                                        </div>
                                        <div class="form-group col-md-4">
                                            <label class="col-form-label">Date &amp; time</label>
                                            <input type="datetime-local" name="appt_datetime" class="form-control" value="<?php echo escape_output($local); ?>" required>
                                        </div>
                                        <div class="form-group col-md-4">
                                            <label class="col-form-label">Reason</label>
                                            <input type="text" name="appt_reason" class="form-control" value="<?php echo escape_output($row->appt_reason); ?>">
                                        </div>
                                    </div>
                                    <button type="submit" name="update_appointment" class="btn btn-success">Save changes</button>
                                    <a href="his_admin_appointments.php" class="btn btn-light ml-1">Back</a>
                                </form>
                                <?php } ?>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <?php include __DIR__ . '/../assets/inc/footer.php'; ?>
    </div>
</div>
<div class="rightbar-overlay"></div>
<script src="<?php echo escape_output(nex_admin_url('assets/js/vendor.min.js')); ?>"></script>
<script src="<?php echo escape_output(nex_admin_url('assets/js/app.min.js')); ?>"></script>
</body>
</html>
