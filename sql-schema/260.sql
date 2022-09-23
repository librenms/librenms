DELETE FROM ospf_instances WHERE context_name = '';
DELETE FROM ospf_areas WHERE context_name = '';
DELETE FROM ospf_ports WHERE context_name = '';
DELETE FROM ospf_nbrs WHERE context_name = '';
ALTER TABLE ospf_instances ADD id int auto_increment NULL PRIMARY KEY AUTO_INCREMENT FIRST;
ALTER TABLE ospf_areas ADD id int auto_increment NULL PRIMARY KEY AUTO_INCREMENT FIRST;
ALTER TABLE ospf_ports ADD id int auto_increment NULL PRIMARY KEY AUTO_INCREMENT FIRST;
ALTER TABLE ospf_nbrs ADD id int auto_increment NULL PRIMARY KEY AUTO_INCREMENT FIRST;
ALTER TABLE ospf_nbrs MODIFY port_id int(11);
