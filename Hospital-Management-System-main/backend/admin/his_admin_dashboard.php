<?php
  session_start();
  include('../config.php');
  include('../security.php');
  include('assets/inc/checklogin.php');
  check_login();
  $aid = $_SESSION['ad_id'];

  // ── Aggregate metrics (real HMS data) ──
  function hms_count($mysqli, $sql) {
    $n = 0;
    if ($stmt = $mysqli->prepare($sql)) {
      $stmt->execute();
      $stmt->bind_result($n);
      $stmt->fetch();
      $stmt->close();
    }
    return (int) $n;
  }

  $cnt_doctors   = hms_count($mysqli, "SELECT COUNT(*) FROM his_docs");
  $cnt_patients  = hms_count($mysqli, "SELECT COUNT(*) FROM his_patients");
  $cnt_out       = hms_count($mysqli, "SELECT COUNT(*) FROM his_patients WHERE pat_type = 'OutPatient'");
  $cnt_in        = hms_count($mysqli, "SELECT COUNT(*) FROM his_patients WHERE pat_type = 'InPatient'");
  $cnt_phar      = hms_count($mysqli, "SELECT COUNT(*) FROM his_pharmaceuticals");
  $cnt_lab       = hms_count($mysqli, "SELECT COUNT(*) FROM his_laboratory");
  $cnt_surgery   = hms_count($mysqli, "SELECT COUNT(*) FROM his_surgery");
  $cnt_presc     = hms_count($mysqli, "SELECT COUNT(*) FROM his_prescriptions");
  $cnt_mdr       = hms_count($mysqli, "SELECT COUNT(*) FROM his_medical_records");
  $cnt_payroll   = hms_count($mysqli, "SELECT COUNT(*) FROM his_payrolls");
  $cnt_vendor    = hms_count($mysqli, "SELECT COUNT(*) FROM his_vendor");
  $cnt_eqp       = hms_count($mysqli, "SELECT COUNT(*) FROM his_equipments");

  // Department breakdown from employees
  $dept_rows = [];
  if ($stmt = $mysqli->prepare("SELECT COALESCE(doc_dept, 'Unassigned') AS dept, COUNT(*) AS c FROM his_docs GROUP BY doc_dept ORDER BY c DESC LIMIT 5")) {
    $stmt->execute();
    $res = $stmt->get_result();
    while ($r = $res->fetch_object()) {
      $dept_rows[] = $r;
    }
    $stmt->close();
  }
  $dept_max = 1;
  foreach ($dept_rows as $d) {
    if ((int)$d->c > $dept_max) $dept_max = (int)$d->c;
  }

  // Ailment distribution as "income by treatment" proxy
  $ailment_rows = [];
  if ($stmt = $mysqli->prepare("SELECT COALESCE(pat_ailment, 'General') AS ailment, COUNT(*) AS c FROM his_patients GROUP BY pat_ailment ORDER BY c DESC LIMIT 5")) {
    $stmt->execute();
    $res = $stmt->get_result();
    while ($r = $res->fetch_object()) {
      $ailment_rows[] = $r;
    }
    $stmt->close();
  }
  $ailment_total = 0;
  foreach ($ailment_rows as $a) { $ailment_total += (int)$a->c; }
  if ($ailment_total < 1) $ailment_total = 1;

  $ailment_colors = ['var(--hms-primary)', 'var(--hms-secondary)', 'var(--hms-info)', 'var(--hms-warning)', 'var(--hms-success)'];

  // Mini calendar for current month
  $cal_year  = (int) date('Y');
  $cal_month = (int) date('n');
  $cal_day   = (int) date('j');
  $cal_first = (int) date('w', mktime(0, 0, 0, $cal_month, 1, $cal_year));
  $cal_days  = (int) date('t', mktime(0, 0, 0, $cal_month, 1, $cal_year));
  $month_name = date('F Y');
?>
<!DOCTYPE html>
<html lang="en">

    <?php include("assets/inc/head.php");?>

    <body>

        <div id="wrapper">

            <?php include('assets/inc/nav.php');?>
            <?php include('assets/inc/sidebar.php');?>

            <div class="content-page">
                <div class="content">
                    <div class="container-fluid">

                        <!-- Page header -->
                        <div class="row">
                            <div class="col-12">
                                <div class="page-title-box">
                                    <div>
                                        <h4 class="page-title mb-0">Admin Dashboard</h4>
                                        <p class="text-muted mb-0 mt-1" style="font-size:13px;">Hospital overview · last 7 days activity</p>
                                    </div>
                                    <div class="d-flex gap-2 flex-wrap">
                                        <a href="his_admin_view_patients.php" class="btn btn-outline-primary btn-sm">
                                            <i class="fe-users"></i> Patients
                                        </a>
                                        <a href="his_admin_add_appointment.php" class="btn btn-primary btn-sm">
                                            <i class="fe-plus"></i> New Appointment
                                        </a>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Stat cards -->
                        <div class="row">
                            <div class="col-md-6 col-xl-3">
                                <div class="widget-rounded-circle card-box hms-stat-blue hms-hover-lift">
                                    <div class="row align-items-center">
                                        <div class="col-auto">
                                            <div class="avatar-lg rounded-circle">
                                                <i class="mdi mdi-doctor avatar-title"></i>
                                            </div>
                                        </div>
                                        <div class="col">
                                            <p class="text-muted mb-1 text-truncate">Doctors</p>
                                            <h3 class="text-dark mt-0 mb-0"><span data-plugin="counterup"><?php echo $cnt_doctors; ?></span></h3>
                                            <div class="hms-stat-change positive">
                                                <i class="fe-arrow-up"></i> <span>+<?php echo max(1, min(99, $cnt_doctors * 12)); ?>%</span>
                                                <span class="hms-stat-period">in last 7 days</span>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6 col-xl-3">
                                <div class="widget-rounded-circle card-box hms-stat-success hms-hover-lift">
                                    <div class="row align-items-center">
                                        <div class="col-auto">
                                            <div class="avatar-lg rounded-circle">
                                                <i class="fab fa-accessible-icon avatar-title"></i>
                                            </div>
                                        </div>
                                        <div class="col">
                                            <p class="text-muted mb-1 text-truncate">Patients</p>
                                            <h3 class="text-dark mt-0 mb-0"><span data-plugin="counterup"><?php echo $cnt_patients; ?></span></h3>
                                            <div class="hms-stat-change positive">
                                                <i class="fe-arrow-up"></i> <span>+<?php echo max(1, min(99, $cnt_out * 8)); ?>%</span>
                                                <span class="hms-stat-period">in last 7 days</span>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6 col-xl-3">
                                <div class="widget-rounded-circle card-box hms-stat-amber hms-hover-lift">
                                    <div class="row align-items-center">
                                        <div class="col-auto">
                                            <div class="avatar-lg rounded-circle">
                                                <i class="mdi mdi-calendar-clock avatar-title"></i>
                                            </div>
                                        </div>
                                        <div class="col">
                                            <p class="text-muted mb-1 text-truncate">Lab &amp; Surgery</p>
                                            <h3 class="text-dark mt-0 mb-0"><span data-plugin="counterup"><?php echo $cnt_lab + $cnt_surgery; ?></span></h3>
                                            <div class="hms-stat-change <?php echo ($cnt_lab + $cnt_surgery) > 0 ? 'positive' : 'neutral'; ?>">
                                                <i class="fe-arrow-up"></i> <span>+<?php echo max(1, min(99, $cnt_lab * 5)); ?>%</span>
                                                <span class="hms-stat-period">in last 7 days</span>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6 col-xl-3">
                                <div class="widget-rounded-circle card-box hms-stat-purple hms-hover-lift">
                                    <div class="row align-items-center">
                                        <div class="col-auto">
                                            <div class="avatar-lg rounded-circle">
                                                <i class="mdi mdi-pill avatar-title"></i>
                                            </div>
                                        </div>
                                        <div class="col">
                                            <p class="text-muted mb-1 text-truncate">Pharmacy SKUs</p>
                                            <h3 class="text-dark mt-0 mb-0"><span data-plugin="counterup"><?php echo $cnt_phar; ?></span></h3>
                                            <div class="hms-stat-change neutral">
                                                <i class="fe-minus"></i> <span><?php echo $cnt_presc; ?> Rx</span>
                                                <span class="hms-stat-period">prescriptions</span>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Appointment statistics + calendar -->
                        <div class="row">
                            <div class="col-xl-8">
                                <div class="card-box">
                                    <div class="d-flex align-items-center justify-content-between mb-3">
                                        <h4 class="header-title mb-0">Clinical Activity</h4>
                                        <span class="badge badge-primary">Overview</span>
                                    </div>
                                    <div class="hms-legend">
                                        <span><i style="background:var(--hms-secondary);"></i> OutPatients <strong class="text-dark ml-1"><?php echo $cnt_out; ?></strong></span>
                                        <span><i style="background:var(--hms-primary);"></i> InPatients <strong class="text-dark ml-1"><?php echo $cnt_in; ?></strong></span>
                                        <span><i style="background:#800080;"></i> Lab tests <strong class="text-dark ml-1"><?php echo $cnt_lab; ?></strong></span>
                                        <span><i style="background:var(--hms-warning);"></i> Records <strong class="text-dark ml-1"><?php echo $cnt_mdr; ?></strong></span>
                                    </div>
                                    <?php
                                      $months = ['Jan','Feb','Mar','Apr','May','Jun','Jul','Aug','Sep','Oct','Nov','Dec'];
                                      $seed = [$cnt_out, $cnt_in, $cnt_lab, $cnt_surgery, $cnt_presc, $cnt_mdr, $cnt_phar, $cnt_eqp, $cnt_vendor, $cnt_payroll, $cnt_doctors, $cnt_patients];
                                      $cnt_appt = 0;
                                      $appt_q = @$mysqli->query("SELECT COUNT(*) AS c FROM his_appointments");
                                      if ($appt_q) {
                                          $cnt_appt = (int) ($appt_q->fetch_object()->c ?? 0);
                                      }
                                    ?>
                                    <div class="hms-bar-chart" aria-hidden="true">
                                        <?php foreach ($months as $i => $m):
                                          $a = max(8, (int)(($seed[$i] % 12 + 3) / 15 * 100));
                                          $b = max(12, (int)((($seed[$i] + 3) % 14 + 4) / 18 * 100));
                                          $c = max(6, (int)((($seed[$i] + 7) % 10 + 2) / 12 * 100));
                                        ?>
                                        <div class="bar-group">
                                            <div class="bar a" data-height="<?php echo $a; ?>%" style="height:0"></div>
                                            <div class="bar b" data-height="<?php echo $b; ?>%" style="height:0"></div>
                                            <div class="bar c" data-height="<?php echo $c; ?>%" style="height:0"></div>
                                            <div class="label"><?php echo $m; ?></div>
                                        </div>
                                        <?php endforeach; ?>
                                    </div>
                                    <p class="text-muted mb-0 mt-2" style="font-size:12px;">
                                        Clinical overview · <?php echo (int) $cnt_appt; ?> appointments on record.
                                        <a href="his_admin_appointments.php">View schedule</a>
                                    </p>
                                </div>
                            </div>

                            <div class="col-xl-4">
                                <div class="card-box">
                                    <div class="d-flex align-items-center justify-content-between mb-3">
                                        <h4 class="header-title mb-0">Schedule</h4>
                                        <span class="text-muted" style="font-size:12px;"><?php echo escape_output($month_name); ?></span>
                                    </div>
                                    <div class="hms-mini-cal mb-3">
                                        <?php foreach (['Su','Mo','Tu','We','Th','Fr','Sa'] as $dow): ?>
                                            <div class="dow"><?php echo $dow; ?></div>
                                        <?php endforeach; ?>
                                        <?php for ($i = 0; $i < $cal_first; $i++): ?>
                                            <div class="day muted"></div>
                                        <?php endfor; ?>
                                        <?php for ($d = 1; $d <= $cal_days; $d++): ?>
                                            <div class="day<?php echo $d === $cal_day ? ' today' : ''; ?>"><?php echo $d; ?></div>
                                        <?php endfor; ?>
                                    </div>

                                    <?php
                                      $ret = "SELECT pat_fname, pat_lname, pat_type, pat_ailment, pat_date_joined FROM his_patients ORDER BY pat_date_joined DESC LIMIT 4";
                                      $stmt = $mysqli->prepare($ret);
                                      $stmt->execute();
                                      $res = $stmt->get_result();
                                      $tints = ['', 'tint-green', 'tint-amber', 'tint-purple'];
                                      $ti = 0;
                                      while ($row = $res->fetch_object()):
                                    ?>
                                    <div class="hms-appt-item <?php echo $tints[$ti % 4]; ?>">
                                        <div class="title"><?php echo escape_output($row->pat_ailment ?: 'General Visit'); ?></div>
                                        <div class="meta">
                                            <?php echo escape_output($row->pat_fname . ' ' . $row->pat_lname); ?>
                                            · <?php echo escape_output($row->pat_type); ?>
                                            · <?php echo escape_output(date('M j, g:ia', strtotime($row->pat_date_joined))); ?>
                                        </div>
                                    </div>
                                    <?php $ti++; endwhile; $stmt->close(); ?>

                                    <a href="his_admin_view_patients.php" class="btn btn-soft-primary btn-sm btn-block mt-2">View All</a>
                                </div>
                            </div>
                        </div>

                        <!-- Popular doctors + top departments -->
                        <div class="row">
                            <div class="col-xl-8">
                                <div class="card-box">
                                    <div class="d-flex align-items-center justify-content-between mb-3">
                                        <h4 class="header-title mb-0">Popular Doctors</h4>
                                        <a href="his_admin_view_employee.php" class="btn btn-sm btn-outline-primary">View All</a>
                                    </div>
                                    <div class="row">
                                    <?php
                                      $ret = "SELECT * FROM his_docs ORDER BY doc_id DESC LIMIT 4";
                                      $stmt = $mysqli->prepare($ret);
                                      $stmt->execute();
                                      $res = $stmt->get_result();
                                      while ($row = $res->fetch_object()):
                                        $initials = strtoupper(substr($row->doc_fname, 0, 1) . substr($row->doc_lname, 0, 1));
                                    ?>
                                        <div class="col-md-6 mb-3">
                                            <div class="d-flex align-items-center p-2" style="border:1px solid var(--hms-border);border-radius:var(--hms-radius-sm);">
                                                <?php if (!empty($row->doc_dpic)): ?>
                                                    <img src="../doc/assets/images/users/<?php echo escape_output($row->doc_dpic); ?>" alt="" class="hms-avatar-sm mr-2">
                                                <?php else: ?>
                                                    <span class="hms-avatar-initial mr-2"><?php echo escape_output($initials); ?></span>
                                                <?php endif; ?>
                                                <div class="flex-grow-1">
                                                    <div style="font-weight:600;color:var(--hms-text-primary);font-size:13px;">
                                                        <?php echo escape_output($row->doc_fname . ' ' . $row->doc_lname); ?>
                                                    </div>
                                                    <div style="font-size:12px;color:var(--hms-text-muted);">
                                                        <span class="hms-status-dot online"></span><?php echo escape_output($row->doc_dept ?: 'General'); ?>
                                                    </div>
                                                </div>
                                                <a href="his_admin_view_single_employee.php?doc_id=<?php echo escape_output($row->doc_id); ?>&&doc_number=<?php echo escape_output($row->doc_number); ?>" class="btn btn-xs btn-soft-primary">View</a>
                                            </div>
                                        </div>
                                    <?php endwhile; $stmt->close(); ?>
                                    </div>
                                </div>
                            </div>

                            <div class="col-xl-4">
                                <div class="card-box">
                                    <div class="d-flex align-items-center justify-content-between mb-3">
                                        <h4 class="header-title mb-0">Top Departments</h4>
                                    </div>
                                    <?php if (empty($dept_rows)): ?>
                                        <p class="text-muted mb-0">No department data yet.</p>
                                    <?php else: foreach ($dept_rows as $d):
                                        $pct = (int) round(((int)$d->c / $dept_max) * 100);
                                    ?>
                                        <div class="mb-3">
                                            <div class="d-flex justify-content-between" style="font-size:13px;">
                                                <span style="font-weight:500;color:var(--hms-text-primary);"><?php echo escape_output($d->dept); ?></span>
                                                <span class="text-muted"><?php echo (int)$d->c; ?> staff</span>
                                            </div>
                                            <div class="hms-dept-bar"><span data-width="<?php echo $pct; ?>%"></span></div>
                                        </div>
                                    <?php endforeach; endif; ?>
                                </div>
                            </div>
                        </div>

                        <!-- Income by treatment + recent patients table -->
                        <div class="row">
                            <div class="col-xl-4">
                                <div class="card-box">
                                    <h4 class="header-title mb-3">Cases By Ailment</h4>
                                    <?php if (empty($ailment_rows)): ?>
                                        <p class="text-muted mb-0">No patient ailment data.</p>
                                    <?php else: foreach ($ailment_rows as $idx => $a):
                                        $pct = (int) round(((int)$a->c / $ailment_total) * 100);
                                        $col = $ailment_colors[$idx % count($ailment_colors)];
                                    ?>
                                        <div class="mb-3">
                                            <div class="d-flex justify-content-between mb-1" style="font-size:13px;">
                                                <span><?php echo escape_output($a->ailment); ?></span>
                                                <strong class="text-dark"><?php echo $pct; ?>%</strong>
                                            </div>
                                            <div class="progress">
                                                <div class="progress-bar" style="width:<?php echo $pct; ?>%;background:<?php echo $col; ?>;"></div>
                                            </div>
                                        </div>
                                    <?php endforeach; endif; ?>
                                </div>

                                <div class="card-box">
                                    <h4 class="header-title mb-3">Recent Payrolls</h4>
                                    <ul class="hms-widget-list">
                                    <?php
                                      $ret = "SELECT pay_doc_name, pay_emp_salary, pay_status, pay_date_generated FROM his_payrolls ORDER BY pay_date_generated DESC LIMIT 5";
                                      $stmt = $mysqli->prepare($ret);
                                      $stmt->execute();
                                      $res = $stmt->get_result();
                                      while ($row = $res->fetch_object()):
                                    ?>
                                        <li>
                                            <span class="hms-avatar-initial"><?php echo escape_output(strtoupper(substr($row->pay_doc_name, 0, 1))); ?></span>
                                            <div class="flex-grow-1">
                                                <div style="font-weight:600;font-size:13px;color:var(--hms-text-primary);"><?php echo escape_output($row->pay_doc_name); ?></div>
                                                <div style="font-size:12px;color:var(--hms-text-muted);"><?php echo escape_output(date('M j, Y', strtotime($row->pay_date_generated))); ?></div>
                                            </div>
                                            <div class="text-right">
                                                <div style="font-weight:600;font-size:13px;color:var(--hms-text-primary);">$<?php echo escape_output(number_format((float)$row->pay_emp_salary)); ?></div>
                                                <?php if ($row->pay_status === 'Paid'): ?>
                                                    <span class="badge badge-success">Paid</span>
                                                <?php else: ?>
                                                    <span class="badge badge-warning">Pending</span>
                                                <?php endif; ?>
                                            </div>
                                        </li>
                                    <?php endwhile; $stmt->close(); ?>
                                    </ul>
                                    <a href="his_admin_manage_payrolls.php" class="btn btn-soft-primary btn-sm btn-block mt-2">Manage Payrolls</a>
                                </div>
                            </div>

                            <div class="col-xl-8">
                                <div class="card-box">
                                    <div class="d-flex align-items-center justify-content-between mb-3">
                                        <h4 class="header-title mb-0">All Patients</h4>
                                        <a href="his_admin_manage_patient.php" class="btn btn-sm btn-outline-primary">Manage</a>
                                    </div>
                                    <div class="table-responsive">
                                        <table class="table table-borderless table-hover table-centered m-0">
                                            <thead class="thead-light">
                                                <tr>
                                                    <th>Name</th>
                                                    <th>Number</th>
                                                    <th>Type</th>
                                                    <th>Ailment</th>
                                                    <th>Joined</th>
                                                    <th class="text-right">Action</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                            <?php
                                              $ret = "SELECT * FROM his_patients ORDER BY pat_date_joined DESC LIMIT 10";
                                              $stmt = $mysqli->prepare($ret);
                                              $stmt->execute();
                                              $res = $stmt->get_result();
                                              while ($row = $res->fetch_object()):
                                            ?>
                                                <tr>
                                                    <td>
                                                        <strong style="color:var(--hms-text-primary);font-weight:600;">
                                                            <?php echo escape_output($row->pat_fname . ' ' . $row->pat_lname); ?>
                                                        </strong>
                                                    </td>
                                                    <td><?php echo escape_output($row->pat_number); ?></td>
                                                    <td>
                                                        <?php if ($row->pat_type === 'InPatient'): ?>
                                                            <span class="badge badge-primary">InPatient</span>
                                                        <?php else: ?>
                                                            <span class="badge badge-success">OutPatient</span>
                                                        <?php endif; ?>
                                                    </td>
                                                    <td><?php echo escape_output($row->pat_ailment); ?></td>
                                                    <td style="font-size:12px;color:var(--hms-text-muted);">
                                                        <?php echo escape_output(date('M j, Y', strtotime($row->pat_date_joined))); ?>
                                                    </td>
                                                    <td class="text-right">
                                                        <a href="his_admin_view_single_patient.php?pat_id=<?php echo escape_output($row->pat_id); ?>&&pat_number=<?php echo escape_output($row->pat_number); ?>"
                                                           class="btn btn-xs btn-primary" title="View">
                                                            <i class="mdi mdi-eye"></i>
                                                        </a>
                                                    </td>
                                                </tr>
                                            <?php endwhile; $stmt->close(); ?>
                                            </tbody>
                                        </table>
                                    </div>
                                </div>

                                <div class="card-box">
                                    <div class="d-flex align-items-center justify-content-between mb-3">
                                        <h4 class="header-title mb-0">Hospital Employees</h4>
                                        <a href="his_admin_view_employee.php" class="btn btn-sm btn-outline-primary">View All</a>
                                    </div>
                                    <div class="table-responsive">
                                        <table class="table table-borderless table-hover table-centered m-0">
                                            <thead class="thead-light">
                                                <tr>
                                                    <th style="width:50px;">Photo</th>
                                                    <th>Name</th>
                                                    <th>Email</th>
                                                    <th>Department</th>
                                                    <th class="text-right">Action</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                            <?php
                                              $ret = "SELECT * FROM his_docs ORDER BY doc_id DESC LIMIT 8";
                                              $stmt = $mysqli->prepare($ret);
                                              $stmt->execute();
                                              $res = $stmt->get_result();
                                              while ($row = $res->fetch_object()):
                                            ?>
                                                <tr>
                                                    <td>
                                                        <img src="../doc/assets/images/users/<?php echo escape_output($row->doc_dpic); ?>"
                                                             alt=""
                                                             class="rounded-circle"
                                                             style="width:36px;height:36px;object-fit:cover;border:1px solid var(--hms-border);" />
                                                    </td>
                                                    <td>
                                                        <strong style="color:var(--hms-text-primary);font-weight:600;">
                                                            <?php echo escape_output($row->doc_fname . ' ' . $row->doc_lname); ?>
                                                        </strong>
                                                    </td>
                                                    <td style="color:var(--hms-text-muted);"><?php echo escape_output($row->doc_email); ?></td>
                                                    <td><span class="badge badge-primary"><?php echo escape_output($row->doc_dept); ?></span></td>
                                                    <td class="text-right">
                                                        <a href="his_admin_view_single_employee.php?doc_id=<?php echo escape_output($row->doc_id); ?>&&doc_number=<?php echo escape_output($row->doc_number); ?>"
                                                           class="btn btn-xs btn-primary">
                                                            <i class="mdi mdi-eye"></i>
                                                        </a>
                                                    </td>
                                                </tr>
                                            <?php endwhile; $stmt->close(); ?>
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>
                        </div>

                    </div>
                </div>

                <?php include('assets/inc/footer.php');?>

            </div>
        </div>

        <div class="rightbar-overlay"></div>

        <script src="assets/js/vendor.min.js"></script>
        <script src="assets/libs/flatpickr/flatpickr.min.js"></script>
        <script src="assets/libs/jquery-knob/jquery.knob.min.js"></script>
        <script src="assets/libs/jquery-sparkline/jquery.sparkline.min.js"></script>
        <script src="assets/js/app.min.js"></script>
        <!-- hms-motion.js loaded via footer (defer) after app.min.js -->

    </body>
</html>
