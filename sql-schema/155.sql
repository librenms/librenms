ALTER TABLE alert_schedule
ADD COLUMN `recurring` tinyint (1) UNSIGNED NOT NULL DEFAULT 0 AFTER `schedule_id`,
ADD COLUMN `start_recurring_dt` DATE NOT NULL DEFAULT '0000-00-00' AFTER `end`,
ADD COLUMN `end_recurring_dt` DATE DEFAULT NULL AFTER `start_recurring_dt`,
ADD COLUMN `start_recurring_hr` TIME NOT NULL DEFAULT '00:00:00' AFTER `end_recurring_dt`,
ADD COLUMN `end_recurring_hr` TIME NOT NULL DEFAULT '00:00:00' AFTER `start_recurring_hr`,
ADD COLUMN `recurring_day` VARCHAR(15) AFTER `end_recurring_hr`;