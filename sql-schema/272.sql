ALTER TABLE devices ADD location_id int NULL AFTER location;
ALTER TABLE locations MODIFY lat double(10,6);
ALTER TABLE locations MODIFY lng double(10,6);

DELETE t1 FROM locations t1 INNER JOIN locations t2 WHERE t1.id < t2.id AND t1.location = t2.location;
INSERT INTO locations (location, timestamp) SELECT devices.location, NOW() FROM devices WHERE devices.location IS NOT NULL AND NOT EXISTS (SELECT location FROM locations WHERE location = devices.location);
UPDATE devices INNER JOIN locations ON devices.location = locations.location SET devices.location_id = locations.id;
ALTER TABLE devices DROP location;

ALTER TABLE locations MODIFY location varchar(255) NOT NULL;
CREATE UNIQUE INDEX locations_location_uindex ON locations (location);

UPDATE alert_rules SET builder=REPLACE(builder, 'devices.location', 'locations.location');
UPDATE device_groups SET pattern=REPLACE(pattern, 'devices.location', 'locations.location');

