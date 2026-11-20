-- NexClinic — Prescription → Pharmacy dispense spine
-- Doctor issues structured Rx lines; pharmacy dispenses and decrements stock.
-- Safe to re-run: ignores duplicate column / key errors when applied twice.

-- Status + dispense provenance on header
SET @db := DATABASE();

SET @exists := (
  SELECT COUNT(*) FROM information_schema.COLUMNS
  WHERE TABLE_SCHEMA = @db AND TABLE_NAME = 'his_prescriptions' AND COLUMN_NAME = 'pres_status'
);
SET @sql := IF(@exists = 0,
  'ALTER TABLE `his_prescriptions` ADD COLUMN `pres_status` varchar(40) NOT NULL DEFAULT ''pending'' AFTER `pres_ins`',
  'SELECT 1');
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

SET @exists := (
  SELECT COUNT(*) FROM information_schema.COLUMNS
  WHERE TABLE_SCHEMA = @db AND TABLE_NAME = 'his_prescriptions' AND COLUMN_NAME = 'dispensed_by'
);
SET @sql := IF(@exists = 0,
  'ALTER TABLE `his_prescriptions` ADD COLUMN `dispensed_by` int(20) DEFAULT NULL AFTER `pres_status`',
  'SELECT 1');
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

SET @exists := (
  SELECT COUNT(*) FROM information_schema.COLUMNS
  WHERE TABLE_SCHEMA = @db AND TABLE_NAME = 'his_prescriptions' AND COLUMN_NAME = 'dispensed_at'
);
SET @sql := IF(@exists = 0,
  'ALTER TABLE `his_prescriptions` ADD COLUMN `dispensed_at` datetime DEFAULT NULL AFTER `dispensed_by`',
  'SELECT 1');
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

SET @exists := (
  SELECT COUNT(*) FROM information_schema.STATISTICS
  WHERE TABLE_SCHEMA = @db AND TABLE_NAME = 'his_prescriptions' AND INDEX_NAME = 'idx_pres_status'
);
SET @sql := IF(@exists = 0,
  'ALTER TABLE `his_prescriptions` ADD KEY `idx_pres_status` (`pres_status`)',
  'SELECT 1');
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

CREATE TABLE IF NOT EXISTS `his_prescription_items` (
  `item_id` int(20) NOT NULL AUTO_INCREMENT,
  `pres_id` int(20) NOT NULL,
  `phar_id` int(20) DEFAULT NULL,
  `drug_name` varchar(200) NOT NULL,
  `qty` int(11) NOT NULL DEFAULT 1,
  `dosage` varchar(120) DEFAULT NULL,
  `frequency` varchar(120) DEFAULT NULL,
  `duration` varchar(120) DEFAULT NULL,
  `notes` varchar(255) DEFAULT NULL,
  `qty_dispensed` int(11) NOT NULL DEFAULT 0,
  PRIMARY KEY (`item_id`),
  KEY `idx_rx_item_pres` (`pres_id`),
  KEY `idx_rx_item_phar` (`phar_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS `his_prescription_dispenses` (
  `dispense_id` int(20) NOT NULL AUTO_INCREMENT,
  `pres_id` int(20) NOT NULL,
  `dispensed_by` int(20) DEFAULT NULL,
  `dispensed_at` datetime NOT NULL,
  `notes` text,
  PRIMARY KEY (`dispense_id`),
  KEY `idx_rx_dispense_pres` (`pres_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

UPDATE `his_prescriptions`
SET `pres_status` = 'pending'
WHERE (`pres_status` IS NULL OR `pres_status` = '')
  AND `dispensed_at` IS NULL;
