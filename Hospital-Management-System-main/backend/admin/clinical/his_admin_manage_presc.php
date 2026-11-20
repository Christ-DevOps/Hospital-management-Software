<?php
$nex_admin_base = '../';
require __DIR__ . '/../_init.php';
require_once __DIR__ . '/../../assets/inc/nex_prescription.php';
check_login();
  $aid=$_SESSION['ad_id'];
    $deleteResult = process_post_delete($mysqli, 'delete_pres_number', 'delete from his_prescriptions where pres_number=?', 's', [], 'Prescription deleted');
  if ($deleteResult !== null) {
      if (isset($deleteResult['success'])) {
          $success = $deleteResult['success'];
      } else {
          $err = $deleteResult['err'];
      }
  }
  if (isset($_POST['cancel_pres_id'])) {
      nex_rx_authorize('prescription.create');
      $cancel = nex_rx_cancel($mysqli, (int) $_POST['cancel_pres_id']);
      if ($cancel['ok']) {
          $success = 'Prescription cancelled.';
      } else {
          $err = $cancel['err'] ?? 'Cancel failed.';
      }
  }
?>

<!DOCTYPE html>
<html lang="en">
    
<?php include __DIR__ . '/../assets/inc/head.php';?>

    <body>

        <!-- Begin page -->
        <div id="wrapper">

            <!-- Topbar Start -->
                <?php include __DIR__ . '/../assets/inc/nav.php';?>
            <!-- end Topbar -->

            <!-- ========== Left Sidebar Start ========== -->
                <?php include __DIR__ . '/../assets/inc/sidebar.php';?>
            <!-- Left Sidebar End -->

            <!-- ============================================================== -->
            <!-- Start Page Content here -->
            <!-- ============================================================== -->

            <div class="content-page">
                <div class="content">

                    <!-- Start Content-->
                    <div class="container-fluid">
                        
                        <!-- start page title -->
                        <div class="row">
                            <div class="col-12">
                                <div class="page-title-box">
                                    <div class="page-title-right">
                                        <ol class="breadcrumb m-0">
                                            <li class="breadcrumb-item"><a href="javascript: void(0);">Dashboard</a></li>
                                            <li class="breadcrumb-item"><a href="javascript: void(0);">Pharmacy</a></li>
                                            <li class="breadcrumb-item active">Manage Prescriptions</li>
                                        </ol>
                                    </div>
                                    <h4 class="page-title">Manage Prescriptions</h4>
                                </div>
                            </div>
                        </div>     
                        <!-- end page title --> 

                        <div class="row">
                            <div class="col-12">
                                <div class="card-box">
                                    <h4 class="header-title"></h4>
                                    <div class="mb-2">
                                        <div class="row">
                                            <div class="col-12 text-sm-center form-inline" >
                                                <div class="form-group mr-2" style="display:none">
                                                    <select id="demo-foo-filter-status" class="custom-select custom-select-sm">
                                                        <option value="">Show all</option>
                                                        <option value="Discharged">Discharged</option>
                                                        <option value="OutPatients">OutPatients</option>
                                                        <option value="InPatients">InPatients</option>
                                                    </select>
                                                </div>
                                                <div class="form-group">
                                                    <input id="demo-foo-search" type="text" placeholder="Search" class="form-control form-control-sm" autocomplete="on">
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    
                                    <div class="table-responsive">
                                        <table id="demo-foo-filtering" class="table table-bordered toggle-circle mb-0" data-page-size="7">
                                            <thead>
                                            <tr>
                                                <th>#</th>
                                                <th data-toggle="true">Patient Name</th>
                                                <th data-hide="phone">Patient Number</th>
                                                <th data-hide="phone">Address</th>
                                                <th data-hide="phone">Ailment</th>
                                                <th data-hide="phone">Age</th>
                                                <th data-hide="phone">Category</th>
                                                <th data-hide="phone">Pharmacy</th>
                                                <th data-hide="phone">Action</th>
                                            </tr>
                                            </thead>
                                            <?php
                                            /*
                                                *get details of allpatients
                                                *
                                            */
                                                $ret="SELECT * FROM  his_prescriptions ORDER BY pres_date DESC "; 
                                                //sql code to get to ten docs  randomly
                                                $stmt= $mysqli->prepare($ret) ;
                                                $stmt->execute() ;//ok
                                                $res=$stmt->get_result();
                                                $cnt=1;
                                                while($row=$res->fetch_object())
                                                {
                                            ?>

                                                <tbody>
                                                <tr>
                                                    <td><?php echo escape_output($cnt); ?></td>
                                                    <td><?php echo escape_output($row->pres_pat_name); ?></td>
                                                    <td><?php echo escape_output($row->pres_pat_number); ?></td>
                                                    <td><?php echo escape_output($row->pres_pat_addr); ?></td>
                                                    <td><?php echo escape_output($row->pres_pat_ailment); ?></td>
                                                    <td><?php echo escape_output($row->pres_pat_age); ?> Years</td>
                                                    <td><?php echo escape_output($row->pres_pat_type); ?></td>
                                                    <td><?php echo escape_output(isset($row->pres_status) ? nex_rx_status_label($row->pres_status) : '—'); ?></td>
                                                    <td>
                                                        <a href="his_admin_view_single_pres.php?pres_number=<?php echo escape_output($row->pres_number); ?>&amp;pres_id=<?php echo (int) $row->pres_id; ?>" class="badge badge-success"><i class="fas fa-eye"></i> View</a>
                                                        <?php if (isset($row->pres_status) && $row->pres_status === 'pending') { ?>
                                                        <form method="post" class="d-inline" onsubmit="return confirm('Cancel this pending prescription?');">
                                                            <input type="hidden" name="csrf_token" value="<?php echo escape_output(generate_csrf_token()); ?>">
                                                            <input type="hidden" name="cancel_pres_id" value="<?php echo (int) $row->pres_id; ?>">
                                                            <button type="submit" class="badge badge-secondary border-0">Cancel</button>
                                                        </form>
                                                        <?php } ?>
                                                        <?php echo render_delete_button('delete_pres_number', $row->pres_number); ?>

                                                    </td>
                                                </tr>
                                                </tbody>
                                            <?php  $cnt = $cnt +1 ; }?>
                                            <tfoot>
                                            <tr class="active">
                                                <td colspan="9">
                                                    <div class="text-right">
                                                        <ul class="pagination pagination-rounded justify-content-end footable-pagination m-t-10 mb-0"></ul>
                                                    </div>
                                                </td>
                                            </tr>
                                            </tfoot>
                                        </table>
                                    </div> <!-- end .table-responsive-->
                                </div> <!-- end card-box -->
                            </div> <!-- end col -->
                        </div>
                        <!-- end row -->

                    </div> <!-- container -->

                </div> <!-- content -->

                <!-- Footer Start -->
                 <?php include __DIR__ . '/../assets/inc/footer.php';?>
                <!-- end Footer -->

            </div>

            <!-- ============================================================== -->
            <!-- End Page content -->
            <!-- ============================================================== -->


        </div>
        <!-- END wrapper -->


        <!-- Right bar overlay-->
        <div class="rightbar-overlay"></div>

        <!-- Vendor js -->
        <script src="<?php echo escape_output(nex_admin_url('assets/js/vendor.min.js')); ?>"></script>

        <!-- Footable js -->
        <script src="<?php echo escape_output(nex_admin_url('assets/libs/footable/footable.all.min.js')); ?>"></script>

        <!-- Init js -->
        <script src="<?php echo escape_output(nex_admin_url('assets/js/pages/foo-tables.init.js')); ?>"></script>

        <!-- App js -->
        <script src="<?php echo escape_output(nex_admin_url('assets/js/app.min.js')); ?>"></script>
        
    </body>

</html>