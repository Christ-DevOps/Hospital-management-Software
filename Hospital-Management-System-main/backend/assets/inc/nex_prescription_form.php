<?php
/**
 * Structured drug-line fields for prescribe forms.
 * Expects $mysqli. Optional: $rx_schema_ready (bool).
 */
if (!isset($rx_schema_ready)) {
    $rx_schema_ready = function_exists('nex_rx_schema_ready') ? nex_rx_schema_ready($mysqli) : false;
}
$rx_drugs = $rx_schema_ready ? nex_rx_list_drugs($mysqli) : [];
?>
<?php if (!$rx_schema_ready) { ?>
    <div class="alert alert-warning">
        Prescription dispense schema is not installed. Run
        <code>Database/migrations/2026_08_07_prescription_dispense.sql</code>
        so pharmacy can dispense from structured drug lines.
    </div>
<?php } else { ?>
    <div class="form-group">
        <label class="col-form-label">Medicines <span class="text-danger">*</span></label>
        <p class="text-muted mb-2" style="font-size:13px;">Select from pharmacy stock. Quantity is units to dispense.</p>
        <div class="table-responsive">
            <table class="table table-sm table-bordered mb-2" id="rx-drug-lines">
                <thead>
                    <tr>
                        <th style="min-width:220px;">Drug</th>
                        <th style="width:90px;">Qty</th>
                        <th>Dosage</th>
                        <th>Frequency</th>
                        <th>Duration</th>
                        <th style="width:40px;"></th>
                    </tr>
                </thead>
                <tbody>
                    <tr class="rx-line">
                        <td>
                            <select name="drug_phar_id[]" class="form-control form-control-sm" required>
                                <option value="">— Select —</option>
                                <?php foreach ($rx_drugs as $d) { ?>
                                    <option value="<?php echo (int) $d->phar_id; ?>">
                                        <?php echo escape_output($d->phar_name); ?>
                                        (stock: <?php echo (int) $d->phar_qty; ?>)
                                    </option>
                                <?php } ?>
                            </select>
                        </td>
                        <td><input type="number" min="1" name="drug_qty[]" value="1" class="form-control form-control-sm" required></td>
                        <td><input type="text" name="drug_dosage[]" class="form-control form-control-sm" placeholder="e.g. 500mg"></td>
                        <td><input type="text" name="drug_frequency[]" class="form-control form-control-sm" placeholder="e.g. 2× daily"></td>
                        <td><input type="text" name="drug_duration[]" class="form-control form-control-sm" placeholder="e.g. 5 days"></td>
                        <td class="text-center align-middle">
                            <button type="button" class="btn btn-link text-danger p-0 rx-remove-line" title="Remove" style="display:none;">&times;</button>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
        <button type="button" class="btn btn-outline-secondary btn-sm" id="rx-add-line">+ Add medicine</button>
    </div>
    <div class="form-group">
        <label for="pres_notes" class="col-form-label">Additional instructions (optional)</label>
        <textarea class="form-control" name="pres_notes" id="pres_notes" rows="2" placeholder="Counseling notes for pharmacy / patient"></textarea>
    </div>
    <script>
    (function () {
        var table = document.getElementById('rx-drug-lines');
        if (!table) return;
        var tbody = table.querySelector('tbody');
        var addBtn = document.getElementById('rx-add-line');
        function refreshRemove() {
            var rows = tbody.querySelectorAll('tr.rx-line');
            rows.forEach(function (row) {
                var btn = row.querySelector('.rx-remove-line');
                if (btn) btn.style.display = rows.length > 1 ? '' : 'none';
            });
        }
        addBtn && addBtn.addEventListener('click', function () {
            var first = tbody.querySelector('tr.rx-line');
            if (!first) return;
            var clone = first.cloneNode(true);
            clone.querySelectorAll('input').forEach(function (el) {
                if (el.type === 'number') el.value = '1';
                else el.value = '';
            });
            var sel = clone.querySelector('select');
            if (sel) sel.selectedIndex = 0;
            tbody.appendChild(clone);
            refreshRemove();
        });
        tbody.addEventListener('click', function (e) {
            if (!e.target.classList.contains('rx-remove-line')) return;
            var row = e.target.closest('tr.rx-line');
            if (row && tbody.querySelectorAll('tr.rx-line').length > 1) {
                row.remove();
                refreshRemove();
            }
        });
        refreshRemove();
    })();
    </script>
<?php } ?>
