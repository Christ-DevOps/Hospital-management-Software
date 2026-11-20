<?php
session_start();
include('../config.php');
include('../security.php');
include('assets/inc/checklogin.php');
check_login();
require_once('../assets/inc/notification_events.php');
require_once('../assets/inc/nex_care.php');
require_once('../assets/inc/nex_prescription.php');

$rx_schema_ready = nex_rx_schema_ready($mysqli);

if (isset($_POST['add_patient_presc'])) {
    nex_rx_authorize('prescription.create');
    $pres_pat_name = $_POST['pres_pat_name'];
    $pres_pat_number = $_POST['pres_pat_number'];
    $pres_pat_type = $_POST['pres_pat_type'];
    $pres_pat_addr = $_POST['pres_pat_addr'];
    $pres_pat_age = $_POST['pres_pat_age'];
    $pres_number = $_POST['pres_number'];
    $pres_pat_ailment = $_POST['pres_pat_ailment'];
    $prescribed_by = nex_actor_id();

    if ($rx_schema_ready) {
        $collected = nex_rx_collect_items_from_post();
        if (!$collected['ok']) {
            $err = $collected['err'] ?? 'Invalid medicines.';
        } else {
            $result = nex_rx_create($mysqli, [
                'pres_pat_name' => $pres_pat_name,
                'pres_pat_number' => $pres_pat_number,
                'pres_pat_type' => $pres_pat_type,
                'pres_pat_addr' => $pres_pat_addr,
                'pres_pat_age' => $pres_pat_age,
                'pres_number' => $pres_number,
                'pres_pat_ailment' => $pres_pat_ailment,
                'pres_notes' => $_POST['pres_notes'] ?? '',
            ], $collected['items']);
            if ($result['ok']) {
                $success = 'Prescription issued — sent to pharmacy queue.';
                $notif_pat_id = $result['pat_id'] ?: nex_resolve_pat_id($mysqli, $pres_pat_number);
                if ($notif_pat_id) {
                    notify_prescription_issued($mysqli, $notif_pat_id, $pres_pat_ailment, $result['pres_ins']);
                    if ($prescribed_by) {
                        nex_ensure_care_relationship($mysqli, $notif_pat_id, $prescribed_by);
                    }
                }
            } else {
                $err = $result['err'] ?? 'Please try again later.';
            }
        }
    } else {
        $pres_ins = $_POST['pres_ins'] ?? '';
        $query = 'INSERT INTO his_prescriptions (pres_pat_name, pres_pat_number, pres_pat_type, pres_pat_addr, pres_pat_age, pres_number, pres_pat_ailment, pres_ins, prescribed_by) VALUES(?,?,?,?,?,?,?,?,?)';
        $stmt = $mysqli->prepare($query);
        $rc = $stmt->bind_param('ssssssssi', $pres_pat_name, $pres_pat_number, $pres_pat_type, $pres_pat_addr, $pres_pat_age, $pres_number, $pres_pat_ailment, $pres_ins, $prescribed_by);
        if ($stmt->execute()) {
            $success = 'Patient Prescription Addded';
            $notif_pat_id = nex_resolve_pat_id($mysqli, $pres_pat_number);
            if ($notif_pat_id) {
                notify_prescription_issued($mysqli, $notif_pat_id, $pres_pat_ailment, $pres_ins);
                if ($prescribed_by) {
                    nex_ensure_care_relationship($mysqli, $notif_pat_id, $prescribed_by);
                }
            }
        } else {
            $err = 'Please Try Again Or Try Later';
        }
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
    <?php
    $pat_number = $_GET['pat_number'] ?? '';
    $ret = 'SELECT * FROM his_patients WHERE pat_number=?';
    $stmt = $mysqli->prepare($ret);
    $stmt->bind_param('s', $pat_number);
    $stmt->execute();
    $res = $stmt->get_result();
    while ($row = $res->fetch_object()) {
        $length = 5;
        $pres_no = substr(str_shuffle('0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZ'), 1, $length);
        ?>
        <div class="content-page">
            <div class="content">
                <div class="container-fluid">
                    <div class="row">
                        <div class="col-12">
                            <div class="page-title-box">
                                <div class="page-title-right">
                                    <ol class="breadcrumb m-0">
                                        <li class="breadcrumb-item"><a href="his_doc_dashboard.php">Dashboard</a></li>
                                        <li class="breadcrumb-item"><a href="javascript:void(0);">Clinical</a></li>
                                        <li class="breadcrumb-item active">Add Prescription</li>
                                    </ol>
                                </div>
                                <h4 class="page-title">Add Patient Prescription</h4>
                            </div>
                        </div>
                    </div>
                    <?php if (!empty($success)) { ?>
                        <div class="alert alert-success"><?php echo escape_output($success); ?></div>
                    <?php } ?>
                    <?php if (!empty($err)) { ?>
                        <div class="alert alert-danger"><?php echo escape_output($err); ?></div>
                    <?php } ?>
                    <div class="row">
                        <div class="col-12">
                            <div class="card">
                                <div class="card-body">
                                    <h4 class="header-title">Issue prescription after consult</h4>
                                    <form method="post">
                                        <input type="hidden" name="csrf_token" value="<?php echo generate_csrf_token(); ?>">
                                        <div class="form-row">
                                            <div class="form-group col-md-6">
                                                <label class="col-form-label">Patient Name</label>
                                                <input type="text" required readonly name="pres_pat_name" value="<?php echo escape_output($row->pat_fname); ?> <?php echo escape_output($row->pat_lname); ?>" class="form-control">
                                            </div>
                                            <div class="form-group col-md-6">
                                                <label class="col-form-label">Patient Age</label>
                                                <input required type="text" readonly name="pres_pat_age" value="<?php echo escape_output($row->pat_age); ?>" class="form-control">
                                            </div>
                                        </div>
                                        <div class="form-row">
                                            <div class="form-group col-md-4">
                                                <label class="col-form-label">Patient Number</label>
                                                <input type="text" required readonly name="pres_pat_number" value="<?php echo escape_output($row->pat_number); ?>" class="form-control">
                                            </div>
                                            <div class="form-group col-md-4">
                                                <label class="col-form-label">Patient Address</label>
                                                <input required type="text" readonly name="pres_pat_addr" value="<?php echo escape_output($row->pat_addr); ?>" class="form-control">
                                            </div>
                                            <div class="form-group col-md-4">
                                                <label class="col-form-label">Patient Type</label>
                                                <input required readonly type="text" name="pres_pat_type" value="<?php echo escape_output($row->pat_type); ?>" class="form-control">
                                            </div>
                                        </div>
                                        <div class="form-group">
                                            <label class="col-form-label">Patient Ailment</label>
                                            <input required type="text" value="<?php echo escape_output($row->pat_ailment); ?>" name="pres_pat_ailment" class="form-control">
                                        </div>
                                        <input type="hidden" name="pres_number" value="<?php echo escape_output($pres_no); ?>">
                                        <hr>
                                        <?php include __DIR__ . '/../assets/inc/nex_prescription_form.php'; ?>
                                        <?php if (!$rx_schema_ready) { ?>
                                            <div class="form-group">
                                                <label class="col-form-label">Prescription</label>
                                                <textarea required class="form-control" name="pres_ins" id="editor"></textarea>
                                            </div>
                                        <?php } ?>
                                        <button type="submit" name="add_patient_presc" class="ladda-button btn btn-primary" data-style="expand-right">Add Patient Prescription</button>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <?php include('assets/inc/footer.php'); ?>
        </div>
    <?php } ?>
</div>
<div class="rightbar-overlay"></div>
<?php if (!$rx_schema_ready) { ?>
<script src="//cdn.ckeditor.com/4.6.2/basic/ckeditor.js"></script>
<script>CKEDITOR.replace('editor');</script>
<?php } ?>
<script src="assets/js/vendor.min.js"></script>
<script src="assets/js/app.min.js"></script>
<script src="assets/libs/ladda/spin.js"></script>
<script src="assets/libs/ladda/ladda.js"></script>
<script src="assets/js/pages/loading-btn.init.js"></script>
</body>
</html>
