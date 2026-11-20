<?php
    include('../config.php');
    include('../security.php');
    if(isset($_POST['admin_login']))
    {
        if (!validate_csrf_token($_POST['csrf_token'])) {
            $err = "Invalid CSRF token.";
        } else {
            $ad_email=$_POST['ad_email'];
            $ad_pwd_input=$_POST['ad_pwd'];
            $stmt=$mysqli->prepare("SELECT ad_pwd , ad_id FROM his_admin WHERE ad_email=? ");
            $stmt->bind_param('s',$ad_email);
            $stmt->execute();
            $stmt -> bind_result($ad_pwd_hash,$ad_id);
            $rs=$stmt->fetch();
            $stmt->close();
            if($rs && verify_password_hash($ad_pwd_input, $ad_pwd_hash))
            {
                session_regenerate_id(true);
                $_SESSION['ad_id']=$ad_id;
                if (!password_verify($ad_pwd_input, $ad_pwd_hash)) {
                    migrate_password_if_legacy($mysqli, 'his_admin', 'ad_id', $ad_id, 'ad_pwd', $ad_pwd_input);
                }
                // Axis 1: platform tier (defaults to admin if column not migrated yet)
                $_SESSION['nex_platform_tier'] = 'admin';
                try {
                    $tier_q = $mysqli->prepare('SELECT platform_tier FROM his_admin WHERE ad_id=? LIMIT 1');
                    if ($tier_q) {
                        $tier_q->bind_param('i', $ad_id);
                        $tier_q->execute();
                        $tier_q->bind_result($pt);
                        if ($tier_q->fetch() && $pt) {
                            $_SESSION['nex_platform_tier'] = $pt;
                        }
                        $tier_q->close();
                    }
                } catch (\Throwable $e) {
                    // platform_tier column not migrated on this database yet; keep default tier.
                }
                if (file_exists(__DIR__ . '/../assets/inc/nex_sectors.php')) {
                    include_once(__DIR__ . '/../assets/inc/nex_sectors.php');
                    if (function_exists('nex_bootstrap_session_from_legacy')) {
                        nex_bootstrap_session_from_legacy($mysqli, 'admin', (int) $ad_id);
                    }
                    if (function_exists('nex_audit')) {
                        nex_audit($mysqli, 'login.admin', 'his_admin', (string) $ad_id, null);
                    }
                }
                header('location:his_admin_dashboard.php');
                exit;
            }
            else
            {
                $err = "Access Denied Please Check Your Credentials";
            }
        }
    }
?>
<!DOCTYPE html>
<html lang="en">
<head>
        <meta charset="utf-8" />
        <title>NexClinic — Admin Login</title>
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <meta content="NexClinic administrator login" name="description" />
        <meta http-equiv="X-UA-Compatible" content="IE=edge" />
        <meta name="theme-color" content="#2E37A4" />
        <link rel="shortcut icon" href="assets/images/favicon.ico">
        <link href="assets/css/bootstrap.min.css" rel="stylesheet" type="text/css" />
        <link href="assets/css/icons.min.css" rel="stylesheet" type="text/css" />
        <link href="assets/css/app.min.css" rel="stylesheet" type="text/css" />
        <link rel="preconnect" href="https://fonts.googleapis.com">
        <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
        <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
        <link href="../assets/css/hms-theme.css" rel="stylesheet" type="text/css" />
        <link href="../assets/css/hms-auth.css" rel="stylesheet" type="text/css" />
        <script src="assets/js/swal.js"></script>
        <?php if(isset($success)) {?>
                <script>
                            setTimeout(function ()
                            {
                                swal("Success","<?php echo escape_output($success); ?>","success");
                            },
                                100);
                </script>
        <?php } ?>
        <?php if(isset($err)) {?>
                <script>
                            setTimeout(function ()
                            {
                                swal("Failed","<?php echo escape_output($err); ?>","Failed");
                            },
                                100);
                </script>
        <?php } ?>
    </head>
    <body class="authentication-bg authentication-bg-pattern hms-auth-page">
        <div class="hms-auth-stage">
            <a href="../../index.php" class="hms-auth-back"><i class="mdi mdi-arrow-left"></i> Back to homepage</a>

            <div class="hms-auth-card" data-hms-auth-card>
                <div class="hms-auth-brandmark">
                    <img src="assets/images/logo-dark.png" alt="NexClinic">
                    <span class="hms-auth-badge">NexClinic · Admin</span>
                    <h1 class="hms-auth-title">Welcome back</h1>
                    <p class="hms-auth-sub">Sign in with your admin email and password to continue.</p>
                </div>

                <form method="post" novalidate>
                    <input type="hidden" name="csrf_token" value="<?php echo generate_csrf_token(); ?>">
                    <div class="form-group">
                        <label for="emailaddress">Email address</label>
                        <input class="form-control" name="ad_email" type="email" id="emailaddress" required placeholder="Enter your email" autocomplete="username">
                    </div>
                    <div class="form-group">
                        <label for="password">Password</label>
                        <div class="hms-auth-pass">
                            <input class="form-control" name="ad_pwd" type="password" required id="password" placeholder="Enter your password" autocomplete="current-password">
                            <button type="button" class="hms-auth-toggle" data-hms-pass-toggle aria-label="Show password"><i class="mdi mdi-eye-outline"></i></button>
                        </div>
                    </div>
                    <div class="form-group mb-0">
                        <button class="btn btn-primary btn-block" name="admin_login" type="submit">Log in</button>
                    </div>
                </form>

                <div class="hms-auth-links">
                    <a href="his_admin_pwd_reset.php">Forgot your password?</a>
                    <span class="mx-1">·</span>
                    <a href="his_admin_register.php">Create admin account</a>
                </div>
            </div>

            <p class="hms-auth-foot">&copy; <?php echo date('Y'); ?> NexClinic</p>
        </div>

        <script src="assets/js/vendor.min.js"></script>
        <script src="assets/js/app.min.js"></script>
        <script src="../assets/js/hms-motion.js"></script>
    </body>
</html>
