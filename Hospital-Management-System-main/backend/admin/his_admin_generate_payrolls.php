<?php
/**
 * Compatibility redirect — page moved to sector folder.
 */
$qs = $_SERVER['QUERY_STRING'] ?? '';
$target = 'payroll/his_admin_generate_payrolls.php';
if ($qs !== '') { $target .= '?' . $qs; }
header('Location: ' . $target, true, 302);
exit;
