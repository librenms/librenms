ALTER TABLE alert_rules DROP query_builder;
ALTER TABLE alert_rules ADD builder TEXT NOT NULL AFTER query;
