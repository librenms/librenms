ALTER TABLE  `sensors` ADD  `sensor_custom` ENUM(  'No',  'Yes' ) NOT NULL DEFAULT  'No' AFTER  `sensor_alert` ;
