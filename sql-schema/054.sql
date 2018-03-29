ALTER TABLE `graph_types` CHANGE `graph_subtype` `graph_subtype` varchar(64);
ALTER TABLE `device_graphs` CHANGE `graph` `graph` varchar(64);
ALTER TABLE `graph_types` CHANGE `graph_descr` `graph_descr` varchar(255);
ALTER TABLE `graph_types` ADD PRIMARY KEY (`graph_type`, `graph_subtype`, `graph_section`);
