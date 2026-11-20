<?php
/**
 * Auth page lives at admin root (public asset paths). Keep sector URL working.
 */
$qs = $_SERVER['QUERY_STRING'] ?? '';
$target = '../his_admin_pwd_reset.php';
if ($qs !== '') {
    $target .= '?' . $qs;
}
header('Location: ' . $target, true, 302);
exit;
