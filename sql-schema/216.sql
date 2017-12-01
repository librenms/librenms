UPDATE `sensors` SET `entPhysicalIndex_measured` = 'ports', `entPhysicalIndex` = entity_link_index WHERE entity_link_type='port';
UPDATE `config` SET `config_value` = "(%sensors.entPhysicalIndex_measured = 'ports' && %sensors.entPhysicalIndex = %ports.ifIndex && %macros.port_up)", `config_default`  = "(%sensors.entPhysicalIndex_measured = 'ports' && %sensors.entPhysicalIndex = %ports.ifIndex && %macros.port_up)" WHERE `config_name` = 'alert.macros.rule.sensor_port_link';
UPDATE `alert_rules` SET `query` = REPLACE(query, 'entity_link_type', 'entPhysicalIndex_measured');
UPDATE `alert_rules` SET `query` = REPLACE(query, 'entity_link_index', 'entPhysicalIndex');
UPDATE `alert_rules` SET `rule` = REPLACE(rule, 'entity_link_type', 'entPhysicalIndex_measured');
UPDATE `alert_rules` SET `rule` = REPLACE(rule, 'entity_link_index', 'entPhysicalIndex');
ALTER TABLE `sensors` DROP `entity_link_type` , DROP `entity_link_index`;
