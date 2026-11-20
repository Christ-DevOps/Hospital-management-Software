-- NexClinic Stage C.2 — Global e-file identifier
-- pat_number is a 5-char per-hospital display code with no global uniqueness
-- guarantee. efile_uuid is a stable, globally-unique identifier for the
-- patient's electronic file, intended for future inter-facility sharing.
-- Run backend/scripts/backfill_efile_uuid.php once after this migration to
-- populate efile_uuid for any pre-existing patients (MySQL's UUID() is
-- avoided here so backfilled and newly-registered UUIDs are generated the
-- same way, in PHP).

ALTER TABLE `his_patients`
  ADD COLUMN `efile_uuid` char(36) DEFAULT NULL AFTER `pat_number`,
  ADD UNIQUE KEY `uq_patients_efile_uuid` (`efile_uuid`);
