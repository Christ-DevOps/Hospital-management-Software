<?php
$qs = $_SERVER['QUERY_STRING'] ?? '';
$target = '../his_admin_account.php';
if ($qs !== '') {
    $target .= '?' . $qs;
}
header('Location: ' . $target, true, 302);
exit;
