ALTER TABLE `alert_templates` ADD `type` VARCHAR(16) NOT NULL DEFAULT 'blade';
UPDATE `alert_templates` SET `type`='librenms';
