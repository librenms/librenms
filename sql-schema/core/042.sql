CREATE TABLE `pollers` (`id` int(11) NOT NULL AUTO_INCREMENT, `poller_name` varchar(255) NOT NULL, `last_polled` datetime NOT NULL, `devices` int(11) NOT NULL, `time_taken` double NOT NULL, KEY `id` (`id`)) ENGINE=InnoDB AUTO_INCREMENT=8 DEFAULT CHARSET=utf8 COLLATE=utf8_bin;
ALTER TABLE `devices` ADD `poller_group` INT(11) NOT NULL DEFAULT '0';
