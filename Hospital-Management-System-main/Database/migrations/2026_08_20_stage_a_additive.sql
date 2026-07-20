-- NexClinic Stage A (additive only) — safe for demo-checkpoint
-- Cameroon patient registration fields + appointments table
-- All new patient columns are NULLABLE (form-level required, not DB NOT NULL).
-- Village / division / sub-division: free text for now; later Region→Division→Subdivision dropdowns (census/mairie).

ALTER TABLE `his_patients`
  ADD COLUMN `pat_sex` varchar(20) DEFAULT NULL AFTER `pat_lname`,
  ADD COLUMN `pat_marital_status` varchar(40) DEFAULT NULL AFTER `pat_sex`,
  ADD COLUMN `pat_religion` varchar(80) DEFAULT NULL AFTER `pat_marital_status`,
  ADD COLUMN `pat_reg_hour` time DEFAULT NULL AFTER `pat_religion`,
  ADD COLUMN `pat_village` varchar(200) DEFAULT NULL AFTER `pat_addr`,
  ADD COLUMN `pat_division` varchar(200) DEFAULT NULL AFTER `pat_village`,
  ADD COLUMN `pat_subdivision` varchar(200) DEFAULT NULL AFTER `pat_division`,
  ADD COLUMN `pat_blood_group` varchar(10) DEFAULT NULL AFTER `pat_subdivision`,
  ADD COLUMN `pat_email` varchar(200) DEFAULT NULL AFTER `pat_phone`,
  ADD COLUMN `pat_emergency_name` varchar(200) DEFAULT NULL AFTER `pat_email`,
  ADD COLUMN `pat_emergency_phone` varchar(40) DEFAULT NULL AFTER `pat_emergency_name`;

CREATE TABLE IF NOT EXISTS `his_appointments` (
  `appt_id` int(20) NOT NULL AUTO_INCREMENT,
  `pat_id` int(20) DEFAULT NULL,
  `pat_number` varchar(200) DEFAULT NULL,
  `doc_id` int(20) DEFAULT NULL,
  `doc_number` varchar(200) DEFAULT NULL,
  `appt_datetime` datetime NOT NULL,
  `appt_status` varchar(40) NOT NULL DEFAULT 'booked',
  `appt_reason` text,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`appt_id`),
  KEY `idx_appt_datetime` (`appt_datetime`),
  KEY `idx_appt_doc` (`doc_id`),
  KEY `idx_appt_pat` (`pat_id`),
  KEY `idx_appt_status` (`appt_status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Down (manual):
-- DROP TABLE IF EXISTS `his_appointments`;
-- ALTER TABLE `his_patients` DROP COLUMN `pat_sex`, DROP COLUMN `pat_marital_status`, ...;
