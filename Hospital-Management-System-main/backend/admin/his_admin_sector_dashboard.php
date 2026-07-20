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
        header('Location: his_admin_sector_picker.php');
        exit;
    }
    nex_set_active_sector($sector);
}

$modules = nex_sector_modules();
$label = $modules[$sector]['label'] ?? ucfirst($sector);

$links = [
    'clinical' => [
        ['Register patient', 'his_admin_register_patient.php'],
        ['View patients', 'his_admin_view_patients.php'],
        ['Medical records', 'his_admin_medical_records.php'],
    ],
    'reception' => [
        ['Appointments', 'his_admin_appointments.php'],
        ['Book appointment', 'his_admin_add_appointment.php'],
        ['Register patient', 'his_admin_register_patient.php'],
    ],
    'pharmacy' => [
        ['Pharmaceuticals', 'his_admin_view_pharmaceuticals.php'],
        ['Prescriptions', 'his_admin_view_presc.php'],
        ['Pharm inventory', 'his_admin_pharm_inventory.php'],
    ],
    'laboratory' => [
        ['Lab tests', 'his_admin_patient_lab_test.php'],
        ['Lab results', 'his_admin_patient_lab_result.php'],
        ['Lab reports', 'his_admin_lab_report.php'],
    ],
    'hr' => [
        ['Employees', 'his_admin_view_employee.php'],
        ['Assign department', 'his_admin_assaign_dept.php'],
    ],
    'payroll' => [
        ['Add payroll', 'his_admin_add_payroll.php'],
        ['Manage payrolls', 'his_admin_manage_payrolls.php'],
    ],
    'accounting' => [
        ['Payable', 'his_admin_manage_acc_payable.php'],
        ['Receivable', 'his_admin_manage_acc_receivable.php'],
        ['Accounting records', 'his_admin_accounting_records.php'],
    ],
    'inventory' => [
        ['Pharmaceuticals', 'his_admin_pharm_inventory.php'],
        ['Assets', 'his_admin_equipments_inventory.php'],
    ],
    'theatre' => [
        ['Theatre patients', 'his_admin_add_theatre_patient.php'],
        ['Surgery records', 'his_admin_surgery_records.php'],
    ],
    'system' => [
        ['Manage users', 'his_admin_manage_nex_users.php'],
        ['Password resets', 'his_admin_manage_password_resets.php'],
        ['Classic dashboard', 'his_admin_dashboard.php'],
    ],
];
$sector_links = $links[$sector] ?? $links['clinical'];
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
                            <div>
                                <h4 class="page-title mb-0"><?php echo escape_output($label); ?> sector</h4>
                                <p class="text-muted mb-0 mt-1">NexClinic operating workspace</p>
                            </div>
                            <a href="his_admin_sector_picker.php" class="btn btn-outline-primary btn-sm">Switch sector</a>
                        </div>
                    </div>
                </div>
                <div class="row">
                    <?php foreach ($sector_links as $item) { ?>
                        <div class="col-md-4 mb-3">
                            <a href="<?php echo escape_output($item[1]); ?>" class="card-box d-block hms-hover-lift text-decoration-none">
                                <h4 class="header-title mb-1"><?php echo escape_output($item[0]); ?></h4>
                                <span class="text-muted">Open module</span>
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
