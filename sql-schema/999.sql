ALTER TABLE `ports_nac` ADD COLUMN `vlan` int(10) unsigned;
ALTER TABLE `ports_nac` ALTER COLUMN `time_left` varchar(50) NULL;
ALTER TABLE `ports_nac` ADD COLUMN `time_elapsed` varchar(50);
