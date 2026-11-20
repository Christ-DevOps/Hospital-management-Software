<?php
// backend/assets/inc/efile_fhir.php
// Builds a FHIR-shaped JSON Bundle from the e-file data returned by
// backend/assets/inc/efile_data.php. This is a pragmatic subset of HL7
// FHIR (not full-spec conformant) intended as a starting shape for future
// inter-facility exchange — there is no second system to exchange with yet.

function nex_fhir_patient($p) {
    return [
        'resourceType' => 'Patient',
        'id' => $p->efile_uuid ?: null,
        'identifier' => [
            ['system' => 'urn:nexclinic:pat_number', 'value' => $p->pat_number],
        ],
        'name' => [['family' => $p->pat_lname, 'given' => array_filter([$p->pat_fname])]],
        'gender' => $p->pat_sex ? strtolower($p->pat_sex) : null,
        'birthDate' => $p->pat_dob ?: null,
        'telecom' => array_values(array_filter([
            $p->pat_phone ? ['system' => 'phone', 'value' => $p->pat_phone] : null,
            $p->pat_email ? ['system' => 'email', 'value' => $p->pat_email] : null,
        ])),
        'address' => [array_filter([
            'text' => $p->pat_addr ?: null,
            'city' => $p->pat_village ?: null,
            'district' => $p->pat_division ?: null,
        ])],
    ];
}

function nex_fhir_encounter($a) {
    return [
        'resourceType' => 'Encounter',
        'id' => 'appt-' . $a->appt_id,
        'status' => strtolower((string) $a->appt_status),
        'period' => ['start' => $a->appt_datetime],
        'reasonCode' => $a->appt_reason ? [['text' => $a->appt_reason]] : [],
        'participant' => [['individual' => ['display' => trim((string) $a->doctor_name)]]],
    ];
}

function nex_fhir_medication_request($pr) {
    return [
        'resourceType' => 'MedicationRequest',
        'id' => 'pres-' . $pr->pres_id,
        'status' => 'completed',
        'intent' => 'order',
        'authoredOn' => $pr->pres_date,
        'reasonCode' => [['text' => $pr->pres_pat_ailment]],
        'dosageInstruction' => [['text' => strip_tags((string) $pr->pres_ins)]],
    ];
}

function nex_fhir_vitals_observation($v) {
    return [
        'resourceType' => 'Observation',
        'id' => 'vit-' . $v->vit_id,
        'status' => 'final',
        'effectiveDateTime' => $v->vit_daterec,
        'component' => [
            ['code' => ['text' => 'Body temperature'], 'valueString' => $v->vit_bodytemp],
            ['code' => ['text' => 'Heart rate'], 'valueString' => $v->vit_heartpulse],
            ['code' => ['text' => 'Respiratory rate'], 'valueString' => $v->vit_resprate],
            ['code' => ['text' => 'Blood pressure'], 'valueString' => $v->vit_bloodpress],
        ],
    ];
}

function nex_fhir_lab_observation($l) {
    return [
        'resourceType' => 'Observation',
        'id' => 'lab-' . $l->lab_id,
        'status' => 'final',
        'effectiveDateTime' => $l->lab_date_rec,
        'code' => ['text' => $l->lab_pat_ailment],
        'note' => [
            ['text' => 'Tests: ' . strip_tags((string) $l->lab_pat_tests)],
            ['text' => 'Results: ' . strip_tags((string) $l->lab_pat_results)],
        ],
    ];
}

function nex_fhir_procedure($s) {
    return [
        'resourceType' => 'Procedure',
        'id' => 'surg-' . $s->s_id,
        'status' => strtolower((string) $s->s_pat_status) === 'successful' ? 'completed' : 'unknown',
        'performedDateTime' => $s->s_pat_date,
        'reasonCode' => [['text' => $s->s_pat_ailment]],
        'performer' => [['actor' => ['display' => $s->s_doc]]],
    ];
}

function nex_fhir_document_reference($m) {
    return [
        'resourceType' => 'DocumentReference',
        'id' => 'mdr-' . $m->mdr_id,
        'status' => 'current',
        'date' => $m->mdr_date_rec,
        'type' => ['text' => $m->mdr_pat_ailment],
        'description' => strip_tags((string) $m->mdr_pat_prescr),
    ];
}

/**
 * @param array $efile Output of nex_efile_bundle()
 */
function nex_fhir_bundle(array $efile) {
    $entries = [['resource' => nex_fhir_patient($efile['patient'])]];
    foreach ($efile['appointments'] as $a) {
        $entries[] = ['resource' => nex_fhir_encounter($a)];
    }
    foreach ($efile['prescriptions'] as $pr) {
        $entries[] = ['resource' => nex_fhir_medication_request($pr)];
    }
    foreach ($efile['vitals'] as $v) {
        $entries[] = ['resource' => nex_fhir_vitals_observation($v)];
    }
    foreach ($efile['lab_results'] as $l) {
        $entries[] = ['resource' => nex_fhir_lab_observation($l)];
    }
    foreach ($efile['surgery'] as $s) {
        $entries[] = ['resource' => nex_fhir_procedure($s)];
    }
    foreach ($efile['medical_records'] as $m) {
        $entries[] = ['resource' => nex_fhir_document_reference($m)];
    }

    return [
        'resourceType' => 'Bundle',
        'type' => 'collection',
        'identifier' => ['system' => 'urn:nexclinic:efile', 'value' => $efile['patient']->efile_uuid],
        'entry' => $entries,
    ];
}
