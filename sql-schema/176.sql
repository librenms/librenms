ALTER TABLE `applications` ADD `timestamp` TIMESTAMP NOT NULL AFTER `app_status`;
ALTER TABLE `applications` ADD `app_state_prev` VARCHAR(32) NOT NULL AFTER `app_state`;
