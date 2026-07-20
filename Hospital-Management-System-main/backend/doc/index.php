<?php
    include('../config.php');
    include('../security.php');
    if(isset($_POST['doc_login']))
    {
        if (!validate_csrf_token($_POST['csrf_token'])) {
            $err = "Invalid CSRF token.";
        } else {
            $doc_number = $_POST['doc_number'];
            $doc_pwd_input = $_POST['doc_pwd'];
            $stmt=$mysqli->prepare("SELECT doc_pwd, doc_id FROM his_docs WHERE doc_number=? ");
            $stmt->bind_param('s', $doc_number);
            $stmt->execute();
            $stmt -> bind_result($doc_pwd_hash ,$doc_id);
            $rs=$stmt->fetch();
            $stmt->close();
            if($rs && verify_password_hash($doc_pwd_input, $doc_pwd_hash))
            {
                session_regenerate_id(true);
                $_SESSION['doc_id'] = $doc_id;
                $_SESSION['doc_number'] = $doc_number;
                if (!password_verify($doc_pwd_input, $doc_pwd_hash)) {
                    migrate_password_if_legacy($mysqli, 'his_docs', 'doc_id', $doc_id, 'doc_pwd', $doc_pwd_input);
                }
                include_once('../assets/inc/nex_sectors.php');
                nex_bootstrap_session_from_legacy($mysqli, 'doc', (int) $doc_id);
                $assignments = $_SESSION['nex_assignments'] ?? [];
                if (count($assignments) > 1) {
                    header('location:his_doc_sector_picker.php');
                } else {
                    header('location:his_doc_dashboard.php');
                }
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
        <title>Hospital Management System — Doctor Login</title>
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <meta content="Hospital Management System doctor login" name="description" />
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
                <script>setTimeout(function () { swal("Success","<?php echo escape_output($success); ?>","success"); }, 100);</script>
        <?php } ?>
        <?php if(isset($err)) {?>
                <script>setTimeout(function () { swal("Failed","<?php echo escape_output($err); ?>","Failed"); }, 100);</script>
        <?php } ?>
    </head>
    <body class="authentication-bg authentication-bg-pattern hms-auth-page">
        <div class="hms-auth-stage">
            <a href="../../index.php" class="hms-auth-back"><i class="mdi mdi-arrow-left"></i> Back to homepage</a>

            <div class="hms-auth-card" data-hms-auth-card>
                <div class="hms-auth-brandmark">
                    <img src="assets/images/logo-dark.png" alt="Hospital Management System">
                    <span class="hms-auth-badge">Clinical Staff Portal</span>
                    <h1 class="hms-auth-title">Welcome back</h1>
                    <p class="hms-auth-sub">Sign in with your doctor number and password to continue.</p>
                </div>

                <form method="post" novalidate>
                    <input type="hidden" name="csrf_token" value="<?php echo generate_csrf_token(); ?>">
                    <div class="form-group">
                        <label for="docnumber">Doctor number</label>
                        <input class="form-control" name="doc_number" type="text" id="docnumber" required placeholder="Enter your doctor number" autocomplete="username">
                    </div>
                    <div class="form-group">
                        <label for="password">Password</label>
                        <div class="hms-auth-pass">
                            <input class="form-control" name="doc_pwd" type="password" required id="password" placeholder="Enter your password" autocomplete="current-password">
                            <button type="button" class="hms-auth-toggle" data-hms-pass-toggle aria-label="Show password"><i class="mdi mdi-eye-outline"></i></button>
                        </div>
                    </div>
                    <div class="form-group mb-0">
                        <button class="btn btn-primary btn-block" name="doc_login" type="submit">Log in</button>
                    </div>
                </form>

                <div class="hms-auth-links">
                    <a href="his_doc_reset_pwd.php">Forgot your password?</a>
                </div>
            </div>

            <p class="hms-auth-foot">&copy; <?php echo date('Y'); ?> Hospital Management System</p>
        </div>

        <script src="assets/js/vendor.min.js"></script>
        <script src="assets/js/app.min.js"></script>
        <script src="../assets/js/hms-motion.js"></script>
    </body>
</html>
