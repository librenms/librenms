ALTER TABLE `sensors_to_state_indexes` DROP FOREIGN KEY `sensors_to_state_indexes_ibfk_1`;
ALTER TABLE `sensors_to_state_indexes` DROP FOREIGN KEY `sensors_to_state_indexes_sensor_id_foreign`;
ALTER TABLE `sensors_to_state_indexes` ADD CONSTRAINT `sensors_to_state_indexes_sensor_id_foreign` FOREIGN KEY (`sensor_id`) REFERENCES `sensors` (`sensor_id`) ON DELETE CASCADE;
