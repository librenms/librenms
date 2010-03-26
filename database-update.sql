CREATE TABLE IF NOT EXISTS `toner` ( `toner_id` int(11) NOT NULL auto_increment, `device_id` int(11) NOT NULL default '0', `toner_index` int(11) NOT NULL, `toner_type` varchar(64) NOT NULL, `toner_oid` varchar(64) NOT NULL, `toner_descr` varchar(32) NOT NULL default '', `toner_capacity` int(11) NOT NULL default '0', `toner_current` int(11) NOT NULL default '0', PRIMARY KEY (`toner_id`), KEY `device_id` (`device_id`)) ENGINE=InnoDB DEFAULT CHARSET=latin1;
ALTER TABLE  `mempools` CHANGE  `mempool_descr`  `mempool_descr` VARCHAR( 64 ) CHARACTER SET latin1 COLLATE latin1_swedish_ci NOT NULL;
ALTER TABLE  `processors` CHANGE  `processor_descr`  `processor_descr` VARCHAR( 64 ) CHARACTER SET latin1 COLLATE latin1_swedish_ci NOT NULL;
DROP TABLE `cempMemPool`;
DROP TABLE `cpmCPU`;
DROP TABLE `cmpMemPool`;
ALTER TABLE `mempools` CHANGE `mempool_used` `mempool_used` BIGINT( 16 ) NOT NULL ,CHANGE `mempool_free` `mempool_free` BIGINT( 16 ) NOT NULL ,CHANGE `mempool_total` `mempool_total` BIGINT( 16 ) NOT NULL ,CHANGE `mempool_largestfree` `mempool_largestfree` BIGINT( 16 ) NULL DEFAULT NULL ,CHANGE `mempool_lowestfree` `mempool_lowestfree` BIGINT( 16 ) NULL DEFAULT NULL ;
