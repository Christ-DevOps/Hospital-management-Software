-- NexClinic Stage C.1 — Patient notification log
-- Every attempted patient notification (appointment, prescription, lab result,
-- discharge, registration) is recorded here regardless of whether the send
-- succeeded, so delivery can be audited and retried.

CREATE TABLE IF NOT EXISTS `his_notifications` (
  `notif_id` int(20) NOT NULL AUTO_INCREMENT,
  `pat_id` int(20) DEFAULT NULL,
  `notif_type` varchar(40) NOT NULL,
  `recipient_email` varchar(200) DEFAULT NULL,
  `subject` varchar(255) DEFAULT NULL,
  `body_html` longtext,
  `status` enum('pending','sent','failed') NOT NULL DEFAULT 'pending',
  `error_message` varchar(500) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `sent_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`notif_id`),
  KEY `idx_notif_pat_id` (`pat_id`),
  KEY `idx_notif_status` (`status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
