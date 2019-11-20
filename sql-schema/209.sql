ALTER TABLE `users` CHANGE COLUMN `username` `username` VARCHAR(255) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL;
ALTER TABLE `session` CHANGE COLUMN `session_username` `session_username` VARCHAR(255) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL;
