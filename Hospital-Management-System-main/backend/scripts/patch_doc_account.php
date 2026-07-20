<?php
declare(strict_types=1);

$file = __DIR__ . '/../doc/his_doc_update-account.php';
$content = file_get_contents($file);

$profilePatch = <<<'PHP'
		if(isset($_POST['update_profile']))
		{
			$doc_fname=$_POST['doc_fname'];
			$doc_lname=$_POST['doc_lname'];
			$doc_id=$_SESSION['doc_id'];
            $doc_email=$_POST['doc_email'];
            $doc_dpic = null;
            if (!empty($_FILES["doc_dpic"]["name"])) {
                $upload = handle_profile_upload('doc_dpic', __DIR__ . '/assets/images/users');
                if (isset($upload['err'])) {
                    $err = $upload['err'];
                } else {
                    $doc_dpic = $upload['filename'];
                }
            }
            if (!isset($err)) {
                if ($doc_dpic !== null) {
                    $query="UPDATE his_docs SET doc_fname=?, doc_lname=?, doc_email=?, doc_dpic=? WHERE doc_id = ?";
                    $stmt = $mysqli->prepare($query);
                    $stmt->bind_param('ssssi', $doc_fname, $doc_lname, $doc_email, $doc_dpic, $doc_id);
                } else {
                    $query="UPDATE his_docs SET doc_fname=?, doc_lname=?, doc_email=? WHERE doc_id = ?";
                    $stmt = $mysqli->prepare($query);
                    $stmt->bind_param('sssi', $doc_fname, $doc_lname, $doc_email, $doc_id);
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
            $doc_id = $_SESSION['doc_id'];
            $current_pwd = $_POST['current_pwd'] ?? '';
            $new_pwd = $_POST['doc_pwd'] ?? '';
            $stmt=$mysqli->prepare("SELECT doc_pwd FROM his_docs WHERE doc_id=?");
            $stmt->bind_param('i', $doc_id);
            $stmt->execute();
            $stmt->bind_result($existing_hash);
            $stmt->fetch();
            $stmt->close();
            if (!verify_password_hash($current_pwd, $existing_hash)) {
                $err = 'Current password is incorrect.';
            } elseif (strlen($new_pwd) < 8) {
                $err = 'New password must be at least 8 characters.';
            } else {
                $doc_pwd=password_hash($new_pwd, PASSWORD_DEFAULT);
                $query="UPDATE his_docs SET doc_pwd =? WHERE doc_id = ?";
                $stmt = $mysqli->prepare($query);
                $stmt->bind_param('si', $doc_pwd, $doc_id);
                if ($stmt->execute()) {
                    $success = "Password Updated";
                } else {
                    $err = "Please Try Again Or Try Later";
                }
                $stmt->close();
            }
		}
PHP;

$pattern = '/if\s*\(\s*isset\(\$_POST\[\'update_profile\'\]\)\s*\).*?if\s*\(\s*isset\(\$_POST\[\'update_pwd\'\]\)\s*\).*?\n\t\t\}/s';
$content = preg_replace($pattern, rtrim($profilePatch), $content, 1);
$content = str_replace(
    '<input type="password" class="form-control" id="firstname" placeholder="Enter Old Password">',
    '<input type="password" class="form-control" id="firstname" name="current_pwd" required placeholder="Enter Old Password">',
    $content
);
file_put_contents($file, $content);
echo "Doc account patched.\n";
