-- NexClinic Stage D.1 — Provenance columns
-- Records who created/authored/ordered each clinical row, so authorization
-- policies can later answer "whose record is this?" Nullable everywhere:
-- historical rows have no recoverable author, and a fabricated author is
-- worse than NULL (a policy can fall back to a sector check on NULL; it
-- cannot detect a wrong author).
--
-- Actor columns reference nex_users.user_id (the canonical actor going
-- forward), NOT his_docs.doc_id / his_admin.ad_id.

ALTER TABLE `his_patients`
  ADD COLUMN `registered_by` int(20) DEFAULT NULL AFTER `pat_id`,
  ADD KEY `idx_pat_registered_by` (`registered_by`);

ALTER TABLE `his_medical_records`
  ADD COLUMN `authored_by` int(20) DEFAULT NULL AFTER `mdr_id`,
  ADD KEY `idx_mdr_authored_by` (`authored_by`);

ALTER TABLE `his_prescriptions`
  ADD COLUMN `prescribed_by` int(20) DEFAULT NULL AFTER `pres_id`,
  ADD KEY `idx_pres_prescribed_by` (`prescribed_by`);

ALTER TABLE `his_laboratory`
  ADD COLUMN `ordered_by` int(20) DEFAULT NULL AFTER `lab_id`,
  ADD COLUMN `validated_by` int(20) DEFAULT NULL AFTER `ordered_by`,
  ADD KEY `idx_lab_ordered_by` (`ordered_by`);

ALTER TABLE `his_vitals`
  ADD COLUMN `recorded_by` int(20) DEFAULT NULL AFTER `vit_id`;

-- ---------------------------------------------------------------------------
-- his_vitals currently commingles patient and employee (occupational-health)
-- vitals under one untyped vit_pat_number column. Confirmed in seed data:
-- vit_pat_number 'BKTWQ' / 'YDS7L' are his_docs.doc_number values (Aletha
-- White, Bryan Arreola), not patients. Split before anything queries this
-- table "by patient" for authorization purposes.
-- ---------------------------------------------------------------------------
ALTER TABLE `his_vitals`
  ADD COLUMN `subject_type` ENUM('patient','employee') NOT NULL DEFAULT 'patient' AFTER `vit_id`;

UPDATE his_vitals v
  INNER JOIN his_docs d ON d.doc_number = v.vit_pat_number
  SET v.subject_type = 'employee';

-- Flag (don't guess) any row matching neither a patient nor a doctor number.
-- Review this result manually; do not auto-classify.
SELECT vit_id, vit_pat_number
FROM his_vitals v
WHERE NOT EXISTS (SELECT 1 FROM his_patients p WHERE p.pat_number = v.vit_pat_number)
  AND NOT EXISTS (SELECT 1 FROM his_docs    d WHERE d.doc_number = v.vit_pat_number);
