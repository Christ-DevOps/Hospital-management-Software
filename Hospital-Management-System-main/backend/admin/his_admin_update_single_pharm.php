<?php
/**
 * Compatibility redirect — page moved to sector folder.
 */
$qs = $_SERVER['QUERY_STRING'] ?? '';
$target = 'pharmacy/his_admin_update_single_pharm.php';
if ($qs !== '') { $target .= '?' . $qs; }
header('Location: ' . $target, true, 302);
exit;
