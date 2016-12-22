ALTER TABLE  `applications` CHANGE  `app_state`  `app_state` VARCHAR( 32 ) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL DEFAULT  'UNKNOWN';
ALTER TABLE  `applications` CHANGE  `app_type`  `app_type` VARCHAR( 64 ) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL;
ALTER TABLE  `devices` CHANGE  `authalgo`  `authalgo` ENUM(  'MD5',  'SHA' ) CHARACTER SET utf8 COLLATE utf8_unicode_ci NULL DEFAULT NULL;
