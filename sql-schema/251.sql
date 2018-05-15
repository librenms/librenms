DELETE FROM `graph_types` WHERE `graph_descr` = 'HTTP Server Connections Acitve';
INSERT INTO `graph_types`(`graph_type`, `graph_subtype`, `graph_section`, `graph_descr`, `graph_order`) VALUES ('device',  'sgos_server_connections_active',  'network',  'HTTP Server Connections Active',  0);
