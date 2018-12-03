ALTER TABLE alert_schedule_items ADD alert_schedulable_type varchar(255) NOT NULL;
UPDATE alert_schedule_items SET alert_schedulable_type = 'device_group' WHERE target LIKE 'g%';
UPDATE alert_schedule_items SET alert_schedulable_type = 'device' WHERE alert_schedulable_type='';
UPDATE alert_schedule_items SET target = SUBSTRING(target, 2) WHERE target LIKE 'g%';
ALTER TABLE alert_schedule_items CHANGE target alert_schedulable_id int(11) unsigned NOT NULL;
ALTER TABLE alert_schedule_items RENAME TO alert_schedulables;
CREATE INDEX `schedulable_morph_index` ON alert_schedulables (alert_schedulable_type, alert_schedulable_id);
