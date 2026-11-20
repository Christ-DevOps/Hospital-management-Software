<?php
$nex_admin_base = '../';
require __DIR__ . '/../_init.php';
require_once __DIR__ . '/../../assets/inc/nex_prescription.php';
require_once __DIR__ . '/../../assets/inc/notification_events.php';
check_login();
nex_rx_authorize('prescription.dispense');
if (function_exists('nex_set_active_sector')) {
    nex_set_active_sector('pharmacy');
}

$pres_id = (int) ($_GET['pres_id'] ?? $_POST['pres_id'] ?? 0);
$rx = $pres_id ? nex_rx_get($mysqli, $pres_id) : null;
$items = $rx ? nex_rx_items($mysqli, $pres_id) : [];
$statuses = nex_rx_statuses();

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['dispense_rx'])) {
    $notes = trim((string) ($_POST['dispense_notes'] ?? ''));
    $result = nex_rx_dispense($mysqli, $pres_id, $notes);
    if ($result['ok']) {
        $pat_id = !empty($rx->pat_id) ? (int) $rx->pat_id : nex_resolve_pat_id($mysqli, $rx->pres_pat_number ?? '');
        if ($pat_id) {
            notify_prescription_dispensed($mysqli, $pat_id, $rx->pres_number ?? '');
        }
        header('Location: his_admin_pharmacy_queue.php?ok=' . rawurlencode('Prescription dispensed. Stock updated.'));
        exit;
    }
    $err = $result['err'] ?? 'Dispense failed.';
    $rx = nex_rx_get($mysqli, $pres_id);
    $items = $rx ? nex_rx_items($mysqli, $pres_id) : [];
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
                                    <li class="breadcrumb-item"><a href="his_admin_pharmacy_queue.php">Dispense queue</a></li>
                                    <li class="breadcrumb-item active">Dispense</li>
                                </ol>
                            </div>
                            <h4 class="page-title">Dispense prescription</h4>
                        </div>
                    </div>
                </div>

                <?php if (!empty($err)) { ?>
                    <div class="alert alert-danger"><?php echo escape_output($err); ?></div>
                <?php } ?>

                <?php if (!$rx) { ?>
                    <div class="alert alert-warning">Prescription not found. <a href="his_admin_pharmacy_queue.php">Back to queue</a></div>
                <?php } else { ?>
                    <div class="card">
                        <div class="card-body">
                            <h4 class="header-title mb-3">#<?php echo escape_output($rx->pres_number); ?></h4>
                            <div class="row mb-3">
                                <div class="col-md-4"><strong>Patient:</strong> <?php echo escape_output($rx->pres_pat_name); ?></div>
                                <div class="col-md-4"><strong>Number:</strong> <?php echo escape_output($rx->pres_pat_number); ?></div>
                                <div class="col-md-4"><strong>Status:</strong> <?php echo escape_output($statuses[$rx->pres_status] ?? $rx->pres_status); ?></div>
                            </div>
                            <div class="mb-3"><strong>Ailment:</strong> <?php echo escape_output($rx->pres_pat_ailment); ?></div>

                            <div class="table-responsive mb-3">
                                <table class="table table-bordered table-sm">
                                    <thead>
                                        <tr>
                                            <th>Drug</th>
                                            <th>Prescribed</th>
                                            <th>Already dispensed</th>
                                            <th>Stock now</th>
                                            <th>Dosage / frequency / duration</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php if (!$items) { ?>
                                            <tr><td colspan="5" class="text-muted">No structured lines (legacy free-text Rx). Dispense will mark complete without stock movement.</td></tr>
                                        <?php } ?>
                                        <?php foreach ($items as $it) {
                                            $remain = (int) $it->qty - (int) $it->qty_dispensed;
                                            $stock = (int) $it->stock_qty;
                                            $low = $remain > $stock;
                                            ?>
                                            <tr class="<?php echo $low ? 'table-warning' : ''; ?>">
                                                <td><?php echo escape_output($it->drug_name); ?></td>
                                                <td><?php echo (int) $it->qty; ?></td>
                                                <td><?php echo (int) $it->qty_dispensed; ?></td>
                                                <td><?php echo $stock; ?><?php echo $low ? ' <span class="text-danger">(short)</span>' : ''; ?></td>
                                                <td>
                                                    <?php
                                                    echo escape_output(trim(implode(' · ', array_filter([
                                                        $it->dosage,
                                                        $it->frequency,
                                                        $it->duration,
                                                    ]))));
                                                    ?>
                                                </td>
                                            </tr>
                                        <?php } ?>
                                    </tbody>
                                </table>
                            </div>

                            <?php if (in_array($rx->pres_status, ['pending', 'partial'], true)) { ?>
                                <form method="post" onsubmit="return confirm('Dispense this prescription and deduct stock?');">
                                    <input type="hidden" name="csrf_token" value="<?php echo escape_output(generate_csrf_token()); ?>">
                                    <input type="hidden" name="pres_id" value="<?php echo (int) $pres_id; ?>">
                                    <div class="form-group">
                                        <label>Dispense notes (optional)</label>
                                        <textarea name="dispense_notes" class="form-control" rows="2"></textarea>
                                    </div>
                                    <button type="submit" name="dispense_rx" class="btn btn-success">Confirm dispense</button>
                                    <a href="his_admin_pharmacy_queue.php" class="btn btn-light">Cancel</a>
                                </form>
                            <?php } else { ?>
                                <div class="alert alert-info mb-0">This prescription is already <?php echo escape_output($rx->pres_status); ?>.</div>
                                <a href="his_admin_pharmacy_queue.php" class="btn btn-light mt-2">Back to queue</a>
                            <?php } ?>
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
