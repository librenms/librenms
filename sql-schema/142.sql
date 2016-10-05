UPDATE `sensors` SET `sensor_oid` = REPLACE(`sensor_oid`, '1.3.6.1.', '.1.3.6.1.') WHERE `sensor_oid` LIKE '1.3.6.1.%';
