<?php
session_start();
include('../config.php');
include('../security.php');
include('../assets/inc/nex_sectors.php');
include('assets/inc/checklogin.php');
check_login();

if (!nex_sectors_available()) {
    $err = 'Sector tables not installed. Run Stage B sector migration first.';
} elseif (empty($_SESSION['nex_is_system_admin'])) {
    http_response_code(403);
    die('Only system administrators can create accounts.');
}

if (isset($_POST['create_user']) && nex_sectors_available()) {
    $fname = trim((string) ($_POST['fname'] ?? ''));
    $lname = trim((string) ($_POST['lname'] ?? ''));
    $email = trim((string) ($_POST['email'] ?? ''));
    $password = (string) ($_POST['password'] ?? '');
    $sector = trim((string) ($_POST['sector_code'] ?? 'clinical'));
    $role = ($_POST['role'] ?? 'staff') === 'lead' ? 'lead' : 'staff';
    $as_admin = !empty($_POST['is_system_admin']);

    if ($fname === '' || $lname === '' || $email === '' || strlen($password) < 8) {
        $err = 'Name, email, and password (8+ chars) are required.';
    } elseif (!nex_has_sector($sector) && !$as_admin) {
        // system admin creating for any seeded sector
        $chk = $mysqli->prepare('SELECT sector_code FROM nex_sectors WHERE sector_code=?');
        $chk->bind_param('s', $sector);
        $chk->execute();
        if (!$chk->get_result()->fetch_object()) {
            $err = 'Unknown sector.';
        }
        $chk->close();
    }

    if (!isset($err)) {
        $hash = password_hash($password, PASSWORD_DEFAULT);
        $length = 5;
        $doc_number = substr(str_shuffle('0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZ'), 1, $length);
        if ($as_admin) {
            $q = 'INSERT INTO his_admin (ad_fname, ad_lname, ad_email, ad_pwd) VALUES (?,?,?,?)';
            $stmt = $mysqli->prepare($q);
            $stmt->bind_param('ssss', $fname, $lname, $email, $hash);
            if ($stmt->execute()) {
                $legacy_id = (int) $stmt->insert_id;
                $stmt->close();
                $u = $mysqli->prepare('INSERT INTO nex_users (legacy_source, legacy_id, email, password_hash, fname, lname, is_system_admin, active) VALUES (\'admin\',?,?,?,?,?,1,1)');
                $u->bind_param('issss', $legacy_id, $email, $hash, $fname, $lname);
                $u->execute();
                $user_id = (int) $u->insert_id;
                $u->close();
                $fac = $mysqli->query("SELECT facility_id FROM nex_facilities WHERE facility_code='HQ' LIMIT 1")->fetch_object();
                $facility_id = (int) ($fac->facility_id ?? 1);
                $sectors = $mysqli->query('SELECT sector_code FROM nex_sectors');
                $ins = $mysqli->prepare('INSERT INTO nex_user_assignments (user_id, facility_id, sector_code, role, active) VALUES (?,?,?,?,1)');
                while ($s = $sectors->fetch_object()) {
                    $code = $s->sector_code;
                    $r = 'lead';
                    $ins->bind_param('iiss', $user_id, $facility_id, $code, $r);
                    $ins->execute();
                }
                $ins->close();
                $success = 'System admin account created.';
            } else {
                $err = 'Could not create admin (email may already exist).';
                $stmt->close();
            }
        } else {
            $q = 'INSERT INTO his_docs (doc_fname, doc_lname, doc_email, doc_pwd, doc_dept, doc_number) VALUES (?,?,?,?,?,?)';
            $stmt = $mysqli->prepare($q);
            $dept = $sector;
            $stmt->bind_param('ssssss', $fname, $lname, $email, $hash, $dept, $doc_number);
            if ($stmt->execute()) {
                $legacy_id = (int) $stmt->insert_id;
                $stmt->close();
                $u = $mysqli->prepare('INSERT INTO nex_users (legacy_source, legacy_id, email, staff_number, password_hash, fname, lname, is_system_admin, active) VALUES (\'doc\',?,?,?,?,?,?,0,1)');
                $u->bind_param('isssss', $legacy_id, $email, $doc_number, $hash, $fname, $lname);
                $u->execute();
                $user_id = (int) $u->insert_id;
                $u->close();
                $fac = $mysqli->query("SELECT facility_id FROM nex_facilities WHERE facility_code='HQ' LIMIT 1")->fetch_object();
                $facility_id = (int) ($fac->facility_id ?? 1);
                $ins = $mysqli->prepare('INSERT INTO nex_user_assignments (user_id, facility_id, sector_code, role, active) VALUES (?,?,?,?,1)');
                $ins->bind_param('iiss', $user_id, $facility_id, $sector, $role);
                $ins->execute();
                if ($sector !== 'clinical') {
                    $clinical = 'clinical';
                    $staff = 'staff';
                    $ins->bind_param('iiss', $user_id, $facility_id, $clinical, $staff);
                    $ins->execute();
                }
                $ins->close();
                $success = 'Staff account created. Staff number: ' . $doc_number;
            } else {
                $err = 'Could not create staff account.';
                $stmt->close();
            }
        }
    }
}

$users = [];
if (nex_sectors_available()) {
    $res = $mysqli->query('SELECT u.user_id, u.email, u.fname, u.lname, u.legacy_source, u.is_system_admin, u.staff_number,
        GROUP_CONCAT(a.sector_code ORDER BY a.sector_code SEPARATOR ", ") AS sectors
        FROM nex_users u
        LEFT JOIN nex_user_assignments a ON a.user_id = u.user_id AND a.active = 1
        WHERE u.active = 1
        GROUP BY u.user_id
        ORDER BY u.user_id');
    if ($res) {
        while ($row = $res->fetch_object()) {
            $users[] = $row;
        }
    }
}
$sector_rows = [];
if (nex_sectors_available()) {
    $sr = $mysqli->query('SELECT sector_code, sector_name FROM nex_sectors WHERE sector_active=1 ORDER BY sector_sort');
    if ($sr) {
        while ($s = $sr->fetch_object()) {
            $sector_rows[] = $s;
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<?php include('assets/inc/head.php'); ?>
<body>
<div id="wrapper">
    <?php include('assets/inc/nav.php'); ?>
    <?php include('assets/inc/sidebar.php'); ?>
    <div class="content-page">
        <div class="content">
            <div class="container-fluid">
                <div class="row">
                    <div class="col-12">
                        <div class="page-title-box">
                            <h4 class="page-title">Manage NexClinic users</h4>
                            <p class="text-muted mb-0">System Admin creates all accounts (no delegated lead create in v1).</p>
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-lg-5">
                        <div class="card-box">
                            <h4 class="header-title">Create account</h4>
                            <form method="post">
                                <input type="hidden" name="csrf_token" value="<?php echo generate_csrf_token(); ?>">
                                <div class="form-group">
                                    <label>First name</label>
                                    <input type="text" name="fname" class="form-control" required>
                                </div>
                                <div class="form-group">
                                    <label>Last name</label>
                                    <input type="text" name="lname" class="form-control" required>
                                </div>
                                <div class="form-group">
                                    <label>Email</label>
                                    <input type="email" name="email" class="form-control" required>
                                </div>
                                <div class="form-group">
                                    <label>Password</label>
                                    <input type="password" name="password" class="form-control" minlength="8" required>
                                </div>
                                <div class="form-group">
                                    <label>Primary sector</label>
                                    <select name="sector_code" class="form-control">
                                        <?php foreach ($sector_rows as $s) { ?>
                                            <option value="<?php echo escape_output($s->sector_code); ?>"><?php echo escape_output($s->sector_name); ?></option>
                                        <?php } ?>
                                    </select>
                                </div>
                                <div class="form-group">
                                    <label>Role</label>
                                    <select name="role" class="form-control">
                                        <option value="staff">Staff</option>
                                        <option value="lead">Lead</option>
                                    </select>
                                </div>
                                <div class="form-group form-check">
                                    <input type="checkbox" class="form-check-input" name="is_system_admin" id="is_system_admin" value="1">
                                    <label class="form-check-label" for="is_system_admin">Create as system admin</label>
                                </div>
                                <button type="submit" name="create_user" class="btn btn-primary">Create</button>
                            </form>
                        </div>
                    </div>
                    <div class="col-lg-7">
                        <div class="card-box">
                            <h4 class="header-title">Users</h4>
                            <div class="table-responsive">
                                <table class="table table-bordered mb-0">
                                    <thead>
                                    <tr>
                                        <th>Name</th>
                                        <th>Email / number</th>
                                        <th>Sectors</th>
                                        <th>Type</th>
                                    </tr>
                                    </thead>
                                    <tbody>
                                    <?php foreach ($users as $u) { ?>
                                        <tr>
                                            <td><?php echo escape_output($u->fname . ' ' . $u->lname); ?></td>
                                            <td><?php echo escape_output($u->email); ?><?php if ($u->staff_number) { echo '<br><small>' . escape_output($u->staff_number) . '</small>'; } ?></td>
                                            <td><?php echo escape_output($u->sectors ?? '—'); ?></td>
                                            <td><?php echo $u->is_system_admin ? 'System admin' : escape_output($u->legacy_source); ?></td>
                                        </tr>
                                    <?php } ?>
                                    <?php if (empty($users)) { ?>
                                        <tr><td colspan="4" class="text-muted">No nex_users yet — run sector migration.</td></tr>
                                    <?php } ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <?php include('assets/inc/footer.php'); ?>
    </div>
</div>
<script src="assets/js/vendor.min.js"></script>
<script src="assets/js/app.min.js"></script>
</body>
</html>
