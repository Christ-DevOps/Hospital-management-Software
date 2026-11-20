<?php
$nex_admin_base = '../';
require __DIR__ . '/../_init.php';
require_once __DIR__ . '/../../assets/inc/nex_prescription.php';
require_once __DIR__ . '/../../assets/inc/nex_dashboard_ui.php';
check_login();
nex_rx_authorize('prescription.dispense');
if (function_exists('nex_set_active_sector')) {
    nex_set_active_sector('pharmacy');
}

$q = trim((string) ($_GET['q'] ?? ''));
if (isset($_GET['ok'])) {
    $success = trim((string) $_GET['ok']);
}
if (isset($_GET['err'])) {
    $err = trim((string) $_GET['err']);
}

$schema_ok = nex_rx_schema_ready($mysqli);
$pending = $schema_ok ? nex_rx_pending_list($mysqli, $q) : [];
$pending_count = $schema_ok ? nex_rx_pending_count($mysqli) : 0;
$statuses = nex_rx_statuses();
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
                                <h4 class="page-title mb-0">Pharmacy dispense queue</h4>
                                <p class="text-muted mb-0 mt-1" style="font-size:13px;">Prescriptions awaiting medication</p>
                            </div>
                            <a href="<?php echo escape_output(nex_admin_url('pharmacy/his_admin_view_pharmaceuticals.php')); ?>" class="btn btn-outline-secondary btn-sm">Stock catalogue</a>
                        </div>
                    </div>
                </div>

                <?php if (!empty($success)) { ?>
                    <div class="alert alert-success"><?php echo escape_output($success); ?></div>
                <?php } ?>
                <?php if (!empty($err)) { ?>
                    <div class="alert alert-danger"><?php echo escape_output($err); ?></div>
                <?php } ?>

                <?php if (!$schema_ok) { ?>
                    <div class="alert alert-warning">
                        Run <code>Database/migrations/2026_08_07_prescription_dispense.sql</code> to enable the dispense queue and stock deduction.
                    </div>
                <?php } else { ?>
                    <div class="row">
                        <?php nex_kpi_card('Awaiting dispense', $pending_count, 'hms-stat-amber', 'mdi-pill', 'Pending + partial'); ?>
                    </div>

                    <div class="card">
                        <div class="card-body">
                            <form method="get" class="form-inline mb-3">
                                <input type="text" name="q" value="<?php echo escape_output($q); ?>" class="form-control form-control-sm mr-2" placeholder="Patient / Rx number">
                                <button type="submit" class="btn btn-sm btn-primary">Search</button>
                            </form>
                            <div class="table-responsive">
                                <table class="table table-centered table-hover mb-0">
                                    <thead>
                                        <tr>
                                            <th>Rx #</th>
                                            <th>Patient</th>
                                            <th>Ailment</th>
                                            <th>Lines</th>
                                            <th>Status</th>
                                            <th>Issued</th>
                                            <th></th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php if (!$pending) { ?>
                                            <tr><td colspan="7" class="text-muted">No prescriptions waiting.</td></tr>
                                        <?php } ?>
                                        <?php foreach ($pending as $row) { ?>
                                            <tr>
                                                <td><?php echo escape_output($row->pres_number); ?></td>
                                                <td>
                                                    <?php echo escape_output($row->pres_pat_name); ?>
                                                    <div class="text-muted" style="font-size:12px;"><?php echo escape_output($row->pres_pat_number); ?></div>
                                                </td>
                                                <td><?php echo escape_output($row->pres_pat_ailment); ?></td>
                                                <td><?php echo (int) $row->item_count; ?></td>
                                                <td><?php echo escape_output($statuses[$row->pres_status] ?? $row->pres_status); ?></td>
                                                <td><?php echo escape_output((string) $row->pres_date); ?></td>
                                                <td class="text-right">
                                                    <a class="btn btn-primary btn-sm" href="his_admin_pharmacy_dispense.php?pres_id=<?php echo (int) $row->pres_id; ?>">
                                                        Dispense
                                                    </a>
                                                </td>
                                            </tr>
                                        <?php } ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                <?php } ?>
            </div>
        </div>
        <?php include __DIR__ . '/../assets/inc/footer.php'; ?>
    </div>
</div>
<script src="<?php echo escape_output(nex_admin_url('assets/js/vendor.min.js')); ?>"></script>
<script src="<?php echo escape_output(nex_admin_url('assets/js/app.min.js')); ?>"></script>
</body>
</html>
