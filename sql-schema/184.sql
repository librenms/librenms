ALTER TABLE users_prefs DROP PRIMARY KEY;
ALTER TABLE users_prefs DROP INDEX pref;
INSERT INTO `users_prefs` (`user_id`, `pref`, `value`) SELECT `user_id`, 'dashboard', `dashboard` FROM `users` WHERE `dashboard` != '0';
INSERT INTO `users_prefs` (`user_id`, `pref`, `value`) SELECT `user_id`, 'twofactor', `twofactor` FROM `users` WHERE `twofactor` != '0';
ALTER TABLE `users` DROP `twofactor`, DROP `dashboard`;
