<?php
// backend/assets/inc/nex_authz.php
// The only authorization entry point. Default-deny: an action with no
// policy, or a session with no nex_user_id, is refused — the exact inverse
// of nex_script_sector_map(), where an unlisted page was unguarded.
//
// Keyed to (action, resource), not to a filename — nex_authorize('patient.view', ...)
// travels with the code, not with basename($_SERVER['SCRIPT_NAME']).
//
// Every decision (grant or denial) is logged via nex_audit_access()
// (backend/assets/inc/nex_sectors.php). Superadmin reach is handled inside
// nex_has_sector() itself (also nex_sectors.php) — visible and audited,
// never a silent bypass — so it is not special-cased again here.

require_once __DIR__ . '/nex_care.php';
require_once __DIR__ . '/nex_sectors.php';

/**
 * @param string $action e.g. 'patient.view'
 * @param array  $ctx    e.g. ['pat_id' => 42]. May also carry 'entity_type'/'entity_id'
 *                        for the audit row; defaults to the action's own context.
 */
function nex_authorize($action, array $ctx = []) {
    global $mysqli;

    if (empty($_SESSION['nex_user_id'])) {
        nex_deny($mysqli, $action, $ctx, 'no_actor');
    }
    $actor = (int) $_SESSION['nex_user_id'];

    $policies = nex_policies();
    if (!isset($policies[$action])) {
        // Unknown action = denied, not allowed. A page added without a
        // policy fails closed instead of open.
        nex_deny($mysqli, $action, $ctx, 'no_policy');
    }

    if (!$policies[$action]($actor, $ctx, $mysqli)) {
        nex_deny($mysqli, $action, $ctx, 'policy_failed');
    }

    nex_audit_access($mysqli, $action, $ctx, 'granted');
    return true;
}

function nex_deny($mysqli, $action, array $ctx, $reason) {
    nex_audit_access($mysqli, $action, $ctx, 'denied:' . $reason);
    http_response_code(403);
    include __DIR__ . '/403.php';
    exit;
}

/** Whoever owns $id_column=$id_value in $table, or null if not found. */
function nex_row_owner($mysqli, $table, $id_column, $owner_column, $id_value) {
    $stmt = $mysqli->prepare("SELECT `{$owner_column}` AS owner_id FROM `{$table}` WHERE `{$id_column}` = ? LIMIT 1");
    if (!$stmt) {
        return null;
    }
    $id_value = (int) $id_value;
    $stmt->bind_param('i', $id_value);
    $stmt->execute();
    $row = $stmt->get_result()->fetch_object();
    $stmt->close();
    return ($row && $row->owner_id !== null) ? (int) $row->owner_id : null;
}

function nex_is_author($mysqli, $table, $id_column, $author_column, $id_value, $actor_id) {
    $owner = nex_row_owner($mysqli, $table, $id_column, $author_column, $id_value);
    return $owner !== null && $owner === (int) $actor_id;
}

/** Patient a medical record belongs to, resolved via the legacy number column. */
function nex_patient_of_note($mysqli, $mdr_id) {
    $stmt = $mysqli->prepare('SELECT mdr_pat_number FROM his_medical_records WHERE mdr_id=? LIMIT 1');
    if (!$stmt) {
        return null;
    }
    $mdr_id = (int) $mdr_id;
    $stmt->bind_param('i', $mdr_id);
    $stmt->execute();
    $row = $stmt->get_result()->fetch_object();
    $stmt->close();
    return $row ? nex_resolve_pat_id($mysqli, $row->mdr_pat_number) : null;
}

/** doctor/nurse/chief, from his_docs.clinical_rank via the nex_users legacy link. Null if unavailable. */
function nex_clinical_rank($mysqli, $actor_id) {
    $stmt = @$mysqli->prepare(
        "SELECT d.clinical_rank FROM nex_users u
         INNER JOIN his_docs d ON d.doc_id = u.legacy_id AND u.legacy_source = 'doc'
         WHERE u.user_id = ? LIMIT 1"
    );
    if (!$stmt) {
        return null;
    }
    $actor_id = (int) $actor_id;
    $stmt->bind_param('i', $actor_id);
    $stmt->execute();
    $row = $stmt->get_result()->fetch_object();
    $stmt->close();
    return $row ? $row->clinical_rank : null;
}

/**
 * action => fn(actorId, ctx, mysqli): bool
 * Absent action = denied (see nex_authorize). Extend this table as later
 * Stage 4 waves need more actions — never widen an existing entry to cover
 * a page it wasn't written for.
 */
function nex_policies() {
    return [
        'patient.view' => function ($actor, $ctx, $db) {
            if (nex_has_sector('reception')) {
                return true;
            }
            if (nex_has_sector('clinical')) {
                return nex_has_active_care_relationship($db, $ctx['pat_id'] ?? 0, $actor);
            }
            return false;
        },
        'patient.edit' => function ($actor, $ctx, $db) {
            // Admin fallback matches reception.dashboard/visit.* below: a hospital
            // admin covering the desk must be able to register a patient.
            return nex_has_sector('reception') || nex_is_hospital_admin();
        },
        'reception.dashboard' => function ($actor, $ctx, $db) {
            return nex_has_sector('reception') || nex_is_hospital_admin();
        },
        'visit.checkin' => function ($actor, $ctx, $db) {
            return nex_has_sector('reception') || nex_is_hospital_admin();
        },
        'visit.manage' => function ($actor, $ctx, $db) {
            return nex_has_sector('reception') || nex_is_hospital_admin();
        },
        'note.view' => function ($actor, $ctx, $db) {
            if (!nex_has_sector('clinical')) {
                return false;
            }
            $patId = nex_patient_of_note($db, $ctx['mdr_id'] ?? 0);
            return $patId !== null && nex_has_active_care_relationship($db, $patId, $actor);
        },
        'note.edit' => function ($actor, $ctx, $db) {
            return nex_is_author($db, 'his_medical_records', 'mdr_id', 'authored_by', $ctx['mdr_id'] ?? 0, $actor);
        },
        'prescription.create' => function ($actor, $ctx, $db) {
            if (!nex_has_sector('clinical')) {
                return false;
            }
            return nex_clinical_rank($db, $actor) !== 'nurse';
        },
        'prescription.dispense' => function ($actor, $ctx, $db) {
            return nex_has_sector('pharmacy');
        },
        'lab.result.enter' => function ($actor, $ctx, $db) {
            return nex_has_sector('laboratory');
        },
        'payroll.view' => function ($actor, $ctx, $db) {
            return nex_has_sector('payroll');
        },
        'employee.view' => function ($actor, $ctx, $db) {
            return nex_has_sector('hr');
        },
    ];
}
