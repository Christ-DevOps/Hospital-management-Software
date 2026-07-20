<?php
    $aid=$_SESSION['ad_id'];
    $ret="select * from his_admin where ad_id=?";
    $stmt= $mysqli->prepare($ret);
    $stmt->bind_param('i',$aid);
    $stmt->execute();
    $res=$stmt->get_result();
    while($row=$res->fetch_object())
    {
?>
    <div class="navbar-custom">

        <!-- Left: Toggle + Search -->
        <ul class="list-unstyled topnav-menu topnav-menu-left m-0">
            <!-- <li>
                <button class="button-menu-mobile waves-effect waves-light">
                    <i class="fe-menu"></i>
                </button>
            </li> -->

            <li class="d-none d-lg-block">
                <form class="app-search" autocomplete="off">
                    <input type="hidden" name="csrf_token" value="<?php echo generate_csrf_token(); ?>">
                    <div class="app-search-box">
                        <button class="btn" type="button"><i class="fe-search"></i></button>
                        <input type="text" class="form-control" placeholder="Search patients, employees, records…">
                    </div>
                </form>
            </li>
        </ul>

        <!-- Logo (positioned absolutely over sidebar) -->
        <div class="logo-box bg-black">
            <a href="his_admin_dashboard.php" class="logo text-center">
                <span class="logo-lg">
                    <img src="assets/images/logo-dark.png" alt="HMS" height="22">
                </span>
                <span class="logo-sm">
                    <img src="assets/images/logo-sm-white.png" alt="" height="24">
                </span>
            </a>
            <button class="button-menu-mobile">
                <i class="fe-menu"></i>
            </button>
        </div>

        <!-- Right: Actions + User -->
        <ul class="list-unstyled topnav-menu float-right mb-0">

            <!-- Quick Create -->
            <li class="dropdown notification-list d-none d-lg-block">
                <a class="nav-link dropdown-toggle waves-effect waves-light" data-toggle="dropdown" href="#" role="button" aria-haspopup="false" aria-expanded="false">
                    <i class="fe-plus-circle mr-1" style="color: var(--hms-primary);"></i>
                    Create New
                    <i class="mdi mdi-chevron-down ml-1"></i>
                </a>
                <div class="dropdown-menu dropdown-menu-right">
                    <div class="dropdown-header">Quick Actions</div>

                    <a href="his_admin_add_employee.php" class="dropdown-item">
                        <i class="fe-users"></i>
                        <div>
                            <span>Add Employee</span>
                            <small class="d-block text-muted" style="font-size:11px;font-weight:400;">Register new staff member</small>
                        </div>
                    </a>

                    <a href="his_admin_register_patient.php" class="dropdown-item">
                        <i class="fe-activity"></i>
                        <div>
                            <span>Register Patient</span>
                            <small class="d-block text-muted" style="font-size:11px;font-weight:400;">New patient admission</small>
                        </div>
                    </a>

                    <a href="his_admin_add_payroll.php" class="dropdown-item">
                        <i class="fe-layers"></i>
                        <div>
                            <span>Add Payroll</span>
                            <small class="d-block text-muted" style="font-size:11px;font-weight:400;">Process staff payment</small>
                        </div>
                    </a>

                    <a href="his_admin_add_vendor.php" class="dropdown-item">
                        <i class="fe-shopping-cart"></i>
                        <div>
                            <span>Add Vendor</span>
                            <small class="d-block text-muted" style="font-size:11px;font-weight:400;">Register new supplier</small>
                        </div>
                    </a>

                    <div class="dropdown-divider"></div>

                    <a href="his_admin_add_medical_record.php" class="dropdown-item">
                        <i class="fe-list"></i>
                        <span>Medical Report</span>
                    </a>

                    <a href="his_admin_lab_report.php" class="dropdown-item">
                        <i class="fe-hard-drive"></i>
                        <span>Laboratory Report</span>
                    </a>

                    <a href="his_admin_surgery_records.php" class="dropdown-item">
                        <i class="fe-anchor"></i>
                        <span>Surgical Report</span>
                    </a>
                </div>
            </li>

            <!-- User Profile Dropdown -->
            <li class="dropdown notification-list">
                <a class="nav-link dropdown-toggle nav-user mr-0 waves-effect waves-light" data-toggle="dropdown" href="#" role="button" aria-haspopup="false" aria-expanded="false">
                    <img src="assets/images/users/<?php echo escape_output($row->ad_dpic); ?>" alt="User" class="rounded-circle">
                    <span class="pro-user-name ml-1 d-none d-lg-inline">
                        <?php echo escape_output($row->ad_fname); ?> <?php echo escape_output($row->ad_lname); ?>
                        <i class="mdi mdi-chevron-down ml-1"></i>
                    </span>
                </a>
                <div class="dropdown-menu dropdown-menu-right profile-dropdown" style="min-width:220px;">

                    <!-- User Info Card -->
                    <div class="dropdown-user-info">
                        <img src="assets/images/users/<?php echo escape_output($row->ad_dpic); ?>" alt="User" style="width:44px;height:44px;border-radius:50%;object-fit:cover;border:2px solid var(--hms-border-light);">
                        <div class="user-detail">
                            <p><?php echo escape_output($row->ad_fname); ?> <?php echo escape_output($row->ad_lname); ?></p>
                            <span>System Administrator</span>
                        </div>
                    </div>

                    <a href="his_admin_account.php" class="dropdown-item">
                        <i class="fe-user"></i>
                        <span>My Account</span>
                    </a>

                    <a href="his_admin_dashboard.php" class="dropdown-item">
                        <i class="fe-home"></i>
                        <span>Dashboard</span>
                    </a>

                    <div class="dropdown-divider"></div>

                    <a href="his_admin_logout_partial.php" class="dropdown-item" style="color: var(--hms-danger);">
                        <i class="fe-log-out" style="color: var(--hms-danger);"></i>
                        <span>Sign Out</span>
                    </a>

                </div>
            </li>

        </ul>
    </div>
<?php }?>