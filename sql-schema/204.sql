ALTER TABLE `dbSchema` DROP PRIMARY KEY;
ALTER TABLE `dbSchema` ADD PRIMARY KEY (`version`);
ALTER TABLE `dbSchema` CHANGE `version` `version` int(11) NOT NULL DEFAULT '0';
