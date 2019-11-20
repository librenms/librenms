ALTER TABLE `notifications` CHANGE `datetime` `datetime` timestamp NOT NULL DEFAULT '1970-01-02 00:00:00';
ALTER TABLE `notifications` ADD `severity` INT DEFAULT 0 NULL COMMENT '0=ok,1=warning,2=critical' AFTER `body`;
CREATE INDEX `notifications_severity_index` ON `notifications` (`severity`);
