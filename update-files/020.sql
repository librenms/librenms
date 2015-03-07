ALTER TABLE `devices` ADD `authlevel` ENUM("noAuthNoPriv", "authNoPriv", "authPriv") NULL DEFAULT NULL AFTER `community`;
ALTER TABLE `devices` ADD `authname` VARCHAR(64) NULL DEFAULT NULL AFTER `authlevel`;
ALTER TABLE `devices` ADD `authpass` VARCHAR(64) NULL DEFAULT NULL AFTER `authname`;
ALTER TABLE `devices` ADD `authalgo` ENUM("MD5", "SHA1") NULL DEFAULT NULL AFTER `authpass`;
ALTER TABLE `devices` ADD `cryptopass` VARCHAR(64) NULL DEFAULT NULL AFTER `authalgo`;
ALTER TABLE `devices` ADD `cryptoalgo` ENUM("AES", "DES") NULL DEFAULT NULL AFTER `cryptopass`;
