<?php
// backend/config.php

function loadEnv($path) {
    if (!file_exists($path)) {
        return;
    }
    $lines = file($path, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($lines as $line) {
        $line = trim($line);
        if ($line === '' || strpos($line, '#') === 0) {
            continue;
        }
        if (strpos($line, '=') === false) {
            continue;
        }
        list($name, $value) = explode('=', $line, 2);
        $name = trim($name);
        $value = trim($value, "\"'");
        if (!array_key_exists($name, $_SERVER) && !array_key_exists($name, $_ENV)) {
            putenv(sprintf('%s=%s', $name, $value));
            $_ENV[$name] = $value;
            $_SERVER[$name] = $value;
        }
    }
}

loadEnv(__DIR__ . '/.env');

$dbuser = getenv('DB_USER') ?: 'root';
$dbpass = getenv('DB_PASS') ?: '';
$host = getenv('DB_HOST') ?: 'localhost';
$db = getenv('DB_NAME') ?: 'hospital-management';

if (!extension_loaded('mysqli')) {
    http_response_code(500);
    die('PHP mysqli extension is required. Enable extension=mysqli in php.ini and restart the web server.');
}

$mysqli = new mysqli($host, $dbuser, $dbpass, $db);
if ($mysqli->connect_error) {
    error_log('Database connection failed: ' . $mysqli->connect_error);
    http_response_code(500);
    die('Database connection failed. Check backend/.env and ensure the database exists.');
}

$mysqli->set_charset('utf8mb4');
