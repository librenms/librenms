ALTER TABLE `devices` ADD `username` VARCHAR(128) NOT NULL AFTER `enable`, ADD `password` VARCHAR(128) NOT NULL AFTER `username`, ADD `enable` VARCHAR(128) NOT NULL AFTER `password`;
