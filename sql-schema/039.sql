DROP TABLE IF EXISTS `processes`;
CREATE TABLE IF NOT EXISTS `processes` (  `device_id` int(11) NOT NULL,  `pid` int(11) NOT NULL,  `vsz` int(11) NOT NULL,  `rss` int(11) NOT NULL,  `pcpu` float NOT NULL,  `user` varchar(50) NOT NULL,  `command` varchar(255) NOT NULL,  KEY `device_id` (`device_id`)) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_bin;
