<?php
	session_start();
	include('../config.php');
	include('../security.php');
	include('../assets/inc/patient_cameroon_fields.php');
	include('assets/inc/checklogin.php');
	check_login();
	require_once('../assets/inc/notification_events.php');
	if (isset($_POST['add_patient'])) {
		$collected = nex_collect_patient_post(true);
		if (!$collected['ok']) {
			$err = $collected['err'];
		} else {
			$d = $collected['data'];
			$pat_reg_hour = date('H:i:s');
			$efile_uuid = nex_generate_uuid_v4();
			$registered_by = nex_actor_id();
			$query = "INSERT INTO his_patients (
				pat_fname, pat_lname, pat_sex, pat_marital_status, pat_religion, pat_reg_hour,
				pat_age, pat_dob, pat_number, efile_uuid, pat_phone, pat_email, pat_type, pat_addr,
				pat_village, pat_division, pat_subdivision, pat_blood_group,
				pat_emergency_name, pat_emergency_phone, pat_ailment, registered_by
			) VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?)";
			$stmt = $mysqli->prepare($query);
			$stmt->bind_param(
				'sssssssssssssssssssssi',
				$d['pat_fname'],
				$d['pat_lname'],
				$d['pat_sex'],
				$d['pat_marital_status'],
				$d['pat_religion'],
				$pat_reg_hour,
				$d['pat_age'],
				$d['pat_dob'],
				$d['pat_number'],
				$efile_uuid,
				$d['pat_phone'],
				$d['pat_email'],
				$d['pat_type'],
				$d['pat_addr'],
				$d['pat_village'],
				$d['pat_division'],
				$d['pat_subdivision'],
				$d['pat_blood_group'],
				$d['pat_emergency_name'],
				$d['pat_emergency_phone'],
				$d['pat_ailment'],
				$registered_by
			);
			if ($stmt->execute()) {
				$success = "Patient Details Added";
				notify_patient_registered($mysqli, $mysqli->insert_id, $efile_uuid);
			} else {
				$err = "Please Try Again Or Try Later";
			}
			$stmt->close();
		}
	}
?>
<!DOCTYPE html>
<html lang="en">
    
    <?php include('assets/inc/head.php');?>
    <body>

        <div id="wrapper">

            <?php include("assets/inc/nav.php");?>
            <?php include("assets/inc/sidebar.php");?>

            <div class="content-page">
                <div class="content">
                    <div class="container-fluid">
                        
                        <div class="row">
                            <div class="col-12">
                                <div class="page-title-box">
                                    <div class="page-title-right">
                                        <ol class="breadcrumb m-0">
                                            <li class="breadcrumb-item"><a href="his_doc_dashboard.php">Dashboard</a></li>
                                            <li class="breadcrumb-item"><a href="javascript: void(0);">Patients</a></li>
                                            <li class="breadcrumb-item active">Add Patient</li>
                                        </ol>
                                    </div>
                                    <h4 class="page-title">Add Patient Details</h4>
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-12">
                                <div class="card">
                                    <div class="card-body">
                                        <h4 class="header-title">NexClinic patient registration</h4>
                                        <form method="post">
                                            <input type="hidden" name="csrf_token" value="<?php echo generate_csrf_token(); ?>">
                                            <div class="form-row">
                                                <div class="form-group col-md-6">
                                                    <label class="col-form-label">First Name <span class="text-danger">*</span></label>
                                                    <input type="text" required="required" name="pat_fname" class="form-control" placeholder="Patient's First Name">
                                                </div>
                                                <div class="form-group col-md-6">
                                                    <label class="col-form-label">Last Name <span class="text-danger">*</span></label>
                                                    <input required="required" type="text" name="pat_lname" class="form-control" placeholder="Patient's Last Name">
                                                </div>
                                            </div>

                                            <div class="form-row">
                                                <div class="form-group col-md-6">
                                                    <label class="col-form-label">Date Of Birth <span class="text-danger">*</span></label>
                                                    <input type="text" required="required" name="pat_dob" class="form-control" placeholder="DD/MM/YYYY">
                                                </div>
                                                <div class="form-group col-md-6">
                                                    <label class="col-form-label">Age <span class="text-danger">*</span></label>
                                                    <input required="required" type="text" name="pat_age" class="form-control" placeholder="Patient's Age">
                                                </div>
                                            </div>

                                            <?php nex_render_cameroon_fields(null); ?>

                                            <div class="form-group">
                                                <label class="col-form-label">Address <span class="text-danger">*</span></label>
                                                <input required="required" type="text" class="form-control" name="pat_addr" placeholder="Patient's Address">
                                            </div>

                                            <div class="form-row">
                                                <div class="form-group col-md-4">
                                                    <label class="col-form-label">Mobile Number <span class="text-danger">*</span></label>
                                                    <input required="required" type="text" name="pat_phone" class="form-control">
                                                </div>
                                                <div class="form-group col-md-4">
                                                    <label class="col-form-label">Patient Ailment <span class="text-danger">*</span></label>
                                                    <input required="required" type="text" name="pat_ailment" class="form-control">
                                                </div>
                                                <div class="form-group col-md-4">
                                                    <label class="col-form-label">Patient's Type <span class="text-danger">*</span></label>
                                                    <select required="required" name="pat_type" class="form-control">
                                                        <option value="">Choose</option>
                                                        <option>InPatient</option>
                                                        <option>OutPatient</option>
                                                    </select>
                                                </div>
                                                <div class="form-group col-md-2" style="display:none">
                                                    <?php 
                                                        $length = 5;    
                                                        $patient_number = substr(str_shuffle('0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZ'), 1, $length);
                                                    ?>
                                                    <label class="col-form-label">Patient Number</label>
                                                    <input type="text" name="pat_number" value="<?php echo escape_output($patient_number); ?>" class="form-control">
                                                </div>
                                            </div>

                                            <button type="submit" name="add_patient" class="ladda-button btn btn-primary" data-style="expand-right">Add Patient</button>
                                        </form>
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
        <script src="assets/js/app.min.js"></script>
        <script src="assets/libs/ladda/spin.js"></script>
        <script src="assets/libs/ladda/ladda.js"></script>
        <script src="assets/js/pages/loading-btn.init.js"></script>
    </body>
</html>
