DELETE a1 FROM alerts a1, alerts a2 WHERE a1.id < a2.id AND a1.device_id = a2.device_id AND a1.rule_id = a2.rule_id;
ALTER TABLE `alerts` ADD UNIQUE `unique_alert`(`device_id`, `rule_id`);