<?php
declare(strict_types=1);

require_once dirname(__DIR__) . '/config.php';

$adminEmail = 'newadmin@hospital.local';
$adminPass = 'Admin@Hospital2026';
$docNumber = 'DOC001';
$docPass = 'Doctor@2026';

$adminHash = password_hash($adminPass, PASSWORD_DEFAULT);
$docHash = password_hash($docPass, PASSWORD_DEFAULT);

$del1 = $mysqli->prepare('DELETE FROM his_admin WHERE ad_email = ?');
$del1->bind_param('s', $adminEmail);
$del1->execute();
$del1->close();

$del2 = $mysqli->prepare('DELETE FROM his_docs WHERE doc_number = ?');
$del2->bind_param('s', $docNumber);
$del2->execute();
$del2->close();

$stmt = $mysqli->prepare('INSERT INTO his_admin (ad_fname, ad_lname, ad_email, ad_pwd, ad_dpic) VALUES (?,?,?,?,?)');
$fname = 'New';
$lname = 'Admin';
$dpic = 'doc-icon.png';
$stmt->bind_param('sssss', $fname, $lname, $adminEmail, $adminHash, $dpic);
$stmt->execute();
$stmt->close();

$stmt = $mysqli->prepare('INSERT INTO his_docs (doc_fname, doc_lname, doc_email, doc_pwd, doc_dept, doc_number, doc_dpic) VALUES (?,?,?,?,?,?,?)');
$dfn = 'New';
$dln = 'Doctor';
$demail = 'newdoctor@hospital.local';
$dept = 'General';
$ddpic = 'defaultimg.jpg';
$stmt->bind_param('sssssss', $dfn, $dln, $demail, $docHash, $dept, $docNumber, $ddpic);
$stmt->execute();
$stmt->close();

header('Content-Type: text/plain; charset=UTF-8');
echo "Accounts created successfully.\n\n";
echo "ADMIN\n";
echo "  Email:    $adminEmail\n";
echo "  Password: $adminPass\n";
echo "  Login:    backend/admin/index.php\n\n";
echo "DOCTOR\n";
echo "  Number:   $docNumber\n";
echo "  Password: $docPass\n";
echo "  Login:    backend/doc/index.php\n";
