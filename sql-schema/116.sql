ALTER TABLE alert_rules ADD COLUMN proc VARCHAR(30) AFTER name;
UPDATE alert_rules SET proc="noproc.pdf";
