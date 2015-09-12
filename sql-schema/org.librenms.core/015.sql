ALTER TABLE  `munin_plugins_ds` CHANGE  `ds_cdef`  `ds_cdef` VARCHAR( 255 ) CHARACTER SET utf8 COLLATE utf8_bin NOT NULL;
ALTER TABLE  `applications` ADD  `app_state` TEXT CHARACTER SET utf8 COLLATE utf8_bin NOT NULL;
