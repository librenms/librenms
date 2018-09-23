CREATE TABLE IF NOT EXISTS `device_groups_perms` (`user_id` int(11) NOT NULL, `group_id` int(11) NOT NULL, `access_level` int(4) NOT NULL DEFAULT '0', KEY `user_id` (`user_id`));
