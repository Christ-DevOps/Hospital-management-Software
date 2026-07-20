<?php
session_start();
include('../config.php');
include('../security.php');
include('../assets/inc/nex_sectors.php');
include('assets/inc/checklogin.php');
check_login();

$sector = trim((string) ($_GET['sector'] ?? nex_active_sector() ?? 'clinical'));
if (nex_sectors_available() && !empty($_SESSION['nex_user_id'])) {
    if (!nex_has_sector($sector)) {
        header('Location: his_doc_sector_picker.php');
        exit;
    }
    nex_set_active_sector($sector);
}

$links = [
    'clinical' => [
        ['My appointments', 'his_doc_appointments.php'],
        ['Patients', 'his_doc_view_patients.php'],
        ['Register patient', 'his_doc_register_patient.php'],
    ],
    'pharmacy' => [
        ['Pharmaceuticals', 'his_doc_view_pharmaceuticals.php'],
        ['Prescriptions', 'his_doc_view_presc.php'],
        ['Inventory', 'his_doc_pharm_inventory.php'],
    ],
    'laboratory' => [
        ['Lab tests', 'his_doc_patient_lab_test.php'],
        ['Lab results', 'his_doc_patient_lab_result.php'],
        ['Lab reports', 'his_doc_lab_report.php'],
    ],
    'payroll' => [
        ['My payrolls', 'his_doc_view_payrolls.php'],
    ],
    'inventory' => [
        ['Pharmaceuticals', 'his_doc_pharm_inventory.php'],
        ['Assets', 'his_doc_equipments_inventory.php'],
    ],
];
$sector_links = $links[$sector] ?? $links['clinical'];
$label = ucfirst($sector);
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
                        <div class="page-title-box d-flex justify-content-between align-items-center">
                            <h4 class="page-title mb-0"><?php echo escape_output($label); ?> sector</h4>
                            <a href="his_doc_sector_picker.php" class="btn btn-outline-primary btn-sm">Switch sector</a>
                        </div>
                    </div>
                </div>
                <div class="row">
                    <?php foreach ($sector_links as $item) { ?>
                        <div class="col-md-4 mb-3">
                            <a href="<?php echo escape_output($item[1]); ?>" class="card-box d-block text-decoration-none">
                                <h4 class="header-title"><?php echo escape_output($item[0]); ?></h4>
                            </a>
                        </div>
                    <?php } ?>
                </div>
            </div>
        </div>
        <?php include('assets/inc/footer.php'); ?>
    </div>
</div>
<script src="assets/js/vendor.min.js"></script>
<script src="assets/js/app.min.js"></script>
</body>
</html>
