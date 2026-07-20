<div class="left-side-menu">

    <div class="slimscroll-menu">

        <!-- ── Sidebar Menu ── -->
        <div id="sidebar-menu">
            <ul class="metismenu" id="side-menu">

                <!-- ────── MAIN MENU ────── -->
                <li class="menu-title">Main Menu</li>

                <li>
                    <a href="his_admin_dashboard.php">
                        <i class="mdi mdi-view-dashboard-outline"></i>
                        <span>Dashboard</span>
                    </a>
                </li>

                <li>
                    <a href="his_admin_sector_picker.php">
                        <i class="mdi mdi-view-grid-outline"></i>
                        <span>Sectors</span>
                    </a>
                </li>

                <!-- ────── CLINIC ────── -->
                <li class="menu-title">Clinic</li>

                <li>
                    <a href="javascript: void(0);">
                        <i class="mdi mdi-hospital-box-outline"></i>
                        <span>Patients</span>
                        <span class="menu-arrow"></span>
                    </a>
                    <ul class="nav-second-level" aria-expanded="false">
                        <li><a href="his_admin_register_patient.php">Register Patient</a></li>
                        <li><a href="his_admin_view_patients.php">View Patients</a></li>
                        <li><a href="his_admin_manage_patient.php">Manage Patients</a></li>
                        <hr>
                        <li><a href="his_admin_discharge_patient.php">Discharge Patients</a></li>
                        <li><a href="his_admin_patient_transfer.php">Patient Transfers</a></li>
                    </ul>
                </li>

                <li>
                    <a href="javascript: void(0);">
                        <i class="mdi mdi-calendar-month-outline"></i>
                        <span>Appointments</span>
                        <span class="menu-arrow"></span>
                    </a>
                    <ul class="nav-second-level" aria-expanded="false">
                        <li><a href="his_admin_appointments.php">View Appointments</a></li>
                        <li><a href="his_admin_add_appointment.php">Book Appointment</a></li>
                    </ul>
                </li>

                <li>
                    <a href="javascript: void(0);">
                        <i class="mdi mdi-pill"></i>
                        <span>Pharmacy</span>
                        <span class="menu-arrow"></span>
                    </a>
                    <ul class="nav-second-level" aria-expanded="false">
                        <li><a href="his_admin_add_pharm_cat.php">Add Pharm Category</a></li>
                        <li><a href="his_admin_view_pharm_cat.php">View Pharm Category</a></li>
                        <li><a href="his_admin_manage_pharm_cat.php">Manage Pharm Category</a></li>
                        <hr>
                        <li><a href="his_admin_add_pharmaceuticals.php">Add Pharmaceuticals</a></li>
                        <li><a href="his_admin_view_pharmaceuticals.php">View Pharmaceuticals</a></li>
                        <li><a href="his_admin_manage_pharmaceuticals.php">Manage Pharmaceuticals</a></li>
                        <hr>
                        <li><a href="his_admin_add_presc.php">Add Prescriptions</a></li>
                        <li><a href="his_admin_view_presc.php">View Prescriptions</a></li>
                        <li><a href="his_admin_manage_presc.php">Manage Prescriptions</a></li>
                    </ul>
                </li>

                <li>
                    <a href="javascript: void(0);">
                        <i class="mdi mdi-flask"></i>
                        <span>Laboratory</span>
                        <span class="menu-arrow"></span>
                    </a>
                    <ul class="nav-second-level" aria-expanded="false">
                        <li><a href="his_admin_lab_report.php">Lab Reports</a></li>
                        <li><a href="his_admin_patient_lab_test.php">Patient Lab Tests</a></li>
                        <li><a href="his_admin_patient_lab_result.php">Lab Results</a></li>
                        <li><a href="his_admin_patient_lab_vitals.php">Patient Vitals</a></li>
                        <li><a href="his_admin_employee_lab_vitals.php">Employee Vitals</a></li>
                        <hr>
                        <li><a href="his_admin_add_lab_equipment.php">Add Lab Equipment</a></li>
                        <li><a href="his_admin_manage_lab_equipment.php">Manage Lab Equipment</a></li>
                    </ul>
                </li>

                <!-- ────── HRM ────── -->
                <li class="menu-title">HRM</li>

                <li>
                    <a href="javascript: void(0);">
                        <i class="mdi mdi-doctor"></i>
                        <span>Employees</span>
                        <span class="menu-arrow"></span>
                    </a>
                    <ul class="nav-second-level" aria-expanded="false">
                        <li><a href="his_admin_add_employee.php">Add Employee</a></li>
                        <li><a href="his_admin_view_employee.php">View Employees</a></li>
                        <li><a href="his_admin_manage_employee.php">Manage Employees</a></li>
                        <hr>
                        <li><a href="his_admin_assaign_dept.php">Assign Department</a></li>
                        <li><a href="his_admin_transfer_employee.php">Transfer Employee</a></li>
                    </ul>
                </li>

                <!-- Attendance module hidden until a real attendance feature ships (was a vitals stub). -->

                <li>
                    <a href="javascript: void(0);">
                        <i class="mdi mdi-cash"></i>
                        <span>Payroll</span>
                        <span class="menu-arrow"></span>
                    </a>
                    <ul class="nav-second-level" aria-expanded="false">
                        <li><a href="his_admin_add_payroll.php">Add Payroll</a></li>
                        <li><a href="his_admin_generate_payrolls.php">Generate Payrolls</a></li>
                        <li><a href="his_admin_manage_payrolls.php">Manage Payrolls</a></li>
                    </ul>
                </li>

                <!-- ────── FINANCE ────── -->
                <li class="menu-title">Finance &amp; Accounts</li>

                <li>
                    <a href="javascript: void(0);">
                        <i class="mdi mdi-cash-multiple"></i>
                        <span>Accounting</span>
                        <span class="menu-arrow"></span>
                    </a>
                    <ul class="nav-second-level" aria-expanded="false">
                        <li><a href="his_admin_add_acc.payable.php">Add Acc. Payable</a></li>
                        <li><a href="his_admin_manage_acc_payable.php">Manage Acc. Payable</a></li>
                        <hr>
                        <li><a href="his_admin_add_acc_receivable.php">Add Acc. Receivable</a></li>
                        <li><a href="his_admin_manage_acc_receivable.php">Manage Acc. Receivable</a></li>
                    </ul>
                </li>

                <li>
                    <a href="javascript: void(0);">
                        <i class="mdi mdi-filter-variant"></i>
                        <span>Inventory</span>
                        <span class="menu-arrow"></span>
                    </a>
                    <ul class="nav-second-level" aria-expanded="false">
                        <li><a href="his_admin_pharm_inventory.php">Pharmaceuticals</a></li>
                        <li><a href="his_admin_equipments_inventory.php">Assets</a></li>
                    </ul>
                </li>

                <li>
                    <a href="javascript: void(0);">
                        <i class="mdi mdi-chart-line"></i>
                        <span>Reports</span>
                        <span class="menu-arrow"></span>
                    </a>
                    <ul class="nav-second-level" aria-expanded="false">
                        <li><a href="his_admin_accounting_records.php">Accounting Records</a></li>
                        <li><a href="his_admin_inpatient_records.php">InPatient Records</a></li>
                        <li><a href="his_admin_outpatient_records.php">OutPatient Records</a></li>
                        <li><a href="his_admin_employee_records.php">Employee Records</a></li>
                        <li><a href="his_admin_pharmaceutical_records.php">Pharmaceutical Records</a></li>
                    </ul>
                </li>

                <li>
                    <a href="javascript: void(0);">
                        <i class="mdi mdi-tag-outline"></i>
                        <span>Vendors</span>
                        <span class="menu-arrow"></span>
                    </a>
                    <ul class="nav-second-level" aria-expanded="false">
                        <li><a href="his_admin_add_vendor.php">Add Vendor</a></li>
                        <li><a href="his_admin_manage_vendor.php">Manage Vendors</a></li>
                    </ul>
                </li>

                <!-- ────── ADMINISTRATION ────── -->
                <li class="menu-title">Administration</li>

                <!-- Departments CRUD pages hidden until Stage B sector model ships (links were 404). -->

                <li>
                    <a href="javascript: void(0);">
                        <i class="mdi mdi-file-document"></i>
                        <span>Medical Records</span>
                        <span class="menu-arrow"></span>
                    </a>
                    <ul class="nav-second-level" aria-expanded="false">
                        <li><a href="his_admin_add_medical_record.php">Add Medical Record</a></li>
                        <li><a href="his_admin_medical_records.php">View Medical Records</a></li>
                        <li><a href="his_admin_manage_medical_record.php">Manage Medical Records</a></li>
                    </ul>
                </li>

                <li>
                    <a href="javascript: void(0);">
                        <i class="mdi mdi-scalpel"></i>
                        <span>Theatre</span>
                        <span class="menu-arrow"></span>
                    </a>
                    <ul class="nav-second-level" aria-expanded="false">
                        <li><a href="his_admin_add_theatre_patient.php">Add Theatre Patient</a></li>
                        <li><a href="his_admin_surgery_records.php">Surgery Records</a></li>
                        <li><a href="his_admin_manage_theatre_patient.php">Manage Theatre Patients</a></li>
                        <li><a href="his_admin_add_equipment.php">Add Equipment</a></li>
                        <li><a href="his_admin_manage_equipment.php">Manage Equipment</a></li>
                    </ul>
                </li>

                <li>
                    <a href="javascript: void(0);">
                        <i class="mdi mdi-account-group"></i>
                        <span>Users</span>
                        <span class="menu-arrow"></span>
                    </a>
                    <ul class="nav-second-level" aria-expanded="false">
                        <li><a href="his_admin_register.php">Add Admin User</a></li>
                        <li><a href="his_admin_account.php">My Account</a></li>
                    </ul>
                </li>

                <!-- ────── SETTINGS ────── -->
                <li class="menu-title">Settings</li>

                <li>
                    <a href="javascript: void(0);">
                        <i class="mdi mdi-account-key"></i>
                        <span>Password Reset</span>
                        <span class="menu-arrow"></span>
                    </a>
                    <ul class="nav-second-level" aria-expanded="false">
                        <li><a href="his_admin_manage_password_resets.php">Manage Reset Requests</a></li>
                    </ul>
                </li>

                <!-- Sign out -->
                <li class="hms-nav-logout">
                    <a href="his_admin_logout_partial.php">
                        <i class="mdi mdi-logout"></i>
                        <span>Sign Out</span>
                    </a>
                </li>

            </ul>
        </div>
        <!-- End Sidebar Menu -->

        <div class="clearfix"></div>
    </div>

</div>
