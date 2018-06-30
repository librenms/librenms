ALTER TABLE `eventlog` CHANGE `datetime` `datetime` DATETIME NOT NULL DEFAULT '1970-01-02 00:00:01';
ALTER TABLE `eventlog` ADD COLUMN `severity` INT(1) NULL DEFAULT 2 AFTER `reference`;
