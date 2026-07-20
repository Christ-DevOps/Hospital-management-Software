<?php
/**
 * Fix remaining delete links and bind_param type mismatches.
 */
declare(strict_types=1);

$root = dirname(__DIR__);

$bindFixes = [
    'admin/his_admin_view_single_pres.php' => ["bind_param('ii',\$pres_number,\$pres_id)", "bind_param('si',\$pres_number,\$pres_id)"],
    'admin/his_admin_view_single_pharm.php' => ["bind_param('i',\$phar_bcode)", "bind_param('s',\$phar_bcode)"],
    'admin/his_admin_view_single_eqp.php' => ["bind_param('i',\$eqp_code)", "bind_param('s',\$eqp_code)"],
    'admin/his_admin_view_single_vendor.php' => ["bind_param('i',\$v_number)", "bind_param('s',\$v_number)"],
    'admin/his_admin_view_single_account.php' => ["bind_param('i',\$acc_number)", "bind_param('s',\$acc_number)"],
    'admin/his_admin_view_single_payable_account.php' => ["bind_param('i',\$acc_number)", "bind_param('s',\$acc_number)"],
    'admin/his_admin_update_single_payable_account.php' => ["bind_param('i',\$acc_number)", "bind_param('s',\$acc_number)"],
    'admin/his_admin_update_single_receivable_account.php' => ["bind_param('i',\$acc_number)", "bind_param('s',\$acc_number)"],
    'admin/his_admin_assaign_single_employee.php' => ["bind_param('i',\$doc_number)", "bind_param('s',\$doc_number)"],
    'admin/his_admin_transfer_single_employee.php' => ["bind_param('i',\$doc_number)", "bind_param('s',\$doc_number)"],
    'admin/his_admin_transfer_single_patient.php' => ["bind_param('i',\$pat_number)", "bind_param('s',\$pat_number)"],
    'admin/his_admin_update_single_employee.php' => ["bind_param('i',\$doc_number)", "bind_param('s',\$doc_number)"],
    'admin/his_admin_update_single_vendor.php' => ["bind_param('i',\$v_number)", "bind_param('s',\$v_number)"],
    'admin/his_admin_update_single_eqp.php' => ["bind_param('i',\$eqp_code)", "bind_param('s',\$eqp_code)"],
    'admin/his_admin_view_single_patient_surgery.php' => ["bind_param('i',\$s_number)", "bind_param('s',\$s_number)"],
    'admin/his_admin_update_single_patient_surgery.php' => ["bind_param('i',\$s_number)", "bind_param('s',\$s_number)"],
    'doc/his_doc_view_single_pres.php' => ["bind_param('ii',\$pres_number,\$pres_id)", "bind_param('si',\$pres_number,\$pres_id)"],
    'doc/his_doc_view_single_pharm.php' => ["bind_param('i',\$phar_bcode)", "bind_param('s',\$phar_bcode)"],
    'doc/his_doc_view_single_eqp.php' => ["bind_param('i',\$eqp_code)", "bind_param('s',\$eqp_code)"],
    'doc/his_doc_discharge_single_patient.php' => ["bind_param('i',\$pat_number)", "bind_param('s',\$pat_number)"],
    'doc/his_doc_transfer_single_patient.php' => ["bind_param('i',\$pat_number)", "bind_param('s',\$pat_number)"],
];

foreach ($bindFixes as $relative => [$from, $to]) {
    $file = $root . '/' . $relative;
    if (!file_exists($file)) {
        continue;
    }
    $content = file_get_contents($file);
    if (str_contains($content, $from)) {
        file_put_contents($file, str_replace($from, $to, $content));
        echo "Bind fix: $relative\n";
    }
}

$deleteLinkPattern = '/<a href="([^"]*\?([a-zA-Z_]+)=<\?php echo escape_output\((\$row->[^)]+)\); \?>)" class="badge badge-danger"><i class="[^"]*"><\/i> Delete<\/a>/';
$iterator = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($root . '/admin'));
$iterator2 = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($root . '/doc'));
foreach ([$iterator, $iterator2] as $it) {
    foreach ($it as $fileInfo) {
        if ($fileInfo->getExtension() !== 'php') {
            continue;
        }
        $path = $fileInfo->getPathname();
        $content = file_get_contents($path);
        $new = preg_replace_callback(
            $deleteLinkPattern,
            static fn(array $m): string => "<?php echo render_delete_button('{$m[2]}', {$m[3]}); ?>",
            $content,
            -1,
            $count
        );
        if ($count > 0) {
            file_put_contents($path, $new);
            echo "Delete links fixed ($count): $path\n";
        }
    }
}

echo "Secondary patch complete.\n";
