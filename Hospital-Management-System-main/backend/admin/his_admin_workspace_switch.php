<?php
/**
 * Switch the active workspace, then land on that workspace's homepage.
 * nex_workspace_set() refuses any workspace the user's sector assignments or
 * platform tier do not grant, so this is not a way around authorization.
 */
require __DIR__ . '/_init.php';
require_once __DIR__ . '/../assets/inc/nex_workspace.php';
check_login();

$requested = trim((string) ($_GET['workspace'] ?? ''));

if ($requested === '' || !nex_workspace_set($requested)) {
    if (function_exists('nex_audit')) {
        global $mysqli;
        nex_audit($mysqli, 'workspace.switch.denied', 'workspace', $requested);
    }
    secure_redirect('his_admin_dashboard.php');
}

if (function_exists('nex_audit')) {
    global $mysqli;
    nex_audit($mysqli, 'workspace.switch', 'workspace', $requested);
}

$meta = nex_workspace_meta($requested);
secure_redirect($meta['home']);
