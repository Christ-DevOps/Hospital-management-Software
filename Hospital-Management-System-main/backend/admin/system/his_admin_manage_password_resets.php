<?php
$nex_admin_base = '../';
require __DIR__ . '/../_init.php';
check_login();
  $aid=$_SESSION['ad_id'];

  if (isset($_POST['approve_reset'])) {
      $reset_id = (int) ($_POST['reset_id'] ?? 0);
      $stmt = $mysqli->prepare('SELECT email, status FROM his_pwdresets WHERE id = ?');
      $stmt->bind_param('i', $reset_id);
      $stmt->execute();
      $res = $stmt->get_result();
      $row = $res->fetch_object();
      $stmt->close();

      if (!$row || $row->status !== 'Pending') {
          $err = 'Reset request not found or already processed.';
      } else {
          $temp_password = bin2hex(random_bytes(4));
          $hashed = password_hash($temp_password, PASSWORD_DEFAULT);
          $updateDoc = $mysqli->prepare('UPDATE his_docs SET doc_pwd = ? WHERE doc_email = ?');
          $updateDoc->bind_param('ss', $hashed, $row->email);
          $updateDoc->execute();
          $updateDoc->close();

          $status = 'Approved';
          $updateReset = $mysqli->prepare('UPDATE his_pwdresets SET status = ? WHERE id = ?');
          $updateReset->bind_param('si', $status, $reset_id);
          $updateReset->execute();
          $updateReset->close();

          $success = 'Password reset approved for ' . $row->email . '. Temporary password: ' . $temp_password;
      }
  }

  $deleteResult = process_post_delete($mysqli, 'deleteRequest', 'DELETE FROM his_pwdresets WHERE id = ?', 'i', [(string) ($_POST['deleteRequest'] ?? '')], 'Reset request deleted');
  if ($deleteResult !== null) {
      if (isset($deleteResult['success'])) {
          $success = $deleteResult['success'];
      } else {
          $err = $deleteResult['err'];
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
                                            <li class="breadcrumb-item"><a href="javascript: void(0);">Password Resets</a></li>
                                            <li class="breadcrumb-item active">Manage</li>
                                        </ol>
                                    </div>
                                    <h4 class="page-title">Accounts Requesting For Password Resets</h4>
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
                                                <th data-toggle="true">Email</th>
                                                <th data-hide="phone">Status</th>
                                                <th data-hide="phone">Date Requested</th>
                                                <th data-hide="phone">Action</th>
                                            </tr>
                                            </thead>
                                            <?php
                                                $ret="SELECT * FROM  his_pwdresets"; 
                                                $stmt= $mysqli->prepare($ret) ;
                                                $stmt->execute() ;
                                                $res=$stmt->get_result();
                                                $cnt=1;
                                                while($row=$res->fetch_object())
                                                {
                                                    $requestedtime = $row->created_at ?? '';
                                                    if($row->status == 'Pending')
                                                    {
                                                        $action = '<form method="post" class="d-inline">'
                                                            . '<input type="hidden" name="csrf_token" value="' . escape_output(generate_csrf_token()) . '">'
                                                            . '<input type="hidden" name="reset_id" value="' . escape_output((string) $row->id) . '">'
                                                            . '<button type="submit" name="approve_reset" class="badge badge-danger border-0"><i class="fas fa-edit"></i> Approve Reset</button>'
                                                            . '</form> '
                                                            . render_delete_button('deleteRequest', $row->id);
                                                    }
                                                    else
                                                    {
                                                        $action = '<span class="badge badge-success">Approved</span> '
                                                            . render_delete_button('deleteRequest', $row->id);
                                                    }
                                            ?>

                                                <tbody>
                                                <tr>
                                                    <td><?php echo escape_output($cnt); ?></td>
                                                    <td><?php echo escape_output($row->email); ?></td>
                                                    <td><?php echo escape_output($row->status); ?></td>
                                                    <td><?php echo $requestedtime ? escape_output(date('d-M-Y H:i', strtotime($requestedtime))) : ''; ?></td>
                                                    <td><?php echo $action; ?></td>
                                                </tr>
                                                </tbody>
                                            <?php  $cnt = $cnt +1 ; }?>
                                            <tfoot>
                                            <tr class="active">
                                                <td colspan="8">
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
