-- NexClinic Stage B.1 — Relational re-architecture (run AFTER demo-checkpoint / Stage A)
-- BACKUP FIRST: mysqldump -u root -p hospital-management > backup_pre_stage_b.sql
-- Additive FK columns first; legacy *_number / name columns retained for display.

SET @OLD_FK = @@FOREIGN_KEY_CHECKS;
SET FOREIGN_KEY_CHECKS = 0;

-- ---------------------------------------------------------------------------
-- 1) Add canonical patient / doctor ID columns (nullable during backfill)
-- ---------------------------------------------------------------------------
ALTER TABLE `his_laboratory`
  ADD COLUMN `pat_id` int(20) DEFAULT NULL AFTER `lab_id`,
  ADD KEY `idx_lab_pat_id` (`pat_id`);

ALTER TABLE `his_prescriptions`
  ADD COLUMN `pat_id` int(20) DEFAULT NULL AFTER `pres_id`,
  ADD KEY `idx_pres_pat_id` (`pat_id`);

ALTER TABLE `his_medical_records`
  ADD COLUMN `pat_id` int(20) DEFAULT NULL AFTER `mdr_id`,
  ADD KEY `idx_mdr_pat_id` (`pat_id`);

ALTER TABLE `his_surgery`
  ADD COLUMN `pat_id` int(20) DEFAULT NULL AFTER `s_id`,
  ADD COLUMN `doc_id` int(20) DEFAULT NULL AFTER `pat_id`,
  ADD KEY `idx_surgery_pat_id` (`pat_id`),
  ADD KEY `idx_surgery_doc_id` (`doc_id`);

ALTER TABLE `his_payrolls`
  ADD COLUMN `doc_id` int(20) DEFAULT NULL AFTER `pay_id`,
  ADD KEY `idx_payroll_doc_id` (`doc_id`);

ALTER TABLE `his_patient_transfers`
  ADD COLUMN `pat_id` int(20) DEFAULT NULL AFTER `t_id`,
  ADD KEY `idx_transfer_pat_id` (`pat_id`);

ALTER TABLE `his_vitals`
  ADD COLUMN `pat_id` int(20) DEFAULT NULL AFTER `vit_id`,
  ADD KEY `idx_vitals_pat_id` (`pat_id`);

-- ---------------------------------------------------------------------------
-- 2) Backfill IDs by matching legacy display codes / names
-- ---------------------------------------------------------------------------
UPDATE his_laboratory l
  INNER JOIN his_patients p ON p.pat_number = l.lab_pat_number
  SET l.pat_id = p.pat_id
  WHERE l.pat_id IS NULL AND l.lab_pat_number IS NOT NULL AND l.lab_pat_number <> '';

UPDATE his_prescriptions pr
  INNER JOIN his_patients p ON p.pat_number = pr.pres_pat_number
  SET pr.pat_id = p.pat_id
  WHERE pr.pat_id IS NULL AND pr.pres_pat_number IS NOT NULL AND pr.pres_pat_number <> '';

UPDATE his_medical_records m
  INNER JOIN his_patients p ON p.pat_number = m.mdr_pat_number
  SET m.pat_id = p.pat_id
  WHERE m.pat_id IS NULL AND m.mdr_pat_number IS NOT NULL AND m.mdr_pat_number <> '';

UPDATE his_surgery s
  INNER JOIN his_patients p ON p.pat_number = s.s_pat_number
  SET s.pat_id = p.pat_id
  WHERE s.pat_id IS NULL AND s.s_pat_number IS NOT NULL AND s.s_pat_number <> '';

UPDATE his_surgery s
  INNER JOIN his_docs d ON CONCAT(d.doc_fname, ' ', d.doc_lname) = s.s_doc
  SET s.doc_id = d.doc_id
  WHERE s.doc_id IS NULL AND s.s_doc IS NOT NULL AND s.s_doc <> '';

UPDATE his_payrolls pay
  INNER JOIN his_docs d ON d.doc_number = pay.pay_doc_number
  SET pay.doc_id = d.doc_id
  WHERE pay.doc_id IS NULL AND pay.pay_doc_number IS NOT NULL AND pay.pay_doc_number <> '';

UPDATE his_patient_transfers t
  INNER JOIN his_patients p ON p.pat_number = t.t_pat_number
  SET t.pat_id = p.pat_id
  WHERE t.pat_id IS NULL AND t.t_pat_number IS NOT NULL AND t.t_pat_number <> '';

UPDATE his_vitals v
  INNER JOIN his_patients p ON p.pat_number = v.vit_pat_number
  SET v.pat_id = p.pat_id
  WHERE v.pat_id IS NULL AND v.vit_pat_number IS NOT NULL AND v.vit_pat_number <> '';

UPDATE his_appointments a
  INNER JOIN his_patients p ON p.pat_number = a.pat_number
  SET a.pat_id = p.pat_id
  WHERE (a.pat_id IS NULL OR a.pat_id = 0) AND a.pat_number IS NOT NULL AND a.pat_number <> '';

UPDATE his_appointments a
  INNER JOIN his_docs d ON d.doc_number = a.doc_number
  SET a.doc_id = d.doc_id
  WHERE (a.doc_id IS NULL OR a.doc_id = 0) AND a.doc_number IS NOT NULL AND a.doc_number <> '';

-- Clear orphan IDs that cannot satisfy FKs (keep legacy string columns)
UPDATE his_laboratory SET pat_id = NULL WHERE pat_id IS NOT NULL AND pat_id NOT IN (SELECT pat_id FROM his_patients);
UPDATE his_prescriptions SET pat_id = NULL WHERE pat_id IS NOT NULL AND pat_id NOT IN (SELECT pat_id FROM his_patients);
UPDATE his_medical_records SET pat_id = NULL WHERE pat_id IS NOT NULL AND pat_id NOT IN (SELECT pat_id FROM his_patients);
UPDATE his_surgery SET pat_id = NULL WHERE pat_id IS NOT NULL AND pat_id NOT IN (SELECT pat_id FROM his_patients);
UPDATE his_surgery SET doc_id = NULL WHERE doc_id IS NOT NULL AND doc_id NOT IN (SELECT doc_id FROM his_docs);
UPDATE his_payrolls SET doc_id = NULL WHERE doc_id IS NOT NULL AND doc_id NOT IN (SELECT doc_id FROM his_docs);
UPDATE his_patient_transfers SET pat_id = NULL WHERE pat_id IS NOT NULL AND pat_id NOT IN (SELECT pat_id FROM his_patients);
UPDATE his_vitals SET pat_id = NULL WHERE pat_id IS NOT NULL AND pat_id NOT IN (SELECT pat_id FROM his_patients);
UPDATE his_appointments SET pat_id = NULL WHERE pat_id IS NOT NULL AND pat_id NOT IN (SELECT pat_id FROM his_patients);
UPDATE his_appointments SET doc_id = NULL WHERE doc_id IS NOT NULL AND doc_id NOT IN (SELECT doc_id FROM his_docs);

-- ---------------------------------------------------------------------------
-- 3) Foreign keys (nullable — unmatched legacy rows remain without FK)
-- ---------------------------------------------------------------------------
ALTER TABLE `his_laboratory`
  ADD CONSTRAINT `fk_lab_pat` FOREIGN KEY (`pat_id`) REFERENCES `his_patients` (`pat_id`) ON DELETE SET NULL ON UPDATE CASCADE;

ALTER TABLE `his_prescriptions`
  ADD CONSTRAINT `fk_pres_pat` FOREIGN KEY (`pat_id`) REFERENCES `his_patients` (`pat_id`) ON DELETE SET NULL ON UPDATE CASCADE;

ALTER TABLE `his_medical_records`
  ADD CONSTRAINT `fk_mdr_pat` FOREIGN KEY (`pat_id`) REFERENCES `his_patients` (`pat_id`) ON DELETE SET NULL ON UPDATE CASCADE;

ALTER TABLE `his_surgery`
  ADD CONSTRAINT `fk_surgery_pat` FOREIGN KEY (`pat_id`) REFERENCES `his_patients` (`pat_id`) ON DELETE SET NULL ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_surgery_doc` FOREIGN KEY (`doc_id`) REFERENCES `his_docs` (`doc_id`) ON DELETE SET NULL ON UPDATE CASCADE;

ALTER TABLE `his_payrolls`
  ADD CONSTRAINT `fk_payroll_doc` FOREIGN KEY (`doc_id`) REFERENCES `his_docs` (`doc_id`) ON DELETE SET NULL ON UPDATE CASCADE;

ALTER TABLE `his_patient_transfers`
  ADD CONSTRAINT `fk_transfer_pat` FOREIGN KEY (`pat_id`) REFERENCES `his_patients` (`pat_id`) ON DELETE SET NULL ON UPDATE CASCADE;

ALTER TABLE `his_vitals`
  ADD CONSTRAINT `fk_vitals_pat` FOREIGN KEY (`pat_id`) REFERENCES `his_patients` (`pat_id`) ON DELETE SET NULL ON UPDATE CASCADE;

ALTER TABLE `his_appointments`
  ADD CONSTRAINT `fk_appt_pat` FOREIGN KEY (`pat_id`) REFERENCES `his_patients` (`pat_id`) ON DELETE SET NULL ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_appt_doc` FOREIGN KEY (`doc_id`) REFERENCES `his_docs` (`doc_id`) ON DELETE SET NULL ON UPDATE CASCADE;

-- ---------------------------------------------------------------------------
-- 4) Tighten numeric types where seed data is numeric
-- ---------------------------------------------------------------------------
UPDATE his_accounts SET acc_amount = NULLIF(TRIM(acc_amount), '') WHERE acc_amount REGEXP '[^0-9.\\-]';
ALTER TABLE `his_accounts`
  MODIFY COLUMN `acc_amount` decimal(15,2) DEFAULT NULL;

UPDATE his_payrolls SET pay_emp_salary = NULLIF(TRIM(pay_emp_salary), '') WHERE pay_emp_salary REGEXP '[^0-9.\\-]';
ALTER TABLE `his_payrolls`
  MODIFY COLUMN `pay_emp_salary` decimal(15,2) DEFAULT NULL;

UPDATE his_pharmaceuticals SET phar_qty = '0' WHERE phar_qty IS NULL OR phar_qty = '' OR phar_qty REGEXP '[^0-9]';
ALTER TABLE `his_pharmaceuticals`
  MODIFY COLUMN `phar_qty` int(11) DEFAULT NULL;

UPDATE his_equipments SET eqp_qty = '0' WHERE eqp_qty IS NULL OR eqp_qty = '' OR eqp_qty REGEXP '[^0-9]';
ALTER TABLE `his_equipments`
  MODIFY COLUMN `eqp_qty` int(11) DEFAULT NULL;

SET FOREIGN_KEY_CHECKS = @OLD_FK;

-- Down script: see 2026_08_21_stage_b_down.sql
