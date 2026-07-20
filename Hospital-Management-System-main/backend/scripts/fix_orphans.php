<?php
declare(strict_types=1);

$dirs = [
    __DIR__ . '/../admin',
    __DIR__ . '/../doc',
];

$orphan = <<<'TEXT'

            else
            {
                $err = "Try Again Later";
            }
    }
TEXT;

foreach ($dirs as $dir) {
    foreach (glob($dir . '/*.php') as $file) {
        $content = file_get_contents($file);
        if (str_contains($content, $orphan)) {
            file_put_contents($file, str_replace($orphan, '', $content));
            echo "Fixed orphan: $file\n";
        }
    }
}

echo "Orphan cleanup done.\n";
