<?php
/**
 * Restore one admin page from git HEAD into a sector folder with clean bootstrap.
 * Usage: php restore_sector_file.php clinical his_admin_view_single_patient.php
 */
if ($argc < 3) {
    fwrite(STDERR, "Usage: php restore_sector_file.php <sector> <filename>\n");
    exit(1);
}
$sector = $argv[1];
$file = $argv[2];
$root = dirname(__DIR__, 2);
$repo = dirname($root);
$gitPaths = [$repo, $root];
$gitFile = "Hospital-Management-System-main/backend/admin/{$file}";
$content = null;
foreach ($gitPaths as $gp) {
    foreach (['HEAD:' . $gitFile, 'HEAD:backend/admin/' . $file] as $spec) {
        $cmd = 'git -C ' . escapeshellarg($gp) . ' show ' . escapeshellarg($spec) . ' 2>&1';
        $out = shell_exec($cmd);
        if ($out && strpos($out, 'fatal:') !== 0 && strpos($out, '<?php') !== false) {
            $content = $out;
            break 2;
        }
    }
}
if ($content === null) {
    fwrite(STDERR, "Could not load from git\n");
    exit(1);
}

$extraRequires = [];
if (preg_match_all("#include(?:_once)?\\(\\s*['\"]\\.\\./assets/inc/([^'\"]+)['\"]\\s*\\)\\s*;#", $content, $m)) {
    foreach ($m[1] as $inc) {
        $extraRequires[] = "require_once __DIR__ . '/../../assets/inc/{$inc}';";
    }
}

$content = preg_replace('#^<\\?php.*?check_login\\(\\);\\s*#s', '', $content, 1);
$content = preg_replace('#^\\s*\\$aid\\s*=\\s*\\$_SESSION\\[[\'"]ad_id[\'"]\\];\\s*#', '', $content);

$close = '?' . '>';
$open = '<' . '?php';

$boot = "{$open}\n\$nex_admin_base = '../';\nrequire __DIR__ . '/../_init.php';\n";
foreach (array_unique($extraRequires) as $req) {
    $boot .= $req . "\n";
}
$boot .= "check_login();\n";
$boot .= "\$aid = \$_SESSION['ad_id'] ?? null;\n";

$trim = ltrim($content);
if (strpos($trim, $close) !== 0 && strpos($trim, $open) !== 0) {
    $boot .= $close . "\n";
}

$content = $boot . $trim;

$content = preg_replace(
    "#include\\s*\\(\\s*['\"]assets/inc/([^'\"]+)['\"]\\s*\\)\\s*;#",
    "include __DIR__ . '/../assets/inc/\$1';",
    $content
);

$repl = '$1="' . $open . ' echo escape_output(nex_admin_url(\'assets/$2\')); ' . $close . '"';
$content = preg_replace('#(src|href)="assets/([^"]+)"#', $repl, $content);

$destDir = dirname(__DIR__) . '/admin/' . $sector;
if (!is_dir($destDir)) {
    mkdir($destDir, 0777, true);
}
$dest = $destDir . '/' . $file;
file_put_contents($dest, $content);
echo "Wrote {$dest}\n";
exec('php -l ' . escapeshellarg($dest) . ' 2>&1', $out, $code);
echo implode("\n", $out) . "\n";
exit($code);
