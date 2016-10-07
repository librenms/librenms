UPDATE `config` SET `config_name` = 'webui.availability_map_compact', `config_hidden` = '0' WHERE `config_name` = 'webui.availability_map_old';
UPDATE `widgets` SET `base_dimensions` = '6,3';
