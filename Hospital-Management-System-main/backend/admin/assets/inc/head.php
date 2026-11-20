<?php
if (!function_exists('nex_admin_url')) {
    require_once dirname(__DIR__, 3) . '/assets/inc/nex_paths.php';
}
?>
<head>
        <meta charset="utf-8" />
        <title>NexClinic</title>
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <meta content="NexClinic hospital operations platform" name="description" />
        <meta content="NexClinic" name="author" />
        <meta http-equiv="X-UA-Compatible" content="IE=edge" />
        <meta name="theme-color" content="#2E37A4" />
        <!-- App favicon -->
        <link rel="shortcut icon" href="<?php echo escape_output(nex_admin_url('assets/images/favicon.ico')); ?>">

        <!-- Plugins css -->
        <link href="<?php echo escape_output(nex_admin_url('assets/libs/flatpickr/flatpickr.min.css')); ?>" rel="stylesheet" type="text/css" />

        <!-- App css -->
        <link href="<?php echo escape_output(nex_admin_url('assets/css/bootstrap.min.css')); ?>" rel="stylesheet" type="text/css" />
        <link href="<?php echo escape_output(nex_admin_url('assets/css/icons.min.css')); ?>" rel="stylesheet" type="text/css" />
        <link href="<?php echo escape_output(nex_admin_url('assets/css/app.min.css')); ?>" rel="stylesheet" type="text/css" />
        <link rel="preconnect" href="https://fonts.googleapis.com">
        <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
        <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
        <!-- HMS Theme CSS (load after app.min.css for overrides) -->
        <link href="<?php echo escape_output(nex_admin_url('../assets/css/hms-theme.css')); ?>" rel="stylesheet" type="text/css" />
        <!-- Loading button css -->
         <link href="<?php echo escape_output(nex_admin_url('assets/libs/ladda/ladda-themeless.min.css')); ?>" rel="stylesheet" type="text/css" />

        <!-- Footable css -->
        <link href="<?php echo escape_output(nex_admin_url('assets/libs/footable/footable.core.min.css')); ?>" rel="stylesheet" type="text/css" />

       <!--Load Sweet Alert Javascript-->
       <script src="<?php echo escape_output(nex_admin_url('assets/js/swal.js')); ?>"></script>
       
        <!--Inject SWAL-->
        <?php if(isset($success)) {?>
        <!--This code for injecting an alert-->
                <script>
                            setTimeout(function () 
                            { 
                                swal("Success","<?php echo escape_output($success); ?>","success");
                            },
                                100);
                </script>

        <?php } ?>

        <?php if(isset($err)) {?>
        <!--This code for injecting an alert-->
                <script>
                            setTimeout(function () 
                            { 
                                swal("Failed","<?php echo escape_output($err); ?>","Failed");
                            },
                                100);
                </script>

        <?php } ?>

</head>
