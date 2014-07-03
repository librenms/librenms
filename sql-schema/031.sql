ALTER TABLE  `sensors` ADD  `sensor_alert` TINYINT( 1 ) NOT NULL DEFAULT  '1' AFTER  `sensor_limit_low_warn` ;
INSERT INTO `graph_types` SET `graph_type`='device', `graph_subtype`='asa_conns',`graph_section`='firewall',`graph_descr`='Current connections',`graph_order`='0';
