ALTER TABLE `services` ADD `service_ds` VARCHAR(255) NOT NULL COMMENT 'Data Sources available for this service';
ALTER TABLE `services` DROP `service_checked`;
