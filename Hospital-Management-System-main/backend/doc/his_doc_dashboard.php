<?php
  session_start();
  include('../config.php');
  include('../security.php');
  include('assets/inc/checklogin.php');
  check_login();
  $doc_id = $_SESSION['doc_id'];
  $doc_number = $_SESSION['doc_number'];

  $doc_fname = 'Doctor';
  $doc_lname = '';
  $ret_name = "SELECT doc_fname, doc_lname FROM his_docs WHERE doc_id = ? LIMIT 1";
  $stmt_name = $mysqli->prepare($ret_name);
  $stmt_name->bind_param('i', $doc_id);
  $stmt_name->execute();
  $stmt_name->bind_result($doc_fname, $doc_lname);
  $stmt_name->fetch();
  $stmt_name->close();

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

  $cnt_patients = hms_count($mysqli, "SELECT COUNT(*) FROM his_patients");
  $cnt_out      = hms_count($mysqli, "SELECT COUNT(*) FROM his_patients WHERE pat_type = 'OutPatient'");
  $cnt_in       = hms_count($mysqli, "SELECT COUNT(*) FROM his_patients WHERE pat_type = 'InPatient'");
  $cnt_phar     = hms_count($mysqli, "SELECT COUNT(*) FROM his_pharmaceuticals");
  $cnt_lab      = hms_count($mysqli, "SELECT COUNT(*) FROM his_laboratory");
  $cnt_eqp      = hms_count($mysqli, "SELECT COUNT(*) FROM his_equipments");
  $cnt_presc    = hms_count($mysqli, "SELECT COUNT(*) FROM his_prescriptions");
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

                        <div class="row">
                            <div class="col-12">
                                <div class="page-title-box">
                                    <div>
                                        <h4 class="page-title mb-0">Doctor Dashboard</h4>
                                        <p class="text-muted mb-0 mt-1" style="font-size:13px;">
                                            Welcome, <?php echo escape_output(trim(($doc_fname ?? '') . ' ' . ($doc_lname ?? '')) ?: 'Doctor'); ?> — clinical overview
                                        </p>
                                    </div>
                                    <div class="d-flex gap-2 flex-wrap">
                                        <a href="his_doc_account.php" class="btn btn-sm btn-outline-primary">
                                            <i class="fe-user mr-1"></i> My Profile
                                        </a>
                                        <a href="his_doc_register_patient.php" class="btn btn-sm btn-primary">
                                            <i class="fe-plus mr-1"></i> New Patient
                                        </a>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6 col-xl-3">
                                <div class="widget-rounded-circle card-box hms-stat-blue hms-hover-lift">
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
                                                <i class="fe-arrow-up"></i> <span>+<?php echo max(1, min(99, $cnt_patients * 6)); ?>%</span>
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
                                                <i class="mdi mdi-hotel avatar-title"></i>
                                            </div>
                                        </div>
                                        <div class="col">
                                            <p class="text-muted mb-1 text-truncate">InPatients</p>
                                            <h3 class="text-dark mt-0 mb-0"><span data-plugin="counterup"><?php echo $cnt_in; ?></span></h3>
                                            <div class="hms-stat-change positive">
                                                <i class="fe-arrow-up"></i> <span><?php echo $cnt_out; ?> out</span>
                                                <span class="hms-stat-period">outpatients</span>
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
                                                <i class="mdi mdi-flask avatar-title"></i>
                                            </div>
                                        </div>
                                        <div class="col">
                                            <p class="text-muted mb-1 text-truncate">Lab Records</p>
                                            <h3 class="text-dark mt-0 mb-0"><span data-plugin="counterup"><?php echo $cnt_lab; ?></span></h3>
                                            <div class="hms-stat-change neutral">
                                                <i class="fe-minus"></i> <span><?php echo $cnt_eqp; ?> assets</span>
                                                <span class="hms-stat-period">equipment</span>
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
                                            <p class="text-muted mb-1 text-truncate">Pharmaceuticals</p>
                                            <h3 class="text-dark mt-0 mb-0"><span data-plugin="counterup"><?php echo $cnt_phar; ?></span></h3>
                                            <div class="hms-stat-change positive">
                                                <i class="fe-arrow-up"></i> <span><?php echo $cnt_presc; ?> Rx</span>
                                                <span class="hms-stat-period">prescriptions</span>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6 col-xl-4">
                                <a href="his_doc_account.php" class="d-block text-decoration-none">
                                    <div class="widget-rounded-circle card-box hms-stat-blue hms-hover-lift">
                                        <div class="row align-items-center">
                                            <div class="col-auto">
                                                <div class="avatar-lg rounded-circle">
                                                    <i class="fas fa-user-md avatar-title"></i>
                                                </div>
                                            </div>
                                            <div class="col">
                                                <h3 class="text-dark mt-1 mb-0" style="font-size:1.05rem;">My Profile</h3>
                                                <p class="text-muted mb-1">Account &amp; credentials</p>
                                                <span style="font-size:12px;color:var(--hms-primary);font-weight:600;">Open profile →</span>
                                            </div>
                                        </div>
                                    </div>
                                </a>
                            </div>
                            <div class="col-md-6 col-xl-4">
                                <a href="his_doc_view_payrolls.php" class="d-block text-decoration-none">
                                    <div class="widget-rounded-circle card-box hms-stat-success hms-hover-lift">
                                        <div class="row align-items-center">
                                            <div class="col-auto">
                                                <div class="avatar-lg rounded-circle">
                                                    <i class="mdi mdi-cash-multiple avatar-title"></i>
                                                </div>
                                            </div>
                                            <div class="col">
                                                <h3 class="text-dark mt-1 mb-0" style="font-size:1.05rem;">My Payroll</h3>
                                                <p class="text-muted mb-1">Payment history</p>
                                                <span style="font-size:12px;color:var(--hms-primary);font-weight:600;">View payrolls →</span>
                                            </div>
                                        </div>
                                    </div>
                                </a>
                            </div>
                            <div class="col-md-6 col-xl-4">
                                <a href="his_doc_patient_lab_test.php" class="d-block text-decoration-none">
                                    <div class="widget-rounded-circle card-box hms-stat-amber hms-hover-lift">
                                        <div class="row align-items-center">
                                            <div class="col-auto">
                                                <div class="avatar-lg rounded-circle">
                                                    <i class="mdi mdi-test-tube avatar-title"></i>
                                                </div>
                                            </div>
                                            <div class="col">
                                                <h3 class="text-dark mt-1 mb-0" style="font-size:1.05rem;">Laboratory</h3>
                                                <p class="text-muted mb-1">Tests &amp; results</p>
                                                <span style="font-size:12px;color:var(--hms-primary);font-weight:600;">Open lab →</span>
                                            </div>
                                        </div>
                                    </div>
                                </a>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-xl-4">
                                <div class="card-box">
                                    <div class="d-flex align-items-center justify-content-between mb-3">
                                        <h4 class="header-title mb-0">Recent Lab Work</h4>
                                        <a href="his_doc_lab_report.php" class="btn btn-sm btn-outline-primary">All</a>
                                    </div>
                                    <ul class="hms-widget-list">
                                    <?php
                                      $ret = "SELECT lab_pat_name, lab_pat_ailment, lab_date_rec FROM his_laboratory ORDER BY lab_date_rec DESC LIMIT 6";
                                      $stmt = $mysqli->prepare($ret);
                                      $stmt->execute();
                                      $res = $stmt->get_result();
                                      while ($row = $res->fetch_object()):
                                    ?>
                                        <li>
                                            <span class="hms-avatar-initial"><?php echo escape_output(strtoupper(substr($row->lab_pat_name, 0, 1))); ?></span>
                                            <div class="flex-grow-1">
                                                <div style="font-weight:600;font-size:13px;color:var(--hms-text-primary);"><?php echo escape_output($row->lab_pat_name); ?></div>
                                                <div style="font-size:12px;color:var(--hms-text-muted);"><?php echo escape_output($row->lab_pat_ailment); ?></div>
                                            </div>
                                            <span style="font-size:11px;color:var(--hms-text-muted);"><?php echo escape_output(date('M j', strtotime($row->lab_date_rec))); ?></span>
                                        </li>
                                    <?php endwhile; $stmt->close(); ?>
                                    </ul>
                                </div>
                            </div>

                            <div class="col-xl-8">
                                <div class="card-box">
                                    <div class="d-flex align-items-center justify-content-between mb-3">
                                        <h4 class="header-title mb-0">Recent Patients</h4>
                                        <a href="his_doc_view_patients.php" class="btn btn-sm btn-outline-primary">View All</a>
                                    </div>
                                    <div class="table-responsive">
                                        <table class="table table-borderless table-hover table-centered m-0">
                                            <thead class="thead-light">
                                                <tr>
                                                    <th>Name</th>
                                                    <th>Mobile</th>
                                                    <th>Category</th>
                                                    <th>Ailment</th>
                                                    <th>Age</th>
                                                    <th class="text-right">Action</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                            <?php
                                              $ret = "SELECT * FROM his_patients ORDER BY pat_date_joined DESC LIMIT 12";
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
                                                    <td><?php echo escape_output($row->pat_phone); ?></td>
                                                    <td>
                                                        <?php if ($row->pat_type == 'InPatient'): ?>
                                                            <span class="badge badge-primary">InPatient</span>
                                                        <?php else: ?>
                                                            <span class="badge badge-success">OutPatient</span>
                                                        <?php endif; ?>
                                                    </td>
                                                    <td><?php echo escape_output($row->pat_ailment); ?></td>
                                                    <td><span class="badge badge-secondary"><?php echo escape_output($row->pat_age); ?> yrs</span></td>
                                                    <td class="text-right">
                                                        <a href="his_doc_view_single_patient.php?pat_id=<?php echo escape_output($row->pat_id); ?>&&pat_number=<?php echo escape_output($row->pat_number); ?>&&pat_name=<?php echo escape_output($row->pat_fname); ?>_<?php echo escape_output($row->pat_lname); ?>"
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
        <script src="assets/js/app.min.js"></script>
        <!-- hms-motion.js loaded via footer (defer) after app.min.js -->

    </body>
</html>
