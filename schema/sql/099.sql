ALTER TABLE  `ports` ADD  `ifOperStatus_prev` VARCHAR( 16 ) NULL AFTER  `ifOperStatus` ;
ALTER TABLE  `ports` ADD  `ifAdminStatus_prev` VARCHAR( 16 ) NULL AFTER  `ifAdminStatus` ;
