<?php
declare(strict_types=1);

$root = dirname(__DIR__);

$patterns = [
    '/<a href="[^"]*\?delete_account=<\?php echo \$row->([^;]+); \?>" class="badge badge-danger"><i class="fas fa-trash-alt "><\/i> Delete<\/a>/'
        => "<?php echo render_delete_button('delete_account', \$row->$1); ?>",
    '/<a href="[^"]*\?delete_vendor_number=<\?php echo \$row->([^>]+)\?>" class="badge badge-danger"><i class="fas fa-trash"><\/i> Delete Record<\/a>/'
        => "<?php echo render_delete_button('delete_vendor_number', \$row->v_number); ?>",
    '/<a href="[^"]*\?delete_eqp=<\?php echo \$row->([^>]+)\?>" class="badge badge-danger"><i class="fas fa-trash"><\/i> Delete Record<\/a>/'
        => "<?php echo render_delete_button('delete_eqp', \$row->eqp_code); ?>",
    '/<a href="[^"]*\?delete_s_number=<\?php echo \$row->([^>]+)\?>" class="badge badge-danger"><i class="fas fa-trash"><\/i> Delete Record<\/a>/'
        => "<?php echo render_delete_button('delete_s_number', \$row->s_number); ?>",
];

foreach ([$root . '/admin', $root . '/doc'] as $dir) {
    foreach (glob($dir . '/*.php') as $file) {
        $content = file_get_contents($file);
        $original = $content;
        foreach ($patterns as $pattern => $replacement) {
            $content = preg_replace($pattern, $replacement, $content);
        }
        $content = preg_replace(
            '/<\?php echo \$row->/',
            '<?php echo escape_output($row->',
            $content
        );
        if ($content !== $original) {
            file_put_contents($file, $content);
            echo "Patched: $file\n";
        }
    }
}

echo "Patch 3 complete.\n";
