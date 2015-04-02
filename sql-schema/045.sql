ALTER TABLE  `alert_rules` CHANGE  `device_id`  `device_id` VARCHAR( 255 ) NOT NULL;
ALTER TABLE  `alert_rules` CHANGE  `device_id`  `target` VARCHAR( 255 ) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL;
CREATE TABLE IF NOT EXISTS `alert_groups` (  `id` int(11) NOT NULL AUTO_INCREMENT,  `name` varchar(255) CHARACTER SET utf8 COLLATE utf8_bin NOT NULL DEFAULT '',  `desc` varchar(255) CHARACTER SET utf8 COLLATE utf8_bin NOT NULL DEFAULT '',  PRIMARY KEY (`id`)) ENGINE=InnoDB  DEFAULT CHARSET=utf8;
