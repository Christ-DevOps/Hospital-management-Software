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

if ($sector === 'reception') {
    header('Location: reception/his_admin_reception_dashboard.php');
    exit;
}

$modules = nex_sector_modules();
$label = $modules[$sector]['label'] ?? ucfirst($sector);

$links = [
    'clinical' => [
        ['Register patient', 'reception/his_admin_register_patient.php'],
        ['View patients', 'clinical/his_admin_view_patients.php'],
        ['Add prescription', 'clinical/his_admin_add_presc.php'],
        ['View prescriptions', 'clinical/his_admin_view_presc.php'],
        ['Medical records', 'clinical/his_admin_medical_records.php'],
    ],
    'reception' => [
        ['Reception dashboard', 'reception/his_admin_reception_dashboard.php'],
        ['Appointments', 'reception/his_admin_appointments.php'],
        ['Book appointment', 'reception/his_admin_add_appointment.php'],
        ['Register patient', 'reception/his_admin_register_patient.php'],
    ],
    'pharmacy' => [
        ['Dispense queue', 'pharmacy/his_admin_pharmacy_queue.php'],
        ['Pharmaceuticals', 'pharmacy/his_admin_view_pharmaceuticals.php'],
        ['View prescriptions', 'clinical/his_admin_view_presc.php'],
        ['Pharm inventory', 'inventory/his_admin_pharm_inventory.php'],
    ],
    'laboratory' => [
        ['Lab tests', 'laboratory/his_admin_patient_lab_test.php'],
        ['Lab results', 'laboratory/his_admin_patient_lab_result.php'],
        ['Lab reports', 'laboratory/his_admin_lab_report.php'],
    ],
    'hr' => [
        ['Employees', 'hr/his_admin_view_employee.php'],
        ['Assign department', 'hr/his_admin_assaign_dept.php'],
    ],
    'payroll' => [
        ['Add payroll', 'payroll/his_admin_add_payroll.php'],
        ['Manage payrolls', 'payroll/his_admin_manage_payrolls.php'],
    ],
    'accounting' => [
        ['Payable', 'accounting/his_admin_manage_acc_payable.php'],
        ['Receivable', 'accounting/his_admin_manage_acc_receivable.php'],
        ['Accounting records', 'accounting/his_admin_accounting_records.php'],
    ],
    'inventory' => [
        ['Pharmaceuticals', 'inventory/his_admin_pharm_inventory.php'],
        ['Assets', 'inventory/his_admin_equipments_inventory.php'],
    ],
    'theatre' => [
        ['Theatre patients', 'theatre/his_admin_add_theatre_patient.php'],
        ['Surgery records', 'theatre/his_admin_surgery_records.php'],
    ],
    'system' => [
        ['Manage users', 'system/his_admin_manage_nex_users.php'],
        ['Password resets', 'system/his_admin_manage_password_resets.php'],
        ['Create admin', 'his_admin_register.php'],
        ['My account', 'his_admin_account.php'],
        ['Classic dashboard', 'his_admin_dashboard.php'],
    ],
];

$sector_links = $links[$sector] ?? $links['clinical'];
require_once __DIR__ . '/../assets/inc/nex_paths.php';
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
                                <p class="text-muted mb-0 mt-1">NexClinic operating workspace · <?php echo escape_output($sector); ?>/</p>
                            </div>
                            <a href="his_admin_sector_picker.php" class="btn btn-outline-primary btn-sm">Switch sector</a>
                        </div>
                    </div>
                </div>
                <div class="row">
                    <?php foreach ($sector_links as $item) { ?>
                        <div class="col-md-4 mb-3">
                            <a href="<?php echo escape_output(nex_admin_url($item[1])); ?>" class="card-box d-block hms-hover-lift text-decoration-none">
                                <h4 class="header-title mb-1"><?php echo escape_output($item[0]); ?></h4>
                                <span class="text-muted"><?php echo escape_output($item[1]); ?></span>
                            </a>
                        </div>
                    <?php } ?>
                </div>
            </div>
        </div>
        <?php include('assets/inc/footer.php'); ?>
    </div>
</div>
<script src="<?php echo escape_output(nex_admin_url('assets/js/vendor.min.js')); ?>"></script>
<script src="<?php echo escape_output(nex_admin_url('assets/js/app.min.js')); ?>"></script>
</body>
</html>
