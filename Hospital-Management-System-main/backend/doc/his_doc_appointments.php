<?php
session_start();
include('../config.php');
include('../security.php');
include('assets/inc/checklogin.php');
check_login();

$doc_id = (int) ($_SESSION['doc_id'] ?? 0);
$filter_date = trim((string) ($_GET['date'] ?? date('Y-m-d')));
if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $filter_date)) {
    $filter_date = date('Y-m-d');
}
$allowed = ['booked', 'completed', 'cancelled', 'rescheduled'];

if (isset($_POST['update_status'])) {
    $appt_id = (int) ($_POST['appt_id'] ?? 0);
    $status = trim((string) ($_POST['appt_status'] ?? ''));
    if ($appt_id > 0 && in_array($status, $allowed, true)) {
        $q = 'UPDATE his_appointments SET appt_status=? WHERE appt_id=? AND doc_id=?';
        $stmt = $mysqli->prepare($q);
        $stmt->bind_param('sii', $status, $appt_id, $doc_id);
        if ($stmt->execute() && $stmt->affected_rows >= 0) {
            $success = 'Appointment status updated.';
        } else {
            $err = 'Could not update status.';
        }
        $stmt->close();
    } else {
        $err = 'Invalid status update.';
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<?php include('assets/inc/head.php'); ?>
<body>
<div id="wrapper">
    <?php include('assets/inc/nav.php'); ?>
    <?php include('assets/inc/sidebar.php'); ?>

    <div class="content-page">
        <div class="content">
            <div class="container-fluid">
                <div class="row">
                    <div class="col-12">
                        <div class="page-title-box">
                            <div class="page-title-right">
                                <ol class="breadcrumb m-0">
                                    <li class="breadcrumb-item"><a href="his_doc_dashboard.php">Dashboard</a></li>
                                    <li class="breadcrumb-item active">My Appointments</li>
                                </ol>
                            </div>
                            <h4 class="page-title">My Appointments</h4>
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-12">
                        <div class="card-box">
                            <h4 class="header-title mb-3">NexClinic — today's schedule</h4>
                            <form method="get" class="form-inline mb-3">
                                <label class="mr-2">Day</label>
                                <input type="date" name="date" class="form-control form-control-sm mr-2" value="<?php echo escape_output($filter_date); ?>">
                                <button type="submit" class="btn btn-outline-primary btn-sm">Show</button>
                            </form>

                            <div class="table-responsive">
                                <table class="table table-bordered mb-0">
                                    <thead>
                                    <tr>
                                        <th>Time</th>
                                        <th>Patient</th>
                                        <th>Status</th>
                                        <th>Reason</th>
                                        <th>Update</th>
                                    </tr>
                                    </thead>
                                    <tbody>
                                    <?php
                                    $sql = "SELECT a.*,
                                            CONCAT(COALESCE(p.pat_fname,''),' ',COALESCE(p.pat_lname,'')) AS patient_name
                                        FROM his_appointments a
                                        LEFT JOIN his_patients p ON p.pat_id = a.pat_id
                                        WHERE a.doc_id = ? AND DATE(a.appt_datetime) = ?
                                        ORDER BY a.appt_datetime ASC";
                                    $stmt = $mysqli->prepare($sql);
                                    $stmt->bind_param('is', $doc_id, $filter_date);
                                    $stmt->execute();
                                    $res = $stmt->get_result();
                                    $found = false;
                                    while ($row = $res->fetch_object()) {
                                        $found = true;
                                    ?>
                                        <tr>
                                            <td><?php echo escape_output(date('H:i', strtotime($row->appt_datetime))); ?></td>
                                            <td><?php echo escape_output(trim($row->patient_name) !== '' ? $row->patient_name : $row->pat_number); ?></td>
                                            <td><?php echo escape_output($row->appt_status); ?></td>
                                            <td><?php echo escape_output($row->appt_reason); ?></td>
                                            <td>
                                                <form method="post" class="form-inline">
                                                    <input type="hidden" name="csrf_token" value="<?php echo generate_csrf_token(); ?>">
                                                    <input type="hidden" name="appt_id" value="<?php echo (int) $row->appt_id; ?>">
                                                    <select name="appt_status" class="form-control form-control-sm mr-1">
                                                        <?php foreach ($allowed as $st) { ?>
                                                            <option value="<?php echo escape_output($st); ?>" <?php echo $row->appt_status === $st ? 'selected' : ''; ?>>
                                                                <?php echo escape_output(ucfirst($st)); ?>
                                                            </option>
                                                        <?php } ?>
                                                    </select>
                                                    <button type="submit" name="update_status" class="btn btn-sm btn-primary">Save</button>
                                                </form>
                                            </td>
                                        </tr>
                                    <?php } $stmt->close(); ?>
                                    <?php if (!$found) { ?>
                                        <tr><td colspan="5" class="text-muted text-center">No appointments for this day.</td></tr>
                                    <?php } ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <?php include('assets/inc/footer.php'); ?>
    </div>
</div>
<div class="rightbar-overlay"></div>
<script src="assets/js/vendor.min.js"></script>
<script src="assets/js/app.min.js"></script>
</body>
</html>
