<?php
$nex_admin_base = '../';
require __DIR__ . '/../_init.php';
require_once __DIR__ . '/../../assets/inc/nex_reception.php';
check_login();
nex_reception_authorize('visit.manage');
$filter_date = trim((string) ($_GET['date'] ?? date('Y-m-d')));
$filter_doc = (int) ($_GET['doc_id'] ?? 0);
if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $filter_date)) {
    $filter_date = date('Y-m-d');
}

$docs = [];
$doc_res = $mysqli->query("SELECT doc_id, doc_fname, doc_lname, doc_number FROM his_docs ORDER BY doc_fname, doc_lname");
if ($doc_res) {
    while ($d = $doc_res->fetch_object()) {
        $docs[] = $d;
    }
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
                                    <li class="breadcrumb-item active">Appointments</li>
                                </ol>
                            </div>
                            <h4 class="page-title">Appointments</h4>
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-12">
                        <div class="card-box">
                            <div class="d-flex flex-wrap align-items-center justify-content-between mb-3">
                                <h4 class="header-title mb-0">NexClinic schedule</h4>
                                <a href="his_admin_add_appointment.php" class="btn btn-primary btn-sm"><i class="fe-plus"></i> Book Appointment</a>
                            </div>

                            <form method="get" class="form-inline mb-3">
                                <label class="mr-2">Day</label>
                                <input type="date" name="date" class="form-control form-control-sm mr-3" value="<?php echo escape_output($filter_date); ?>">
                                <label class="mr-2">Doctor</label>
                                <select name="doc_id" class="form-control form-control-sm mr-3">
                                    <option value="0">All doctors</option>
                                    <?php foreach ($docs as $d) { ?>
                                        <option value="<?php echo (int) $d->doc_id; ?>" <?php echo $filter_doc === (int) $d->doc_id ? 'selected' : ''; ?>>
                                            <?php echo escape_output($d->doc_fname . ' ' . $d->doc_lname); ?>
                                        </option>
                                    <?php } ?>
                                </select>
                                <button type="submit" class="btn btn-outline-primary btn-sm">Filter</button>
                            </form>

                            <div class="table-responsive">
                                <table class="table table-bordered mb-0">
                                    <thead>
                                    <tr>
                                        <th>#</th>
                                        <th>Time</th>
                                        <th>Patient</th>
                                        <th>Doctor</th>
                                        <th>Status</th>
                                        <th>Reason</th>
                                        <th>Action</th>
                                    </tr>
                                    </thead>
                                    <tbody>
                                    <?php
                                    $sql = "SELECT a.*,
                                            CONCAT(COALESCE(p.pat_fname,''),' ',COALESCE(p.pat_lname,'')) AS patient_name,
                                            CONCAT(COALESCE(d.doc_fname,''),' ',COALESCE(d.doc_lname,'')) AS doctor_name
                                        FROM his_appointments a
                                        LEFT JOIN his_patients p ON p.pat_id = a.pat_id
                                        LEFT JOIN his_docs d ON d.doc_id = a.doc_id
                                        WHERE DATE(a.appt_datetime) = ?";
                                    if ($filter_doc > 0) {
                                        $sql .= " AND a.doc_id = ?";
                                    }
                                    $sql .= " ORDER BY a.appt_datetime ASC";
                                    $stmt = $mysqli->prepare($sql);
                                    if ($filter_doc > 0) {
                                        $stmt->bind_param('si', $filter_date, $filter_doc);
                                    } else {
                                        $stmt->bind_param('s', $filter_date);
                                    }
                                    $stmt->execute();
                                    $res = $stmt->get_result();
                                    $cnt = 1;
                                    while ($row = $res->fetch_object()) {
                                        $badge = 'secondary';
                                        if ($row->appt_status === 'booked') $badge = 'primary';
                                        if ($row->appt_status === 'completed') $badge = 'success';
                                        if ($row->appt_status === 'cancelled') $badge = 'danger';
                                        if ($row->appt_status === 'rescheduled') $badge = 'warning';
                                    ?>
                                        <tr>
                                            <td><?php echo $cnt; ?></td>
                                            <td><?php echo escape_output(date('H:i', strtotime($row->appt_datetime))); ?></td>
                                            <td><?php echo escape_output(trim($row->patient_name) !== '' ? $row->patient_name : $row->pat_number); ?></td>
                                            <td><?php echo escape_output(trim($row->doctor_name) !== '' ? $row->doctor_name : $row->doc_number); ?></td>
                                            <td><span class="badge badge-<?php echo $badge; ?>"><?php echo escape_output($row->appt_status); ?></span></td>
                                            <td><?php echo escape_output($row->appt_reason); ?></td>
                                            <td>
                                                <a href="his_admin_manage_appointment.php?appt_id=<?php echo (int) $row->appt_id; ?>" class="badge badge-info">
                                                    <i class="mdi mdi-pencil"></i> Manage
                                                </a>
                                            </td>
                                        </tr>
                                    <?php $cnt++; } $stmt->close(); ?>
                                    <?php if ($cnt === 1) { ?>
                                        <tr><td colspan="7" class="text-muted text-center">No appointments for this day.</td></tr>
                                    <?php } ?>
                                    </tbody>
                                </table>
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
