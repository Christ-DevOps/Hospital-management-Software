<?php
$nex_admin_base = '../';
require __DIR__ . '/../_init.php';
check_login();
  $aid=$_SESSION['ad_id'];
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
            <?php
                $v_number=$_GET['v_number'];
                $ret="SELECT  * FROM his_vendor WHERE v_number = ?";
                $stmt= $mysqli->prepare($ret) ;
                $stmt->bind_param('s',$v_number);
                $stmt->execute() ;//ok
                $res=$stmt->get_result();
                //$cnt=1;
                while($row=$res->fetch_object())
                {
            ?>

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
                                                <li class="breadcrumb-item"><a href="javascript: void(0);">Vendors</a></li>
                                                <li class="breadcrumb-item active">Manage Vendors</li>
                                            </ol>
                                        </div>
                                        <h4 class="page-title">#<?php echo escape_output($row->v_number); ?></h4>
                                    </div>
                                </div>
                            </div>     
                            <!-- end page title --> 

                            <div class="row">
                                <div class="col-12">
                                    <div class="card-box">
                                        <div class="row">
                                            <div class="col-xl-5">

                                                <div class="tab-content pt-0">

                                                    <div class="tab-pane active show" id="product-1-item">
                                                        <img src="<?php echo escape_output(nex_admin_url('assets/images/vendor.png" alt="" class="img-fluid mx-auto d-block rounded">
                                                    </div>
                            
                                                </div>
                                            </div> <!-- end col -->
                                            <div class="col-xl-7">
                                                <div class="pl-xl-3 mt-3 mt-xl-0">
                                                    <h2 class="mb-3">Vendor Name : <?php echo escape_output($row->v_name); ?></h2>
                                                    <hr>
                                                    <h3 class="text-danger">Vendor Contacts : <?php echo escape_output($row->v_phone); ?></h3>
                                                    <hr>
                                                    <h3 class="text-danger ">Vendor Email : <?php echo escape_output($row->v_email); ?></h3>
                                                    <hr>
                                                    <h3 class="text-danger ">Vendor Address : <?php echo escape_output($row->v_adr); ?></h3>
                                                    <hr>
                                                    
                                                    <h2 class="align-centre">Vendor Details</h2>
                                                    <hr>
                                                    <p class="text-muted mb-4')); ?>">
                                                        <?php echo escape_output($row->v_desc); ?>
                                                    </p>
                                                    <hr>
                                                   
                                                </div>
                                            </div> <!-- end col -->
                                        </div>
                                        <!-- end row -->

                                        
                                    </div> <!-- end card-->
                                </div> <!-- end col-->
                            </div>
                            <!-- end row-->
                            
                        </div> <!-- container -->

                    </div> <!-- content -->

                    <!-- Footer Start -->
                        <?php include __DIR__ . '/../assets/inc/footer.php';?>
                    <!-- end Footer -->

                </div>
            <?php }?>

            <!-- ============================================================== -->
            <!-- End Page content -->
            <!-- ============================================================== -->


        </div>
        <!-- END wrapper -->

        

        <!-- Right bar overlay-->
        <div class="rightbar-overlay"></div>

        <!-- Vendor js -->
        <script src="<?php echo escape_output(nex_admin_url('assets/js/vendor.min.js')); ?>"></script>

        <!-- App js -->
        <script src="<?php echo escape_output(nex_admin_url('assets/js/app.min.js')); ?>"></script>
        
    </body>

</html>