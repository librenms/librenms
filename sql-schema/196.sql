CREATE TABLE `ports_fdb` ( `ports_fdb_id` BIGINT(20) PRIMARY KEY NOT NULL AUTO_INCREMENT, `port_id` INT(11) unsigned NOT NULL, `mac_address` VARCHAR(32) NOT NULL, `vlan_id` INT(11) unsigned NOT NULL, `device_id` INT(11) unsigned NOT NULL);
ALTER TABLE `ports_fdb` ADD INDEX ( `mac_address` );
