ALTER TABLE `bill_port_counters` ADD `bill_id` INT NOT NULL ;
ALTER TABLE `bill_port_counters` DROP PRIMARY KEY, ADD PRIMARY KEY ( `port_id` , `bill_id` );
