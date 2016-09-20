CREATE TABLE `rrd_values` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `device_id` int(255) NOT NULL,
  `ds_name` varchar(255) NOT NULL DEFAULT '',
  `ds_last_value` int(255) NOT NULL,
  `ds_before_last_value` int(255) DEFAULT NULL,
  `rrd_filename` varchar(255) NOT NULL DEFAULT '',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=28 DEFAULT CHARSET=latin1;
UPDATE `dbSchema` SET `version` = '135';
