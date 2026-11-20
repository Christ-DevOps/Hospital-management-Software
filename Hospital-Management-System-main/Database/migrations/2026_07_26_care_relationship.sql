-- NexClinic Stage D.2 — Care relationship
-- Answers "is this my patient?" Deliberately a separate table, not a single
-- attending_doc_id column on his_patients: care is many-to-many and
-- time-bounded (a covering doctor last Tuesday is not the same fact as
-- today's attending). ON DELETE RESTRICT (not SET NULL, unlike Stage B's
-- clinical FKs) — a care relationship must never silently orphan.
--
-- Requires Stage B.2 (nex_users) already applied.

CREATE TABLE IF NOT EXISTS `nex_care_relationship` (
  `rel_id` int(20) NOT NULL AUTO_INCREMENT,
  `pat_id` int(20) NOT NULL,
  `user_id` int(20) NOT NULL,
  `relation` ENUM('attending','consulting','nurse','covering') NOT NULL DEFAULT 'attending',
  `started_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `ended_at` timestamp NULL DEFAULT NULL,
  `created_by` int(20) DEFAULT NULL,
  PRIMARY KEY (`rel_id`),
  KEY `idx_rel_pat` (`pat_id`),
  KEY `idx_rel_user_active` (`user_id`, `ended_at`),
  CONSTRAINT `fk_rel_pat` FOREIGN KEY (`pat_id`) REFERENCES `his_patients` (`pat_id`) ON DELETE RESTRICT,
  CONSTRAINT `fk_rel_user` FOREIGN KEY (`user_id`) REFERENCES `nex_users` (`user_id`) ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ---------------------------------------------------------------------------
-- Pulled forward from the original Stage 5 plan: break-glass access (below,
-- and the authorization kernel's every-decision logging in Stage 3) needs
-- these columns to exist now, not after Stage 4/5 land. nex_audit_log itself
-- was defined in 2026_07_21_platform_tiers.sql (not yet applied on every
-- deployment); create it here too so this migration doesn't silently depend
-- on that unrelated one having run first.
--
-- Placed BEFORE the his_surgery backfill below: that backfill requires
-- Stage B *relational* (his_surgery.pat_id/doc_id), which is a separate
-- migration from Stage B.2 (sectors) and may not be applied yet. If it
-- isn't, that one INSERT fails and aborts the rest of a naive multi-query
-- run — the schema this migration exists to add must not be collateral
-- damage to an optional backfill.
-- ---------------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `nex_audit_log` (
  `audit_id` bigint(20) NOT NULL AUTO_INCREMENT,
  `actor_user_ref` varchar(80) DEFAULT NULL,
  `actor_tier` varchar(40) DEFAULT NULL,
  `action` varchar(120) NOT NULL,
  `entity_type` varchar(80) DEFAULT NULL,
  `entity_id` varchar(80) DEFAULT NULL,
  `detail` text,
  `ip_address` varchar(64) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`audit_id`),
  KEY `idx_audit_created` (`created_at`),
  KEY `idx_audit_actor` (`actor_user_ref`),
  KEY `idx_audit_action` (`action`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

ALTER TABLE `nex_audit_log`
  ADD COLUMN `outcome` varchar(40) DEFAULT NULL AFTER `action`,
  ADD COLUMN `break_glass` tinyint(1) NOT NULL DEFAULT 0,
  ADD KEY `idx_audit_outcome` (`outcome`),
  ADD KEY `idx_audit_entity` (`entity_type`, `entity_id`);

-- ---------------------------------------------------------------------------
-- Backfill from the two places a doctor-patient link is already implied.
-- Run each statement independently (not as one multi-query batch) — the
-- his_surgery backfill requires Stage B relational and is expected to fail
-- with "Unknown column 's.pat_id'" on any deployment where that migration
-- hasn't run yet; that failure must not block the his_appointments backfill
-- or anything above this line.
-- ---------------------------------------------------------------------------
-- NOT EXISTS guards make this safe to re-run (no unique key on
-- pat_id+user_id+relation — an active relationship may legitimately have
-- historical ended_at rows alongside it, so uniqueness isn't enforced at
-- the schema level; idempotency is enforced here instead).
INSERT INTO nex_care_relationship (pat_id, user_id, relation, started_at)
SELECT DISTINCT a.pat_id, u.user_id, 'attending', a.appt_datetime
FROM his_appointments a
JOIN nex_users u ON u.legacy_source='doc' AND u.legacy_id = a.doc_id
WHERE a.pat_id IS NOT NULL AND a.doc_id IS NOT NULL
  AND NOT EXISTS (
    SELECT 1 FROM nex_care_relationship existing
    WHERE existing.pat_id = a.pat_id AND existing.user_id = u.user_id AND existing.ended_at IS NULL
  );

-- Requires Stage B relational (his_surgery.pat_id/doc_id). Run separately;
-- expected to fail until that migration is applied.
INSERT INTO nex_care_relationship (pat_id, user_id, relation, started_at)
SELECT DISTINCT s.pat_id, u.user_id, 'attending', NOW()
FROM his_surgery s
JOIN nex_users u ON u.legacy_source='doc' AND u.legacy_id = s.doc_id
WHERE s.pat_id IS NOT NULL AND s.doc_id IS NOT NULL
  AND NOT EXISTS (
    SELECT 1 FROM nex_care_relationship existing
    WHERE existing.pat_id = s.pat_id AND existing.user_id = u.user_id AND existing.ended_at IS NULL
  );
