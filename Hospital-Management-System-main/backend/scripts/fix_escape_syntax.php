<?php
declare(strict_types=1);

$root = dirname(__DIR__, 2);
$fixed = 0;

$iterator = new RecursiveIteratorIterator(
    new RecursiveDirectoryIterator($root . '/backend')
);

foreach ($iterator as $fileInfo) {
    if ($fileInfo->getExtension() !== 'php') {
        continue;
    }
    $path = $fileInfo->getPathname();
    if (str_contains($path, DIRECTORY_SEPARATOR . 'scripts' . DIRECTORY_SEPARATOR)) {
        continue;
    }
    $content = file_get_contents($path);
    $new = preg_replace(
        '/escape_output\((\$[a-zA-Z_][\w>-]*(?:->[a-zA-Z_][\w]*)*);/',
        'escape_output($1);',
        $content,
        -1,
        $count
    );
    if ($count > 0) {
        file_put_contents($path, $new);
        $fixed += $count;
        echo "Fixed $count in $path\n";
    }
}

echo "\nTotal fixes: $fixed\n";
