<?php
function check_login()
{
    if (empty($_SESSION['doc_id'])) {
        $_SESSION['doc_id'] = '';
        $_SESSION['doc_number'] = '';
        secure_redirect('index.php');
    }
    if (file_exists(dirname(__DIR__, 3) . '/assets/inc/nex_sectors.php')) {
        include_once dirname(__DIR__, 3) . '/assets/inc/nex_sectors.php';
        if (function_exists('nex_enforce_sector_access')) {
            nex_enforce_sector_access();
        }
    }
}
