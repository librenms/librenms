ALTER TABLE  `links` ADD  `local_device_id` INT NOT NULL AFTER  `local_port_id` , ADD  `remote_device_id` INT NOT NULL AFTER  `remote_hostname` ;
ALTER TABLE  `links` ADD INDEX (  `local_device_id` ,  `remote_device_id` ) ;
