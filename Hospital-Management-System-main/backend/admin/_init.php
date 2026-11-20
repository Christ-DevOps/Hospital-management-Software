<?php
/**
 * Shared bootstrap for admin portal pages (root or sector subfolder).
 *
 * Usage at top of a page:
 *   $nex_admin_base = '../'; // only inside admin/{sector}/
 *   require __DIR__ . '/../_init.php'; // from sector
 *   // or: require __DIR__ . '/_init.php'; // from admin root
 */

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!isset($nex_admin_base)) {
    $nex_admin_base = '';
}
$GLOBALS['nex_admin_base'] = $nex_admin_base;

require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../security.php';
require_once __DIR__ . '/../assets/inc/nex_paths.php';
require_once __DIR__ . '/../assets/inc/nex_sectors.php';
require_once __DIR__ . '/assets/inc/checklogin.php';
