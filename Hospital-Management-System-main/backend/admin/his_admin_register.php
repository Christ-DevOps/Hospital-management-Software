<!--Server side code to handle  sign up-->
<?php
	session_start();
	include('../config.php');
    include('../security.php');
    // Public create-admin screen (seeded admins can also open it while logged in).
    // Do not require check_login() — otherwise the page only redirects to login under php -S / any host.
		if(isset($_POST['admin_sup']))
		{
			if (!validate_csrf_token($_POST['csrf_token'] ?? '')) {
				$err = 'Invalid CSRF token.';
			} else {
			$ad_fname=$_POST['ad_fname'];
			$ad_lname=$_POST['ad_lname'];
			$ad_email=$_POST['ad_email'];
			$ad_pwd=password_hash($_POST['ad_pwd'], PASSWORD_DEFAULT);
			$query="insert into his_admin (ad_fname, ad_lname, ad_email, ad_pwd) values(?,?,?,?)";
			$stmt = $mysqli->prepare($query);
			$rc=$stmt->bind_param('ssss', $ad_fname, $ad_lname, $ad_email, $ad_pwd);
			$stmt->execute();
			if($stmt)
			{
				$success = "Created Account Proceed To Log In";
			}
			else {
				$err = "Please Try Again Or Try Later";
			}
			$stmt->close();
			}
		}
?>
<!--End Server Side-->
<!--End Login-->
<!DOCTYPE html>
<html lang="en">
<head>
        <meta charset="utf-8" />
        <title>Hospital Management System — Create Admin</title>
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <meta content="Create a new administrator account" name="description" />
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
            <a href="index.php" class="hms-auth-back"><i class="mdi mdi-arrow-left"></i> Back to login</a>

            <div class="hms-auth-card is-wide" data-hms-auth-card>
                <div class="hms-auth-brandmark">
                    <img src="assets/images/logo-dark.png" alt="NexClinic">
                    <span class="hms-auth-badge">NexClinic · Admin</span>
                    <h1 class="hms-auth-title">Create admin account</h1>
                    <p class="hms-auth-sub">Add a new administrator. It only takes a minute.</p>
                </div>

                <form method="post" novalidate>
                    <input type="hidden" name="csrf_token" value="<?php echo generate_csrf_token(); ?>">

                    <div class="hms-auth-row">
                        <div class="form-group">
                            <label for="ad_fname">First name</label>
                            <input class="form-control" type="text" name="ad_fname" id="ad_fname" placeholder="First name" required autocomplete="given-name">
                        </div>
                        <div class="form-group">
                            <label for="ad_lname">Last name</label>
                            <input class="form-control" type="text" name="ad_lname" id="ad_lname" placeholder="Last name" required autocomplete="family-name">
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="emailaddress">Email address</label>
                        <input class="form-control" name="ad_email" type="email" id="emailaddress" required placeholder="Enter email" autocomplete="email">
                    </div>

                    <div class="form-group">
                        <label for="password">Password</label>
                        <div class="hms-auth-pass">
                            <input class="form-control" name="ad_pwd" type="password" required id="password" placeholder="Create a password" autocomplete="new-password">
                            <button type="button" class="hms-auth-toggle" data-hms-pass-toggle aria-label="Show password"><i class="mdi mdi-eye-outline"></i></button>
                        </div>
                    </div>

                    <div class="form-group mb-0">
                        <button class="btn btn-primary btn-block" name="admin_sup" type="submit">Create account</button>
                    </div>
                </form>

                <div class="hms-auth-links">
                    Already have an account? <a href="index.php">Sign in</a>
                </div>
            </div>

            <p class="hms-auth-foot">&copy; <?php echo date('Y'); ?> Hospital Management System</p>
        </div>

        <script src="assets/js/vendor.min.js"></script>
        <script src="assets/js/app.min.js"></script>
        <script src="../assets/js/hms-motion.js"></script>
    </body>
</html>
