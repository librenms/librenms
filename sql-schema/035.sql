-- Update to add ping response time and last ping datetime
ALTER TABLE  `devices` ADD  `last_ping` TIMESTAMP NULL AFTER  `last_discovered` , ADD  `last_ping_timetaken` DOUBLE( 5, 2 ) NULL AFTER  `last_ping` ;
