ALTER TABLE  `ipv4_addresses` CHANGE  `interface_id`  `port_id` INT( 11 ) NOT NULL;
ALTER TABLE  `ipv6_addresses` CHANGE  `interface_id`  `port_id` INT( 11 ) NOT NULL;
