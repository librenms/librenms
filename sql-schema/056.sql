CREATE TABLE IF NOT EXISTS `device_perf` ( `id` int(11) NOT NULL AUTO_INCREMENT, `device_id` int(11) NOT NULL, `timestamp` datetime NOT NULL, `xmt` float NOT NULL, `rcv` float NOT NULL, `loss` float NOT NULL, `min` float NOT NULL, `max` float NOT NULL, `avg` float NOT NULL, KEY `id` (`id`,`device_id`)) ENGINE=InnoDB  DEFAULT CHARSET=latin1 AUTO_INCREMENT=1 ;
ALTER TABLE  `devices` ADD  `status_reason` VARCHAR( 50 ) NOT NULL AFTER  `status` ;
UPDATE `devices` SET `status_reason`='down' WHERE `status`=0;
