CREATE TABLE IF NOT EXISTS `ports_fdb` ( `port_id` int(11) unsigned NOT NULL, `mac_address` varchar(32) NOT NULL, `vlan_id` int(11) unsigned NOT NULL, `device_id` int(11) unsigned NOT NULL ) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

ALTER TABLE `ports_fdb` ADD INDEX ( `mac_address` );

