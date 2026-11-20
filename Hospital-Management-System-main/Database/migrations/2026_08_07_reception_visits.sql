-- NexClinic Reception — visit / queue spine
-- Appointments remain the schedule; visits capture check-in, walk-in,
-- emergency arrival, queue position, and registration timing.

CREATE TABLE IF NOT EXISTS `his_visits` (
  `visit_id` int(20) NOT NULL AUTO_INCREMENT,
  `facility_id` int(20) DEFAULT NULL,
  `pat_id` int(20) NOT NULL,
  `appt_id` int(20) DEFAULT NULL,
  `visit_type` varchar(40) NOT NULL DEFAULT 'scheduled',
  `visit_status` varchar(40) NOT NULL DEFAULT 'waiting',
  `queue_number` int(11) NOT NULL DEFAULT 0,
  `priority` tinyint(3) NOT NULL DEFAULT 0,
  `arrived_at` datetime NOT NULL,
  `registration_started_at` datetime DEFAULT NULL,
  `registration_completed_at` datetime DEFAULT NULL,
  `called_at` datetime DEFAULT NULL,
  `completed_at` datetime DEFAULT NULL,
  `created_by` int(20) DEFAULT NULL,
  `notes` text,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`visit_id`),
  KEY `idx_visit_arrived` (`arrived_at`),
  KEY `idx_visit_status` (`visit_status`),
  KEY `idx_visit_type` (`visit_type`),
  KEY `idx_visit_pat` (`pat_id`),
  KEY `idx_visit_appt` (`appt_id`),
  KEY `idx_visit_queue_day` (`arrived_at`, `queue_number`),
  CONSTRAINT `fk_visit_pat` FOREIGN KEY (`pat_id`) REFERENCES `his_patients` (`pat_id`) ON DELETE RESTRICT ON UPDATE CASCADE,
  CONSTRAINT `fk_visit_appt` FOREIGN KEY (`appt_id`) REFERENCES `his_appointments` (`appt_id`) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
