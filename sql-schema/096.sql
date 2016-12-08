CREATE TABLE IF NOT EXISTS `port_association_mode` (pom_id  int(11) NOT NULL AUTO_INCREMENT PRIMARY KEY, name varchar(12) NOT NULL);
INSERT INTO port_association_mode (pom_id, name) values (1, 'ifIndex');
INSERT INTO port_association_mode (name) values ('ifName');
INSERT INTO port_association_mode (name) values ('ifDescr');
ALTER TABLE devices ADD port_association_mode int(11) NOT NULL DEFAULT 1;
