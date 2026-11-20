<?php
$admin = dirname(__DIR__) . '/admin';
$errors = 0;
$files = 0;
foreach (glob($admin . '/*/*.php') as $f) {
    $files++;
    $out = [];
    $code = 0;
    exec('php -l ' . escapeshellarg($f) . ' 2>&1', $out, $code);
    $line = trim(implode(' ', $out));
    if ($code !== 0 || stripos($line, 'No syntax errors') === false) {
        echo $line . PHP_EOL;
        $errors++;
    }
}
echo "files={$files} errors={$errors}" . PHP_EOL;
exit($errors > 0 ? 1 : 0);
