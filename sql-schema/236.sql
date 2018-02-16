CREATE TABLE `router_utilization` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `device_id` int(11) unsigned NOT NULL DEFAULT '0',
  `oid_current` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `oid_maximum` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `resource` varchar(128) COLLATE utf8_unicode_ci DEFAULT NULL,
  `feature` varchar(128) COLLATE utf8_unicode_ci DEFAULT NULL,
  `forwarding_element` varchar(128) COLLATE utf8_unicode_ci DEFAULT NULL,
  `current` float NOT NULL,
  `maximum` float NOT NULL,
  `lastupdate` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `unique_id` (`device_id`,`oid_current`,`oid_maximum`),
  KEY `device_id` (`device_id`),
  CONSTRAINT `router_utilization_device_id_foreign` FOREIGN KEY (`device_id`) REFERENCES `devices` (`device_id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=998 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
