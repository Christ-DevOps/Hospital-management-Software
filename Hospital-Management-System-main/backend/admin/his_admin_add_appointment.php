<?php
session_start();
include('../config.php');
include('../security.php');
include('assets/inc/checklogin.php');
check_login();

if (isset($_POST['add_appointment'])) {
    $pat_id = (int) ($_POST['pat_id'] ?? 0);
    $doc_id = (int) ($_POST['doc_id'] ?? 0);
    $appt_datetime = trim((string) ($_POST['appt_datetime'] ?? ''));
    $appt_reason = trim((string) ($_POST['appt_reason'] ?? ''));

    if ($pat_id < 1 || $doc_id < 1 || $appt_datetime === '') {
        $err = 'Patient, doctor, and date/time are required.';
    } else {
        $dt = date_create($appt_datetime);
        if (!$dt) {
            $err = 'Invalid appointment date/time.';
        } else {
            $appt_sql = $dt->format('Y-m-d H:i:s');
            $pat_number = '';
            $doc_number = '';
            $ps = $mysqli->prepare('SELECT pat_number FROM his_patients WHERE pat_id=?');
            $ps->bind_param('i', $pat_id);
            $ps->execute();
            $pr = $ps->get_result()->fetch_object();
            $ps->close();
            $ds = $mysqli->prepare('SELECT doc_number FROM his_docs WHERE doc_id=?');
            $ds->bind_param('i', $doc_id);
            $ds->execute();
            $dr = $ds->get_result()->fetch_object();
            $ds->close();
            if (!$pr || !$dr) {
                $err = 'Selected patient or doctor not found.';
            } else {
                $pat_number = $pr->pat_number;
                $doc_number = $dr->doc_number;
                $status = 'booked';
                $q = 'INSERT INTO his_appointments (pat_id, pat_number, doc_id, doc_number, appt_datetime, appt_status, appt_reason) VALUES (?,?,?,?,?,?,?)';
                $stmt = $mysqli->prepare($q);
                $stmt->bind_param('isissss', $pat_id, $pat_number, $doc_id, $doc_number, $appt_sql, $status, $appt_reason);
                if ($stmt->execute()) {
                    $success = 'Appointment booked.';
                } else {
                    $err = 'Could not book appointment. Try again.';
                }
                $stmt->close();
            }
        }
    }
}

$patients = $mysqli->query("SELECT pat_id, pat_fname, pat_lname, pat_number FROM his_patients ORDER BY pat_fname, pat_lname");
$doctors = $mysqli->query("SELECT doc_id, doc_fname, doc_lname, doc_number FROM his_docs ORDER BY doc_fname, doc_lname");
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
                                    <li class="breadcrumb-item"><a href="his_admin_dashboard.php">Dashboard</a></li>
                                    <li class="breadcrumb-item"><a href="his_admin_appointments.php">Appointments</a></li>
                                    <li class="breadcrumb-item active">Book</li>
                                </ol>
                            </div>
                            <h4 class="page-title">Book Appointment</h4>
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-12">
                        <div class="card">
                            <div class="card-body">
                                <h4 class="header-title">New NexClinic appointment</h4>
                                <form method="post">
                                    <input type="hidden" name="csrf_token" value="<?php echo generate_csrf_token(); ?>">
                                    <div class="form-row">
                                        <div class="form-group col-md-6">
                                            <label class="col-form-label">Patient <span class="text-danger">*</span></label>
                                            <select name="pat_id" class="form-control" required>
                                                <option value="">Select patient</option>
                                                <?php while ($p = $patients->fetch_object()) { ?>
                                                    <option value="<?php echo (int) $p->pat_id; ?>">
                                                        <?php echo escape_output($p->pat_fname . ' ' . $p->pat_lname . ' (' . $p->pat_number . ')'); ?>
                                                    </option>
                                                <?php } ?>
                                            </select>
                                        </div>
                                        <div class="form-group col-md-6">
                                            <label class="col-form-label">Doctor <span class="text-danger">*</span></label>
                                            <select name="doc_id" class="form-control" required>
                                                <option value="">Select doctor</option>
                                                <?php while ($d = $doctors->fetch_object()) { ?>
                                                    <option value="<?php echo (int) $d->doc_id; ?>">
                                                        <?php echo escape_output($d->doc_fname . ' ' . $d->doc_lname . ' (' . $d->doc_number . ')'); ?>
                                                    </option>
                                                <?php } ?>
                                            </select>
                                        </div>
                                    </div>
                                    <div class="form-row">
                                        <div class="form-group col-md-6">
                                            <label class="col-form-label">Date &amp; time <span class="text-danger">*</span></label>
                                            <input type="datetime-local" name="appt_datetime" class="form-control" required>
                                        </div>
                                        <div class="form-group col-md-6">
                                            <label class="col-form-label">Reason</label>
                                            <input type="text" name="appt_reason" class="form-control" placeholder="Optional reason">
                                        </div>
                                    </div>
                                    <button type="submit" name="add_appointment" class="ladda-button btn btn-primary" data-style="expand-right">Book Appointment</button>
                                    <a href="his_admin_appointments.php" class="btn btn-light ml-1">Back to list</a>
                                </form>
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
<script src="assets/libs/ladda/spin.js"></script>
<script src="assets/libs/ladda/ladda.js"></script>
<script src="assets/js/pages/loading-btn.init.js"></script>
</body>
</html>
