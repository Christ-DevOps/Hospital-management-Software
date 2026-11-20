<?php
$nex_admin_base = '../';
require __DIR__ . '/../_init.php';
check_login();
		if(isset($_POST['update_doc']))
		{
			$doc_fname=$_POST['doc_fname'];
			$doc_lname=$_POST['doc_lname'];
			$doc_number=$_GET['doc_number'];
            $doc_email=$_POST['doc_email'];
            $doc_dpic = null;
            if (!empty($_FILES["doc_dpic"]["name"])) {
                $upload = handle_profile_upload('doc_dpic', __DIR__ . '/../doc/assets/images/users');
                if (isset($upload['err'])) {
                    $err = $upload['err'];
                } else {
                    $doc_dpic = $upload['filename'];
                }
            }
            if (!isset($err)) {
                if (!empty($_POST['doc_pwd'])) {
                    $doc_pwd = password_hash($_POST['doc_pwd'], PASSWORD_DEFAULT);
                    if ($doc_dpic !== null) {
                        $query="UPDATE his_docs SET doc_fname=?, doc_lname=?, doc_email=?, doc_pwd=?, doc_dpic=? WHERE doc_number = ?";
                        $stmt = $mysqli->prepare($query);
                        $stmt->bind_param('ssssss', $doc_fname, $doc_lname, $doc_email, $doc_pwd, $doc_dpic, $doc_number);
                    } else {
                        $query="UPDATE his_docs SET doc_fname=?, doc_lname=?, doc_email=?, doc_pwd=? WHERE doc_number = ?";
                        $stmt = $mysqli->prepare($query);
                        $stmt->bind_param('sssss', $doc_fname, $doc_lname, $doc_email, $doc_pwd, $doc_number);
                    }
                } else {
                    if ($doc_dpic !== null) {
                        $query="UPDATE his_docs SET doc_fname=?, doc_lname=?, doc_email=?, doc_dpic=? WHERE doc_number = ?";
                        $stmt = $mysqli->prepare($query);
                        $stmt->bind_param('sssss', $doc_fname, $doc_lname, $doc_email, $doc_dpic, $doc_number);
                    } else {
                        $query="UPDATE his_docs SET doc_fname=?, doc_lname=?, doc_email=? WHERE doc_number = ?";
                        $stmt = $mysqli->prepare($query);
                        $stmt->bind_param('ssss', $doc_fname, $doc_lname, $doc_email, $doc_number);
                    }
                }
                if ($stmt->execute()) {
                    $success = "Employee Details Updated";
                } else {
                    $err = "Please Try Again Or Try Later";
                }
                $stmt->close();
            }
        }
?>
<!--End Server Side-->
<!DOCTYPE html>
<html lang="en">
    
    <!--Head-->
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
                                            <li class="breadcrumb-item"><a href="his_admin_dashboard.php">Dashboard</a></li>
                                            <li class="breadcrumb-item"><a href="javascript: void(0);">Employee</a></li>
                                            <li class="breadcrumb-item active">Manage Employee</li>
                                        </ol>
                                    </div>
                                    <h4 class="page-title">Update Employee Details</h4>
                                </div>
                            </div>
                        </div>     
                        <!-- end page title --> 
                        <!-- Form row -->
                        <?php
                            $doc_number=$_GET['doc_number'];
                            $ret="SELECT  * FROM his_docs WHERE doc_number=?";
                            $stmt= $mysqli->prepare($ret) ;
                            $stmt->bind_param('s',$doc_number);
                            $stmt->execute() ;//ok
                            $res=$stmt->get_result();
                            //$cnt=1;
                            while($row=$res->fetch_object())
                            {
                        ?>
                        <div class="row">
                            <div class="col-12">
                                <div class="card">
                                    <div class="card-body">
                                        <h4 class="header-title">Fill all fields</h4>
                                        <!--Add Patient Form-->
                                        <form method="post" enctype="multipart/form-data">
                                    <input type="hidden" name="csrf_token" value="<?php echo generate_csrf_token(); ?>">
                                            <div class="form-row">
                                                <div class="form-group col-md-6">
                                                    <label for="inputEmail4" class="col-form-label">First Name</label>
                                                    <input type="text" required="required" value="<?php echo escape_output($row->doc_fname); ?>" name="doc_fname" class="form-control" id="inputEmail4" >
                                                </div>
                                                <div class="form-group col-md-6">
                                                    <label for="inputPassword4" class="col-form-label">Last Name</label>
                                                    <input required="required" type="text" value="<?php echo escape_output($row->doc_lname); ?>" name="doc_lname" class="form-control"  id="inputPassword4">
                                                </div>
                                            </div>

                                            <div class="form-group">
                                                <label for="inputAddress" class="col-form-label">Email</label>
                                                <input required="required" type="email" value="<?php echo escape_output($row->doc_email); ?>" class="form-control" name="doc_email" id="inputAddress">
                                            </div>
                                            
                                            <div class="form-row">
                                                <div class="form-group col-md-6">
                                                    <label for="inputCity" class="col-form-label">Password</label>
                                                    <input required="required"  type="password" name="doc_pwd" class="form-control" id="inputCity">
                                                </div> 
                                                
                                                <div class="form-group col-md-6">
                                                    <label for="inputCity" class="col-form-label">Profile Picture</label>
                                                    <input required="required"  type="file" name="doc_dpic" class="btn btn-success form-control"  id="inputCity">
                                                </div>
                                            </div>                                            

                                            <button type="submit" name="update_doc" class="ladda-button btn btn-success" data-style="expand-right">Add Employee</button>

                                        </form>
                                        <!--End Patient Form-->
                                    </div> <!-- end card-body -->
                                </div> <!-- end card-->
                            </div> <!-- end col -->
                        </div>
                        <!-- end row -->
                        <?php }?>

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

        <!-- App js-->
        <script src="<?php echo escape_output(nex_admin_url('assets/js/app.min.js')); ?>"></script>

        <!-- Loading buttons js -->
        <script src="<?php echo escape_output(nex_admin_url('assets/libs/ladda/spin.js')); ?>"></script>
        <script src="<?php echo escape_output(nex_admin_url('assets/libs/ladda/ladda.js')); ?>"></script>

        <!-- Buttons init js-->
        <script src="<?php echo escape_output(nex_admin_url('assets/js/pages/loading-btn.init.js')); ?>"></script>
        
    </body>

</html>