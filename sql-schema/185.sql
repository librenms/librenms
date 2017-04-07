ALTER TABLE `dbSchema` CHANGE `version` `version` int(11) NOT NULL DEFAULT '0';
ALTER TABLE `dbSchema` ADD PRIMARY KEY (`version`);
ALTER TABLE `users` ADD `remember_token` varchar(100) NULL AFTER `realname`;
ALTER TABLE `users` CHANGE `updated_at` `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP;