ALTER TABLE  `storage` ADD  `storage_deleted` BOOL NOT NULL DEFAULT  '0';
ALTER TABLE  `links` CHANGE  `local_interface_id`  `local_port_id` INT( 11 ) NULL DEFAULT NULL;
ALTER TABLE  `links` CHANGE  `remote_interface_id`  `remote_port_id` INT( 11 ) NULL DEFAULT NULL;
ALTER TABLE  `sensors` ADD  `sensor_deleted` BOOL NOT NULL DEFAULT  '0' AFTER  `sensor_id`;
ALTER TABLE  `mempools` ADD  `mempool_deleted` BOOL NOT NULL DEFAULT  '0';
