-- NexClinic Stage B.2 — Sector operating model
-- BACKUP FIRST (same dump as relational migration recommended).
-- Mairie is NOT a sector — no mairie UI (future API only).
-- Legacy his_admin / his_docs kept; nex_users mirrors hashes for dual login.

CREATE TABLE IF NOT EXISTS `nex_facilities` (
  `facility_id` int(20) NOT NULL AUTO_INCREMENT,
  `facility_code` varchar(40) NOT NULL,
  `facility_name` varchar(200) NOT NULL,
  `facility_active` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`facility_id`),
  UNIQUE KEY `uq_facility_code` (`facility_code`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS `nex_sectors` (
  `sector_id` int(20) NOT NULL AUTO_INCREMENT,
  `sector_code` varchar(40) NOT NULL,
  `sector_name` varchar(120) NOT NULL,
  `sector_sort` int(11) NOT NULL DEFAULT 100,
  `sector_active` tinyint(1) NOT NULL DEFAULT 1,
  PRIMARY KEY (`sector_id`),
  UNIQUE KEY `uq_sector_code` (`sector_code`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS `nex_users` (
  `user_id` int(20) NOT NULL AUTO_INCREMENT,
  `legacy_source` varchar(20) NOT NULL,
  `legacy_id` int(20) NOT NULL,
  `email` varchar(200) DEFAULT NULL,
  `staff_number` varchar(80) DEFAULT NULL,
  `password_hash` varchar(255) NOT NULL,
  `fname` varchar(120) DEFAULT NULL,
  `lname` varchar(120) DEFAULT NULL,
  `is_system_admin` tinyint(1) NOT NULL DEFAULT 0,
  `active` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`user_id`),
  UNIQUE KEY `uq_legacy` (`legacy_source`, `legacy_id`),
  KEY `idx_nex_users_email` (`email`),
  KEY `idx_nex_users_staff` (`staff_number`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS `nex_user_assignments` (
  `assignment_id` int(20) NOT NULL AUTO_INCREMENT,
  `user_id` int(20) NOT NULL,
  `facility_id` int(20) NOT NULL,
  `sector_code` varchar(40) NOT NULL,
  `role` enum('lead','staff') NOT NULL DEFAULT 'staff',
  `active` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`assignment_id`),
  UNIQUE KEY `uq_user_facility_sector` (`user_id`, `facility_id`, `sector_code`),
  KEY `idx_assign_sector` (`sector_code`),
  CONSTRAINT `fk_assign_user` FOREIGN KEY (`user_id`) REFERENCES `nex_users` (`user_id`) ON DELETE CASCADE,
  CONSTRAINT `fk_assign_facility` FOREIGN KEY (`facility_id`) REFERENCES `nex_facilities` (`facility_id`) ON DELETE CASCADE,
  CONSTRAINT `fk_assign_sector` FOREIGN KEY (`sector_code`) REFERENCES `nex_sectors` (`sector_code`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

INSERT INTO `nex_facilities` (`facility_code`, `facility_name`) VALUES
('HQ', 'NexClinic Main Facility')
ON DUPLICATE KEY UPDATE facility_name = VALUES(facility_name);

INSERT INTO `nex_sectors` (`sector_code`, `sector_name`, `sector_sort`) VALUES
('system', 'System', 1),
('clinical', 'Clinical', 10),
('reception', 'Reception', 20),
('pharmacy', 'Pharmacy', 30),
('laboratory', 'Laboratory', 40),
('theatre', 'Theatre', 50),
('hr', 'HR', 60),
('payroll', 'Payroll', 70),
('accounting', 'Accounting', 80),
('inventory', 'Inventory', 90)
ON DUPLICATE KEY UPDATE sector_name = VALUES(sector_name), sector_sort = VALUES(sector_sort);

-- Migrate admins (preserve bcrypt / legacy hashes)
INSERT INTO `nex_users` (`legacy_source`, `legacy_id`, `email`, `staff_number`, `password_hash`, `fname`, `lname`, `is_system_admin`, `active`)
SELECT 'admin', a.ad_id, a.ad_email, NULL, a.ad_pwd, a.ad_fname, a.ad_lname, 1, 1
FROM his_admin a
ON DUPLICATE KEY UPDATE
  email = VALUES(email),
  password_hash = VALUES(password_hash),
  fname = VALUES(fname),
  lname = VALUES(lname),
  is_system_admin = 1;

-- Migrate doctors / staff
INSERT INTO `nex_users` (`legacy_source`, `legacy_id`, `email`, `staff_number`, `password_hash`, `fname`, `lname`, `is_system_admin`, `active`)
SELECT 'doc', d.doc_id, d.doc_email, d.doc_number, d.doc_pwd, d.doc_fname, d.doc_lname, 0, 1
FROM his_docs d
ON DUPLICATE KEY UPDATE
  email = VALUES(email),
  staff_number = VALUES(staff_number),
  password_hash = VALUES(password_hash),
  fname = VALUES(fname),
  lname = VALUES(lname);

-- System admin → all sectors at HQ
INSERT INTO nex_user_assignments (user_id, facility_id, sector_code, role, active)
SELECT u.user_id, f.facility_id, s.sector_code, 'lead', 1
FROM nex_users u
CROSS JOIN nex_facilities f
CROSS JOIN nex_sectors s
WHERE u.legacy_source = 'admin' AND f.facility_code = 'HQ' AND u.active = 1
ON DUPLICATE KEY UPDATE role = 'lead', active = 1;

-- Map doc_dept strings → sectors
INSERT INTO nex_user_assignments (user_id, facility_id, sector_code, role, active)
SELECT u.user_id, f.facility_id,
  CASE
    WHEN LOWER(COALESCE(d.doc_dept, '')) LIKE '%lab%' THEN 'laboratory'
    WHEN LOWER(COALESCE(d.doc_dept, '')) LIKE '%pharm%' THEN 'pharmacy'
    WHEN LOWER(COALESCE(d.doc_dept, '')) LIKE '%surg%' OR LOWER(COALESCE(d.doc_dept, '')) LIKE '%theatre%' THEN 'theatre'
    WHEN LOWER(COALESCE(d.doc_dept, '')) LIKE '%account%' OR LOWER(COALESCE(d.doc_dept, '')) LIKE '%financ%' THEN 'accounting'
    WHEN LOWER(COALESCE(d.doc_dept, '')) LIKE '%hr%' OR LOWER(COALESCE(d.doc_dept, '')) LIKE '%human%' THEN 'hr'
    WHEN LOWER(COALESCE(d.doc_dept, '')) LIKE '%payroll%' THEN 'payroll'
    WHEN LOWER(COALESCE(d.doc_dept, '')) LIKE '%invent%' THEN 'inventory'
    WHEN LOWER(COALESCE(d.doc_dept, '')) LIKE '%recept%' THEN 'reception'
    ELSE 'clinical'
  END AS sector_code,
  'staff', 1
FROM nex_users u
INNER JOIN his_docs d ON d.doc_id = u.legacy_id AND u.legacy_source = 'doc'
CROSS JOIN nex_facilities f
WHERE f.facility_code = 'HQ' AND u.active = 1
ON DUPLICATE KEY UPDATE active = 1;

-- Doctors also get clinical by default when mapped elsewhere
INSERT INTO nex_user_assignments (user_id, facility_id, sector_code, role, active)
SELECT u.user_id, f.facility_id, 'clinical', 'staff', 1
FROM nex_users u
CROSS JOIN nex_facilities f
WHERE u.legacy_source = 'doc' AND f.facility_code = 'HQ' AND u.active = 1
ON DUPLICATE KEY UPDATE active = 1;
