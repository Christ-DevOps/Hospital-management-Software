-- Run on existing databases imported from the original hmisphp dump.
USE `hospital-management`;

ALTER TABLE `his_pwdresets`
  ADD COLUMN `token` varchar(255) DEFAULT NULL,
  ADD COLUMN `status` varchar(50) DEFAULT 'Pending',
  ADD COLUMN `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP;

ALTER TABLE `his_pwdresets` DROP COLUMN `pwd`;

UPDATE `his_admin`
SET `ad_pwd` = '$2y$12$H5bYaXZR3K0LlGtVlPzrEOb/bO736CaTHcey/KCO2qYr7ks7fHbmS'
WHERE `ad_email` = 'admin@mail.com';

UPDATE `his_docs`
SET `doc_pwd` = '$2y$12$xKfZPdS13D5.b5n..dp56e3zZM7WrxmvF4VamKdS/uG4ONjJynsEy'
WHERE `doc_number` IN ('BKTWQ', 'YDS7L', '5VIFT');
