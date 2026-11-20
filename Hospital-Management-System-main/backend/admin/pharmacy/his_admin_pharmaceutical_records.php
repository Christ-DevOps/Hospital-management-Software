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
                                            <li class="breadcrumb-item"><a href="javascript: void(0);">Reporting</a></li>
                                            <li class="breadcrumb-item active"> Pharmaceutical Records</li>
                                        </ol>
                                    </div>
                                    <h4 class="page-title">Pharmaceuticals</h4>
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
                                                <th data-toggle="true">Pharmaceutical Name</th>
                                                <th data-hide="phone">Pharmaceutical Barcode</th>
                                                <th data-hide="phone">Pharmaceutical Vendor</th>
                                                <th data-hide="phone">Pharmaceutical Category</th>
                                                <th data-hide="phone">Pharmaceutical Quantity</th>
                                                <th data-hide="phone">Action</th>
                                            </tr>
                                            </thead>
                                            <?php
                                            /*
                                                *get details of allpatients
                                                *
                                            */
                                                $ret="SELECT * FROM  his_pharmaceuticals ORDER BY RAND() "; 
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
                                                    <td><?php echo escape_output($row->phar_name); ?></td>
                                                    <td><?php echo escape_output($row->phar_bcode); ?></td>
                                                    <td><?php echo escape_output($row->phar_vendor); ?></td>
                                                    <td><?php echo escape_output($row->phar_cat); ?></td>
                                                    <td><?php echo escape_output($row->phar_qty); ?> Cartons</td>

                                                    <td><a href="his_admin_view_single_pharm.php?phar_bcode=<?php echo escape_output($row->phar_bcode); ?>" class="badge badge-success"><i class="far fa-eye "></i> View</a></td>
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