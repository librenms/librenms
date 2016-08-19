ALTER TABLE `applications` ADD `discovered` TINYINT NOT NULL DEFAULT '0' AFTER `app_state`;
ALTER IGNORE TABLE `applications` ADD UNIQUE `unique_index`(`device_id`, `app_type`);
