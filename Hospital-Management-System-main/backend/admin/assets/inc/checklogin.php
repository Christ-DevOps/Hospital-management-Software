<?php
function check_login()
{
    if (empty($_SESSION['ad_id'])) {
        $_SESSION['ad_id'] = '';
        // Sector subfolders set $nex_admin_base = '../' so login resolves to admin/index.php
        $login = (!empty($GLOBALS['nex_admin_base']) ? $GLOBALS['nex_admin_base'] : '') . 'index.php';
        secure_redirect($login);
    }
    if (file_exists(dirname(__DIR__, 3) . '/assets/inc/nex_sectors.php')) {
        include_once dirname(__DIR__, 3) . '/assets/inc/nex_sectors.php';
        if (function_exists('nex_enforce_sector_access')) {
            nex_enforce_sector_access();
        }
    }
}
