ALTER TABLE `alerts` ADD `info` TEXT NOT NULL;
INSERT INTO `config` (`config_name`, `config_value`, `config_default`, `config_descr`, `config_group`, `config_group_order`, `config_sub_group`, `config_sub_group_order`, `config_hidden`, `config_disabled`) VALUES('alert.ack_until_clear', 'false', 'false', 'Default acknowledge until alert clears', 'alerting', 0, 'general', 0, '0', '0');
