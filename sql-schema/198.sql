ALTER TABLE `ports_fdb` DROP `ports_fdb_id`;
CREATE INDEX `ports_fdb_port_id_index` ON `ports_fdb` (`port_id`);
CREATE INDEX `ports_fdb_device_id_index` ON `ports_fdb` (`device_id`);
CREATE INDEX `ports_fdb_vlan_id_index` ON `ports_fdb` (`vlan_id`);
