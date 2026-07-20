<?php
	session_start();
	include('../config.php');
    include('../security.php');
    send_security_headers();

    if(isset($_POST['reset_pwd']))
    {
        $email = trim($_POST['email'] ?? '');
        $check = $mysqli->prepare('SELECT doc_id FROM his_docs WHERE doc_email = ?');
        $check->bind_param('s', $email);
        $check->execute();
        $check->store_result();
        if ($check->num_rows === 0) {
            $err = 'No account found for that email address.';
        } else {
            $raw_token = bin2hex(random_bytes(32));
            $token_hash = password_hash($raw_token, PASSWORD_DEFAULT);
            $status = 'Pending';
            $query = 'INSERT INTO his_pwdresets (email, token, status) VALUES (?,?,?)';
            $stmt = $mysqli->prepare($query);
            $stmt->bind_param('sss', $email, $token_hash, $status);
            if ($stmt->execute()) {
                $success = 'If the email exists, a reset request was submitted. An administrator will review it.';
            } else {
                $err = 'Please try again later.';
            }
            $stmt->close();
        }
        $check->close();
    }
?>
<!DOCTYPE html>
<html lang="en">
<head>
        <meta charset="utf-8" />
        <title>Hospital Management System — Doctor Password Reset</title>
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <meta content="Request a doctor password reset" name="description" />
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
                    <span class="hms-auth-badge">Clinical Staff</span>
                    <h1 class="hms-auth-title">Reset your password</h1>
                    <p class="hms-auth-sub">Enter your email and an administrator will review the reset request.</p>
                </div>

                <form method="post" novalidate>
                    <input type="hidden" name="csrf_token" value="<?php echo generate_csrf_token(); ?>">
                    <div class="form-group">
                        <label for="emailaddress">Email address</label>
                        <input class="form-control" name="email" type="email" id="emailaddress" required placeholder="Enter your email" autocomplete="email">
                    </div>
                    <div class="form-group mb-0">
                        <button name="reset_pwd" class="btn btn-primary btn-block" type="submit">Request reset</button>
                    </div>
                </form>

                <div class="hms-auth-links">
                    <a href="index.php">Back to login</a>
                </div>
            </div>

            <p class="hms-auth-foot">&copy; <?php echo date('Y'); ?> Hospital Management System</p>
        </div>

        <script src="assets/js/vendor.min.js"></script>
        <script src="assets/js/app.min.js"></script>
        <script src="../assets/js/hms-motion.js"></script>
    </body>
</html>
