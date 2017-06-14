ALTER TABLE `eventlog` ADD `device_id` INT NOT NULL AFTER `host` ;
ALTER TABLE `eventlog` ADD INDEX ( `device_id` ) ;
UPDATE eventlog SET device_id=host WHERE device_id=0;
