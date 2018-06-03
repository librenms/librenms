ALTER TABLE `alert_templates` ADD `type` VARCHAR(16) NOT NULL DEFAULT 'blade';
ALTER TABLE `alert_templates` DROP `rule_id`;
UPDATE `alert_templates` SET `type`='librenms';
