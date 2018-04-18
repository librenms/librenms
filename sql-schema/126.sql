DROP TABLE IF EXISTS `component_statuslog`;
CREATE TABLE `component_statuslog` ( `id` int(11) unsigned NOT NULL AUTO_INCREMENT COMMENT 'ID for each log entry, unique index', `component_id` int(11) unsigned NOT NULL COMMENT 'id from the component table', `status` tinyint(1) NOT NULL DEFAULT '0' COMMENT 'The status that the component was changed TO', `timestamp` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT 'When the status of the component was changed', PRIMARY KEY (`id`), KEY `device` (`component_id`), CONSTRAINT `component_statuslog_ibfk_1` FOREIGN KEY (`component_id`) REFERENCES `component` (`id`) ON DELETE CASCADE ON UPDATE CASCADE ) ENGINE=InnoDB AUTO_INCREMENT=0 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci COMMENT='log of status changes to a component.';
ALTER TABLE `component` CHANGE `status` `status` TINYINT(1) NOT NULL DEFAULT '0' COMMENT 'The status of the component, retreived from the device';
UPDATE component SET status=2 WHERE status=0;
UPDATE component SET status=0 WHERE status=1;
INSERT INTO `widgets` (`widget_title`,`widget`,`base_dimensions`) VALUES ('Component Status','component-status','3,2');
