ALTER TABLE devices ADD location_id int NULL AFTER location;
ALTER TABLE locations MODIFY lat double(10,6);
ALTER TABLE locations MODIFY lng double(10,6);
ALTER TABLE locations MODIFY location varchar(255) NOT NULL;

INSERT INTO locations (location, timestamp) SELECT devices.location, NOW() FROM devices WHERE devices.location IS NOT NULL AND NOT EXISTS (SELECT location FROM locations WHERE location = devices.location);
DELETE t1 FROM locations t1 INNER JOIN locations t2 WHERE t1.id < t2.id AND t1.location = t2.location;
CREATE UNIQUE INDEX locations_location_uindex ON locations (location);

UPDATE devices INNER JOIN locations ON devices.location = locations.location SET devices.location_id = locations.id;
ALTER TABLE devices DROP location;

UPDATE alert_rules SET builder=REPLACE(builder, 'devices.location', 'locations.location');
UPDATE device_groups SET pattern=REPLACE(pattern, 'devices.location', 'locations.location');

INSERT INTO config (config_name,config_value,config_default,config_descr,config_group,config_group_order,config_sub_group,config_sub_group_order,config_hidden,config_disabled) values ('geoloc.engine','','','Geocoding Engine','external',0,'location',0,'0','0');
INSERT INTO config (config_name,config_value,config_default,config_descr,config_group,config_group_order,config_sub_group,config_sub_group_order,config_hidden,config_disabled) values ('geoloc.api_key','','','Geocoding API Key (Required to function)','external',0,'location',0,'0','0');
