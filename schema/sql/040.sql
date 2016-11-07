ALTER TABLE  `devices` CHANGE  `agent_uptime`  `agent_uptime` INT( 11 ) NOT NULL DEFAULT  '0';
ALTER TABLE  `devices` CHANGE  `type`  `type` VARCHAR( 20 ) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL DEFAULT  '';
ALTER TABLE  `ports` CHANGE  `ifVrf`  `ifVrf` INT( 11 ) NOT NULL DEFAULT  '0';
ALTER TABLE  `storage` CHANGE  `storage_free`  `storage_free` BIGINT( 20 ) NOT NULL DEFAULT  '0';
ALTER TABLE  `storage` CHANGE  `storage_used`  `storage_used` BIGINT( 20 ) NOT NULL DEFAULT  '0';
ALTER TABLE  `storage` CHANGE  `storage_perc`  `storage_perc` INT NOT NULL DEFAULT  '0';
ALTER TABLE  `processors` CHANGE  `entPhysicalIndex`  `entPhysicalIndex` INT( 11 ) NOT NULL DEFAULT  '0';
ALTER TABLE  `hrDevice` CHANGE  `hrDeviceErrors`  `hrDeviceErrors` INT( 11 ) NOT NULL DEFAULT  '0';
