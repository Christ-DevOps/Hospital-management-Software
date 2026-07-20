<?php
session_start();
include('../config.php');
include('../security.php');
include('assets/inc/checklogin.php');
check_login();
header('Location: his_admin_manage_password_resets.php');
exit;
