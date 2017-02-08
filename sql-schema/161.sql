ALTER TABLE `librenms`.`eventlog` ADD COLUMN `severity` INT(1) NULL DEFAULT 0 AFTER `reference`;
