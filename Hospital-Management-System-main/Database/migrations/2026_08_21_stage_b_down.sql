-- NexClinic Stage B reverse migration (manual / emergency)
-- Restores pre-Stage-B shape for FK columns and sector tables.
-- Numeric column types are reverted to VARCHAR; restore values from backup if needed.

SET @OLD_FK = @@FOREIGN_KEY_CHECKS;
SET FOREIGN_KEY_CHECKS = 0;

DROP TABLE IF EXISTS `nex_user_assignments`;
DROP TABLE IF EXISTS `nex_users`;
DROP TABLE IF EXISTS `nex_sectors`;
DROP TABLE IF EXISTS `nex_facilities`;

ALTER TABLE `his_appointments` DROP FOREIGN KEY `fk_appt_pat`;
ALTER TABLE `his_appointments` DROP FOREIGN KEY `fk_appt_doc`;

ALTER TABLE `his_laboratory` DROP FOREIGN KEY `fk_lab_pat`;
ALTER TABLE `his_prescriptions` DROP FOREIGN KEY `fk_pres_pat`;
ALTER TABLE `his_medical_records` DROP FOREIGN KEY `fk_mdr_pat`;
ALTER TABLE `his_surgery` DROP FOREIGN KEY `fk_surgery_pat`;
ALTER TABLE `his_surgery` DROP FOREIGN KEY `fk_surgery_doc`;
ALTER TABLE `his_payrolls` DROP FOREIGN KEY `fk_payroll_doc`;
ALTER TABLE `his_patient_transfers` DROP FOREIGN KEY `fk_transfer_pat`;
ALTER TABLE `his_vitals` DROP FOREIGN KEY `fk_vitals_pat`;

ALTER TABLE `his_laboratory` DROP COLUMN `pat_id`;
ALTER TABLE `his_prescriptions` DROP COLUMN `pat_id`;
ALTER TABLE `his_medical_records` DROP COLUMN `pat_id`;
ALTER TABLE `his_surgery` DROP COLUMN `pat_id`, DROP COLUMN `doc_id`;
ALTER TABLE `his_payrolls` DROP COLUMN `doc_id`;
ALTER TABLE `his_patient_transfers` DROP COLUMN `pat_id`;
ALTER TABLE `his_vitals` DROP COLUMN `pat_id`;

ALTER TABLE `his_accounts` MODIFY COLUMN `acc_amount` varchar(200) DEFAULT NULL;
ALTER TABLE `his_payrolls` MODIFY COLUMN `pay_emp_salary` varchar(200) DEFAULT NULL;
ALTER TABLE `his_pharmaceuticals` MODIFY COLUMN `phar_qty` varchar(200) DEFAULT NULL;
ALTER TABLE `his_equipments` MODIFY COLUMN `eqp_qty` varchar(200) DEFAULT NULL;

SET FOREIGN_KEY_CHECKS = @OLD_FK;
