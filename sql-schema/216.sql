UPDATE `sensors` SET `entPhysicalIndex_measured` = 'ports', `entPhysicalIndex` = entity_link_index WHERE entity_link_type='port';
UPDATE `alert_rules` SET `query` = REPLACE(query, 'entity_link_type', 'entPhysicalIndex_measured');
UPDATE `alert_rules` SET `query` = REPLACE(query, 'entity_link_index', 'entPhysicalIndex');
UPDATE `alert_rules` SET `rule` = REPLACE(rule, 'entity_link_type', 'entPhysicalIndex_measured');
UPDATE `alert_rules` SET `rule` = REPLACE(rule, 'entity_link_index', 'entPhysicalIndex');
ALTER TABLE `sensors` DROP `entity_link_type` , DROP `entity_link_index`;
