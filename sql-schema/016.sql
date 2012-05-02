ALTER TABLE  `vlans` ADD  `vlan_type` VARCHAR( 16 ) NULL;
ALTER TABLE  `vlans` CHANGE  `vlan_domain`  `vlan_domain` INT NULL DEFAULT NULL;
ALTER TABLE  `vlans` CHANGE  `vlan_descr`  `vlan_name` VARCHAR( 32 ) CHARACTER SET latin1 COLLATE latin1_swedish_ci NULL DEFAULT NULL;
ALTER TABLE  `vlans` ADD  `vlan_mtu` INT NULL;
