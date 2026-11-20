<?php
if (!function_exists('nex_admin_url')) {
    require_once dirname(__DIR__, 3) . '/assets/inc/nex_paths.php';
}
?>
<footer class="footer">
    <div class="container-fluid">
        <div class="row">
            <div class="col-md-12">
                2020 - <?php echo date('Y'); ?> &copy; NexClinic. </a>
            </div>

        </div>
    </div>
</footer>

<!-- HMS Motion: deferred so it runs after sync vendor/app scripts -->
<script defer src="<?php echo escape_output(nex_admin_url('../assets/js/hms-motion.js')); ?>"></script>
