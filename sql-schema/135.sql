CREATE TABLE `rrd_values` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `device_id` int(255) NOT NULL,
  `alert_name` varchar(255) NOT NULL DEFAULT '',
  `rrd_name` varchar(255) NOT NULL DEFAULT '',
  `rrd_last_value` int(255) NOT NULL,
  `rrd_before_last_value` int(255) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=21 DEFAULT CHARSET=latin1;
