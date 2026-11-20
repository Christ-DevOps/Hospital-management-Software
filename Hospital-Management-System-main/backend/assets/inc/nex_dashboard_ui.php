<?php
/**
 * Shared dashboard UI helpers (KPI cards, alert blocks).
 * Markup follows docs/HMS-DESIGN-SYSTEM.md §5.3.
 */

/**
 * @param string $label
 * @param string|int|float $value
 * @param string $tint hms-stat-blue|success|amber|purple|danger|info
 * @param string $icon mdi icon class without "mdi "
 * @param string $hint optional footer hint
 */
function nex_kpi_card($label, $value, $tint = 'hms-stat-blue', $icon = 'mdi-chart-line', $hint = '') {
    $tint = preg_replace('/[^a-z\-]/', '', (string) $tint);
    if ($tint === '') {
        $tint = 'hms-stat-blue';
    }
    $icon = preg_replace('/[^a-z0-9\-]/', '', (string) $icon);
    if (strpos($icon, 'mdi-') !== 0) {
        $icon = 'mdi-' . $icon;
    }
    ?>
    <div class="col-md-6 col-xl-4 col-lg-4 mb-3">
        <div class="widget-rounded-circle card-box <?php echo escape_output($tint); ?> hms-hover-lift h-100">
            <div class="row align-items-center">
                <div class="col-auto">
                    <div class="avatar-lg rounded-circle">
                        <i class="mdi <?php echo escape_output($icon); ?> avatar-title"></i>
                    </div>
                </div>
                <div class="col">
                    <p class="text-muted mb-1 text-truncate"><?php echo escape_output($label); ?></p>
                    <h3 class="mb-0"><span data-plugin="counterup"><?php echo escape_output((string) $value); ?></span></h3>
                    <?php if ($hint !== '') { ?>
                        <div class="hms-stat-change neutral mt-1"><?php echo escape_output($hint); ?></div>
                    <?php } ?>
                </div>
            </div>
        </div>
    </div>
    <?php
}

/**
 * @param string $title
 * @param string[] $items already escaped HTML strings, or plain text
 * @param string $tone danger|warning|info
 * @param bool $itemsAreHtml
 */
function nex_alert_panel($title, array $items, $tone = 'warning', $itemsAreHtml = false) {
    if (!$items) {
        return;
    }
    $tone = in_array($tone, ['danger', 'warning', 'info', 'success'], true) ? $tone : 'warning';
    ?>
    <div class="card-box border-left border-<?php echo escape_output($tone); ?> mb-3">
        <h5 class="header-title mb-2 text-<?php echo escape_output($tone); ?>"><?php echo escape_output($title); ?></h5>
        <ul class="mb-0 pl-3">
            <?php foreach ($items as $item) { ?>
                <li class="mb-1"><?php echo $itemsAreHtml ? $item : escape_output($item); ?></li>
            <?php } ?>
        </ul>
    </div>
    <?php
}
