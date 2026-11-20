-- NexClinic — platform tier + clinical rank foundations (Axis 1 / Axis 2)
-- Additive; safe after Stage A. Does not remove legacy his_admin / his_docs login.
-- Run only when ready to extend auth; Stage A demo works without this.

ALTER TABLE `his_admin`
  ADD COLUMN `platform_tier` enum('superadmin','admin') NOT NULL DEFAULT 'admin' AFTER `ad_pwd`;

-- Seed first admin as superadmin (adjust ad_id if needed)
UPDATE `his_admin` SET `platform_tier` = 'superadmin' WHERE `ad_id` = 1 OR `ad_email` = 'admin@mail.com';

ALTER TABLE `his_docs`
  ADD COLUMN `clinical_rank` enum('chief','doctor','nurse','none') NOT NULL DEFAULT 'doctor' AFTER `doc_dept`,
  ADD COLUMN `platform_tier` enum('standard') NOT NULL DEFAULT 'standard' AFTER `clinical_rank`;

-- If Stage B nex_users exists, align columns (ignore errors if table missing)
-- ALTER TABLE `nex_users`
--   ADD COLUMN `platform_tier` enum('superadmin','admin','standard') NOT NULL DEFAULT 'standard' AFTER `is_system_admin`,
--   ADD COLUMN `clinical_rank` enum('chief','doctor','nurse','none') NOT NULL DEFAULT 'none' AFTER `platform_tier`;

-- Audit log (cross-cutting requirement)
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
