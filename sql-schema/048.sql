ALTER TABLE `alert_schedule` DROP `device_id`;
ALTER TABLE  `alert_schedule` CHANGE  `id`  `schedule_id` INT( 11 ) NOT NULL AUTO_INCREMENT;
ALTER TABLE  `alert_schedule` ADD  `title` VARCHAR( 255 ) NOT NULL ,ADD  `notes` TEXT NOT NULL ;
CREATE TABLE  `alert_schedule_items` (`item_id` INT NOT NULL AUTO_INCREMENT PRIMARY KEY ,`schedule_id` INT NOT NULL ,`target` VARCHAR( 255 ) NOT NULL ,INDEX (  `schedule_id` )) ENGINE = INNODB;
