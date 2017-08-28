CREATE TABLE `dashboards` ( `dashboard_id` int(11) NOT NULL AUTO_INCREMENT, `user_id` int(11) NOT NULL DEFAULT 0, `dashboard_name` varchar(255) NOT NULL, `access` int(1) NOT NULL DEFAULT 0, PRIMARY KEY (`dashboard_id`)) ENGINE=InnoDB DEFAULT CHARSET=utf8;
ALTER TABLE `users_widgets` ADD COLUMN `dashboard_id` int(11) NOT NULL;
