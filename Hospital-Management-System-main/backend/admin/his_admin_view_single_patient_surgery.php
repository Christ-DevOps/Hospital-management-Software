<?php
/**
 * Compatibility redirect — page moved to sector folder.
 */
$qs = $_SERVER['QUERY_STRING'] ?? '';
$target = 'theatre/his_admin_view_single_patient_surgery.php';
if ($qs !== '') { $target .= '?' . $qs; }
header('Location: ' . $target, true, 302);
exit;
