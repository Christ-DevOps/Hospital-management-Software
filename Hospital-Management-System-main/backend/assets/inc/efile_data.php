<?php
// backend/assets/inc/efile_data.php
// Shared patient e-file aggregation queries, reused by both the HTML e-file
// page and the FHIR-lite JSON export so the SQL isn't duplicated.
//
// Stage B (Database/migrations/2026_08_21_stage_b_relational.sql) adds a
// pat_id FK to his_prescriptions/his_laboratory/his_vitals/his_surgery/
// his_medical_records, but is not guaranteed to be applied on every
// deployment yet. These tables are therefore always looked up by the
// legacy *_number string column (matched against the patient's pat_number),
// which is populated on every deployment regardless of migration stage.
// his_appointments is the exception — it has always carried pat_id.

function nex_efile_patient($mysqli, $pat_id) {
    $stmt = $mysqli->prepare('SELECT * FROM his_patients WHERE pat_id=?');
    $stmt->bind_param('i', $pat_id);
    $stmt->execute();
    $row = $stmt->get_result()->fetch_object();
    $stmt->close();
    return $row;
}

function nex_efile_appointments($mysqli, $pat_id) {
    $stmt = $mysqli->prepare("SELECT a.*, CONCAT(COALESCE(d.doc_fname,''),' ',COALESCE(d.doc_lname,'')) AS doctor_name
        FROM his_appointments a
        LEFT JOIN his_docs d ON d.doc_id = a.doc_id
        WHERE a.pat_id=?
        ORDER BY a.appt_datetime DESC");
    $stmt->bind_param('i', $pat_id);
    $stmt->execute();
    $rows = [];
    $res = $stmt->get_result();
    while ($row = $res->fetch_object()) {
        $rows[] = $row;
    }
    $stmt->close();
    return $rows;
}

function nex_efile_prescriptions($mysqli, $pat_number) {
    $stmt = $mysqli->prepare('SELECT * FROM his_prescriptions WHERE pres_pat_number=? ORDER BY pres_date DESC');
    $stmt->bind_param('s', $pat_number);
    $stmt->execute();
    $rows = [];
    $res = $stmt->get_result();
    while ($row = $res->fetch_object()) {
        $rows[] = $row;
    }
    $stmt->close();
    return $rows;
}

function nex_efile_lab_results($mysqli, $pat_number) {
    $stmt = $mysqli->prepare('SELECT * FROM his_laboratory WHERE lab_pat_number=? ORDER BY lab_date_rec DESC');
    $stmt->bind_param('s', $pat_number);
    $stmt->execute();
    $rows = [];
    $res = $stmt->get_result();
    while ($row = $res->fetch_object()) {
        $rows[] = $row;
    }
    $stmt->close();
    return $rows;
}

function nex_efile_vitals($mysqli, $pat_number) {
    $stmt = $mysqli->prepare('SELECT * FROM his_vitals WHERE vit_pat_number=? ORDER BY vit_daterec DESC');
    $stmt->bind_param('s', $pat_number);
    $stmt->execute();
    $rows = [];
    $res = $stmt->get_result();
    while ($row = $res->fetch_object()) {
        $rows[] = $row;
    }
    $stmt->close();
    return $rows;
}

function nex_efile_surgery($mysqli, $pat_number) {
    $stmt = $mysqli->prepare('SELECT * FROM his_surgery WHERE s_pat_number=? ORDER BY s_pat_date DESC');
    $stmt->bind_param('s', $pat_number);
    $stmt->execute();
    $rows = [];
    $res = $stmt->get_result();
    while ($row = $res->fetch_object()) {
        $rows[] = $row;
    }
    $stmt->close();
    return $rows;
}

function nex_efile_medical_records($mysqli, $pat_number) {
    $stmt = $mysqli->prepare('SELECT * FROM his_medical_records WHERE mdr_pat_number=? ORDER BY mdr_date_rec DESC');
    $stmt->bind_param('s', $pat_number);
    $stmt->execute();
    $rows = [];
    $res = $stmt->get_result();
    while ($row = $res->fetch_object()) {
        $rows[] = $row;
    }
    $stmt->close();
    return $rows;
}

/**
 * Full e-file bundle for one patient, or null if the patient does not exist.
 */
function nex_efile_bundle($mysqli, $pat_id) {
    $patient = nex_efile_patient($mysqli, $pat_id);
    if (!$patient) {
        return null;
    }
    return [
        'patient' => $patient,
        'appointments' => nex_efile_appointments($mysqli, $pat_id),
        'prescriptions' => nex_efile_prescriptions($mysqli, $patient->pat_number),
        'lab_results' => nex_efile_lab_results($mysqli, $patient->pat_number),
        'vitals' => nex_efile_vitals($mysqli, $patient->pat_number),
        'surgery' => nex_efile_surgery($mysqli, $patient->pat_number),
        'medical_records' => nex_efile_medical_records($mysqli, $patient->pat_number),
    ];
}
