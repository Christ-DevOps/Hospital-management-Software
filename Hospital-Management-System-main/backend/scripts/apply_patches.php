<?php
/**
 * CLI patcher for HMS bulk security fixes.
 * Usage: php backend/scripts/apply_patches.php
 */
declare(strict_types=1);

$root = dirname(__DIR__);
$admin = $root . '/admin';
$doc = $root . '/doc';

$adminAuthRequired = [
    'his_admin_register.php',
    'his_admin_add_vendor.php',
    'his_admin_add_pharm_cat.php',
    'his_admin_add_single_pres.php',
    'his_admin_add_single_patient_vitals.php',
    'his_admin_add_single_patient_medical_record.php',
    'his_admin_add_single_lab_test.php',
    'his_admin_add_single_lab_result.php',
    'his_admin_add_single_employee_vitals.php',
    'his_admin_add_single_employee_payroll.php',
    'his_admin_assaign_single_employee.php',
    'his_admin_discharge_single_patient.php',
];

$deleteConfigs = [
    [$admin . '/his_admin_manage_patient.php', 'delete', 'delete from his_patients where pat_id=?', 'i', 'Patient record deleted'],
    [$admin . '/his_admin_manage_employee.php', 'delete', 'delete from his_docs where doc_id=?', 'i', 'Employee record deleted'],
    [$admin . '/his_admin_manage_equipment.php', 'delete_eqp', 'delete from his_equipments where eqp_code=?', 's', 'Equipment deleted'],
    [$admin . '/his_admin_manage_lab_equipment.php', 'delete_eqp', 'delete from his_equipments where eqp_code=?', 's', 'Equipment deleted'],
    [$admin . '/his_admin_manage_vendor.php', 'delete_vendor_number', 'delete from his_vendor where v_number=?', 's', 'Vendor record deleted'],
    [$admin . '/his_admin_manage_presc.php', 'delete_pres_number', 'delete from his_prescriptions where pres_number=?', 's', 'Prescription deleted'],
    [$admin . '/his_admin_manage_pharmaceuticals.php', 'delete_pharm_name', 'delete from his_pharmaceuticals where phar_name=?', 's', 'Pharmaceutical deleted'],
    [$admin . '/his_admin_manage_pharm_cat.php', 'delete_pharm_cat', 'delete from his_pharmaceuticals_categories where pharm_cat_id=?', 'i', 'Category deleted'],
    [$admin . '/his_admin_manage_medical_record.php', 'delete_mdr_number', 'delete from his_medical_records where mdr_number=?', 's', 'Medical record deleted'],
    [$admin . '/his_admin_manage_payrolls.php', 'delete_pay_number', 'delete from his_payrolls where pay_number=?', 's', 'Payroll deleted'],
    [$admin . '/his_admin_generate_payrolls.php', 'delete_pay_number', 'delete from his_payrolls where pay_number=?', 's', 'Payroll deleted'],
    [$admin . '/his_admin_manage_acc_payable.php', 'delete_account', 'delete from his_accounts where acc_id=?', 'i', 'Account deleted'],
    [$admin . '/his_admin_manage_acc_receivable.php', 'delete_account', 'delete from his_accounts where acc_id=?', 'i', 'Account deleted'],
    [$admin . '/his_admin_manage_theatre_patient.php', 'delete_s_number', 'delete from his_surgery where s_number=?', 's', 'Surgery record deleted'],
    [$admin . '/his_admin_surgery_records.php', 'delete_s_number', 'delete from his_surgery where s_number=?', 's', 'Surgery record deleted'],
    [$admin . '/his_admin_discharge_patient.php', 'delete', 'delete from his_patients where pat_id=?', 'i', 'Patient record deleted'],
    [$admin . '/his_admin_patient_transfer.php', 'delete', 'delete from his_patient_transfers where tr_id=?', 'i', 'Transfer record deleted'],
    [$admin . '/his_admin_transfer_employee.php', 'delete', 'delete from his_patient_transfers where tr_id=?', 'i', 'Transfer record deleted'],
    [$admin . '/his_admin_assaign_dept.php', 'delete', 'delete from his_docs where doc_id=?', 'i', 'Employee record deleted'],
    [$admin . '/his_admin_add_theatre_patient.php', 'delete', 'delete from his_surgery where s_id=?', 'i', 'Surgery record deleted'],
    [$admin . '/his_admin_medical_records.php', 'delete_mdr_number', 'delete from his_medical_records where mdr_number=?', 's', 'Medical record deleted'],
    [$doc . '/his_doc_manage_patient.php', 'delete', 'delete from his_patients where pat_id=?', 'i', 'Patient record deleted'],
    [$doc . '/his_doc_manage_presc.php', 'delete_pres_number', 'delete from his_prescriptions where pres_number=?', 's', 'Prescription deleted'],
    [$doc . '/his_doc_manage_pharmaceuticals.php', 'delete_pharm_name', 'delete from his_pharmaceuticals where phar_name=?', 's', 'Pharmaceutical deleted'],
    [$doc . '/his_doc_manage_pharm_cat.php', 'delete_pharm_cat', 'delete from his_pharmaceuticals_categories where pharm_cat_id=?', 'i', 'Category deleted'],
    [$doc . '/his_doc_discharge_patient.php', 'delete', 'delete from his_patients where pat_id=?', 'i', 'Patient record deleted'],
    [$doc . '/his_doc_patient_transfer.php', 'delete', 'delete from his_patient_transfers where tr_id=?', 'i', 'Transfer record deleted'],
    [$doc . '/his_doc_view_payrolls.php', 'delete_pay_number', 'delete from his_payrolls where pay_number=?', 's', 'Payroll deleted'],
];

function patch_auth(string $file): void {
    $content = file_get_contents($file);
    if (str_contains($content, 'checklogin.php')) {
        return;
    }
    $needle = "include('../security.php');";
    $replacement = "include('../security.php');\n  include('assets/inc/checklogin.php');\n  check_login();";
    if (!str_contains($content, $needle)) {
        return;
    }
    file_put_contents($file, str_replace($needle, $replacement, $content));
    echo "Auth patched: $file\n";
}

function patch_delete_block(string $file, string $postKey, string $sql, string $bindType, string $message): void {
    $content = file_get_contents($file);
    if (str_contains($content, "process_post_delete(\$mysqli, '$postKey'")) {
        return;
    }
    $block = <<<PHP
  \$deleteResult = process_post_delete(\$mysqli, '$postKey', '$sql', '$bindType', [], '$message');
  if (\$deleteResult !== null) {
      if (isset(\$deleteResult['success'])) {
          \$success = \$deleteResult['success'];
      } else {
          \$err = \$deleteResult['err'];
      }
  }

PHP;
    $pattern = '/if\s*\(\s*isset\(\$_GET\[[\'"]' . preg_quote($postKey, '/') . '[\'"]\]\s*\)\s*\)\s*\{.*?\n\s*\}/s';
    if (preg_match($pattern, $content)) {
        $content = preg_replace($pattern, rtrim($block), $content, 1);
        file_put_contents($file, $content);
        echo "Delete block patched: $file\n";
    }
}

function patch_delete_link(string $file, string $postKey): void {
    $content = file_get_contents($file);
    $pattern = '/<a href="[^"]*\?' . preg_quote($postKey, '/') . '=\<\?php echo escape_output\((\$row->[^)]+)\); \?\>" class="badge badge-danger"><i class="[^"]*"><\/i> Delete<\/a>/';
    $new = preg_replace($pattern, "<?php echo render_delete_button('$postKey', $1); ?>", $content, -1, $count);
    if ($count > 0) {
        file_put_contents($file, $new);
        echo "Delete links patched ($count) in $file\n";
    }
}

foreach ($adminAuthRequired as $name) {
    patch_auth($admin . '/' . $name);
}

foreach ($deleteConfigs as [$file, $key, $sql, $type, $message]) {
    if (!file_exists($file)) {
        continue;
    }
    patch_delete_block($file, $key, $sql, $type, $message);
    patch_delete_link($file, $key);
}

echo "Patch complete.\n";
