<?php
session_start();
include('../config.php');
include('../security.php');
include('../assets/inc/nex_sectors.php');
include('assets/inc/checklogin.php');
check_login();

if (!nex_sectors_available() || empty($_SESSION['nex_user_id'])) {
    header('Location: his_doc_dashboard.php');
    exit;
}

if (isset($_POST['pick_sector'])) {
    $code = trim((string) ($_POST['sector_code'] ?? ''));
    $facility = (int) ($_POST['facility_id'] ?? 0);
    if (nex_set_active_sector($code, $facility > 0 ? $facility : null)) {
        header('Location: his_doc_sector_dashboard.php?sector=' . urlencode($code));
        exit;
    }
    $err = 'You are not assigned to that sector.';
}
$assignments = $_SESSION['nex_assignments'] ?? [];
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
                            <h4 class="page-title">Choose a sector</h4>
                            <p class="text-muted mb-0">NexClinic staff workspace picker</p>
                        </div>
                    </div>
                </div>
                <div class="row">
                    <?php foreach ($assignments as $a) { ?>
                        <div class="col-md-4 mb-3">
                            <div class="card-box">
                                <h4 class="header-title"><?php echo escape_output($a['sector_name']); ?></h4>
                                <p class="text-muted"><?php echo escape_output($a['facility_name']); ?></p>
                                <form method="post">
                                    <input type="hidden" name="csrf_token" value="<?php echo generate_csrf_token(); ?>">
                                    <input type="hidden" name="sector_code" value="<?php echo escape_output($a['sector_code']); ?>">
                                    <input type="hidden" name="facility_id" value="<?php echo (int) $a['facility_id']; ?>">
                                    <button type="submit" name="pick_sector" class="btn btn-primary btn-sm">Enter</button>
                                </form>
                            </div>
                        </div>
                    <?php } ?>
                </div>
                <p><a href="his_doc_dashboard.php">Classic doctor dashboard</a></p>
            </div>
        </div>
        <?php include('assets/inc/footer.php'); ?>
    </div>
</div>
<script src="assets/js/vendor.min.js"></script>
<script src="assets/js/app.min.js"></script>
</body>
</html>
