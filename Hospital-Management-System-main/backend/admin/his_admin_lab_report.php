<?php
/**
 * Compatibility redirect — page moved to sector folder.
 */
$qs = $_SERVER['QUERY_STRING'] ?? '';
$target = 'laboratory/his_admin_lab_report.php';
if ($qs !== '') { $target .= '?' . $qs; }
header('Location: ' . $target, true, 302);
exit;
