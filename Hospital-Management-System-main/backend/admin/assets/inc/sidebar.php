<?php
/**
 * Workspace sidebar. Renders ONLY the active workspace's navigation.
 * Spec: docs/NEXCLINIC_WORKSPACES.md — other workspaces' items are never
 * emitted here, so there is nothing to un-hide in the DOM. Menu contents
 * live in backend/assets/inc/nex_workspace.php, not in this file.
 */
if (!function_exists('nex_admin_url')) {
    require_once dirname(__DIR__, 3) . '/assets/inc/nex_paths.php';
}
if (!function_exists('nex_workspace_for_user')) {
    require_once dirname(__DIR__, 3) . '/assets/inc/nex_workspace.php';
}

$nex_ws = nex_workspace_for_user();
$nex_ws_meta = nex_workspace_meta($nex_ws);
$nex_ws_nav = nex_workspace_nav($nex_ws);
$nex_ws_switch = nex_workspace_available();
$nex_current = basename($_SERVER['SCRIPT_NAME'] ?? '');

/** Mark the item matching the current script. */
$nex_is_active = function ($href) use ($nex_current) {
    return $nex_current !== '' && basename($href) === $nex_current;
};
?>
<div class="left-side-menu hms-workspace <?php echo escape_output($nex_ws_meta['accent']); ?>">

    <div class="slimscroll-menu">

        <div class="hms-workspace-brand">
            <i class="mdi <?php echo escape_output($nex_ws_meta['icon']); ?>"></i>
            <div class="hms-workspace-brand-text">
                <span class="hms-workspace-label"><?php echo escape_output($nex_ws_meta['label']); ?></span>
                <small class="hms-workspace-tagline"><?php echo escape_output($nex_ws_meta['tagline']); ?></small>
            </div>
        </div>

        <div id="sidebar-menu">
            <ul class="metismenu" id="side-menu">

                <?php foreach ($nex_ws_nav as $section) { ?>
                    <?php if (!empty($section['title'])) { ?>
                        <li class="menu-title"><?php echo escape_output($section['title']); ?></li>
                    <?php } ?>

                    <?php foreach ($section['items'] as $item) { ?>
                        <?php if (!empty($item['children'])) { ?>
                            <li>
                                <a href="javascript: void(0);">
                                    <i class="mdi <?php echo escape_output($item['icon'] ?? 'mdi-circle-small'); ?>"></i>
                                    <span><?php echo escape_output($item['label']); ?></span>
                                    <span class="menu-arrow"></span>
                                </a>
                                <ul class="nav-second-level" aria-expanded="false">
                                    <?php foreach ($item['children'] as $child) { ?>
                                        <li<?php echo $nex_is_active($child['href']) ? ' class="menuitem-active"' : ''; ?>>
                                            <a href="<?php echo escape_output(nex_admin_url($child['href'])); ?>">
                                                <?php echo escape_output($child['label']); ?>
                                            </a>
                                        </li>
                                    <?php } ?>
                                </ul>
                            </li>
                        <?php } else { ?>
                            <li<?php echo $nex_is_active($item['href']) ? ' class="menuitem-active"' : ''; ?>>
                                <a href="<?php echo escape_output(nex_admin_url($item['href'])); ?>">
                                    <i class="mdi <?php echo escape_output($item['icon'] ?? 'mdi-circle-small'); ?>"></i>
                                    <span><?php echo escape_output($item['label']); ?></span>
                                </a>
                            </li>
                        <?php } ?>
                    <?php } ?>
                <?php } ?>

                <?php if (count($nex_ws_switch) > 1) { ?>
                    <li class="menu-title">Switch Workspace</li>
                    <?php foreach ($nex_ws_switch as $code => $meta) { ?>
                        <?php if ($code === $nex_ws) { continue; } ?>
                        <li>
                            <a href="<?php echo escape_output(nex_admin_url('his_admin_workspace_switch.php?workspace=' . urlencode($code))); ?>">
                                <i class="mdi <?php echo escape_output($meta['icon']); ?>"></i>
                                <span><?php echo escape_output($meta['label']); ?></span>
                            </a>
                        </li>
                    <?php } ?>
                <?php } ?>

                <li class="hms-nav-logout">
                    <a href="<?php echo escape_output(nex_admin_url('his_admin_logout_partial.php')); ?>">
                        <i class="mdi mdi-logout"></i>
                        <span>Sign Out</span>
                    </a>
                </li>

            </ul>
        </div>

        <div class="clearfix"></div>
    </div>

</div>
