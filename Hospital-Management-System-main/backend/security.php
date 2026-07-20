<?php
// backend/security.php

function escape_output($string) {
    if (is_null($string)) {
        return '';
    }
    return htmlspecialchars((string) $string, ENT_QUOTES, 'UTF-8');
}

function generate_csrf_token() {
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

function validate_csrf_token($token) {
    if (empty($_SESSION['csrf_token']) || empty($token)) {
        return false;
    }
    return hash_equals($_SESSION['csrf_token'], $token);
}

function init_secure_session() {
    if (session_status() === PHP_SESSION_ACTIVE) {
        return;
    }
    $secure = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off');
    session_set_cookie_params([
        'lifetime' => 0,
        'path' => '/',
        'secure' => $secure,
        'httponly' => true,
        'samesite' => 'Lax',
    ]);
    ini_set('session.use_strict_mode', '1');
    session_start();
}

function send_security_headers() {
    if (headers_sent()) {
        return;
    }
    header('X-Frame-Options: SAMEORIGIN');
    header('X-Content-Type-Options: nosniff');
    header('Referrer-Policy: strict-origin-when-cross-origin');
    header('X-XSS-Protection: 1; mode=block');
    if (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') {
        header('Strict-Transport-Security: max-age=31536000; includeSubDomains');
    }
}

function secure_redirect($path) {
    $host = $_SERVER['HTTP_HOST'] ?? 'localhost';
    $scheme = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
    $base = rtrim(dirname($_SERVER['PHP_SELF'] ?? ''), '/\\');
    $url = $scheme . '://' . $host . $base . '/' . ltrim($path, '/');
    header('Location: ' . $url);
    exit;
}

function enforce_post_csrf() {
    if (($_SERVER['REQUEST_METHOD'] ?? 'GET') !== 'POST') {
        return;
    }
    $script = basename($_SERVER['SCRIPT_NAME'] ?? '');
    $exempt = ['index.php'];
    if (in_array($script, $exempt, true)) {
        return;
    }
    if (!isset($_POST['csrf_token']) || !validate_csrf_token($_POST['csrf_token'])) {
        http_response_code(403);
        die('Invalid or missing CSRF token.');
    }
}

function process_post_delete($mysqli, $post_key, $sql, $bind_types, array $bind_values, $success_message) {
    if (($_SERVER['REQUEST_METHOD'] ?? 'GET') !== 'POST' || !isset($_POST[$post_key])) {
        return null;
    }
    if (!validate_csrf_token($_POST['csrf_token'] ?? '')) {
        return ['err' => 'Invalid CSRF token.'];
    }
    $stmt = $mysqli->prepare($sql);
    if (!$stmt) {
        return ['err' => 'Delete failed. Please try again.'];
    }
    if ($bind_types === 'i') {
        $value = (int) $_POST[$post_key];
        $stmt->bind_param($bind_types, $value);
    } else {
        $value = (string) $_POST[$post_key];
        $stmt->bind_param($bind_types, $value);
    }
    $stmt->execute();
    $affected = $stmt->affected_rows;
    $stmt->close();
    if ($affected > 0) {
        return ['success' => $success_message];
    }
    return ['err' => 'Record not found or already deleted.'];
}

function render_delete_button($post_key, $id_value, $confirm_message = 'Delete this record?') {
    $token = escape_output(generate_csrf_token());
    $id = escape_output((string) $id_value);
    $key = escape_output($post_key);
    $confirm = escape_output($confirm_message);
    return '<form method="post" class="d-inline" onsubmit="return confirm(\'' . $confirm . '\');">'
        . '<input type="hidden" name="csrf_token" value="' . $token . '">'
        . '<input type="hidden" name="' . $key . '" value="' . $id . '">'
        . '<button type="submit" class="badge badge-danger border-0"><i class="mdi mdi-trash-can-outline"></i> Delete</button>'
        . '</form>';
}

function handle_profile_upload($file_field, $upload_dir) {
    if (empty($_FILES[$file_field]['name']) || $_FILES[$file_field]['error'] === UPLOAD_ERR_NO_FILE) {
        return null;
    }
    if ($_FILES[$file_field]['error'] !== UPLOAD_ERR_OK) {
        return ['err' => 'File upload failed.'];
    }
    if ($_FILES[$file_field]['size'] > 2 * 1024 * 1024) {
        return ['err' => 'Image must be 2MB or smaller.'];
    }
    $allowed = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
    $finfo = new finfo(FILEINFO_MIME_TYPE);
    $mime = $finfo->file($_FILES[$file_field]['tmp_name']);
    if (!in_array($mime, $allowed, true)) {
        return ['err' => 'Only JPEG, PNG, GIF, and WebP images are allowed.'];
    }
    $ext = match ($mime) {
        'image/jpeg' => 'jpg',
        'image/png' => 'png',
        'image/gif' => 'gif',
        'image/webp' => 'webp',
        default => 'bin',
    };
    if (!is_dir($upload_dir)) {
        mkdir($upload_dir, 0755, true);
    }
    $filename = bin2hex(random_bytes(16)) . '.' . $ext;
    $destination = rtrim($upload_dir, '/\\') . DIRECTORY_SEPARATOR . $filename;
    if (!move_uploaded_file($_FILES[$file_field]['tmp_name'], $destination)) {
        return ['err' => 'Unable to save uploaded file.'];
    }
    return ['filename' => $filename];
}

function verify_password_hash($input, $hash) {
    if (password_verify($input, $hash)) {
        return true;
    }
    // Support legacy SHA1 hashes from original seed data during migration.
    return hash_equals((string) $hash, sha1($input));
}

function migrate_password_if_legacy($mysqli, $table, $id_column, $id_value, $password_column, $plain_password) {
    $new_hash = password_hash($plain_password, PASSWORD_DEFAULT);
    $stmt = $mysqli->prepare("UPDATE {$table} SET {$password_column} = ? WHERE {$id_column} = ?");
    if (!$stmt) {
        return;
    }
    $stmt->bind_param('si', $new_hash, $id_value);
    $stmt->execute();
    $stmt->close();
}

function require_admin_session() {
    if (empty($_SESSION['ad_id'])) {
        secure_redirect('index.php');
    }
}

function require_doctor_session() {
    if (empty($_SESSION['doc_id'])) {
        secure_redirect('index.php');
    }
}

if (session_status() !== PHP_SESSION_ACTIVE) {
    init_secure_session();
}
send_security_headers();
enforce_post_csrf();
