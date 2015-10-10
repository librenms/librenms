CREATE TABLE IF NOT EXISTS `alert_schedule_items` (`item_id` INT NOT NULL AUTO_INCREMENT PRIMARY KEY ,`schedule_id` INT NOT NULL ,`target` VARCHAR( 255 ) NOT NULL ,INDEX (  `schedule_id` )) ENGINE = INNODB;

