<?php
  session_start();
  include('../config.php');
  include('../security.php');
  include('../assets/inc/patient_cameroon_fields.php');
  include('../assets/inc/efile_data.php');
  include('assets/inc/checklogin.php');
  check_login();

  $pat_id = (int) ($_GET['pat_id'] ?? 0);
  $efile = $pat_id > 0 ? nex_efile_bundle($mysqli, $pat_id) : null;
?>

<!DOCTYPE html>
    <html lang="en">

    <?php include('assets/inc/head.php');?>

    <body>

        <!-- Begin page -->
        <div id="wrapper">

            <!-- Topbar Start -->
             <?php include("assets/inc/nav.php");?>
            <!-- end Topbar -->

            <!-- ========== Left Sidebar Start ========== -->
                <?php include("assets/inc/sidebar.php");?>
            <!-- Left Sidebar End -->

            <div class="content-page">
                <div class="content">

                    <div class="container-fluid">

                        <div class="row">
                            <div class="col-12">
                                <div class="page-title-box">
                                    <div class="page-title-right">
                                        <ol class="breadcrumb m-0">
                                            <li class="breadcrumb-item"><a href="his_doc_dashboard.php">Dashboard</a></li>
                                            <li class="breadcrumb-item"><a href="his_doc_view_patients.php">Patients</a></li>
                                            <li class="breadcrumb-item active">Electronic File</li>
                                        </ol>
                                    </div>
                                    <h4 class="page-title">Patient Electronic File</h4>
                                </div>
                            </div>
                        </div>

                        <?php if (!$efile) { ?>
                            <div class="row">
                                <div class="col-12">
                                    <div class="card-box">
                                        <p class="text-muted mb-0">Patient not found. <a href="his_doc_view_patients.php">Back to patient list</a></p>
                                    </div>
                                </div>
                            </div>
                        <?php } else {
                            $p = $efile['patient'];
                        ?>
                        <div class="row">
                            <div class="col-lg-4 col-xl-4">
                                <div class="card-box">
                                    <h4 class="header-title"><?php echo escape_output($p->pat_fname . ' ' . $p->pat_lname); ?></h4>
                                    <p class="text-muted mb-2 font-13"><strong>Patient Number :</strong> <span class="ml-2"><?php echo escape_output($p->pat_number); ?></span></p>
                                    <p class="text-muted mb-2 font-13"><strong>E-File Reference :</strong> <span class="ml-2"><?php echo escape_output($p->efile_uuid ?: 'Not yet generated'); ?></span></p>
                                    <p class="text-muted mb-2 font-13"><strong>Mobile :</strong> <span class="ml-2"><?php echo escape_output($p->pat_phone); ?></span></p>
                                    <p class="text-muted mb-2 font-13"><strong>Address :</strong> <span class="ml-2"><?php echo escape_output($p->pat_addr); ?></span></p>
                                    <p class="text-muted mb-2 font-13"><strong>Date Of Birth :</strong> <span class="ml-2"><?php echo escape_output($p->pat_dob); ?></span></p>
                                    <p class="text-muted mb-2 font-13"><strong>Age :</strong> <span class="ml-2"><?php echo escape_output($p->pat_age); ?> Years</span></p>
                                    <p class="text-muted mb-2 font-13"><strong>Ailment :</strong> <span class="ml-2"><?php echo escape_output($p->pat_ailment); ?></span></p>
                                    <?php nex_render_cameroon_view($p); ?>
                                    <hr>
                                    <?php if ($p->efile_uuid) { ?>
                                        <a class="btn btn-outline-primary btn-sm btn-block" href="his_doc_efile_export.php?pat_id=<?php echo (int) $p->pat_id; ?>" target="_blank">
                                            <i class="mdi mdi-download"></i> Export FHIR-lite JSON
                                        </a>
                                    <?php } ?>
                                </div>
                            </div>

                            <div class="col-lg-8 col-xl-8">
                                <div class="card-box">
                                    <h4 class="header-title">Appointments</h4>
                                    <?php if (empty($efile['appointments'])) { ?>
                                        <p class="text-muted">No appointments on file.</p>
                                    <?php } else { ?>
                                        <div class="table-responsive mb-4">
                                            <table class="table table-bordered mb-0">
                                                <thead><tr><th>Date/Time</th><th>Doctor</th><th>Status</th><th>Reason</th></tr></thead>
                                                <tbody>
                                                <?php foreach ($efile['appointments'] as $a) { ?>
                                                    <tr>
                                                        <td><?php echo escape_output(date('Y-m-d H:i', strtotime($a->appt_datetime))); ?></td>
                                                        <td><?php echo escape_output(trim($a->doctor_name)); ?></td>
                                                        <td><?php echo escape_output(ucfirst($a->appt_status)); ?></td>
                                                        <td><?php echo escape_output($a->appt_reason); ?></td>
                                                    </tr>
                                                <?php } ?>
                                                </tbody>
                                            </table>
                                        </div>
                                    <?php } ?>

                                    <h4 class="header-title">Prescriptions</h4>
                                    <?php if (empty($efile['prescriptions'])) { ?>
                                        <p class="text-muted">No prescriptions on file.</p>
                                    <?php } else { foreach ($efile['prescriptions'] as $pr) { ?>
                                        <div class="mb-3">
                                            <span class="text-muted"><?php echo escape_output(date('Y-m-d', strtotime($pr->pres_date))); ?></span>
                                            <h5 class="mt-0 mb-1"><?php echo escape_output($pr->pres_pat_ailment); ?></h5>
                                            <p class="text-muted"><?php echo escape_output(strip_tags($pr->pres_ins)); ?></p>
                                        </div>
                                    <?php } } ?>
                                    <hr>

                                    <h4 class="header-title">Vitals</h4>
                                    <?php if (empty($efile['vitals'])) { ?>
                                        <p class="text-muted">No vitals on file.</p>
                                    <?php } else { ?>
                                        <div class="table-responsive mb-4">
                                            <table class="table table-bordered mb-0">
                                                <thead><tr><th>Date</th><th>Temp</th><th>Heart Rate</th><th>Resp. Rate</th><th>Blood Pressure</th></tr></thead>
                                                <tbody>
                                                <?php foreach ($efile['vitals'] as $v) { ?>
                                                    <tr>
                                                        <td><?php echo escape_output(date('Y-m-d', strtotime($v->vit_daterec))); ?></td>
                                                        <td><?php echo escape_output($v->vit_bodytemp); ?>&deg;C</td>
                                                        <td><?php echo escape_output($v->vit_heartpulse); ?> BPM</td>
                                                        <td><?php echo escape_output($v->vit_resprate); ?> bpm</td>
                                                        <td><?php echo escape_output($v->vit_bloodpress); ?> mmHg</td>
                                                    </tr>
                                                <?php } ?>
                                                </tbody>
                                            </table>
                                        </div>
                                    <?php } ?>

                                    <h4 class="header-title">Laboratory Records</h4>
                                    <?php if (empty($efile['lab_results'])) { ?>
                                        <p class="text-muted">No laboratory records on file.</p>
                                    <?php } else { foreach ($efile['lab_results'] as $l) { ?>
                                        <div class="mb-3">
                                            <span class="text-muted"><?php echo escape_output(date('Y-m-d', strtotime($l->lab_date_rec))); ?></span>
                                            <h5 class="mt-0 mb-1"><?php echo escape_output($l->lab_pat_ailment); ?></h5>
                                            <p class="mb-1"><strong>Tests:</strong> <?php echo escape_output(strip_tags($l->lab_pat_tests)); ?></p>
                                            <p class="text-muted"><strong>Results:</strong> <?php echo escape_output(strip_tags($l->lab_pat_results)); ?></p>
                                        </div>
                                    <?php } } ?>
                                    <hr>

                                    <h4 class="header-title">Surgery Records</h4>
                                    <?php if (empty($efile['surgery'])) { ?>
                                        <p class="text-muted">No surgery records on file.</p>
                                    <?php } else { ?>
                                        <div class="table-responsive mb-4">
                                            <table class="table table-bordered mb-0">
                                                <thead><tr><th>Date</th><th>Surgeon</th><th>Ailment</th><th>Status</th></tr></thead>
                                                <tbody>
                                                <?php foreach ($efile['surgery'] as $s) { ?>
                                                    <tr>
                                                        <td><?php echo escape_output(date('Y-m-d', strtotime($s->s_pat_date))); ?></td>
                                                        <td><?php echo escape_output($s->s_doc); ?></td>
                                                        <td><?php echo escape_output($s->s_pat_ailment); ?></td>
                                                        <td><?php echo escape_output($s->s_pat_status); ?></td>
                                                    </tr>
                                                <?php } ?>
                                                </tbody>
                                            </table>
                                        </div>
                                    <?php } ?>

                                    <h4 class="header-title">Medical Records</h4>
                                    <?php if (empty($efile['medical_records'])) { ?>
                                        <p class="text-muted">No medical records on file.</p>
                                    <?php } else { foreach ($efile['medical_records'] as $m) { ?>
                                        <div class="mb-3">
                                            <span class="text-muted"><?php echo escape_output(date('Y-m-d', strtotime($m->mdr_date_rec))); ?></span>
                                            <h5 class="mt-0 mb-1"><?php echo escape_output($m->mdr_pat_ailment); ?></h5>
                                            <p class="text-muted"><?php echo escape_output(strip_tags((string) $m->mdr_pat_prescr)); ?></p>
                                        </div>
                                    <?php } } ?>
                                </div>
                            </div>
                        </div>
                        <?php } ?>

                    </div> <!-- container -->

                </div> <!-- content -->

                <!-- Footer Start -->
                <?php include('assets/inc/footer.php');?>
                <!-- end Footer -->

            </div>

        </div>
        <!-- END wrapper -->

        <div class="rightbar-overlay"></div>

        <script src="assets/js/vendor.min.js"></script>
        <script src="assets/js/app.min.js"></script>

    </body>

</html>
