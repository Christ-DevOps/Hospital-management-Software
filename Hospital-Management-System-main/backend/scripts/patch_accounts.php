<?php
declare(strict_types=1);

$files = [
    __DIR__ . '/../admin/his_admin_account.php',
    __DIR__ . '/../doc/his_doc_update-account.php',
];

$accountPatch = <<<'PHP'
		if(isset($_POST['update_profile']))
		{
			$ad_fname=$_POST['ad_fname'];
			$ad_lname=$_POST['ad_lname'];
			$ad_id=$_SESSION['ad_id'];
            $ad_email=$_POST['ad_email'];
            $ad_dpic = null;
            if (!empty($_FILES["ad_dpic"]["name"])) {
                $upload = handle_profile_upload('ad_dpic', __DIR__ . '/assets/images/users');
                if (isset($upload['err'])) {
                    $err = $upload['err'];
                } else {
                    $ad_dpic = $upload['filename'];
                }
            }
            if (!isset($err)) {
                if ($ad_dpic !== null) {
                    $query="UPDATE his_admin SET ad_fname=?, ad_lname=?, ad_email=?, ad_dpic=? WHERE ad_id = ?";
                    $stmt = $mysqli->prepare($query);
                    $stmt->bind_param('ssssi', $ad_fname, $ad_lname, $ad_email, $ad_dpic, $ad_id);
                } else {
                    $query="UPDATE his_admin SET ad_fname=?, ad_lname=?, ad_email=? WHERE ad_id = ?";
                    $stmt = $mysqli->prepare($query);
                    $stmt->bind_param('sssi', $ad_fname, $ad_lname, $ad_email, $ad_id);
                }
                if ($stmt->execute()) {
                    $success = "Profile Updated";
                } else {
                    $err = "Please Try Again Or Try Later";
                }
                $stmt->close();
            }
        }
        if(isset($_POST['update_pwd']))
		{
            $ad_id=$_SESSION['ad_id'];
            $current_pwd = $_POST['current_pwd'] ?? '';
            $new_pwd = $_POST['ad_pwd'] ?? '';
            $stmt=$mysqli->prepare("SELECT ad_pwd FROM his_admin WHERE ad_id=?");
            $stmt->bind_param('i', $ad_id);
            $stmt->execute();
            $stmt->bind_result($existing_hash);
            $stmt->fetch();
            $stmt->close();
            if (!verify_password_hash($current_pwd, $existing_hash)) {
                $err = 'Current password is incorrect.';
            } elseif (strlen($new_pwd) < 8) {
                $err = 'New password must be at least 8 characters.';
            } else {
                $ad_pwd=password_hash($new_pwd, PASSWORD_DEFAULT);
                $query="UPDATE his_admin SET ad_pwd =? WHERE ad_id = ?";
                $stmt = $mysqli->prepare($query);
                $stmt->bind_param('si', $ad_pwd, $ad_id);
                if ($stmt->execute()) {
                    $success = "Password Updated";
                } else {
                    $err = "Please Try Again Or Try Later";
                }
                $stmt->close();
            }
		}
PHP;

foreach ($files as $file) {
    if (!file_exists($file)) {
        continue;
    }
    $content = file_get_contents($file);
    if (str_contains($file, 'his_admin_account.php')) {
        $pattern = '/if\s*\(\s*isset\(\$_POST\[\'update_profile\'\]\)\s*\).*?if\s*\(\s*isset\(\$_POST\[\'update_pwd\'\]\)\s*\).*?\n\t\t\}/s';
        if (preg_match($pattern, $content)) {
            $content = preg_replace($pattern, rtrim($accountPatch), $content, 1);
            $content = str_replace(
                '<input type="password" class="form-control" id="firstname" placeholder="Enter Old Password">',
                '<input type="password" class="form-control" id="firstname" name="current_pwd" required placeholder="Enter Old Password">',
                $content
            );
            file_put_contents($file, $content);
            echo "Patched admin account\n";
        }
    }
}

echo "Account patch done.\n";
