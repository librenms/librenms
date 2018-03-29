CREATE TABLE  `locations` ( `id` INT NOT NULL AUTO_INCREMENT ,`location` TEXT NOT NULL ,`lat` FLOAT( 10, 6 ) NOT NULL ,`lng` FLOAT( 10, 6 ) NOT NULL ,`timestamp` DATETIME NOT NULL ,INDEX (  `id` )) ENGINE = INNODB;
LOCK TABLES `devices` WRITE;
ALTER TABLE `devices` ADD `override_sysLocation` bool DEFAULT false;
UNLOCK TABLES;
UPDATE `devices` LEFT JOIN devices_attribs AS sysloc_bool ON(devices.device_id=sysloc_bool.device_id and sysloc_bool.attrib_type = 'override_sysLocation_bool') LEFT JOIN devices_attribs AS sysloc_string ON(devices.device_id=sysloc_string.device_id and sysloc_string.attrib_type = 'override_sysLocation_string') SET `override_sysLocation` = true, `location` = sysloc_string.attrib_value WHERE sysloc_bool.attrib_value = 1;
