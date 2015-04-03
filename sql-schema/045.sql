CREATE TABLE IF NOT EXISTS `device_groups` (  `id` int(11) NOT NULL AUTO_INCREMENT,  `name` varchar(255) NOT NULL DEFAULT '',  `desc` varchar(255) NOT NULL DEFAULT '',  `pattern` varchar(255) NOT NULL DEFAULT '',  PRIMARY KEY (`id`),  UNIQUE KEY `name` (`name`)) ENGINE=InnoDB DEFAULT;
CREATE TABLE IF NOT EXISTS `alert_map` (  `id` int(11) NOT NULL AUTO_INCREMENT,  `rule` int(11) NOT NULL DEFAULT '0',  `target` varchar(255) CHARACTER SET utf8 NOT NULL DEFAULT '',  PRIMARY KEY (`id`)) ENGINE=InnoDB DEFAULT;
ALTER TABLE  `alert_rules` ADD UNIQUE (`name`);
