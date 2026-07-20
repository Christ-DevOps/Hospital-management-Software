<?php
function check_login()
{
    if (empty($_SESSION['doc_id'])) {
        $_SESSION['doc_id'] = '';
        $_SESSION['doc_number'] = '';
        secure_redirect('index.php');
    }
}
