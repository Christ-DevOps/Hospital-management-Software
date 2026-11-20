<?php
// backend/assets/inc/nex_care.php
// "Is this my patient?" — backed by nex_care_relationship
// (Database/migrations/2026_07_26_care_relationship.sql), not a single
// attending-doctor column, since care is many-to-many and time-bounded.

function nex_has_active_care_relationship($mysqli, $pat_id, $user_id) {
    $stmt = $mysqli->prepare(
        'SELECT rel_id FROM nex_care_relationship WHERE pat_id=? AND user_id=? AND ended_at IS NULL LIMIT 1'
    );
    if (!$stmt) {
        return false;
    }
    $pat_id = (int) $pat_id;
    $user_id = (int) $user_id;
    $stmt->bind_param('ii', $pat_id, $user_id);
    $stmt->execute();
    $row = $stmt->get_result()->fetch_object();
    $stmt->close();
    return (bool) $row;
}

/**
 * Auto-create the relationship the moment a clinician treats a patient they
 * have no active relationship with yet — the table populates itself from
 * the normal act of care, no extra data entry required.
 */
function nex_ensure_care_relationship($mysqli, $pat_id, $user_id, $relation = 'attending') {
    $pat_id = (int) $pat_id;
    $user_id = (int) $user_id;
    if ($pat_id <= 0 || $user_id <= 0) {
        return false;
    }
    if (nex_has_active_care_relationship($mysqli, $pat_id, $user_id)) {
        return true;
    }
    $stmt = $mysqli->prepare(
        'INSERT INTO nex_care_relationship (pat_id, user_id, relation, created_by) VALUES (?,?,?,?)'
    );
    if (!$stmt) {
        return false;
    }
    $stmt->bind_param('iisi', $pat_id, $user_id, $relation, $user_id);
    $ok = $stmt->execute();
    $stmt->close();
    return $ok;
}

/**
 * Emergency override: any authenticated clinician may access a record they
 * have no care relationship for, provided they give a reason. Access is
 * granted immediately — deterrence is through visibility (a break_glass=1
 * audit row), not prevention. A hard block here gets worked around with
 * shared logins, which is worse than a logged override.
 */
function nex_break_glass($mysqli, $action, $pat_id, $reason) {
    $reason = trim((string) $reason);
    if ($reason === '') {
        return false;
    }
    nex_audit_access($mysqli, $action, ['entity_type' => 'patient', 'entity_id' => $pat_id, 'reason' => $reason], 'granted:break_glass', true);
    return true;
}
