<?php
// backend/assets/inc/notification_events.php
// One composer per patient-facing event. Each builds a subject/body and
// hands off to send_patient_notification() (backend/assets/inc/mailer.php),
// which never throws — callers do not need to guard these calls.

require_once __DIR__ . '/mailer.php';

function notify_appointment_booked($mysqli, $pat_id, $appt_datetime, $doc_name) {
    $when = date('l, d M Y \a\t H:i', strtotime($appt_datetime));
    $body = nex_render_notification_html('Appointment Confirmed', [
        "Your appointment with Dr. {$doc_name} has been booked for {$when}.",
        'Please arrive 15 minutes early with any relevant documents.',
    ]);
    return send_patient_notification($mysqli, $pat_id, 'appt_booked', 'Your appointment is confirmed', $body);
}

function notify_appointment_rescheduled($mysqli, $pat_id, $appt_datetime, $doc_name) {
    $when = date('l, d M Y \a\t H:i', strtotime($appt_datetime));
    $body = nex_render_notification_html('Appointment Updated', [
        "Your appointment with Dr. {$doc_name} has been rescheduled to {$when}.",
        'If this new time does not work for you, please contact the hospital.',
    ]);
    return send_patient_notification($mysqli, $pat_id, 'appt_rescheduled', 'Your appointment has been rescheduled', $body);
}

function notify_prescription_issued($mysqli, $pat_id, $ailment, $instructions) {
    $body = nex_render_notification_html('New Prescription', [
        "A new prescription has been issued for: {$ailment}.",
        'Please log in to your patient portal or visit the pharmacy for full instructions.',
    ]);
    return send_patient_notification($mysqli, $pat_id, 'prescription', 'A new prescription has been added to your file', $body);
}

function notify_prescription_dispensed($mysqli, $pat_id, $pres_number) {
    $ref = $pres_number !== '' ? " (#{$pres_number})" : '';
    $body = nex_render_notification_html('Medication Ready', [
        "Your prescription{$ref} has been dispensed by the pharmacy.",
        'Please follow the dosage instructions provided with your medication.',
    ]);
    return send_patient_notification($mysqli, $pat_id, 'prescription_dispensed', 'Your medication has been dispensed', $body);
}

function notify_lab_result_ready($mysqli, $pat_id) {
    $body = nex_render_notification_html('Lab Results Ready', [
        'Your laboratory results are now available.',
        'Please contact the hospital or log in to your patient portal to review them with your doctor.',
    ]);
    return send_patient_notification($mysqli, $pat_id, 'lab_result', 'Your lab results are ready', $body);
}

function notify_patient_discharged($mysqli, $pat_id, $status) {
    $body = nex_render_notification_html('Discharge Update', [
        "Your discharge status has been updated to: {$status}.",
        'Thank you for choosing our hospital. Please follow any discharge instructions given by your doctor.',
    ]);
    return send_patient_notification($mysqli, $pat_id, 'discharge', 'Your discharge status has been updated', $body);
}

function notify_patient_registered($mysqli, $pat_id, $efile_uuid) {
    $body = nex_render_notification_html('Welcome', [
        'Your patient file has been created.',
        "Your electronic file reference number is: {$efile_uuid}.",
        'Keep this reference for any future consultation or transfer between facilities.',
    ]);
    return send_patient_notification($mysqli, $pat_id, 'registration', 'Welcome — your patient file has been created', $body);
}
