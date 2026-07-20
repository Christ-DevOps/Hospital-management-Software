<?php
function check_login()
{
    if (empty($_SESSION['ad_id'])) {
        $_SESSION['ad_id'] = '';
        secure_redirect('index.php');
    }
}
