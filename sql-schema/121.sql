DELETE FROM `sensors_to_state_indexes` WHERE `sensors_to_state_indexes`.`sensor_id` NOT IN (SELECT DISTINCT `sensors`.`sensor_id` FROM `sensors` INNER JOIN `devices` ON `sensors`.`device_id` = `devices`.`device_id`);
DELETE FROM `sensors` WHERE `sensors`.`device_id` NOT IN (SELECT `device_id` FROM `devices`);
DELETE FROM `state_indexes` WHERE `state_indexes`.`state_index_id` NOT IN (SELECT `sensors_to_state_indexes`.`state_index_id` FROM `sensors_to_state_indexes`);
DELETE FROM `state_translations` WHERE `state_translations`.`state_index_id` NOT IN (SELECT `state_indexes`.`state_index_id` FROM `state_indexes`);

ALTER TABLE `sensors` CHANGE `device_id` `device_id` INT(11) UNSIGNED NOT NULL DEFAULT '0';
ALTER TABLE `sensors` ADD CONSTRAINT `sensors_device_id_foreign` FOREIGN KEY (`device_id`) REFERENCES `devices` (`device_id`) ON DELETE CASCADE;
ALTER TABLE `sensors_to_state_indexes` ADD CONSTRAINT `sensors_to_state_indexes_sensor_id_foreign` FOREIGN KEY (`sensor_id`) REFERENCES `sensors` (`sensor_id`) ON DELETE CASCADE;