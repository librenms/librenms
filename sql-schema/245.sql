DELETE FROM `alert_device_map` WHERE `device_id` NOT IN (SELECT `device_id` FROM `devices`);
DELETE FROM `alert_device_map` WHERE `rule_id` NOT IN (SELECT `id` FROM `alert_rules`);
DELETE FROM `alert_group_map` WHERE  `group_id` NOT IN (SELECT `group_id` FROM `device_groups`);
DELETE FROM `alert_group_map` WHERE `rule_id` NOT IN (SELECT `id` FROM `alert_rules`);

