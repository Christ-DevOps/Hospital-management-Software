<?php
// backend/assets/inc/mailer.php
// Patient notification delivery. Never throws — every attempt is logged to
// his_notifications regardless of outcome, so a mail failure can never
// block the clinical action that triggered it.

$phpmailer_dir = dirname(__DIR__, 2) . '/vendor/phpmailer/src';
if (file_exists($phpmailer_dir . '/Exception.php')) {
    require_once $phpmailer_dir . '/Exception.php';
    require_once $phpmailer_dir . '/PHPMailer.php';
    require_once $phpmailer_dir . '/SMTP.php';
}

function nex_generate_uuid_v4() {
    $data = random_bytes(16);
    $data[6] = chr((ord($data[6]) & 0x0f) | 0x40);
    $data[8] = chr((ord($data[8]) & 0x3f) | 0x80);
    return vsprintf('%s%s-%s-%s-%s-%s%s%s', str_split(bin2hex($data), 4));
}

function nex_mail_enabled() {
    $flag = getenv('MAIL_ENABLED');
    return $flag === '1' || strtolower((string) $flag) === 'true';
}

function nex_render_notification_html($subject, array $bodyLines) {
    $fromName = escape_output(getenv('MAIL_FROM_NAME') ?: 'NexClinic');
    $html = '<div style="font-family:Arial,sans-serif;max-width:520px;margin:0 auto;">'
        . '<h2 style="color:#2E37A4;margin-bottom:4px;">' . escape_output($fromName) . '</h2>'
        . '<h3 style="margin-top:0;">' . escape_output($subject) . '</h3>';
    foreach ($bodyLines as $line) {
        $html .= '<p style="color:#333;line-height:1.5;">' . escape_output($line) . '</p>';
    }
    $html .= '<hr style="border:none;border-top:1px solid #eee;margin:20px 0;">'
        . '<p style="color:#999;font-size:12px;">This is an automated message. Please do not reply to this email.</p>'
        . '</div>';
    return $html;
}

function send_patient_notification($mysqli, $pat_id, $type, $subject, $bodyHtml) {
    $pat_id = (int) $pat_id;
    $recipient_email = null;

    if ($pat_id > 0) {
        $stmt = $mysqli->prepare('SELECT pat_email FROM his_patients WHERE pat_id = ?');
        $stmt->bind_param('i', $pat_id);
        $stmt->execute();
        $row = $stmt->get_result()->fetch_object();
        $stmt->close();
        if ($row) {
            $recipient_email = $row->pat_email ?: null;
        }
    }

    $log = $mysqli->prepare(
        'INSERT INTO his_notifications (pat_id, notif_type, recipient_email, subject, body_html, status) VALUES (?,?,?,?,?,?)'
    );
    $pending = 'pending';
    $pat_id_param = $pat_id > 0 ? $pat_id : null;
    $log->bind_param('isssss', $pat_id_param, $type, $recipient_email, $subject, $bodyHtml, $pending);
    $log->execute();
    $notif_id = $mysqli->insert_id;
    $log->close();

    if (empty($recipient_email)) {
        nex_mark_notification($mysqli, $notif_id, 'failed', 'No email address on file for this patient.');
        return false;
    }
    if (!nex_mail_enabled()) {
        nex_mark_notification($mysqli, $notif_id, 'failed', 'Mail delivery disabled (MAIL_ENABLED=0).');
        return false;
    }
    if (!class_exists('PHPMailer\\PHPMailer\\PHPMailer')) {
        nex_mark_notification($mysqli, $notif_id, 'failed', 'PHPMailer library not installed.');
        return false;
    }

    try {
        $mail = new PHPMailer\PHPMailer\PHPMailer(true);
        $mail->isSMTP();
        $mail->Host = getenv('MAIL_HOST') ?: '';
        $mail->SMTPAuth = true;
        $mail->Username = getenv('MAIL_USERNAME') ?: '';
        $mail->Password = getenv('MAIL_PASSWORD') ?: '';
        $encryption = getenv('MAIL_ENCRYPTION') ?: 'tls';
        if ($encryption === 'ssl') {
            $mail->SMTPSecure = PHPMailer\PHPMailer\PHPMailer::ENCRYPTION_SMTPS;
        } else {
            $mail->SMTPSecure = PHPMailer\PHPMailer\PHPMailer::ENCRYPTION_STARTTLS;
        }
        $mail->Port = (int) (getenv('MAIL_PORT') ?: 587);
        $mail->setFrom(getenv('MAIL_FROM_ADDRESS') ?: 'noreply@nexclinic.local', getenv('MAIL_FROM_NAME') ?: 'NexClinic');
        $mail->addAddress($recipient_email);
        $mail->isHTML(true);
        $mail->Subject = $subject;
        $mail->Body = $bodyHtml;
        $mail->send();
        nex_mark_notification($mysqli, $notif_id, 'sent', null);
        return true;
    } catch (\Throwable $e) {
        nex_mark_notification($mysqli, $notif_id, 'failed', substr($e->getMessage(), 0, 500));
        return false;
    }
}

function nex_mark_notification($mysqli, $notif_id, $status, $error_message) {
    $stmt = $mysqli->prepare(
        'UPDATE his_notifications SET status = ?, error_message = ?, sent_at = IF(? = \'sent\', NOW(), sent_at) WHERE notif_id = ?'
    );
    $stmt->bind_param('sssi', $status, $error_message, $status, $notif_id);
    $stmt->execute();
    $stmt->close();
}
