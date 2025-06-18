/*M!999999\- enable the sandbox mode */ 
/*!40103 SET @OLD_TIME_ZONE=@@TIME_ZONE */;
/*!40103 SET TIME_ZONE='+00:00' */;
/*!40014 SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;
DROP TABLE IF EXISTS `access_points`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `access_points` (
  `accesspoint_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `device_id` int(10) unsigned NOT NULL,
  `name` varchar(255) NOT NULL,
  `radio_number` tinyint(4) DEFAULT NULL,
  `type` varchar(16) NOT NULL,
  `mac_addr` varchar(24) NOT NULL,
  `deleted` tinyint(1) NOT NULL DEFAULT 0,
  `channel` tinyint(3) unsigned NOT NULL DEFAULT 0,
  `txpow` tinyint(4) NOT NULL DEFAULT 0,
  `radioutil` tinyint(4) NOT NULL DEFAULT 0,
  `numasoclients` smallint(6) NOT NULL DEFAULT 0,
  `nummonclients` smallint(6) NOT NULL DEFAULT 0,
  `numactbssid` tinyint(4) NOT NULL DEFAULT 0,
  `nummonbssid` int(11) NOT NULL DEFAULT 0,
  `interference` tinyint(3) unsigned NOT NULL,
  PRIMARY KEY (`accesspoint_id`),
  KEY `name` (`name`,`radio_number`),
  KEY `access_points_deleted_index` (`deleted`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `alert_device_map`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `alert_device_map` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `rule_id` int(10) unsigned NOT NULL,
  `device_id` int(10) unsigned NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `alert_device_map_rule_id_device_id_unique` (`rule_id`,`device_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `alert_group_map`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `alert_group_map` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `rule_id` int(10) unsigned NOT NULL,
  `group_id` int(10) unsigned NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `alert_group_map_rule_id_group_id_unique` (`rule_id`,`group_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `alert_location_map`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `alert_location_map` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `rule_id` int(10) unsigned NOT NULL,
  `location_id` int(10) unsigned NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `alert_location_map_rule_id_location_id_uindex` (`rule_id`,`location_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `alert_log`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `alert_log` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `rule_id` int(10) unsigned NOT NULL,
  `device_id` int(10) unsigned NOT NULL,
  `state` int(11) NOT NULL,
  `details` longblob DEFAULT NULL,
  `time_logged` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `alert_log_time_logged_index` (`time_logged`),
  KEY `alert_log_rule_id_device_id_state_index` (`rule_id`,`device_id`,`state`),
  KEY `alert_log_device_id_rule_id_time_logged_index` (`device_id`,`rule_id`,`time_logged`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `alert_rules`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `alert_rules` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `rule` text NOT NULL,
  `severity` enum('ok','warning','critical') NOT NULL,
  `extra` varchar(255) NOT NULL,
  `disabled` tinyint(1) NOT NULL,
  `name` varchar(255) NOT NULL,
  `query` text NOT NULL,
  `builder` text NOT NULL,
  `proc` varchar(80) DEFAULT NULL,
  `invert_map` tinyint(1) NOT NULL DEFAULT 0,
  `notes` text DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `alert_rules_name_unique` (`name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `alert_schedulables`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `alert_schedulables` (
  `item_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `schedule_id` int(10) unsigned NOT NULL,
  `alert_schedulable_id` int(10) unsigned NOT NULL,
  `alert_schedulable_type` varchar(255) NOT NULL,
  PRIMARY KEY (`item_id`),
  KEY `schedulable_morph_index` (`alert_schedulable_type`,`alert_schedulable_id`),
  KEY `alert_schedulables_schedule_id_index` (`schedule_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `alert_schedule`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `alert_schedule` (
  `schedule_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `recurring` tinyint(1) unsigned NOT NULL DEFAULT 0,
  `start` datetime NOT NULL DEFAULT '1970-01-02 00:00:01',
  `end` datetime NOT NULL DEFAULT '1970-01-02 00:00:01',
  `recurring_day` varchar(15) DEFAULT NULL,
  `title` varchar(255) NOT NULL,
  `notes` text NOT NULL,
  PRIMARY KEY (`schedule_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `alert_template_map`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `alert_template_map` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `alert_templates_id` int(10) unsigned NOT NULL,
  `alert_rule_id` int(10) unsigned NOT NULL,
  PRIMARY KEY (`id`),
  KEY `alert_templates_id` (`alert_templates_id`,`alert_rule_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `alert_templates`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `alert_templates` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `template` longtext NOT NULL,
  `title` varchar(255) DEFAULT NULL,
  `title_rec` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `alert_transport_groups`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `alert_transport_groups` (
  `transport_group_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `transport_group_name` varchar(30) NOT NULL,
  PRIMARY KEY (`transport_group_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `alert_transport_map`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `alert_transport_map` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `rule_id` int(10) unsigned NOT NULL,
  `transport_or_group_id` int(10) unsigned NOT NULL,
  `target_type` varchar(16) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `alert_transports`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `alert_transports` (
  `transport_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `transport_name` varchar(30) NOT NULL,
  `transport_type` varchar(20) NOT NULL DEFAULT 'mail',
  `is_default` tinyint(1) NOT NULL DEFAULT 0,
  `transport_config` text DEFAULT NULL,
  PRIMARY KEY (`transport_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `alerts`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `alerts` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `device_id` int(10) unsigned NOT NULL,
  `rule_id` int(10) unsigned NOT NULL,
  `state` int(11) NOT NULL,
  `alerted` int(11) NOT NULL,
  `open` int(11) NOT NULL,
  `note` text DEFAULT NULL,
  `timestamp` timestamp NOT NULL DEFAULT current_timestamp(),
  `info` text NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `alerts_device_id_rule_id_unique` (`device_id`,`rule_id`),
  KEY `alerts_device_id_index` (`device_id`),
  KEY `alerts_rule_id_index` (`rule_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `api_tokens`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `api_tokens` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `user_id` int(10) unsigned NOT NULL,
  `token_hash` varchar(255) DEFAULT NULL,
  `description` varchar(100) NOT NULL,
  `disabled` tinyint(1) NOT NULL DEFAULT 0,
  PRIMARY KEY (`id`),
  UNIQUE KEY `api_tokens_token_hash_unique` (`token_hash`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `application_metrics`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `application_metrics` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `app_id` int(10) unsigned NOT NULL,
  `metric` varchar(64) NOT NULL,
  `value` double DEFAULT NULL,
  `value_prev` double DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `application_metrics_app_id_metric_unique` (`app_id`,`metric`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `applications`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `applications` (
  `app_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `device_id` int(10) unsigned NOT NULL,
  `app_type` varchar(64) NOT NULL,
  `app_state` varchar(32) NOT NULL DEFAULT 'UNKNOWN',
  `discovered` tinyint(4) NOT NULL DEFAULT 0,
  `app_state_prev` varchar(32) DEFAULT NULL,
  `app_status` varchar(1024) NOT NULL DEFAULT '',
  `timestamp` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `app_instance` varchar(255) NOT NULL DEFAULT '',
  `data` longtext DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`app_id`),
  UNIQUE KEY `applications_device_id_app_type_unique` (`device_id`,`app_type`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `authlog`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `authlog` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `datetime` timestamp NOT NULL DEFAULT current_timestamp(),
  `user` text NOT NULL,
  `address` text NOT NULL,
  `result` text NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `availability`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `availability` (
  `availability_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `device_id` int(10) unsigned NOT NULL,
  `duration` bigint(20) NOT NULL,
  `availability_perc` decimal(9,6) NOT NULL DEFAULT 0.000000,
  PRIMARY KEY (`availability_id`),
  UNIQUE KEY `availability_device_id_duration_unique` (`device_id`,`duration`),
  KEY `availability_device_id_index` (`device_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `bgpPeers`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `bgpPeers` (
  `bgpPeer_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `device_id` int(10) unsigned NOT NULL,
  `vrf_id` int(10) unsigned DEFAULT NULL,
  `astext` varchar(255) NOT NULL,
  `bgpPeerIdentifier` text NOT NULL,
  `bgpPeerRemoteAs` bigint(20) NOT NULL,
  `bgpPeerState` text NOT NULL,
  `bgpPeerAdminStatus` text NOT NULL,
  `bgpPeerLastErrorCode` int(11) DEFAULT NULL,
  `bgpPeerLastErrorSubCode` int(11) DEFAULT NULL,
  `bgpPeerLastErrorText` varchar(254) DEFAULT NULL,
  `bgpPeerIface` int(10) unsigned DEFAULT NULL,
  `bgpLocalAddr` text NOT NULL,
  `bgpPeerRemoteAddr` text NOT NULL,
  `bgpPeerDescr` varchar(255) NOT NULL DEFAULT '',
  `bgpPeerInUpdates` int(10) unsigned NOT NULL,
  `bgpPeerOutUpdates` int(10) unsigned NOT NULL,
  `bgpPeerInTotalMessages` int(10) unsigned NOT NULL,
  `bgpPeerOutTotalMessages` int(10) unsigned NOT NULL,
  `bgpPeerFsmEstablishedTime` int(10) unsigned NOT NULL,
  `bgpPeerInUpdateElapsedTime` int(10) unsigned NOT NULL,
  `context_name` varchar(128) DEFAULT NULL,
  PRIMARY KEY (`bgpPeer_id`),
  KEY `bgppeers_device_id_context_name_index` (`device_id`,`context_name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `bgpPeers_cbgp`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `bgpPeers_cbgp` (
  `device_id` int(10) unsigned NOT NULL,
  `bgpPeerIdentifier` varchar(64) NOT NULL,
  `afi` varchar(16) NOT NULL,
  `safi` varchar(16) NOT NULL,
  `AcceptedPrefixes` int(10) unsigned NOT NULL,
  `DeniedPrefixes` int(10) unsigned NOT NULL,
  `PrefixAdminLimit` int(10) unsigned NOT NULL,
  `PrefixThreshold` int(10) unsigned NOT NULL,
  `PrefixClearThreshold` int(10) unsigned NOT NULL,
  `AdvertisedPrefixes` int(10) unsigned NOT NULL,
  `SuppressedPrefixes` int(10) unsigned NOT NULL,
  `WithdrawnPrefixes` int(10) unsigned NOT NULL,
  `AcceptedPrefixes_delta` int(11) NOT NULL,
  `AcceptedPrefixes_prev` int(10) unsigned NOT NULL,
  `DeniedPrefixes_delta` int(11) NOT NULL,
  `DeniedPrefixes_prev` int(10) unsigned NOT NULL,
  `AdvertisedPrefixes_delta` int(11) NOT NULL,
  `AdvertisedPrefixes_prev` int(10) unsigned NOT NULL,
  `SuppressedPrefixes_delta` int(11) NOT NULL,
  `SuppressedPrefixes_prev` int(10) unsigned NOT NULL,
  `WithdrawnPrefixes_delta` int(11) NOT NULL,
  `WithdrawnPrefixes_prev` int(10) unsigned NOT NULL,
  `context_name` varchar(128) DEFAULT NULL,
  UNIQUE KEY `bgppeers_cbgp_device_id_bgppeeridentifier_afi_safi_unique` (`device_id`,`bgpPeerIdentifier`,`afi`,`safi`),
  KEY `bgppeers_cbgp_device_id_bgppeeridentifier_context_name_index` (`device_id`,`bgpPeerIdentifier`,`context_name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `bill_data`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `bill_data` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `bill_id` int(10) unsigned NOT NULL,
  `timestamp` datetime NOT NULL,
  `period` int(11) NOT NULL,
  `delta` bigint(20) NOT NULL,
  `in_delta` bigint(20) NOT NULL,
  `out_delta` bigint(20) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `bill_data_bill_id_index` (`bill_id`),
  KEY `bill_data_bill_id_timestamp_index` (`bill_id`,`timestamp`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `bill_history`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `bill_history` (
  `bill_hist_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `bill_id` int(10) unsigned NOT NULL,
  `updated` timestamp NOT NULL DEFAULT current_timestamp(),
  `bill_datefrom` datetime NOT NULL,
  `bill_dateto` datetime NOT NULL,
  `bill_type` text NOT NULL,
  `bill_allowed` bigint(20) NOT NULL,
  `bill_used` bigint(20) NOT NULL,
  `bill_overuse` bigint(20) NOT NULL,
  `bill_percent` decimal(10,2) NOT NULL,
  `rate_95th_in` bigint(20) NOT NULL,
  `rate_95th_out` bigint(20) NOT NULL,
  `rate_95th` bigint(20) NOT NULL,
  `dir_95th` varchar(3) NOT NULL,
  `rate_average` bigint(20) NOT NULL,
  `rate_average_in` bigint(20) NOT NULL,
  `rate_average_out` bigint(20) NOT NULL,
  `traf_in` bigint(20) NOT NULL,
  `traf_out` bigint(20) NOT NULL,
  `traf_total` bigint(20) NOT NULL,
  `bill_peak_out` bigint(20) DEFAULT NULL,
  `bill_peak_in` bigint(20) DEFAULT NULL,
  `pdf` longblob DEFAULT NULL,
  PRIMARY KEY (`bill_hist_id`),
  UNIQUE KEY `bill_history_bill_id_bill_datefrom_bill_dateto_unique` (`bill_id`,`bill_datefrom`,`bill_dateto`),
  KEY `bill_history_bill_id_index` (`bill_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `bill_perms`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `bill_perms` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `user_id` int(10) unsigned NOT NULL,
  `bill_id` int(10) unsigned NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `bill_port_counters`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `bill_port_counters` (
  `port_id` int(10) unsigned NOT NULL,
  `timestamp` timestamp NOT NULL DEFAULT current_timestamp(),
  `in_counter` bigint(20) DEFAULT NULL,
  `in_delta` bigint(20) NOT NULL DEFAULT 0,
  `out_counter` bigint(20) DEFAULT NULL,
  `out_delta` bigint(20) NOT NULL DEFAULT 0,
  `bill_id` int(10) unsigned NOT NULL,
  PRIMARY KEY (`port_id`,`bill_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `bill_ports`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `bill_ports` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `bill_id` int(10) unsigned NOT NULL,
  `port_id` int(10) unsigned NOT NULL,
  `bill_port_autoadded` tinyint(1) NOT NULL DEFAULT 0,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `bills`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `bills` (
  `bill_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `bill_name` text NOT NULL,
  `bill_type` text NOT NULL,
  `bill_cdr` bigint(20) DEFAULT NULL,
  `bill_day` int(11) NOT NULL DEFAULT 1,
  `bill_quota` bigint(20) DEFAULT NULL,
  `rate_95th_in` bigint(20) NOT NULL,
  `rate_95th_out` bigint(20) NOT NULL,
  `rate_95th` bigint(20) NOT NULL,
  `dir_95th` varchar(3) NOT NULL,
  `total_data` bigint(20) NOT NULL,
  `total_data_in` bigint(20) NOT NULL,
  `total_data_out` bigint(20) NOT NULL,
  `rate_average_in` bigint(20) NOT NULL,
  `rate_average_out` bigint(20) NOT NULL,
  `rate_average` bigint(20) NOT NULL,
  `bill_last_calc` datetime NOT NULL,
  `bill_custid` varchar(64) NOT NULL,
  `bill_ref` varchar(64) NOT NULL,
  `bill_notes` varchar(256) NOT NULL,
  `bill_autoadded` tinyint(1) NOT NULL,
  PRIMARY KEY (`bill_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `cache`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `cache` (
  `key` varchar(255) NOT NULL,
  `value` mediumtext NOT NULL,
  `expiration` int(11) NOT NULL,
  UNIQUE KEY `cache_key_unique` (`key`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `cache_locks`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `cache_locks` (
  `key` varchar(255) NOT NULL,
  `owner` varchar(255) NOT NULL,
  `expiration` int(11) NOT NULL,
  PRIMARY KEY (`key`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `callback`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `callback` (
  `callback_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `name` char(64) NOT NULL,
  `value` char(64) NOT NULL,
  PRIMARY KEY (`callback_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `cef_switching`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `cef_switching` (
  `cef_switching_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `device_id` int(10) unsigned NOT NULL,
  `entPhysicalIndex` int(11) NOT NULL,
  `afi` varchar(4) NOT NULL,
  `cef_index` int(11) NOT NULL,
  `cef_path` varchar(16) NOT NULL,
  `drop` int(11) NOT NULL,
  `punt` int(11) NOT NULL,
  `punt2host` int(11) NOT NULL,
  `drop_prev` int(11) NOT NULL,
  `punt_prev` int(11) NOT NULL,
  `punt2host_prev` int(11) NOT NULL,
  `updated` int(10) unsigned NOT NULL,
  `updated_prev` int(10) unsigned NOT NULL,
  PRIMARY KEY (`cef_switching_id`),
  UNIQUE KEY `cef_switching_device_id_entphysicalindex_afi_cef_index_unique` (`device_id`,`entPhysicalIndex`,`afi`,`cef_index`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `component`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `component` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT 'ID for each component, unique index',
  `device_id` int(10) unsigned NOT NULL COMMENT 'device_id from the devices table',
  `type` varchar(50) NOT NULL COMMENT 'name from the component_type table',
  `label` varchar(255) DEFAULT NULL COMMENT 'Display label for the component',
  `status` tinyint(1) NOT NULL DEFAULT 0 COMMENT 'The status of the component, retreived from the device',
  `disabled` tinyint(1) NOT NULL DEFAULT 0 COMMENT 'Should this component be polled',
  `ignore` tinyint(1) NOT NULL DEFAULT 0 COMMENT 'Should this component be alerted on',
  `error` varchar(255) DEFAULT NULL COMMENT 'Error message if in Alert state',
  PRIMARY KEY (`id`),
  KEY `component_device_id_index` (`device_id`),
  KEY `component_type_index` (`type`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `component_prefs`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `component_prefs` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT 'ID for each entry',
  `component` int(10) unsigned NOT NULL COMMENT 'id from the component table',
  `attribute` varchar(255) NOT NULL COMMENT 'Attribute for the Component',
  `value` text NOT NULL COMMENT 'Value for the Component',
  PRIMARY KEY (`id`),
  KEY `component_prefs_component_index` (`component`),
  CONSTRAINT `component_prefs_ibfk_1` FOREIGN KEY (`component`) REFERENCES `component` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `component_statuslog`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `component_statuslog` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT 'ID for each log entry, unique index',
  `component_id` int(10) unsigned NOT NULL COMMENT 'id from the component table',
  `status` tinyint(1) NOT NULL DEFAULT 0 COMMENT 'The status that the component was changed TO',
  `message` text DEFAULT NULL,
  `timestamp` timestamp NOT NULL DEFAULT current_timestamp() COMMENT 'When the status of the component was changed',
  PRIMARY KEY (`id`),
  KEY `component_statuslog_component_id_index` (`component_id`),
  CONSTRAINT `component_statuslog_ibfk_1` FOREIGN KEY (`component_id`) REFERENCES `component` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `config`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `config` (
  `config_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `config_name` varchar(255) NOT NULL,
  `config_value` mediumtext NOT NULL,
  PRIMARY KEY (`config_id`),
  UNIQUE KEY `config_config_name_unique` (`config_name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `custom_map_backgrounds`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `custom_map_backgrounds` (
  `custom_map_background_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `custom_map_id` int(10) unsigned NOT NULL,
  `background_image` mediumblob DEFAULT NULL,
  PRIMARY KEY (`custom_map_background_id`),
  UNIQUE KEY `custom_map_backgrounds_custom_map_id_unique` (`custom_map_id`),
  CONSTRAINT `custom_map_backgrounds_custom_map_id_foreign` FOREIGN KEY (`custom_map_id`) REFERENCES `custom_maps` (`custom_map_id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `custom_map_edges`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `custom_map_edges` (
  `custom_map_edge_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `custom_map_id` int(10) unsigned NOT NULL,
  `custom_map_node1_id` int(10) unsigned NOT NULL,
  `custom_map_node2_id` int(10) unsigned NOT NULL,
  `port_id` int(10) unsigned DEFAULT NULL,
  `reverse` tinyint(1) NOT NULL,
  `style` varchar(50) NOT NULL,
  `showpct` tinyint(1) NOT NULL,
  `showbps` tinyint(1) NOT NULL DEFAULT 0,
  `label` varchar(255) NOT NULL DEFAULT '',
  `fixed_width` decimal(3,1) DEFAULT NULL,
  `text_face` varchar(50) NOT NULL,
  `text_size` int(11) NOT NULL,
  `text_colour` varchar(10) NOT NULL,
  `text_align` varchar(16) NOT NULL DEFAULT 'horizontal',
  `mid_x` int(11) NOT NULL,
  `mid_y` int(11) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`custom_map_edge_id`),
  KEY `custom_map_edges_custom_map_id_index` (`custom_map_id`),
  KEY `custom_map_edges_custom_map_node1_id_index` (`custom_map_node1_id`),
  KEY `custom_map_edges_custom_map_node2_id_index` (`custom_map_node2_id`),
  KEY `custom_map_edges_port_id_index` (`port_id`),
  CONSTRAINT `custom_map_edges_custom_map_id_foreign` FOREIGN KEY (`custom_map_id`) REFERENCES `custom_maps` (`custom_map_id`) ON DELETE CASCADE,
  CONSTRAINT `custom_map_edges_custom_map_node1_id_foreign` FOREIGN KEY (`custom_map_node1_id`) REFERENCES `custom_map_nodes` (`custom_map_node_id`) ON DELETE CASCADE,
  CONSTRAINT `custom_map_edges_custom_map_node2_id_foreign` FOREIGN KEY (`custom_map_node2_id`) REFERENCES `custom_map_nodes` (`custom_map_node_id`) ON DELETE CASCADE,
  CONSTRAINT `custom_map_edges_port_id_foreign` FOREIGN KEY (`port_id`) REFERENCES `ports` (`port_id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `custom_map_node_images`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `custom_map_node_images` (
  `custom_map_node_image_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `image` mediumblob DEFAULT NULL,
  `mime` varchar(255) NOT NULL,
  `version` varchar(255) NOT NULL,
  `name` varchar(255) NOT NULL,
  PRIMARY KEY (`custom_map_node_image_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `custom_map_nodes`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `custom_map_nodes` (
  `custom_map_node_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `custom_map_id` int(10) unsigned NOT NULL,
  `device_id` int(10) unsigned DEFAULT NULL,
  `linked_custom_map_id` int(10) unsigned DEFAULT NULL,
  `label` varchar(50) NOT NULL,
  `style` varchar(50) NOT NULL,
  `icon` varchar(8) DEFAULT NULL,
  `image` varchar(255) NOT NULL DEFAULT '',
  `node_image_id` int(10) unsigned DEFAULT NULL,
  `size` int(11) NOT NULL,
  `border_width` int(11) NOT NULL,
  `text_face` varchar(50) NOT NULL,
  `text_size` int(11) NOT NULL,
  `text_colour` varchar(10) NOT NULL,
  `colour_bg` varchar(10) DEFAULT NULL,
  `colour_bdr` varchar(10) DEFAULT NULL,
  `x_pos` int(11) NOT NULL,
  `y_pos` int(11) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`custom_map_node_id`),
  KEY `custom_map_nodes_custom_map_id_index` (`custom_map_id`),
  KEY `custom_map_nodes_device_id_index` (`device_id`),
  KEY `custom_map_nodes_linked_custom_map_id_index` (`linked_custom_map_id`),
  KEY `custom_map_nodes_node_image_id_index` (`node_image_id`),
  CONSTRAINT `custom_map_nodes_custom_map_id_foreign` FOREIGN KEY (`custom_map_id`) REFERENCES `custom_maps` (`custom_map_id`) ON DELETE CASCADE,
  CONSTRAINT `custom_map_nodes_device_id_foreign` FOREIGN KEY (`device_id`) REFERENCES `devices` (`device_id`) ON DELETE SET NULL,
  CONSTRAINT `custom_map_nodes_linked_custom_map_id_foreign` FOREIGN KEY (`linked_custom_map_id`) REFERENCES `custom_maps` (`custom_map_id`) ON DELETE SET NULL,
  CONSTRAINT `custom_map_nodes_node_image_id_foreign` FOREIGN KEY (`node_image_id`) REFERENCES `custom_map_node_images` (`custom_map_node_image_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `custom_maps`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `custom_maps` (
  `custom_map_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(100) NOT NULL,
  `width` varchar(10) NOT NULL,
  `height` varchar(10) NOT NULL,
  `node_align` smallint(6) NOT NULL DEFAULT 0,
  `reverse_arrows` tinyint(1) NOT NULL DEFAULT 0,
  `edge_separation` smallint(6) NOT NULL DEFAULT 10,
  `legend_x` int(11) NOT NULL DEFAULT -1,
  `legend_y` int(11) NOT NULL DEFAULT -1,
  `legend_steps` smallint(6) NOT NULL DEFAULT 7,
  `legend_font_size` smallint(6) NOT NULL DEFAULT 14,
  `legend_hide_invalid` tinyint(1) NOT NULL DEFAULT 0,
  `legend_hide_overspeed` tinyint(1) NOT NULL DEFAULT 0,
  `legend_colours` longtext DEFAULT NULL,
  `options` longtext DEFAULT NULL,
  `newnodeconfig` longtext NOT NULL,
  `newedgeconfig` longtext NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `menu_group` varchar(100) DEFAULT NULL,
  `background_type` varchar(16) NOT NULL DEFAULT 'none',
  `background_data` text DEFAULT NULL,
  PRIMARY KEY (`custom_map_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `customers`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `customers` (
  `customer_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `username` char(64) NOT NULL,
  `password` char(32) NOT NULL,
  `string` char(64) NOT NULL,
  `level` tinyint(4) NOT NULL DEFAULT 0,
  PRIMARY KEY (`customer_id`),
  UNIQUE KEY `customers_username_unique` (`username`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `customoids`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `customoids` (
  `customoid_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `device_id` int(10) unsigned NOT NULL DEFAULT 0,
  `customoid_descr` varchar(255) DEFAULT '',
  `customoid_deleted` tinyint(4) NOT NULL DEFAULT 0,
  `customoid_current` double DEFAULT NULL,
  `customoid_prev` double DEFAULT NULL,
  `customoid_oid` varchar(255) DEFAULT NULL,
  `customoid_datatype` varchar(20) NOT NULL DEFAULT 'GAUGE',
  `customoid_unit` varchar(20) DEFAULT NULL,
  `customoid_divisor` int(10) unsigned NOT NULL DEFAULT 1,
  `customoid_multiplier` int(10) unsigned NOT NULL DEFAULT 1,
  `customoid_limit` double DEFAULT NULL,
  `customoid_limit_warn` double DEFAULT NULL,
  `customoid_limit_low` double DEFAULT NULL,
  `customoid_limit_low_warn` double DEFAULT NULL,
  `customoid_alert` tinyint(4) NOT NULL DEFAULT 0,
  `customoid_passed` tinyint(4) NOT NULL DEFAULT 0,
  `lastupdate` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `user_func` varchar(100) DEFAULT NULL,
  PRIMARY KEY (`customoid_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `dashboards`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `dashboards` (
  `dashboard_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `user_id` int(10) unsigned NOT NULL DEFAULT 0,
  `dashboard_name` varchar(255) NOT NULL,
  `access` tinyint(1) NOT NULL DEFAULT 0,
  PRIMARY KEY (`dashboard_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `device_graphs`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `device_graphs` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `device_id` int(10) unsigned NOT NULL,
  `graph` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `device_graphs_device_id_index` (`device_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `device_group_device`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `device_group_device` (
  `device_group_id` int(10) unsigned NOT NULL,
  `device_id` int(10) unsigned NOT NULL,
  PRIMARY KEY (`device_group_id`,`device_id`),
  KEY `device_group_device_device_group_id_index` (`device_group_id`),
  KEY `device_group_device_device_id_index` (`device_id`),
  CONSTRAINT `device_group_device_device_group_id_foreign` FOREIGN KEY (`device_group_id`) REFERENCES `device_groups` (`id`) ON DELETE CASCADE,
  CONSTRAINT `device_group_device_device_id_foreign` FOREIGN KEY (`device_id`) REFERENCES `devices` (`device_id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `device_groups`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `device_groups` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL DEFAULT '',
  `desc` varchar(255) DEFAULT '',
  `type` varchar(16) NOT NULL DEFAULT 'dynamic',
  `rules` text DEFAULT NULL,
  `pattern` text DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `device_groups_name_unique` (`name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `device_outages`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `device_outages` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `device_id` int(10) unsigned NOT NULL,
  `going_down` bigint(20) NOT NULL,
  `up_again` bigint(20) DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `device_outages_device_id_going_down_unique` (`device_id`,`going_down`),
  KEY `device_outages_device_id_index` (`device_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `device_relationships`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `device_relationships` (
  `parent_device_id` int(10) unsigned NOT NULL DEFAULT 0,
  `child_device_id` int(10) unsigned NOT NULL,
  PRIMARY KEY (`parent_device_id`,`child_device_id`),
  KEY `device_relationships_child_device_id_index` (`child_device_id`),
  CONSTRAINT `device_relationship_child_device_id_fk` FOREIGN KEY (`child_device_id`) REFERENCES `devices` (`device_id`) ON DELETE CASCADE,
  CONSTRAINT `device_relationship_parent_device_id_fk` FOREIGN KEY (`parent_device_id`) REFERENCES `devices` (`device_id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `devices`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `devices` (
  `device_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `inserted` timestamp NULL DEFAULT current_timestamp(),
  `hostname` varchar(128) NOT NULL,
  `sysName` varchar(128) DEFAULT NULL,
  `display` varchar(128) DEFAULT NULL,
  `ip` varbinary(16) DEFAULT NULL,
  `overwrite_ip` varchar(40) DEFAULT NULL,
  `community` varchar(255) DEFAULT NULL,
  `authlevel` enum('noAuthNoPriv','authNoPriv','authPriv') DEFAULT NULL,
  `authname` varchar(64) DEFAULT NULL,
  `authpass` varchar(64) DEFAULT NULL,
  `authalgo` varchar(10) DEFAULT NULL,
  `cryptopass` varchar(64) DEFAULT NULL,
  `cryptoalgo` varchar(10) DEFAULT NULL,
  `snmpver` varchar(4) NOT NULL DEFAULT 'v2c',
  `port` smallint(5) unsigned NOT NULL DEFAULT 161,
  `transport` varchar(16) NOT NULL DEFAULT 'udp',
  `timeout` int(11) DEFAULT NULL,
  `retries` int(11) DEFAULT NULL,
  `snmp_disable` tinyint(1) NOT NULL DEFAULT 0,
  `bgpLocalAs` int(10) unsigned DEFAULT NULL,
  `sysObjectID` varchar(128) DEFAULT NULL,
  `sysDescr` text DEFAULT NULL,
  `sysContact` text DEFAULT NULL,
  `version` text DEFAULT NULL,
  `hardware` text DEFAULT NULL,
  `features` text DEFAULT NULL,
  `location_id` int(10) unsigned DEFAULT NULL,
  `os` varchar(32) DEFAULT NULL,
  `status` tinyint(1) NOT NULL DEFAULT 0,
  `status_reason` varchar(50) NOT NULL,
  `ignore` tinyint(1) NOT NULL DEFAULT 0,
  `disabled` tinyint(1) NOT NULL DEFAULT 0,
  `uptime` bigint(20) DEFAULT NULL,
  `agent_uptime` int(10) unsigned NOT NULL DEFAULT 0,
  `last_polled` timestamp NULL DEFAULT NULL,
  `last_poll_attempted` timestamp NULL DEFAULT NULL,
  `last_polled_timetaken` double unsigned DEFAULT NULL,
  `last_discovered_timetaken` double unsigned DEFAULT NULL,
  `last_discovered` timestamp NULL DEFAULT NULL,
  `last_ping` timestamp NULL DEFAULT NULL,
  `last_ping_timetaken` double unsigned DEFAULT NULL,
  `purpose` text DEFAULT NULL,
  `type` varchar(20) NOT NULL DEFAULT '',
  `serial` text DEFAULT NULL,
  `icon` varchar(255) DEFAULT NULL,
  `poller_group` int(11) NOT NULL DEFAULT 0,
  `override_sysLocation` tinyint(1) DEFAULT 0,
  `notes` text DEFAULT NULL,
  `port_association_mode` int(11) NOT NULL DEFAULT 1,
  `max_depth` int(11) NOT NULL DEFAULT 0,
  `disable_notify` tinyint(1) NOT NULL DEFAULT 0,
  `ignore_status` tinyint(1) NOT NULL DEFAULT 0,
  PRIMARY KEY (`device_id`),
  KEY `devices_sysname_index` (`sysName`),
  KEY `devices_os_index` (`os`),
  KEY `devices_status_index` (`status`),
  KEY `devices_last_polled_index` (`last_polled`),
  KEY `devices_last_poll_attempted_index` (`last_poll_attempted`),
  KEY `devices_hostname_sysname_display_index` (`hostname`,`sysName`,`display`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `devices_attribs`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `devices_attribs` (
  `attrib_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `device_id` int(10) unsigned NOT NULL,
  `attrib_type` varchar(64) NOT NULL,
  `attrib_value` text NOT NULL,
  `updated` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`attrib_id`),
  KEY `devices_attribs_device_id_index` (`device_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `devices_group_perms`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `devices_group_perms` (
  `user_id` int(10) unsigned NOT NULL,
  `device_group_id` int(10) unsigned NOT NULL,
  PRIMARY KEY (`device_group_id`,`user_id`),
  KEY `devices_group_perms_user_id_index` (`user_id`),
  KEY `devices_group_perms_device_group_id_index` (`device_group_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `devices_perms`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `devices_perms` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `user_id` int(10) unsigned NOT NULL,
  `device_id` int(10) unsigned NOT NULL,
  PRIMARY KEY (`id`),
  KEY `devices_perms_user_id_index` (`user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `entPhysical`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `entPhysical` (
  `entPhysical_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `device_id` int(10) unsigned NOT NULL,
  `entPhysicalIndex` int(11) NOT NULL,
  `entPhysicalDescr` text DEFAULT NULL,
  `entPhysicalClass` text DEFAULT NULL,
  `entPhysicalName` text DEFAULT NULL,
  `entPhysicalHardwareRev` varchar(96) DEFAULT NULL,
  `entPhysicalFirmwareRev` varchar(96) DEFAULT NULL,
  `entPhysicalSoftwareRev` varchar(96) DEFAULT NULL,
  `entPhysicalAlias` varchar(32) DEFAULT NULL,
  `entPhysicalAssetID` varchar(32) DEFAULT NULL,
  `entPhysicalIsFRU` varchar(8) DEFAULT NULL,
  `entPhysicalModelName` text DEFAULT NULL,
  `entPhysicalVendorType` text DEFAULT NULL,
  `entPhysicalSerialNum` text DEFAULT NULL,
  `entPhysicalContainedIn` int(11) NOT NULL DEFAULT 0,
  `entPhysicalParentRelPos` int(11) NOT NULL DEFAULT -1,
  `entPhysicalMfgName` text DEFAULT NULL,
  `ifIndex` int(10) unsigned DEFAULT NULL,
  PRIMARY KEY (`entPhysical_id`),
  KEY `entphysical_device_id_index` (`device_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `entPhysical_state`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `entPhysical_state` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `device_id` int(10) unsigned NOT NULL,
  `entPhysicalIndex` varchar(64) NOT NULL,
  `subindex` varchar(64) DEFAULT NULL,
  `group` varchar(64) NOT NULL,
  `key` varchar(64) NOT NULL,
  `value` varchar(255) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `device_id_index` (`device_id`,`entPhysicalIndex`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `entityState`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `entityState` (
  `entity_state_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `device_id` int(10) unsigned DEFAULT NULL,
  `entPhysical_id` int(10) unsigned DEFAULT NULL,
  `entStateLastChanged` datetime DEFAULT NULL,
  `entStateAdmin` int(11) DEFAULT NULL,
  `entStateOper` int(11) DEFAULT NULL,
  `entStateUsage` int(11) DEFAULT NULL,
  `entStateAlarm` text DEFAULT NULL,
  `entStateStandby` int(11) DEFAULT NULL,
  PRIMARY KEY (`entity_state_id`),
  KEY `entitystate_device_id_index` (`device_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `eventlog`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `eventlog` (
  `event_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `device_id` int(10) unsigned DEFAULT NULL,
  `datetime` datetime NOT NULL DEFAULT '1970-01-02 00:00:01',
  `message` text DEFAULT NULL,
  `type` varchar(64) DEFAULT NULL,
  `reference` varchar(64) DEFAULT NULL,
  `username` varchar(128) DEFAULT NULL,
  `severity` tinyint(4) NOT NULL DEFAULT 2,
  PRIMARY KEY (`event_id`),
  KEY `eventlog_device_id_index` (`device_id`),
  KEY `eventlog_datetime_index` (`datetime`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `graph_types`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `graph_types` (
  `graph_type` varchar(32) NOT NULL,
  `graph_subtype` varchar(64) NOT NULL,
  `graph_section` varchar(32) NOT NULL,
  `graph_descr` varchar(255) DEFAULT NULL,
  `graph_order` int(11) NOT NULL,
  PRIMARY KEY (`graph_type`,`graph_subtype`,`graph_section`),
  KEY `graph_types_graph_type_index` (`graph_type`),
  KEY `graph_types_graph_subtype_index` (`graph_subtype`),
  KEY `graph_types_graph_section_index` (`graph_section`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `hrDevice`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `hrDevice` (
  `hrDevice_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `device_id` int(10) unsigned NOT NULL,
  `hrDeviceIndex` int(11) NOT NULL,
  `hrDeviceDescr` text NOT NULL,
  `hrDeviceType` text NOT NULL,
  `hrDeviceErrors` int(11) NOT NULL DEFAULT 0,
  `hrDeviceStatus` text NOT NULL,
  `hrProcessorLoad` tinyint(4) DEFAULT NULL,
  PRIMARY KEY (`hrDevice_id`),
  KEY `hrdevice_device_id_index` (`device_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `hrSystem`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `hrSystem` (
  `hrSystem_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `device_id` int(10) unsigned NOT NULL,
  `hrSystemNumUsers` int(11) DEFAULT NULL,
  `hrSystemProcesses` int(11) DEFAULT NULL,
  `hrSystemMaxProcesses` int(11) DEFAULT NULL,
  PRIMARY KEY (`hrSystem_id`),
  KEY `hrsystem_device_id_index` (`device_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `ipsec_tunnels`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `ipsec_tunnels` (
  `tunnel_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `device_id` int(10) unsigned NOT NULL,
  `peer_port` int(10) unsigned NOT NULL,
  `peer_addr` varchar(64) NOT NULL,
  `local_addr` varchar(64) NOT NULL,
  `local_port` int(10) unsigned NOT NULL,
  `tunnel_name` varchar(96) NOT NULL,
  `tunnel_status` varchar(11) NOT NULL,
  PRIMARY KEY (`tunnel_id`),
  UNIQUE KEY `ipsec_tunnels_device_id_peer_addr_unique` (`device_id`,`peer_addr`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `ipv4_addresses`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `ipv4_addresses` (
  `ipv4_address_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `ipv4_address` varchar(32) NOT NULL,
  `ipv4_prefixlen` int(11) NOT NULL,
  `ipv4_network_id` int(10) unsigned NOT NULL DEFAULT 0,
  `port_id` int(10) unsigned NOT NULL,
  `context_name` varchar(128) DEFAULT NULL,
  PRIMARY KEY (`ipv4_address_id`),
  KEY `ipv4_addresses_port_id_index` (`port_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `ipv4_mac`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `ipv4_mac` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `port_id` int(10) unsigned NOT NULL,
  `device_id` int(10) unsigned DEFAULT NULL,
  `mac_address` varchar(32) NOT NULL,
  `ipv4_address` varchar(32) NOT NULL,
  `context_name` varchar(128) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `ipv4_mac_port_id_index` (`port_id`),
  KEY `ipv4_mac_mac_address_index` (`mac_address`),
  KEY `ipv4_mac_device_id_index` (`device_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `ipv4_networks`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `ipv4_networks` (
  `ipv4_network_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `ipv4_network` varchar(64) NOT NULL,
  `context_name` varchar(128) DEFAULT NULL,
  PRIMARY KEY (`ipv4_network_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `ipv6_addresses`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `ipv6_addresses` (
  `ipv6_address_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `ipv6_address` varchar(128) NOT NULL,
  `ipv6_compressed` varchar(128) NOT NULL,
  `ipv6_prefixlen` int(11) NOT NULL,
  `ipv6_origin` varchar(16) NOT NULL,
  `ipv6_network_id` int(10) unsigned NOT NULL DEFAULT 0,
  `port_id` int(10) unsigned NOT NULL,
  `context_name` varchar(128) DEFAULT NULL,
  PRIMARY KEY (`ipv6_address_id`),
  KEY `ipv6_addresses_port_id_index` (`port_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `ipv6_nd`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `ipv6_nd` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `port_id` bigint(20) unsigned NOT NULL,
  `device_id` bigint(20) unsigned NOT NULL,
  `mac_address` varchar(32) NOT NULL,
  `ipv6_address` varchar(128) NOT NULL,
  `context_name` varchar(128) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `ipv6_nd_port_id_index` (`port_id`),
  KEY `ipv6_nd_device_id_index` (`device_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `ipv6_networks`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `ipv6_networks` (
  `ipv6_network_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `ipv6_network` varchar(64) NOT NULL,
  `context_name` varchar(128) DEFAULT NULL,
  PRIMARY KEY (`ipv6_network_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `isis_adjacencies`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `isis_adjacencies` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `device_id` int(11) NOT NULL,
  `index` varchar(16) DEFAULT NULL,
  `port_id` int(11) DEFAULT NULL,
  `ifIndex` int(11) NOT NULL,
  `isisISAdjState` varchar(13) NOT NULL,
  `isisISAdjNeighSysType` varchar(128) DEFAULT NULL,
  `isisISAdjNeighSysID` varchar(128) DEFAULT NULL,
  `isisISAdjNeighPriority` varchar(128) DEFAULT NULL,
  `isisISAdjLastUpTime` bigint(20) unsigned DEFAULT NULL,
  `isisISAdjAreaAddress` varchar(128) DEFAULT NULL,
  `isisISAdjIPAddrType` varchar(128) DEFAULT NULL,
  `isisISAdjIPAddrAddress` varchar(128) DEFAULT NULL,
  `isisCircAdminState` varchar(16) NOT NULL DEFAULT 'off',
  PRIMARY KEY (`id`),
  KEY `isis_adjacencies_device_id_index` (`device_id`),
  KEY `isis_adjacencies_port_id_index` (`port_id`),
  KEY `isis_adjacencies_ifindex_index` (`ifIndex`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `juniAtmVp`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `juniAtmVp` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `juniAtmVp_id` int(10) unsigned NOT NULL,
  `port_id` int(10) unsigned NOT NULL,
  `vp_id` int(10) unsigned NOT NULL,
  `vp_descr` varchar(32) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `juniatmvp_port_id_index` (`port_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `links`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `links` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `local_port_id` int(10) unsigned DEFAULT NULL,
  `local_device_id` int(10) unsigned NOT NULL,
  `remote_port_id` int(10) unsigned DEFAULT NULL,
  `active` tinyint(1) NOT NULL DEFAULT 1,
  `protocol` varchar(11) DEFAULT NULL,
  `remote_hostname` varchar(128) NOT NULL,
  `remote_device_id` int(10) unsigned NOT NULL,
  `remote_port` varchar(128) NOT NULL,
  `remote_platform` varchar(256) DEFAULT NULL,
  `remote_version` varchar(256) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `local_device_id` (`local_device_id`,`remote_device_id`),
  KEY `links_local_port_id_index` (`local_port_id`),
  KEY `links_remote_port_id_index` (`remote_port_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `loadbalancer_rservers`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `loadbalancer_rservers` (
  `rserver_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `farm_id` varchar(128) NOT NULL,
  `device_id` int(10) unsigned NOT NULL,
  `StateDescr` varchar(64) NOT NULL,
  PRIMARY KEY (`rserver_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `loadbalancer_vservers`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `loadbalancer_vservers` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `classmap_id` int(10) unsigned NOT NULL,
  `classmap` varchar(128) NOT NULL,
  `serverstate` varchar(64) NOT NULL,
  `device_id` int(10) unsigned NOT NULL,
  PRIMARY KEY (`id`),
  KEY `loadbalancer_vservers_device_id_index` (`device_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `locations`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `locations` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `location` varchar(255) NOT NULL,
  `lat` decimal(10,8) DEFAULT NULL,
  `lng` decimal(11,8) DEFAULT NULL,
  `timestamp` datetime NOT NULL,
  `fixed_coordinates` tinyint(1) NOT NULL DEFAULT 0,
  PRIMARY KEY (`id`),
  UNIQUE KEY `locations_location_unique` (`location`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `mac_accounting`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `mac_accounting` (
  `ma_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `port_id` int(10) unsigned NOT NULL,
  `mac` varchar(32) NOT NULL,
  `in_oid` varchar(128) NOT NULL,
  `out_oid` varchar(128) NOT NULL,
  `bps_out` int(11) NOT NULL,
  `bps_in` int(11) NOT NULL,
  `cipMacHCSwitchedBytes_input` bigint(20) DEFAULT NULL,
  `cipMacHCSwitchedBytes_input_prev` bigint(20) DEFAULT NULL,
  `cipMacHCSwitchedBytes_input_delta` bigint(20) DEFAULT NULL,
  `cipMacHCSwitchedBytes_input_rate` int(11) DEFAULT NULL,
  `cipMacHCSwitchedBytes_output` bigint(20) DEFAULT NULL,
  `cipMacHCSwitchedBytes_output_prev` bigint(20) DEFAULT NULL,
  `cipMacHCSwitchedBytes_output_delta` bigint(20) DEFAULT NULL,
  `cipMacHCSwitchedBytes_output_rate` int(11) DEFAULT NULL,
  `cipMacHCSwitchedPkts_input` bigint(20) DEFAULT NULL,
  `cipMacHCSwitchedPkts_input_prev` bigint(20) DEFAULT NULL,
  `cipMacHCSwitchedPkts_input_delta` bigint(20) DEFAULT NULL,
  `cipMacHCSwitchedPkts_input_rate` int(11) DEFAULT NULL,
  `cipMacHCSwitchedPkts_output` bigint(20) DEFAULT NULL,
  `cipMacHCSwitchedPkts_output_prev` bigint(20) DEFAULT NULL,
  `cipMacHCSwitchedPkts_output_delta` bigint(20) DEFAULT NULL,
  `cipMacHCSwitchedPkts_output_rate` int(11) DEFAULT NULL,
  `poll_time` int(10) unsigned DEFAULT NULL,
  `poll_prev` int(10) unsigned DEFAULT NULL,
  `poll_period` int(10) unsigned DEFAULT NULL,
  PRIMARY KEY (`ma_id`),
  KEY `mac_accounting_port_id_index` (`port_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `mefinfo`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `mefinfo` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `device_id` int(10) unsigned NOT NULL,
  `mefID` int(11) NOT NULL,
  `mefType` varchar(128) NOT NULL,
  `mefIdent` varchar(128) NOT NULL,
  `mefMTU` int(11) NOT NULL DEFAULT 1500,
  `mefAdmState` varchar(128) NOT NULL,
  `mefRowState` varchar(128) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `mefinfo_device_id_index` (`device_id`),
  KEY `mefinfo_mefid_index` (`mefID`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `mempools`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `mempools` (
  `mempool_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `mempool_index` varchar(16) NOT NULL,
  `entPhysicalIndex` int(11) DEFAULT NULL,
  `mempool_type` varchar(32) NOT NULL,
  `mempool_class` varchar(32) NOT NULL DEFAULT 'system',
  `mempool_precision` int(11) NOT NULL DEFAULT 1,
  `mempool_descr` varchar(128) NOT NULL,
  `device_id` int(10) unsigned NOT NULL,
  `mempool_perc` int(11) NOT NULL,
  `mempool_perc_oid` varchar(255) DEFAULT NULL,
  `mempool_used` bigint(20) NOT NULL,
  `mempool_used_oid` varchar(255) DEFAULT NULL,
  `mempool_free` bigint(20) NOT NULL,
  `mempool_free_oid` varchar(255) DEFAULT NULL,
  `mempool_total` bigint(20) NOT NULL,
  `mempool_total_oid` varchar(255) DEFAULT NULL,
  `mempool_largestfree` bigint(20) DEFAULT NULL,
  `mempool_lowestfree` bigint(20) DEFAULT NULL,
  `mempool_deleted` tinyint(1) NOT NULL DEFAULT 0,
  `mempool_perc_warn` int(11) DEFAULT NULL,
  PRIMARY KEY (`mempool_id`),
  KEY `mempools_device_id_index` (`device_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `migrations`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `migrations` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `migration` varchar(255) NOT NULL,
  `batch` int(11) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `model_has_permissions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `model_has_permissions` (
  `permission_id` bigint(20) unsigned NOT NULL,
  `model_type` varchar(255) NOT NULL,
  `model_id` bigint(20) unsigned NOT NULL,
  PRIMARY KEY (`permission_id`,`model_id`,`model_type`),
  KEY `model_has_permissions_model_id_model_type_index` (`model_id`,`model_type`),
  CONSTRAINT `model_has_permissions_permission_id_foreign` FOREIGN KEY (`permission_id`) REFERENCES `permissions` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `model_has_roles`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `model_has_roles` (
  `role_id` bigint(20) unsigned NOT NULL,
  `model_type` varchar(255) NOT NULL,
  `model_id` bigint(20) unsigned NOT NULL,
  PRIMARY KEY (`role_id`,`model_id`,`model_type`),
  KEY `model_has_roles_model_id_model_type_index` (`model_id`,`model_type`),
  CONSTRAINT `model_has_roles_role_id_foreign` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `mpls_lsp_paths`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `mpls_lsp_paths` (
  `lsp_path_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `lsp_id` int(10) unsigned NOT NULL,
  `path_oid` int(10) unsigned NOT NULL,
  `device_id` int(10) unsigned NOT NULL,
  `mplsLspPathRowStatus` enum('active','notInService','notReady','createAndGo','createAndWait','destroy') NOT NULL,
  `mplsLspPathLastChange` bigint(20) NOT NULL,
  `mplsLspPathType` enum('other','primary','standby','secondary') NOT NULL,
  `mplsLspPathBandwidth` int(10) unsigned NOT NULL,
  `mplsLspPathOperBandwidth` int(10) unsigned NOT NULL,
  `mplsLspPathAdminState` enum('noop','inService','outOfService') NOT NULL,
  `mplsLspPathOperState` enum('unknown','inService','outOfService','transition') NOT NULL,
  `mplsLspPathState` enum('unknown','active','inactive') NOT NULL,
  `mplsLspPathFailCode` varchar(64) NOT NULL,
  `mplsLspPathFailNodeAddr` varchar(32) NOT NULL,
  `mplsLspPathMetric` int(10) unsigned NOT NULL,
  `mplsLspPathOperMetric` int(10) unsigned DEFAULT NULL,
  `mplsLspPathTimeUp` bigint(20) DEFAULT NULL,
  `mplsLspPathTimeDown` bigint(20) DEFAULT NULL,
  `mplsLspPathTransitionCount` int(10) unsigned DEFAULT NULL,
  `mplsLspPathTunnelARHopListIndex` int(10) unsigned DEFAULT NULL,
  `mplsLspPathTunnelCHopListIndex` int(10) unsigned DEFAULT NULL,
  PRIMARY KEY (`lsp_path_id`),
  KEY `mpls_lsp_paths_device_id_index` (`device_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `mpls_lsps`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `mpls_lsps` (
  `lsp_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `vrf_oid` int(10) unsigned NOT NULL,
  `lsp_oid` int(10) unsigned NOT NULL,
  `device_id` int(10) unsigned NOT NULL,
  `mplsLspRowStatus` enum('active','notInService','notReady','createAndGo','createAndWait','destroy') NOT NULL,
  `mplsLspLastChange` bigint(20) DEFAULT NULL,
  `mplsLspName` varchar(64) NOT NULL,
  `mplsLspAdminState` enum('noop','inService','outOfService') NOT NULL,
  `mplsLspOperState` enum('unknown','inService','outOfService','transition') NOT NULL,
  `mplsLspFromAddr` varchar(32) NOT NULL,
  `mplsLspToAddr` varchar(32) NOT NULL,
  `mplsLspType` enum('unknown','dynamic','static','bypassOnly','p2mpLsp','p2mpAuto','mplsTp','meshP2p','oneHopP2p','srTe','meshP2pSrTe','oneHopP2pSrTe') NOT NULL,
  `mplsLspFastReroute` enum('true','false') NOT NULL,
  `mplsLspAge` bigint(20) DEFAULT NULL,
  `mplsLspTimeUp` bigint(20) DEFAULT NULL,
  `mplsLspTimeDown` bigint(20) DEFAULT NULL,
  `mplsLspPrimaryTimeUp` bigint(20) DEFAULT NULL,
  `mplsLspTransitions` int(10) unsigned DEFAULT NULL,
  `mplsLspLastTransition` bigint(20) DEFAULT NULL,
  `mplsLspConfiguredPaths` int(10) unsigned DEFAULT NULL,
  `mplsLspStandbyPaths` int(10) unsigned DEFAULT NULL,
  `mplsLspOperationalPaths` int(10) unsigned DEFAULT NULL,
  PRIMARY KEY (`lsp_id`),
  KEY `mpls_lsps_device_id_index` (`device_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `mpls_saps`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `mpls_saps` (
  `sap_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `svc_id` int(10) unsigned NOT NULL,
  `svc_oid` int(10) unsigned NOT NULL,
  `sapPortId` int(10) unsigned NOT NULL,
  `ifName` varchar(255) DEFAULT NULL,
  `device_id` int(10) unsigned NOT NULL,
  `sapEncapValue` varchar(255) DEFAULT NULL,
  `sapRowStatus` enum('active','notInService','notReady','createAndGo','createAndWait','destroy') DEFAULT NULL,
  `sapType` enum('unknown','epipe','tls','vprn','ies','mirror','apipe','fpipe','ipipe','cpipe','intTls','evpnIsaTls') DEFAULT NULL,
  `sapDescription` varchar(80) DEFAULT NULL,
  `sapAdminStatus` enum('up','down') DEFAULT NULL,
  `sapOperStatus` enum('up','down') DEFAULT NULL,
  `sapLastMgmtChange` bigint(20) DEFAULT NULL,
  `sapLastStatusChange` bigint(20) DEFAULT NULL,
  PRIMARY KEY (`sap_id`),
  KEY `mpls_saps_device_id_index` (`device_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `mpls_sdp_binds`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `mpls_sdp_binds` (
  `bind_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `sdp_id` int(10) unsigned NOT NULL,
  `svc_id` int(10) unsigned NOT NULL,
  `sdp_oid` int(10) unsigned NOT NULL,
  `svc_oid` int(10) unsigned NOT NULL,
  `device_id` int(10) unsigned NOT NULL,
  `sdpBindRowStatus` varchar(30) DEFAULT NULL,
  `sdpBindAdminStatus` varchar(5) DEFAULT NULL,
  `sdpBindOperStatus` varchar(5) DEFAULT NULL,
  `sdpBindLastMgmtChange` bigint(20) DEFAULT NULL,
  `sdpBindLastStatusChange` bigint(20) DEFAULT NULL,
  `sdpBindType` varchar(10) DEFAULT NULL,
  `sdpBindVcType` varchar(30) DEFAULT NULL,
  `sdpBindBaseStatsIngFwdPackets` bigint(20) DEFAULT NULL,
  `sdpBindBaseStatsIngFwdOctets` bigint(20) DEFAULT NULL,
  `sdpBindBaseStatsEgrFwdPackets` bigint(20) DEFAULT NULL,
  `sdpBindBaseStatsEgrFwdOctets` bigint(20) DEFAULT NULL,
  PRIMARY KEY (`bind_id`),
  KEY `mpls_sdp_binds_device_id_index` (`device_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `mpls_sdps`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `mpls_sdps` (
  `sdp_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `sdp_oid` int(10) unsigned NOT NULL,
  `device_id` int(10) unsigned NOT NULL,
  `sdpRowStatus` enum('active','notInService','notReady','createAndGo','createAndWait','destroy') DEFAULT NULL,
  `sdpDelivery` enum('gre','mpls','l2tpv3','greethbridged') DEFAULT NULL,
  `sdpDescription` varchar(80) DEFAULT NULL,
  `sdpAdminStatus` enum('up','down') DEFAULT NULL,
  `sdpOperStatus` enum('up','notAlive','notReady','invalidEgressInterface','transportTunnelDown','down') DEFAULT NULL,
  `sdpAdminPathMtu` int(11) DEFAULT NULL,
  `sdpOperPathMtu` int(11) DEFAULT NULL,
  `sdpLastMgmtChange` bigint(20) DEFAULT NULL,
  `sdpLastStatusChange` bigint(20) DEFAULT NULL,
  `sdpActiveLspType` enum('not-applicable','rsvp','ldp','bgp','none','mplsTp','srIsis','srOspf','srTeLsp','fpe') DEFAULT NULL,
  `sdpFarEndInetAddress` varchar(46) DEFAULT NULL,
  `sdpFarEndInetAddressType` enum('unknown','ipv4','ipv6','ipv4z','ipv6z','dns') DEFAULT NULL,
  PRIMARY KEY (`sdp_id`),
  KEY `mpls_sdps_device_id_index` (`device_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `mpls_services`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `mpls_services` (
  `svc_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `svc_oid` int(10) unsigned NOT NULL,
  `device_id` int(10) unsigned NOT NULL,
  `svcRowStatus` enum('active','notInService','notReady','createAndGo','createAndWait','destroy') DEFAULT NULL,
  `svcType` enum('unknown','epipe','tls','vprn','ies','mirror','apipe','fpipe','ipipe','cpipe','intTls','evpnIsaTls') DEFAULT NULL,
  `svcCustId` int(10) unsigned DEFAULT NULL,
  `svcAdminStatus` enum('up','down') DEFAULT NULL,
  `svcOperStatus` enum('up','down') DEFAULT NULL,
  `svcDescription` varchar(80) DEFAULT NULL,
  `svcMtu` int(11) DEFAULT NULL,
  `svcNumSaps` int(11) DEFAULT NULL,
  `svcNumSdps` int(11) DEFAULT NULL,
  `svcLastMgmtChange` bigint(20) DEFAULT NULL,
  `svcLastStatusChange` bigint(20) DEFAULT NULL,
  `svcVRouterId` int(11) DEFAULT NULL,
  `svcTlsMacLearning` enum('enabled','disabled') DEFAULT NULL,
  `svcTlsStpAdminStatus` enum('enabled','disabled') DEFAULT NULL,
  `svcTlsStpOperStatus` enum('up','down') DEFAULT NULL,
  `svcTlsFdbTableSize` int(11) DEFAULT NULL,
  `svcTlsFdbNumEntries` int(11) DEFAULT NULL,
  PRIMARY KEY (`svc_id`),
  KEY `mpls_services_device_id_index` (`device_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `mpls_tunnel_ar_hops`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `mpls_tunnel_ar_hops` (
  `ar_hop_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `mplsTunnelARHopListIndex` int(10) unsigned NOT NULL,
  `mplsTunnelARHopIndex` int(10) unsigned NOT NULL,
  `device_id` int(10) unsigned NOT NULL,
  `lsp_path_id` int(10) unsigned NOT NULL,
  `mplsTunnelARHopAddrType` enum('unknown','ipV4','ipV6','asNumber','lspid','unnum') DEFAULT NULL,
  `mplsTunnelARHopIpv4Addr` varchar(15) DEFAULT NULL,
  `mplsTunnelARHopIpv6Addr` varchar(45) DEFAULT NULL,
  `mplsTunnelARHopAsNumber` int(10) unsigned DEFAULT NULL,
  `mplsTunnelARHopStrictOrLoose` enum('strict','loose') DEFAULT NULL,
  `mplsTunnelARHopRouterId` varchar(15) DEFAULT NULL,
  `localProtected` enum('false','true') NOT NULL DEFAULT 'false',
  `linkProtectionInUse` enum('false','true') NOT NULL DEFAULT 'false',
  `bandwidthProtected` enum('false','true') NOT NULL DEFAULT 'false',
  `nextNodeProtected` enum('false','true') NOT NULL DEFAULT 'false',
  PRIMARY KEY (`ar_hop_id`),
  KEY `mpls_tunnel_ar_hops_device_id_index` (`device_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `mpls_tunnel_c_hops`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `mpls_tunnel_c_hops` (
  `c_hop_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `mplsTunnelCHopListIndex` int(10) unsigned NOT NULL,
  `mplsTunnelCHopIndex` int(10) unsigned NOT NULL,
  `device_id` int(10) unsigned NOT NULL,
  `lsp_path_id` int(10) unsigned DEFAULT NULL,
  `mplsTunnelCHopAddrType` enum('unknown','ipV4','ipV6','asNumber','lspid','unnum') DEFAULT NULL,
  `mplsTunnelCHopIpv4Addr` varchar(15) DEFAULT NULL,
  `mplsTunnelCHopIpv6Addr` varchar(45) DEFAULT NULL,
  `mplsTunnelCHopAsNumber` int(10) unsigned DEFAULT NULL,
  `mplsTunnelCHopStrictOrLoose` enum('strict','loose') DEFAULT NULL,
  `mplsTunnelCHopRouterId` varchar(15) DEFAULT NULL,
  PRIMARY KEY (`c_hop_id`),
  KEY `mpls_tunnel_c_hops_device_id_index` (`device_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `munin_plugins`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `munin_plugins` (
  `mplug_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `device_id` int(10) unsigned NOT NULL,
  `mplug_type` varchar(255) NOT NULL,
  `mplug_instance` varchar(128) DEFAULT NULL,
  `mplug_category` varchar(32) DEFAULT NULL,
  `mplug_title` varchar(128) DEFAULT NULL,
  `mplug_info` text DEFAULT NULL,
  `mplug_vlabel` varchar(128) DEFAULT NULL,
  `mplug_args` varchar(512) DEFAULT NULL,
  `mplug_total` tinyint(1) NOT NULL DEFAULT 0,
  `mplug_graph` tinyint(1) NOT NULL DEFAULT 1,
  PRIMARY KEY (`mplug_id`),
  UNIQUE KEY `munin_plugins_device_id_mplug_type_unique` (`device_id`,`mplug_type`),
  KEY `munin_plugins_device_id_index` (`device_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `munin_plugins_ds`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `munin_plugins_ds` (
  `mplug_id` int(10) unsigned NOT NULL,
  `ds_name` varchar(32) NOT NULL,
  `ds_type` enum('COUNTER','ABSOLUTE','DERIVE','GAUGE') NOT NULL DEFAULT 'GAUGE',
  `ds_label` varchar(64) NOT NULL,
  `ds_cdef` varchar(255) NOT NULL,
  `ds_draw` varchar(64) NOT NULL,
  `ds_graph` enum('no','yes') NOT NULL DEFAULT 'yes',
  `ds_info` varchar(255) NOT NULL,
  `ds_extinfo` text NOT NULL,
  `ds_max` varchar(32) NOT NULL,
  `ds_min` varchar(32) NOT NULL,
  `ds_negative` varchar(32) NOT NULL,
  `ds_warning` varchar(32) NOT NULL,
  `ds_critical` varchar(32) NOT NULL,
  `ds_colour` varchar(32) NOT NULL,
  `ds_sum` text NOT NULL,
  `ds_stack` text NOT NULL,
  `ds_line` varchar(64) NOT NULL,
  UNIQUE KEY `munin_plugins_ds_mplug_id_ds_name_unique` (`mplug_id`,`ds_name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `netscaler_vservers`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `netscaler_vservers` (
  `vsvr_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `device_id` int(10) unsigned NOT NULL,
  `vsvr_name` varchar(128) NOT NULL,
  `vsvr_ip` varchar(128) NOT NULL,
  `vsvr_port` int(11) NOT NULL,
  `vsvr_type` varchar(64) NOT NULL,
  `vsvr_state` varchar(32) NOT NULL,
  `vsvr_clients` int(11) NOT NULL,
  `vsvr_server` int(11) NOT NULL,
  `vsvr_req_rate` int(11) NOT NULL,
  `vsvr_bps_in` int(11) NOT NULL,
  `vsvr_bps_out` int(11) NOT NULL,
  PRIMARY KEY (`vsvr_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `notifications`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `notifications` (
  `notifications_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `title` varchar(255) NOT NULL DEFAULT '',
  `body` text NOT NULL,
  `severity` int(11) DEFAULT 0 COMMENT '0=ok,1=warning,2=critical',
  `source` varchar(255) NOT NULL DEFAULT '',
  `checksum` varchar(128) NOT NULL,
  `datetime` timestamp NOT NULL DEFAULT '1970-01-02 00:00:00',
  PRIMARY KEY (`notifications_id`),
  UNIQUE KEY `notifications_checksum_unique` (`checksum`),
  KEY `notifications_severity_index` (`severity`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `notifications_attribs`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `notifications_attribs` (
  `attrib_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `notifications_id` int(10) unsigned NOT NULL,
  `user_id` int(10) unsigned NOT NULL,
  `key` varchar(255) NOT NULL DEFAULT '',
  `value` varchar(255) NOT NULL DEFAULT '',
  PRIMARY KEY (`attrib_id`),
  KEY `notifications_attribs_notifications_id_user_id_index` (`notifications_id`,`user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `ospf_areas`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `ospf_areas` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `device_id` int(10) unsigned NOT NULL,
  `ospfAreaId` varchar(32) NOT NULL,
  `ospfAuthType` varchar(64) DEFAULT NULL,
  `ospfImportAsExtern` varchar(128) NOT NULL,
  `ospfSpfRuns` int(10) unsigned NOT NULL,
  `ospfAreaBdrRtrCount` int(10) unsigned NOT NULL,
  `ospfAsBdrRtrCount` int(10) unsigned NOT NULL,
  `ospfAreaLsaCount` int(10) unsigned NOT NULL,
  `ospfAreaLsaCksumSum` int(11) NOT NULL,
  `ospfAreaSummary` varchar(64) NOT NULL,
  `ospfAreaStatus` varchar(64) NOT NULL,
  `context_name` varchar(128) DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `ospf_areas_device_id_ospfareaid_context_name_unique` (`device_id`,`ospfAreaId`,`context_name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `ospf_instances`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `ospf_instances` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `device_id` int(10) unsigned NOT NULL,
  `ospf_instance_id` int(10) unsigned NOT NULL,
  `ospfRouterId` varchar(32) NOT NULL,
  `ospfAdminStat` varchar(32) NOT NULL,
  `ospfVersionNumber` varchar(32) NOT NULL,
  `ospfAreaBdrRtrStatus` varchar(32) NOT NULL,
  `ospfASBdrRtrStatus` varchar(32) NOT NULL,
  `ospfExternLsaCount` int(10) unsigned NOT NULL,
  `ospfExternLsaCksumSum` int(11) NOT NULL,
  `ospfTOSSupport` varchar(32) NOT NULL,
  `ospfOriginateNewLsas` int(10) unsigned NOT NULL,
  `ospfRxNewLsas` int(10) unsigned NOT NULL,
  `ospfExtLsdbLimit` int(11) DEFAULT NULL,
  `ospfMulticastExtensions` int(11) DEFAULT NULL,
  `ospfExitOverflowInterval` int(11) DEFAULT NULL,
  `ospfDemandExtensions` varchar(32) DEFAULT NULL,
  `context_name` varchar(128) DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `ospf_instances_device_id_ospf_instance_id_context_name_unique` (`device_id`,`ospf_instance_id`,`context_name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `ospf_nbrs`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `ospf_nbrs` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `device_id` int(10) unsigned NOT NULL,
  `port_id` int(10) unsigned DEFAULT NULL,
  `ospf_nbr_id` varchar(32) NOT NULL,
  `ospfNbrIpAddr` varchar(32) NOT NULL,
  `ospfNbrAddressLessIndex` int(11) NOT NULL,
  `ospfNbrRtrId` varchar(32) NOT NULL,
  `ospfNbrOptions` int(11) NOT NULL,
  `ospfNbrPriority` int(11) NOT NULL,
  `ospfNbrState` varchar(32) NOT NULL,
  `ospfNbrEvents` int(10) unsigned NOT NULL,
  `ospfNbrLsRetransQLen` int(10) unsigned NOT NULL,
  `ospfNbmaNbrStatus` varchar(32) NOT NULL,
  `ospfNbmaNbrPermanence` varchar(32) NOT NULL,
  `ospfNbrHelloSuppressed` varchar(32) NOT NULL,
  `context_name` varchar(128) DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `ospf_nbrs_device_id_ospf_nbr_id_context_name_unique` (`device_id`,`ospf_nbr_id`,`context_name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `ospf_ports`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `ospf_ports` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `device_id` int(10) unsigned NOT NULL,
  `port_id` int(10) unsigned NOT NULL,
  `ospf_port_id` varchar(32) NOT NULL,
  `ospfIfIpAddress` varchar(32) NOT NULL,
  `ospfAddressLessIf` int(11) NOT NULL,
  `ospfIfAreaId` varchar(32) NOT NULL,
  `ospfIfType` varchar(32) DEFAULT NULL,
  `ospfIfAdminStat` varchar(32) DEFAULT NULL,
  `ospfIfRtrPriority` int(11) DEFAULT NULL,
  `ospfIfTransitDelay` int(11) DEFAULT NULL,
  `ospfIfRetransInterval` int(11) DEFAULT NULL,
  `ospfIfHelloInterval` int(11) DEFAULT NULL,
  `ospfIfRtrDeadInterval` int(11) DEFAULT NULL,
  `ospfIfPollInterval` int(11) DEFAULT NULL,
  `ospfIfState` varchar(32) DEFAULT NULL,
  `ospfIfDesignatedRouter` varchar(32) DEFAULT NULL,
  `ospfIfBackupDesignatedRouter` varchar(32) DEFAULT NULL,
  `ospfIfEvents` int(10) unsigned DEFAULT NULL,
  `ospfIfAuthKey` varchar(128) DEFAULT NULL,
  `ospfIfStatus` varchar(32) DEFAULT NULL,
  `ospfIfMulticastForwarding` varchar(32) DEFAULT NULL,
  `ospfIfDemand` varchar(32) DEFAULT NULL,
  `ospfIfAuthType` varchar(32) DEFAULT NULL,
  `ospfIfMetricIpAddress` varchar(32) DEFAULT NULL,
  `ospfIfMetricAddressLessIf` int(11) DEFAULT NULL,
  `ospfIfMetricTOS` int(11) DEFAULT NULL,
  `ospfIfMetricValue` int(11) DEFAULT NULL,
  `ospfIfMetricStatus` varchar(32) DEFAULT NULL,
  `context_name` varchar(128) DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `ospf_ports_device_id_ospf_port_id_context_name_unique` (`device_id`,`ospf_port_id`,`context_name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `ospfv3_areas`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `ospfv3_areas` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `device_id` int(10) unsigned NOT NULL,
  `ospfv3_instance_id` int(10) unsigned NOT NULL,
  `ospfv3AreaId` int(10) unsigned NOT NULL,
  `ospfv3AreaImportAsExtern` varchar(32) NOT NULL,
  `ospfv3AreaSpfRuns` int(10) unsigned NOT NULL,
  `ospfv3AreaBdrRtrCount` int(10) unsigned NOT NULL,
  `ospfv3AreaAsBdrRtrCount` int(10) unsigned NOT NULL,
  `ospfv3AreaScopeLsaCount` int(10) unsigned NOT NULL,
  `ospfv3AreaScopeLsaCksumSum` int(10) unsigned NOT NULL,
  `ospfv3AreaSummary` varchar(32) NOT NULL,
  `ospfv3AreaStubMetric` int(10) unsigned NOT NULL,
  `ospfv3AreaStubMetricType` varchar(32) NOT NULL,
  `ospfv3AreaNssaTranslatorRole` varchar(32) NOT NULL,
  `ospfv3AreaNssaTranslatorState` varchar(32) NOT NULL,
  `ospfv3AreaNssaTranslatorStabInterval` int(10) unsigned NOT NULL,
  `ospfv3AreaNssaTranslatorEvents` int(10) unsigned NOT NULL,
  `ospfv3AreaTEEnabled` varchar(32) NOT NULL,
  `context_name` varchar(128) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `ospfv3_areas_device_id_ospfv3areaid_context_name_unique` (`device_id`,`ospfv3AreaId`,`context_name`),
  KEY `ospfv3AreaId` (`ospfv3_instance_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `ospfv3_instances`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `ospfv3_instances` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `device_id` int(10) unsigned NOT NULL,
  `router_id` varchar(32) NOT NULL,
  `ospfv3RouterId` int(10) unsigned NOT NULL,
  `ospfv3AdminStatus` varchar(32) NOT NULL,
  `ospfv3VersionNumber` varchar(32) NOT NULL,
  `ospfv3AreaBdrRtrStatus` varchar(32) NOT NULL,
  `ospfv3ASBdrRtrStatus` varchar(32) NOT NULL,
  `ospfv3OriginateNewLsas` int(10) unsigned NOT NULL,
  `ospfv3RxNewLsas` int(10) unsigned NOT NULL,
  `ospfv3ExtLsaCount` int(10) unsigned NOT NULL,
  `ospfv3ExtAreaLsdbLimit` int(11) NOT NULL,
  `ospfv3AsScopeLsaCount` int(10) unsigned NOT NULL,
  `ospfv3AsScopeLsaCksumSum` int(10) unsigned NOT NULL,
  `ospfv3ExitOverflowInterval` int(10) unsigned NOT NULL,
  `ospfv3ReferenceBandwidth` int(10) unsigned NOT NULL,
  `ospfv3RestartSupport` varchar(32) NOT NULL,
  `ospfv3RestartInterval` int(10) unsigned NOT NULL,
  `ospfv3RestartStrictLsaChecking` varchar(32) NOT NULL,
  `ospfv3RestartStatus` varchar(32) NOT NULL,
  `ospfv3RestartAge` int(10) unsigned NOT NULL,
  `ospfv3RestartExitReason` varchar(32) NOT NULL,
  `ospfv3StubRouterSupport` varchar(32) NOT NULL,
  `ospfv3StubRouterAdvertisement` varchar(32) NOT NULL,
  `ospfv3DiscontinuityTime` int(10) unsigned NOT NULL,
  `ospfv3RestartTime` int(10) unsigned NOT NULL,
  `context_name` varchar(128) DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `ospfv3_instances_device_id_context_name_unique` (`device_id`,`context_name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `ospfv3_nbrs`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `ospfv3_nbrs` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `device_id` int(10) unsigned NOT NULL,
  `ospfv3_instance_id` int(10) unsigned NOT NULL,
  `port_id` int(10) unsigned DEFAULT NULL,
  `router_id` varchar(32) NOT NULL,
  `ospfv3NbrIfIndex` int(10) unsigned NOT NULL,
  `ospfv3NbrIfInstId` int(10) unsigned NOT NULL,
  `ospfv3NbrRtrId` int(10) unsigned NOT NULL,
  `ospfv3NbrAddressType` varchar(32) NOT NULL,
  `ospfv3NbrAddress` varchar(39) NOT NULL,
  `ospfv3NbrOptions` int(11) NOT NULL,
  `ospfv3NbrPriority` int(11) NOT NULL,
  `ospfv3NbrState` varchar(32) NOT NULL,
  `ospfv3NbrEvents` int(10) unsigned NOT NULL,
  `ospfv3NbrLsRetransQLen` int(10) unsigned NOT NULL,
  `ospfv3NbrHelloSuppressed` varchar(32) NOT NULL,
  `ospfv3NbrIfId` int(10) unsigned NOT NULL,
  `ospfv3NbrRestartHelperStatus` varchar(32) NOT NULL,
  `ospfv3NbrRestartHelperAge` int(10) unsigned NOT NULL,
  `ospfv3NbrRestartHelperExitReason` varchar(32) NOT NULL,
  `context_name` varchar(128) DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `ospfv3_nbrs_device_id_index_context_name_unique` (`device_id`,`ospfv3NbrIfIndex`,`ospfv3NbrIfInstId`,`ospfv3NbrRtrId`,`context_name`),
  KEY `ospfv3_nbrs_ospfv3_instance_id_index` (`ospfv3_instance_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `ospfv3_ports`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `ospfv3_ports` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `device_id` int(10) unsigned NOT NULL,
  `ospfv3_instance_id` int(10) unsigned NOT NULL,
  `ospfv3_area_id` int(10) unsigned DEFAULT NULL,
  `port_id` int(10) unsigned DEFAULT NULL,
  `ospfv3IfIndex` int(10) unsigned NOT NULL,
  `ospfv3IfInstId` int(10) unsigned NOT NULL,
  `ospfv3IfAreaId` int(10) unsigned NOT NULL,
  `ospfv3IfType` varchar(32) NOT NULL,
  `ospfv3IfAdminStatus` varchar(32) NOT NULL,
  `ospfv3IfRtrPriority` int(10) unsigned NOT NULL,
  `ospfv3IfTransitDelay` int(10) unsigned NOT NULL,
  `ospfv3IfRetransInterval` int(10) unsigned NOT NULL,
  `ospfv3IfHelloInterval` int(10) unsigned NOT NULL,
  `ospfv3IfRtrDeadInterval` int(10) unsigned NOT NULL,
  `ospfv3IfPollInterval` int(10) unsigned NOT NULL,
  `ospfv3IfState` varchar(32) NOT NULL,
  `ospfv3IfDesignatedRouter` varchar(32) NOT NULL,
  `ospfv3IfBackupDesignatedRouter` varchar(32) NOT NULL,
  `ospfv3IfEvents` int(10) unsigned NOT NULL,
  `ospfv3IfDemand` varchar(32) NOT NULL,
  `ospfv3IfMetricValue` int(10) unsigned NOT NULL,
  `ospfv3IfLinkScopeLsaCount` int(10) unsigned DEFAULT NULL,
  `ospfv3IfLinkLsaCksumSum` int(10) unsigned DEFAULT NULL,
  `ospfv3IfDemandNbrProbe` varchar(32) DEFAULT NULL,
  `ospfv3IfDemandNbrProbeRetransLimit` int(10) unsigned DEFAULT NULL,
  `ospfv3IfDemandNbrProbeInterval` int(10) unsigned DEFAULT NULL,
  `ospfv3IfTEDisabled` varchar(32) DEFAULT NULL,
  `ospfv3IfLinkLSASuppression` varchar(32) DEFAULT NULL,
  `context_name` varchar(128) DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `ospfv3_ports_device_id_index_context_name_unique` (`device_id`,`ospfv3IfIndex`,`ospfv3IfInstId`,`context_name`),
  KEY `ospfv3_area_id` (`ospfv3_instance_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `packages`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `packages` (
  `pkg_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `device_id` int(10) unsigned NOT NULL,
  `name` varchar(128) NOT NULL,
  `manager` varchar(16) NOT NULL DEFAULT '1',
  `status` tinyint(1) NOT NULL,
  `version` varchar(255) NOT NULL,
  `build` varchar(64) NOT NULL,
  `arch` varchar(16) NOT NULL,
  `size` bigint(20) DEFAULT NULL,
  PRIMARY KEY (`pkg_id`),
  UNIQUE KEY `packages_device_id_name_manager_arch_version_build_unique` (`device_id`,`name`,`manager`,`arch`,`version`,`build`),
  KEY `packages_device_id_index` (`device_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `pdb_ix`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `pdb_ix` (
  `pdb_ix_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `ix_id` int(10) unsigned NOT NULL,
  `name` varchar(255) NOT NULL,
  `asn` int(10) unsigned NOT NULL,
  `timestamp` int(10) unsigned NOT NULL,
  PRIMARY KEY (`pdb_ix_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `pdb_ix_peers`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `pdb_ix_peers` (
  `pdb_ix_peers_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `ix_id` int(10) unsigned NOT NULL,
  `peer_id` int(10) unsigned NOT NULL,
  `remote_asn` int(10) unsigned NOT NULL,
  `remote_ipaddr4` varchar(15) DEFAULT NULL,
  `remote_ipaddr6` varchar(128) DEFAULT NULL,
  `name` varchar(255) DEFAULT NULL,
  `timestamp` int(10) unsigned DEFAULT NULL,
  PRIMARY KEY (`pdb_ix_peers_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `permissions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `permissions` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `guard_name` varchar(255) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `permissions_name_guard_name_unique` (`name`,`guard_name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `plugins`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `plugins` (
  `plugin_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `plugin_name` varchar(60) NOT NULL,
  `plugin_active` int(11) NOT NULL,
  `version` int(11) NOT NULL DEFAULT 1,
  `settings` longtext DEFAULT NULL,
  PRIMARY KEY (`plugin_id`),
  UNIQUE KEY `plugins_version_plugin_name_unique` (`version`,`plugin_name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `poller_cluster`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `poller_cluster` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `node_id` varchar(255) NOT NULL,
  `poller_name` varchar(255) NOT NULL,
  `poller_version` varchar(255) NOT NULL DEFAULT '',
  `poller_groups` varchar(255) NOT NULL DEFAULT '',
  `last_report` datetime NOT NULL,
  `master` tinyint(1) NOT NULL,
  `poller_enabled` tinyint(1) DEFAULT NULL,
  `poller_frequency` int(11) DEFAULT NULL,
  `poller_workers` int(11) DEFAULT NULL,
  `poller_down_retry` int(11) DEFAULT NULL,
  `discovery_enabled` tinyint(1) DEFAULT NULL,
  `discovery_frequency` int(11) DEFAULT NULL,
  `discovery_workers` int(11) DEFAULT NULL,
  `services_enabled` tinyint(1) DEFAULT NULL,
  `services_frequency` int(11) DEFAULT NULL,
  `services_workers` int(11) DEFAULT NULL,
  `billing_enabled` tinyint(1) DEFAULT NULL,
  `billing_frequency` int(11) DEFAULT NULL,
  `billing_calculate_frequency` int(11) DEFAULT NULL,
  `alerting_enabled` tinyint(1) DEFAULT NULL,
  `alerting_frequency` int(11) DEFAULT NULL,
  `ping_enabled` tinyint(1) DEFAULT NULL,
  `ping_frequency` int(11) DEFAULT NULL,
  `update_enabled` tinyint(1) DEFAULT NULL,
  `update_frequency` int(11) DEFAULT NULL,
  `loglevel` varchar(8) DEFAULT NULL,
  `watchdog_enabled` tinyint(1) DEFAULT NULL,
  `watchdog_log` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `poller_cluster_node_id_unique` (`node_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `poller_cluster_stats`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `poller_cluster_stats` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `parent_poller` int(10) unsigned NOT NULL DEFAULT 0,
  `poller_type` varchar(64) NOT NULL DEFAULT '',
  `depth` int(10) unsigned NOT NULL,
  `devices` int(10) unsigned NOT NULL,
  `worker_seconds` double NOT NULL,
  `workers` int(10) unsigned NOT NULL,
  `frequency` int(10) unsigned NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `poller_cluster_stats_parent_poller_poller_type_unique` (`parent_poller`,`poller_type`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `poller_groups`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `poller_groups` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `group_name` varchar(255) NOT NULL,
  `descr` varchar(255) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `pollers`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `pollers` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `poller_name` varchar(255) NOT NULL,
  `last_polled` datetime NOT NULL,
  `devices` int(10) unsigned NOT NULL,
  `time_taken` double NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `pollers_poller_name_unique` (`poller_name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `port_group_port`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `port_group_port` (
  `port_group_id` int(10) unsigned NOT NULL,
  `port_id` int(10) unsigned NOT NULL,
  PRIMARY KEY (`port_group_id`,`port_id`),
  KEY `port_group_port_port_group_id_index` (`port_group_id`),
  KEY `port_group_port_port_id_index` (`port_id`),
  CONSTRAINT `port_group_port_port_group_id_foreign` FOREIGN KEY (`port_group_id`) REFERENCES `port_groups` (`id`) ON DELETE CASCADE,
  CONSTRAINT `port_group_port_port_id_foreign` FOREIGN KEY (`port_id`) REFERENCES `ports` (`port_id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `port_groups`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `port_groups` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `desc` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `port_groups_name_unique` (`name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `ports`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `ports` (
  `port_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `device_id` int(10) unsigned NOT NULL DEFAULT 0,
  `port_descr_type` varchar(255) DEFAULT NULL,
  `port_descr_descr` varchar(255) DEFAULT NULL,
  `port_descr_circuit` varchar(255) DEFAULT NULL,
  `port_descr_speed` varchar(32) DEFAULT NULL,
  `port_descr_notes` varchar(255) DEFAULT NULL,
  `ifDescr` varchar(255) DEFAULT NULL,
  `ifName` varchar(255) DEFAULT NULL,
  `portName` varchar(128) DEFAULT NULL,
  `ifIndex` bigint(20) DEFAULT 0,
  `ifSpeed` bigint(20) DEFAULT NULL,
  `ifSpeed_prev` bigint(20) DEFAULT NULL,
  `ifConnectorPresent` varchar(12) DEFAULT NULL,
  `ifOperStatus` varchar(16) DEFAULT NULL,
  `ifOperStatus_prev` varchar(16) DEFAULT NULL,
  `ifAdminStatus` varchar(16) DEFAULT NULL,
  `ifAdminStatus_prev` varchar(16) DEFAULT NULL,
  `ifDuplex` varchar(12) DEFAULT NULL,
  `ifMtu` int(11) DEFAULT NULL,
  `ifType` varchar(64) DEFAULT NULL,
  `ifAlias` varchar(255) DEFAULT NULL,
  `ifPhysAddress` varchar(64) DEFAULT NULL,
  `ifLastChange` bigint(20) unsigned NOT NULL DEFAULT 0,
  `ifVlan` varchar(8) DEFAULT NULL,
  `ifTrunk` varchar(16) DEFAULT NULL,
  `ifVrf` int(11) NOT NULL DEFAULT 0,
  `ignore` tinyint(1) NOT NULL DEFAULT 0,
  `disabled` tinyint(1) NOT NULL DEFAULT 0,
  `deleted` tinyint(1) NOT NULL DEFAULT 0,
  `pagpOperationMode` varchar(32) DEFAULT NULL,
  `pagpPortState` varchar(16) DEFAULT NULL,
  `pagpPartnerDeviceId` varchar(48) DEFAULT NULL,
  `pagpPartnerLearnMethod` varchar(16) DEFAULT NULL,
  `pagpPartnerIfIndex` int(11) DEFAULT NULL,
  `pagpPartnerGroupIfIndex` int(11) DEFAULT NULL,
  `pagpPartnerDeviceName` varchar(128) DEFAULT NULL,
  `pagpEthcOperationMode` varchar(16) DEFAULT NULL,
  `pagpDeviceId` varchar(48) DEFAULT NULL,
  `pagpGroupIfIndex` int(11) DEFAULT NULL,
  `ifInUcastPkts` bigint(20) unsigned DEFAULT NULL,
  `ifInUcastPkts_prev` bigint(20) unsigned DEFAULT NULL,
  `ifInUcastPkts_delta` bigint(20) unsigned DEFAULT NULL,
  `ifInUcastPkts_rate` bigint(20) unsigned DEFAULT NULL,
  `ifOutUcastPkts` bigint(20) unsigned DEFAULT NULL,
  `ifOutUcastPkts_prev` bigint(20) unsigned DEFAULT NULL,
  `ifOutUcastPkts_delta` bigint(20) unsigned DEFAULT NULL,
  `ifOutUcastPkts_rate` bigint(20) unsigned DEFAULT NULL,
  `ifInErrors` bigint(20) unsigned DEFAULT NULL,
  `ifInErrors_prev` bigint(20) unsigned DEFAULT NULL,
  `ifInErrors_delta` bigint(20) unsigned DEFAULT NULL,
  `ifInErrors_rate` bigint(20) unsigned DEFAULT NULL,
  `ifOutErrors` bigint(20) unsigned DEFAULT NULL,
  `ifOutErrors_prev` bigint(20) unsigned DEFAULT NULL,
  `ifOutErrors_delta` bigint(20) unsigned DEFAULT NULL,
  `ifOutErrors_rate` bigint(20) unsigned DEFAULT NULL,
  `ifInOctets` bigint(20) unsigned DEFAULT NULL,
  `ifInOctets_prev` bigint(20) unsigned DEFAULT NULL,
  `ifInOctets_delta` bigint(20) unsigned DEFAULT NULL,
  `ifInOctets_rate` bigint(20) unsigned DEFAULT NULL,
  `ifOutOctets` bigint(20) unsigned DEFAULT NULL,
  `ifOutOctets_prev` bigint(20) unsigned DEFAULT NULL,
  `ifOutOctets_delta` bigint(20) unsigned DEFAULT NULL,
  `ifOutOctets_rate` bigint(20) unsigned DEFAULT NULL,
  `poll_time` int(10) unsigned DEFAULT NULL,
  `poll_prev` int(10) unsigned DEFAULT NULL,
  `poll_period` int(10) unsigned DEFAULT NULL,
  PRIMARY KEY (`port_id`),
  KEY `ports_ifalias_port_descr_descr_portname_index` (`ifAlias`,`port_descr_descr`,`portName`),
  KEY `ports_ifdescr_ifname_index` (`ifDescr`,`ifName`),
  KEY `ports_device_id_ifindex_index` (`device_id`,`ifIndex`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `ports_adsl`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `ports_adsl` (
  `port_id` int(10) unsigned NOT NULL,
  `port_adsl_updated` timestamp NOT NULL DEFAULT current_timestamp(),
  `adslLineCoding` varchar(8) NOT NULL DEFAULT '',
  `adslLineType` varchar(16) NOT NULL DEFAULT '',
  `adslAtucInvVendorID` varchar(16) NOT NULL DEFAULT '',
  `adslAtucInvVersionNumber` varchar(16) NOT NULL DEFAULT '',
  `adslAtucCurrSnrMgn` decimal(5,1) NOT NULL DEFAULT 0.0,
  `adslAtucCurrAtn` decimal(5,1) NOT NULL DEFAULT 0.0,
  `adslAtucCurrOutputPwr` decimal(5,1) NOT NULL DEFAULT 0.0,
  `adslAtucCurrAttainableRate` int(11) NOT NULL DEFAULT 0,
  `adslAtucChanCurrTxRate` int(11) NOT NULL DEFAULT 0,
  `adslAturInvSerialNumber` varchar(32) NOT NULL DEFAULT '',
  `adslAturInvVendorID` varchar(16) NOT NULL DEFAULT '',
  `adslAturInvVersionNumber` varchar(16) NOT NULL DEFAULT '',
  `adslAturChanCurrTxRate` int(11) NOT NULL DEFAULT 0,
  `adslAturCurrSnrMgn` decimal(5,1) NOT NULL DEFAULT 0.0,
  `adslAturCurrAtn` decimal(5,1) NOT NULL DEFAULT 0.0,
  `adslAturCurrOutputPwr` decimal(5,1) NOT NULL DEFAULT 0.0,
  `adslAturCurrAttainableRate` int(11) NOT NULL DEFAULT 0,
  UNIQUE KEY `ports_adsl_port_id_unique` (`port_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `ports_fdb`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `ports_fdb` (
  `ports_fdb_id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `port_id` int(10) unsigned NOT NULL,
  `mac_address` varchar(32) NOT NULL,
  `vlan_id` int(10) unsigned NOT NULL,
  `device_id` int(10) unsigned NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`ports_fdb_id`),
  KEY `ports_fdb_port_id_index` (`port_id`),
  KEY `ports_fdb_mac_address_index` (`mac_address`),
  KEY `ports_fdb_vlan_id_index` (`vlan_id`),
  KEY `ports_fdb_device_id_index` (`device_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `ports_nac`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `ports_nac` (
  `ports_nac_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `auth_id` varchar(50) NOT NULL,
  `device_id` int(10) unsigned NOT NULL,
  `port_id` int(10) unsigned NOT NULL,
  `domain` varchar(50) NOT NULL,
  `username` varchar(50) NOT NULL,
  `mac_address` varchar(50) NOT NULL,
  `ip_address` varchar(50) NOT NULL,
  `host_mode` varchar(50) NOT NULL,
  `authz_status` varchar(50) NOT NULL,
  `authz_by` varchar(50) NOT NULL,
  `authc_status` varchar(50) NOT NULL,
  `method` varchar(50) NOT NULL,
  `timeout` varchar(50) NOT NULL,
  `time_left` varchar(50) DEFAULT NULL,
  `vlan` int(10) unsigned DEFAULT NULL,
  `time_elapsed` varchar(50) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `historical` tinyint(1) NOT NULL DEFAULT 0,
  PRIMARY KEY (`ports_nac_id`),
  KEY `ports_nac_port_id_mac_address_index` (`port_id`,`mac_address`),
  KEY `ports_nac_device_id_index` (`device_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `ports_perms`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `ports_perms` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `user_id` int(10) unsigned NOT NULL,
  `port_id` int(10) unsigned NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `ports_stack`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `ports_stack` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `device_id` int(10) unsigned NOT NULL,
  `high_ifIndex` int(10) unsigned NOT NULL,
  `high_port_id` bigint(20) unsigned DEFAULT NULL,
  `low_ifIndex` int(10) unsigned NOT NULL,
  `low_port_id` bigint(20) unsigned DEFAULT NULL,
  `ifStackStatus` varchar(32) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `ports_stack_device_id_port_id_high_port_id_low_unique` (`device_id`,`high_ifIndex`,`low_ifIndex`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `ports_statistics`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `ports_statistics` (
  `port_id` int(10) unsigned NOT NULL,
  `ifInNUcastPkts` bigint(20) unsigned DEFAULT NULL,
  `ifInNUcastPkts_prev` bigint(20) unsigned DEFAULT NULL,
  `ifInNUcastPkts_delta` bigint(20) DEFAULT NULL,
  `ifInNUcastPkts_rate` bigint(20) DEFAULT NULL,
  `ifOutNUcastPkts` bigint(20) unsigned DEFAULT NULL,
  `ifOutNUcastPkts_prev` bigint(20) unsigned DEFAULT NULL,
  `ifOutNUcastPkts_delta` bigint(20) DEFAULT NULL,
  `ifOutNUcastPkts_rate` bigint(20) DEFAULT NULL,
  `ifInDiscards` bigint(20) unsigned DEFAULT NULL,
  `ifInDiscards_prev` bigint(20) unsigned DEFAULT NULL,
  `ifInDiscards_delta` bigint(20) DEFAULT NULL,
  `ifInDiscards_rate` bigint(20) DEFAULT NULL,
  `ifOutDiscards` bigint(20) unsigned DEFAULT NULL,
  `ifOutDiscards_prev` bigint(20) unsigned DEFAULT NULL,
  `ifOutDiscards_delta` bigint(20) DEFAULT NULL,
  `ifOutDiscards_rate` bigint(20) DEFAULT NULL,
  `ifInUnknownProtos` bigint(20) unsigned DEFAULT NULL,
  `ifInUnknownProtos_prev` bigint(20) unsigned DEFAULT NULL,
  `ifInUnknownProtos_delta` bigint(20) DEFAULT NULL,
  `ifInUnknownProtos_rate` bigint(20) DEFAULT NULL,
  `ifInBroadcastPkts` bigint(20) unsigned DEFAULT NULL,
  `ifInBroadcastPkts_prev` bigint(20) unsigned DEFAULT NULL,
  `ifInBroadcastPkts_delta` bigint(20) DEFAULT NULL,
  `ifInBroadcastPkts_rate` bigint(20) DEFAULT NULL,
  `ifOutBroadcastPkts` bigint(20) unsigned DEFAULT NULL,
  `ifOutBroadcastPkts_prev` bigint(20) unsigned DEFAULT NULL,
  `ifOutBroadcastPkts_delta` bigint(20) DEFAULT NULL,
  `ifOutBroadcastPkts_rate` bigint(20) DEFAULT NULL,
  `ifInMulticastPkts` bigint(20) unsigned DEFAULT NULL,
  `ifInMulticastPkts_prev` bigint(20) unsigned DEFAULT NULL,
  `ifInMulticastPkts_delta` bigint(20) DEFAULT NULL,
  `ifInMulticastPkts_rate` bigint(20) DEFAULT NULL,
  `ifOutMulticastPkts` bigint(20) unsigned DEFAULT NULL,
  `ifOutMulticastPkts_prev` bigint(20) unsigned DEFAULT NULL,
  `ifOutMulticastPkts_delta` bigint(20) DEFAULT NULL,
  `ifOutMulticastPkts_rate` bigint(20) DEFAULT NULL,
  PRIMARY KEY (`port_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `ports_stp`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `ports_stp` (
  `port_stp_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `device_id` int(10) unsigned NOT NULL,
  `vlan` int(10) unsigned DEFAULT NULL,
  `port_id` int(10) unsigned NOT NULL,
  `port_index` int(10) unsigned NOT NULL DEFAULT 0,
  `priority` tinyint(3) unsigned NOT NULL,
  `state` varchar(11) NOT NULL,
  `enable` varchar(8) NOT NULL,
  `pathCost` int(10) unsigned NOT NULL,
  `designatedRoot` varchar(32) NOT NULL,
  `designatedCost` int(10) unsigned NOT NULL,
  `designatedBridge` varchar(32) NOT NULL,
  `designatedPort` mediumint(9) NOT NULL,
  `forwardTransitions` int(10) unsigned NOT NULL,
  PRIMARY KEY (`port_stp_id`),
  UNIQUE KEY `ports_stp_device_id_vlan_port_index_unique` (`device_id`,`vlan`,`port_index`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `ports_vdsl`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `ports_vdsl` (
  `port_id` int(10) unsigned NOT NULL,
  `port_vdsl_updated` timestamp NOT NULL DEFAULT current_timestamp(),
  `xdsl2LineStatusAttainableRateDs` int(11) NOT NULL DEFAULT 0,
  `xdsl2LineStatusAttainableRateUs` int(11) NOT NULL DEFAULT 0,
  `xdsl2ChStatusActDataRateXtur` int(11) NOT NULL DEFAULT 0,
  `xdsl2ChStatusActDataRateXtuc` int(11) NOT NULL DEFAULT 0,
  `xdsl2LineStatusActAtpDs` decimal(8,2) NOT NULL DEFAULT 0.00,
  `xdsl2LineStatusActAtpUs` decimal(8,2) NOT NULL DEFAULT 0.00,
  UNIQUE KEY `ports_vdsl_port_id_unique` (`port_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `ports_vlans`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `ports_vlans` (
  `port_vlan_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `device_id` int(10) unsigned NOT NULL,
  `port_id` int(10) unsigned NOT NULL,
  `vlan` int(11) NOT NULL,
  `baseport` int(11) NOT NULL,
  `priority` bigint(20) NOT NULL,
  `state` varchar(16) NOT NULL,
  `cost` int(11) NOT NULL,
  `untagged` tinyint(1) NOT NULL DEFAULT 0,
  PRIMARY KEY (`port_vlan_id`),
  UNIQUE KEY `ports_vlans_device_id_port_id_vlan_unique` (`device_id`,`port_id`,`vlan`),
  KEY `ports_vlans_port_id_index` (`port_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `printer_supplies`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `printer_supplies` (
  `supply_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `device_id` int(10) unsigned NOT NULL DEFAULT 0,
  `supply_index` int(11) NOT NULL,
  `supply_type` varchar(64) NOT NULL,
  `supply_oid` varchar(64) NOT NULL,
  `supply_descr` varchar(255) NOT NULL DEFAULT '',
  `supply_capacity` int(11) NOT NULL DEFAULT 0,
  `supply_current` int(11) NOT NULL DEFAULT 0,
  `supply_capacity_oid` varchar(64) DEFAULT NULL,
  PRIMARY KEY (`supply_id`),
  KEY `toner_device_id_index` (`device_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `processes`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `processes` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `device_id` int(10) unsigned NOT NULL,
  `pid` int(11) NOT NULL,
  `vsz` int(11) NOT NULL,
  `rss` int(11) NOT NULL,
  `cputime` varchar(14) NOT NULL,
  `user` varchar(50) NOT NULL,
  `command` text NOT NULL,
  PRIMARY KEY (`id`),
  KEY `processes_device_id_index` (`device_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `processors`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `processors` (
  `processor_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `entPhysicalIndex` int(11) NOT NULL DEFAULT 0,
  `hrDeviceIndex` int(11) DEFAULT NULL,
  `device_id` int(10) unsigned NOT NULL,
  `processor_oid` varchar(128) NOT NULL,
  `processor_index` varchar(32) NOT NULL,
  `processor_type` varchar(16) NOT NULL,
  `processor_usage` int(11) NOT NULL,
  `processor_descr` varchar(64) NOT NULL,
  `processor_precision` int(11) NOT NULL DEFAULT 1,
  `processor_perc_warn` int(11) DEFAULT 75,
  PRIMARY KEY (`processor_id`),
  KEY `processors_device_id_index` (`device_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `proxmox`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `proxmox` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `device_id` int(10) unsigned NOT NULL DEFAULT 0,
  `vmid` int(11) NOT NULL,
  `cluster` varchar(255) NOT NULL,
  `description` varchar(255) DEFAULT NULL,
  `last_seen` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `proxmox_cluster_vmid_unique` (`cluster`,`vmid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `proxmox_ports`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `proxmox_ports` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `vm_id` int(11) NOT NULL,
  `port` varchar(10) NOT NULL,
  `last_seen` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `proxmox_ports_vm_id_port_unique` (`vm_id`,`port`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `pseudowires`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `pseudowires` (
  `pseudowire_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `device_id` int(10) unsigned NOT NULL,
  `port_id` int(10) unsigned NOT NULL,
  `peer_device_id` int(10) unsigned NOT NULL,
  `peer_ldp_id` int(11) NOT NULL,
  `cpwVcID` int(10) unsigned NOT NULL,
  `cpwOid` int(11) NOT NULL,
  `pw_type` varchar(32) NOT NULL,
  `pw_psntype` varchar(32) NOT NULL,
  `pw_local_mtu` int(11) NOT NULL,
  `pw_peer_mtu` int(11) NOT NULL,
  `pw_descr` varchar(128) NOT NULL,
  PRIMARY KEY (`pseudowire_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `push_subscriptions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `push_subscriptions` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `subscribable_type` varchar(255) NOT NULL,
  `subscribable_id` bigint(20) unsigned NOT NULL,
  `endpoint` varchar(500) NOT NULL,
  `public_key` varchar(255) DEFAULT NULL,
  `auth_token` varchar(255) DEFAULT NULL,
  `content_encoding` varchar(255) DEFAULT NULL,
  `description` varchar(255) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `push_subscriptions_endpoint_unique` (`endpoint`),
  KEY `push_subscriptions_subscribable_type_subscribable_id_index` (`subscribable_type`,`subscribable_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `qos`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `qos` (
  `qos_id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `device_id` int(10) unsigned NOT NULL,
  `port_id` int(10) unsigned DEFAULT NULL,
  `parent_id` bigint(20) unsigned DEFAULT NULL,
  `type` varchar(50) NOT NULL COMMENT 'Type of QoS',
  `title` varchar(255) NOT NULL COMMENT 'Graph Title',
  `tooltip` longtext DEFAULT NULL,
  `snmp_idx` varchar(255) NOT NULL COMMENT 'SNMP Index for polling QoS data',
  `rrd_id` varchar(255) NOT NULL COMMENT 'Suffix for the RRD file to identify this QoS',
  `ingress` tinyint(1) NOT NULL DEFAULT 0 COMMENT 'Does this process ingress bytes',
  `egress` tinyint(1) NOT NULL DEFAULT 0 COMMENT 'Does this process egress bytes',
  `disabled` tinyint(1) NOT NULL DEFAULT 0 COMMENT 'Should this QoS be polled',
  `ignore` tinyint(1) NOT NULL DEFAULT 0 COMMENT 'Should this QoS be alerted on',
  `last_polled` bigint(20) DEFAULT NULL COMMENT 'Last polled time for calculating rate',
  `max_in` bigint(20) DEFAULT NULL COMMENT 'Maximum value for input data if defined',
  `max_out` bigint(20) DEFAULT NULL COMMENT 'Maximum value for output data if defined',
  `last_bytes_in` bigint(20) DEFAULT NULL COMMENT 'Last polled counter for input bytes',
  `last_bytes_out` bigint(20) DEFAULT NULL COMMENT 'Last polled counter for output bytes',
  `bytes_in_rate` bigint(20) DEFAULT NULL COMMENT 'Output rate for bytes',
  `bytes_out_rate` bigint(20) DEFAULT NULL COMMENT 'Input rate for bytes',
  `last_bytes_drop_in` bigint(20) DEFAULT NULL COMMENT 'Last polled counter for input dropped bytes',
  `last_bytes_drop_out` bigint(20) DEFAULT NULL COMMENT 'Last polled counter for output dropped bytes',
  `bytes_drop_in_rate` bigint(20) DEFAULT NULL COMMENT 'Output rate for dropped bytes',
  `bytes_drop_out_rate` bigint(20) DEFAULT NULL COMMENT 'Input rate for dropped bytes',
  `last_packets_in` bigint(20) DEFAULT NULL COMMENT 'Last polled counter for input packets',
  `last_packets_out` bigint(20) DEFAULT NULL COMMENT 'Last polled counter for output packets',
  `packets_in_rate` bigint(20) DEFAULT NULL COMMENT 'Output rate for packets',
  `packets_out_rate` bigint(20) DEFAULT NULL COMMENT 'Input rate for packets',
  `last_packets_drop_in` bigint(20) DEFAULT NULL COMMENT 'Last polled counter for input dropped packets',
  `last_packets_drop_out` bigint(20) DEFAULT NULL COMMENT 'Last polled counter for output dropped packets',
  `packets_drop_in_rate` bigint(20) DEFAULT NULL COMMENT 'Output rate for dropped packets',
  `packets_drop_out_rate` bigint(20) DEFAULT NULL COMMENT 'Input rate for dropped packets',
  `bytes_drop_in_pct` decimal(6,2) DEFAULT NULL COMMENT 'Percentage of input bytes dropped',
  `bytes_drop_out_pct` decimal(6,2) DEFAULT NULL COMMENT 'Percentage of output bytes dropped',
  `packets_drop_in_pct` decimal(6,2) DEFAULT NULL COMMENT 'Percentage of input packets dropped',
  `packets_drop_out_pct` decimal(6,2) DEFAULT NULL COMMENT 'Percentage of output packets dropped',
  PRIMARY KEY (`qos_id`),
  KEY `qos_device_id_index` (`device_id`),
  KEY `qos_port_id_index` (`port_id`),
  KEY `qos_parent_id_index` (`parent_id`),
  KEY `qos_type_index` (`type`),
  CONSTRAINT `qos_device_id_foreign` FOREIGN KEY (`device_id`) REFERENCES `devices` (`device_id`) ON DELETE CASCADE,
  CONSTRAINT `qos_parent_id_foreign` FOREIGN KEY (`parent_id`) REFERENCES `qos` (`qos_id`) ON DELETE SET NULL,
  CONSTRAINT `qos_port_id_foreign` FOREIGN KEY (`port_id`) REFERENCES `ports` (`port_id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `role_has_permissions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `role_has_permissions` (
  `permission_id` bigint(20) unsigned NOT NULL,
  `role_id` bigint(20) unsigned NOT NULL,
  PRIMARY KEY (`permission_id`,`role_id`),
  KEY `role_has_permissions_role_id_foreign` (`role_id`),
  CONSTRAINT `role_has_permissions_permission_id_foreign` FOREIGN KEY (`permission_id`) REFERENCES `permissions` (`id`) ON DELETE CASCADE,
  CONSTRAINT `role_has_permissions_role_id_foreign` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `roles`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `roles` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `guard_name` varchar(255) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `roles_name_guard_name_unique` (`name`,`guard_name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `route`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `route` (
  `route_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `device_id` int(10) unsigned NOT NULL,
  `port_id` int(10) unsigned NOT NULL,
  `context_name` varchar(255) DEFAULT NULL,
  `inetCidrRouteIfIndex` bigint(20) NOT NULL,
  `inetCidrRouteType` int(10) unsigned NOT NULL,
  `inetCidrRouteProto` int(10) unsigned NOT NULL,
  `inetCidrRouteNextHopAS` int(10) unsigned NOT NULL,
  `inetCidrRouteMetric1` int(10) unsigned NOT NULL,
  `inetCidrRouteDestType` varchar(255) NOT NULL,
  `inetCidrRouteDest` varchar(255) NOT NULL,
  `inetCidrRouteNextHopType` varchar(255) NOT NULL,
  `inetCidrRouteNextHop` varchar(255) NOT NULL,
  `inetCidrRoutePolicy` varchar(255) NOT NULL,
  `inetCidrRoutePfxLen` int(10) unsigned NOT NULL,
  PRIMARY KEY (`route_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `sensors`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `sensors` (
  `sensor_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `sensor_deleted` tinyint(1) NOT NULL DEFAULT 0,
  `sensor_class` varchar(64) NOT NULL,
  `device_id` int(10) unsigned NOT NULL DEFAULT 0,
  `poller_type` varchar(16) NOT NULL DEFAULT 'snmp',
  `sensor_oid` varchar(255) NOT NULL,
  `sensor_index` varchar(128) DEFAULT NULL,
  `sensor_type` varchar(255) NOT NULL,
  `sensor_descr` varchar(255) DEFAULT NULL,
  `group` varchar(255) DEFAULT NULL,
  `sensor_divisor` bigint(20) NOT NULL DEFAULT 1,
  `sensor_multiplier` int(11) NOT NULL DEFAULT 1,
  `sensor_current` double DEFAULT NULL,
  `sensor_limit` double DEFAULT NULL,
  `sensor_limit_warn` double DEFAULT NULL,
  `sensor_limit_low` double DEFAULT NULL,
  `sensor_limit_low_warn` double DEFAULT NULL,
  `sensor_alert` tinyint(1) NOT NULL DEFAULT 1,
  `sensor_custom` enum('No','Yes') NOT NULL DEFAULT 'No',
  `entPhysicalIndex` varchar(16) DEFAULT NULL,
  `entPhysicalIndex_measured` varchar(16) DEFAULT NULL,
  `lastupdate` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `sensor_prev` double DEFAULT NULL,
  `user_func` varchar(100) DEFAULT NULL,
  `rrd_type` enum('GAUGE','COUNTER','DERIVE','DCOUNTER','DDERIVE','ABSOLUTE') NOT NULL DEFAULT 'GAUGE',
  PRIMARY KEY (`sensor_id`),
  KEY `sensors_sensor_class_index` (`sensor_class`),
  KEY `sensors_device_id_index` (`device_id`),
  KEY `sensors_sensor_type_index` (`sensor_type`),
  CONSTRAINT `sensors_device_id_foreign` FOREIGN KEY (`device_id`) REFERENCES `devices` (`device_id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `sensors_to_state_indexes`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `sensors_to_state_indexes` (
  `sensors_to_state_translations_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `sensor_id` int(10) unsigned NOT NULL,
  `state_index_id` int(10) unsigned NOT NULL,
  PRIMARY KEY (`sensors_to_state_translations_id`),
  UNIQUE KEY `sensors_to_state_indexes_sensor_id_state_index_id_unique` (`sensor_id`,`state_index_id`),
  KEY `sensors_to_state_indexes_state_index_id_index` (`state_index_id`),
  CONSTRAINT `sensors_to_state_indexes_ibfk_1` FOREIGN KEY (`state_index_id`) REFERENCES `state_indexes` (`state_index_id`),
  CONSTRAINT `sensors_to_state_indexes_sensor_id_foreign` FOREIGN KEY (`sensor_id`) REFERENCES `sensors` (`sensor_id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `service_templates`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `service_templates` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `ip` text DEFAULT NULL,
  `check` varchar(255) NOT NULL,
  `type` varchar(16) NOT NULL DEFAULT 'static',
  `rules` text DEFAULT NULL,
  `desc` text DEFAULT NULL,
  `param` text DEFAULT NULL,
  `ignore` tinyint(1) NOT NULL DEFAULT 0,
  `changed` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `disabled` tinyint(1) NOT NULL DEFAULT 0,
  `name` varchar(255) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `service_templates_device`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `service_templates_device` (
  `service_template_id` int(10) unsigned NOT NULL,
  `device_id` int(10) unsigned NOT NULL,
  PRIMARY KEY (`service_template_id`,`device_id`),
  KEY `service_templates_device_service_template_id_index` (`service_template_id`),
  KEY `service_templates_device_device_id_index` (`device_id`),
  CONSTRAINT `service_templates_device_device_id_foreign` FOREIGN KEY (`device_id`) REFERENCES `devices` (`device_id`) ON DELETE CASCADE,
  CONSTRAINT `service_templates_device_service_template_id_foreign` FOREIGN KEY (`service_template_id`) REFERENCES `service_templates` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `service_templates_device_group`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `service_templates_device_group` (
  `service_template_id` int(10) unsigned NOT NULL,
  `device_group_id` int(10) unsigned NOT NULL,
  PRIMARY KEY (`service_template_id`,`device_group_id`),
  KEY `service_templates_device_group_service_template_id_index` (`service_template_id`),
  KEY `service_templates_device_group_device_group_id_index` (`device_group_id`),
  CONSTRAINT `service_templates_device_group_device_group_id_foreign` FOREIGN KEY (`device_group_id`) REFERENCES `device_groups` (`id`) ON DELETE CASCADE,
  CONSTRAINT `service_templates_device_group_service_template_id_foreign` FOREIGN KEY (`service_template_id`) REFERENCES `service_templates` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `services`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `services` (
  `service_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `device_id` int(10) unsigned NOT NULL,
  `service_ip` text DEFAULT NULL,
  `service_type` varchar(255) NOT NULL,
  `service_desc` text DEFAULT NULL,
  `service_param` text DEFAULT NULL,
  `service_ignore` tinyint(1) NOT NULL DEFAULT 0,
  `service_status` tinyint(4) NOT NULL DEFAULT 0,
  `service_changed` int(10) unsigned NOT NULL DEFAULT 0,
  `service_message` text DEFAULT NULL,
  `service_disabled` tinyint(1) NOT NULL DEFAULT 0,
  `service_ds` text DEFAULT NULL,
  `service_template_id` int(10) unsigned NOT NULL DEFAULT 0,
  `service_name` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`service_id`),
  KEY `services_device_id_index` (`device_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `session`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `session` (
  `session_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `session_username` varchar(255) NOT NULL,
  `session_value` varchar(60) NOT NULL,
  `session_token` varchar(60) NOT NULL,
  `session_auth` varchar(16) NOT NULL,
  `session_expiry` int(11) NOT NULL,
  PRIMARY KEY (`session_id`),
  UNIQUE KEY `session_session_value_unique` (`session_value`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `sessions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `sessions` (
  `id` varchar(255) NOT NULL,
  `user_id` bigint(20) unsigned DEFAULT NULL,
  `ip_address` varchar(45) DEFAULT NULL,
  `user_agent` text DEFAULT NULL,
  `payload` text NOT NULL,
  `last_activity` int(11) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `sessions_user_id_index` (`user_id`),
  KEY `sessions_last_activity_index` (`last_activity`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `slas`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `slas` (
  `sla_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `device_id` int(10) unsigned NOT NULL,
  `sla_nr` int(10) unsigned NOT NULL,
  `owner` varchar(255) NOT NULL,
  `tag` varchar(255) NOT NULL,
  `rtt_type` varchar(16) NOT NULL,
  `rtt` double DEFAULT NULL,
  `status` tinyint(1) NOT NULL,
  `opstatus` tinyint(3) unsigned NOT NULL DEFAULT 0,
  `deleted` tinyint(1) NOT NULL DEFAULT 0,
  PRIMARY KEY (`sla_id`),
  UNIQUE KEY `slas_device_id_sla_nr_unique` (`device_id`,`sla_nr`),
  KEY `slas_device_id_index` (`device_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `state_indexes`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `state_indexes` (
  `state_index_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `state_name` varchar(64) NOT NULL,
  PRIMARY KEY (`state_index_id`),
  UNIQUE KEY `state_indexes_state_name_unique` (`state_name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `state_translations`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `state_translations` (
  `state_translation_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `state_index_id` int(10) unsigned NOT NULL,
  `state_descr` varchar(255) NOT NULL,
  `state_draw_graph` tinyint(1) NOT NULL,
  `state_value` smallint(6) NOT NULL DEFAULT 0,
  `state_generic_value` tinyint(1) NOT NULL,
  `state_lastupdated` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`state_translation_id`),
  UNIQUE KEY `state_translations_state_index_id_state_value_unique` (`state_index_id`,`state_value`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `storage`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `storage` (
  `storage_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `device_id` int(10) unsigned NOT NULL,
  `type` varchar(16) NOT NULL,
  `storage_index` varchar(64) DEFAULT NULL,
  `storage_type` varchar(32) DEFAULT NULL,
  `storage_descr` text NOT NULL,
  `storage_size` bigint(20) NOT NULL,
  `storage_size_oid` varchar(255) DEFAULT NULL,
  `storage_units` int(11) NOT NULL,
  `storage_used` bigint(20) NOT NULL DEFAULT 0,
  `storage_used_oid` varchar(255) DEFAULT NULL,
  `storage_free` bigint(20) NOT NULL DEFAULT 0,
  `storage_free_oid` varchar(255) DEFAULT NULL,
  `storage_perc` int(11) NOT NULL DEFAULT 0,
  `storage_perc_oid` varchar(255) DEFAULT NULL,
  `storage_perc_warn` int(11) DEFAULT 60,
  PRIMARY KEY (`storage_id`),
  UNIQUE KEY `storage_device_id_storage_mib_storage_index_unique` (`device_id`,`type`,`storage_index`),
  KEY `storage_device_id_index` (`device_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `stp`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `stp` (
  `stp_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `device_id` int(10) unsigned NOT NULL,
  `vlan` int(10) unsigned DEFAULT NULL,
  `rootBridge` tinyint(1) NOT NULL,
  `bridgeAddress` varchar(32) NOT NULL,
  `protocolSpecification` varchar(16) NOT NULL,
  `priority` mediumint(9) NOT NULL,
  `timeSinceTopologyChange` varchar(32) NOT NULL,
  `topChanges` mediumint(9) NOT NULL,
  `designatedRoot` varchar(32) NOT NULL,
  `rootCost` mediumint(9) NOT NULL,
  `rootPort` int(11) DEFAULT NULL,
  `maxAge` mediumint(9) NOT NULL,
  `helloTime` mediumint(9) NOT NULL,
  `holdTime` mediumint(9) NOT NULL,
  `forwardDelay` mediumint(9) NOT NULL,
  `bridgeMaxAge` smallint(6) NOT NULL,
  `bridgeHelloTime` smallint(6) NOT NULL,
  `bridgeForwardDelay` smallint(6) NOT NULL,
  PRIMARY KEY (`stp_id`),
  KEY `stp_device_id_index` (`device_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `syslog`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `syslog` (
  `device_id` int(10) unsigned DEFAULT NULL,
  `facility` varchar(10) DEFAULT NULL,
  `priority` varchar(10) DEFAULT NULL,
  `level` varchar(10) DEFAULT NULL,
  `tag` varchar(10) DEFAULT NULL,
  `timestamp` timestamp NOT NULL DEFAULT current_timestamp(),
  `program` varchar(32) DEFAULT NULL,
  `msg` text DEFAULT NULL,
  `seq` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  PRIMARY KEY (`seq`),
  KEY `syslog_priority_level_index` (`priority`,`level`),
  KEY `syslog_device_id_timestamp_index` (`device_id`,`timestamp`),
  KEY `syslog_device_id_index` (`device_id`),
  KEY `syslog_timestamp_index` (`timestamp`),
  KEY `syslog_program_index` (`program`),
  KEY `syslog_device_id_program_index` (`device_id`,`program`),
  KEY `syslog_device_id_priority_index` (`device_id`,`priority`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `tnmsneinfo`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `tnmsneinfo` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `device_id` int(10) unsigned NOT NULL,
  `neID` int(11) NOT NULL,
  `neType` varchar(128) NOT NULL,
  `neName` varchar(128) NOT NULL,
  `neLocation` varchar(128) NOT NULL,
  `neAlarm` varchar(128) NOT NULL,
  `neOpMode` varchar(128) NOT NULL,
  `neOpState` varchar(128) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `tnmsneinfo_device_id_index` (`device_id`),
  KEY `tnmsneinfo_neid_index` (`neID`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `transceivers`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `transceivers` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `device_id` bigint(20) NOT NULL,
  `port_id` bigint(20) NOT NULL,
  `index` varchar(255) NOT NULL,
  `entity_physical_index` int(11) DEFAULT NULL,
  `type` varchar(128) DEFAULT NULL,
  `vendor` varchar(16) DEFAULT NULL,
  `oui` varchar(16) DEFAULT NULL,
  `model` varchar(32) DEFAULT NULL,
  `revision` varchar(16) DEFAULT NULL,
  `serial` varchar(32) DEFAULT NULL,
  `date` date DEFAULT NULL,
  `ddm` tinyint(1) DEFAULT NULL,
  `encoding` varchar(16) DEFAULT NULL,
  `cable` varchar(16) DEFAULT NULL,
  `distance` int(11) DEFAULT NULL,
  `wavelength` int(11) DEFAULT NULL,
  `connector` varchar(16) DEFAULT NULL,
  `channels` smallint(6) NOT NULL DEFAULT 1,
  PRIMARY KEY (`id`),
  KEY `transceivers_device_id_entity_physical_index_index` (`device_id`,`entity_physical_index`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `transport_group_transport`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `transport_group_transport` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `transport_group_id` int(10) unsigned NOT NULL,
  `transport_id` int(10) unsigned NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `ucd_diskio`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `ucd_diskio` (
  `diskio_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `device_id` int(10) unsigned NOT NULL,
  `diskio_index` int(11) NOT NULL,
  `diskio_descr` varchar(32) NOT NULL,
  PRIMARY KEY (`diskio_id`),
  KEY `ucd_diskio_device_id_index` (`device_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `users`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `users` (
  `user_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `auth_type` varchar(32) DEFAULT NULL,
  `auth_id` varchar(255) DEFAULT NULL,
  `username` varchar(255) NOT NULL,
  `password` varchar(255) DEFAULT NULL,
  `realname` varchar(64) NOT NULL,
  `email` varchar(64) NOT NULL,
  `descr` char(30) NOT NULL,
  `can_modify_passwd` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT '1970-01-02 00:00:01',
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `remember_token` varchar(100) DEFAULT NULL,
  `enabled` tinyint(1) NOT NULL DEFAULT 1,
  PRIMARY KEY (`user_id`),
  UNIQUE KEY `users_auth_type_username_unique` (`auth_type`,`username`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `users_prefs`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `users_prefs` (
  `user_id` int(10) unsigned NOT NULL,
  `pref` varchar(32) NOT NULL,
  `value` varchar(128) NOT NULL,
  UNIQUE KEY `users_prefs_user_id_pref_unique` (`user_id`,`pref`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `users_widgets`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `users_widgets` (
  `user_widget_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `user_id` int(10) unsigned NOT NULL,
  `widget` varchar(32) NOT NULL,
  `col` tinyint(4) NOT NULL,
  `row` tinyint(4) NOT NULL,
  `size_x` tinyint(4) NOT NULL,
  `size_y` tinyint(4) NOT NULL,
  `title` varchar(255) NOT NULL,
  `refresh` tinyint(4) NOT NULL DEFAULT 60,
  `settings` text NOT NULL,
  `dashboard_id` int(10) unsigned NOT NULL,
  PRIMARY KEY (`user_widget_id`),
  KEY `user_id` (`user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `vendor_ouis`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `vendor_ouis` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `vendor` varchar(255) NOT NULL,
  `oui` varchar(12) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `vendor_ouis_oui_unique` (`oui`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `vlans`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `vlans` (
  `vlan_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `device_id` int(10) unsigned DEFAULT NULL,
  `vlan_vlan` int(11) DEFAULT NULL,
  `vlan_domain` int(11) DEFAULT NULL,
  `vlan_name` varchar(64) DEFAULT NULL,
  `vlan_type` varchar(16) DEFAULT NULL,
  `vlan_mtu` int(11) DEFAULT NULL,
  PRIMARY KEY (`vlan_id`),
  KEY `device_id` (`device_id`,`vlan_vlan`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `vminfo`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `vminfo` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `device_id` int(10) unsigned NOT NULL,
  `vm_type` varchar(16) NOT NULL DEFAULT 'vmware',
  `vmwVmVMID` int(11) NOT NULL,
  `vmwVmDisplayName` varchar(128) NOT NULL,
  `vmwVmGuestOS` varchar(256) DEFAULT NULL,
  `vmwVmMemSize` int(11) NOT NULL,
  `vmwVmCpus` int(11) NOT NULL,
  `vmwVmState` smallint(5) unsigned NOT NULL,
  PRIMARY KEY (`id`),
  KEY `vminfo_device_id_index` (`device_id`),
  KEY `vminfo_vmwvmvmid_index` (`vmwVmVMID`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `vrf_lite_cisco`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `vrf_lite_cisco` (
  `vrf_lite_cisco_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `device_id` int(10) unsigned NOT NULL,
  `context_name` varchar(128) NOT NULL,
  `intance_name` varchar(128) DEFAULT '',
  `vrf_name` varchar(128) DEFAULT 'Default',
  PRIMARY KEY (`vrf_lite_cisco_id`),
  KEY `vrf_lite_cisco_device_id_context_name_vrf_name_index` (`device_id`,`context_name`,`vrf_name`),
  KEY `vrf_lite_cisco_device_id_index` (`device_id`),
  KEY `vrf_lite_cisco_context_name_index` (`context_name`),
  KEY `vrf_lite_cisco_vrf_name_index` (`vrf_name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `vrfs`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `vrfs` (
  `vrf_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `vrf_oid` varchar(256) NOT NULL,
  `vrf_name` varchar(128) DEFAULT NULL,
  `bgpLocalAs` int(10) unsigned DEFAULT NULL,
  `mplsVpnVrfRouteDistinguisher` varchar(128) DEFAULT NULL,
  `mplsVpnVrfDescription` text NOT NULL,
  `device_id` int(10) unsigned NOT NULL,
  PRIMARY KEY (`vrf_id`),
  KEY `vrfs_device_id_index` (`device_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `wireless_sensors`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `wireless_sensors` (
  `sensor_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `sensor_deleted` tinyint(1) NOT NULL DEFAULT 0,
  `sensor_class` varchar(64) NOT NULL,
  `device_id` int(10) unsigned NOT NULL DEFAULT 0,
  `sensor_index` varchar(64) DEFAULT NULL,
  `sensor_type` varchar(255) NOT NULL,
  `sensor_descr` varchar(255) DEFAULT NULL,
  `sensor_divisor` int(11) NOT NULL DEFAULT 1,
  `sensor_multiplier` int(11) NOT NULL DEFAULT 1,
  `sensor_aggregator` varchar(16) NOT NULL DEFAULT 'sum',
  `sensor_current` double DEFAULT NULL,
  `sensor_prev` double DEFAULT NULL,
  `sensor_limit` double DEFAULT NULL,
  `sensor_limit_warn` double DEFAULT NULL,
  `sensor_limit_low` double DEFAULT NULL,
  `sensor_limit_low_warn` double DEFAULT NULL,
  `sensor_alert` tinyint(1) NOT NULL DEFAULT 1,
  `sensor_custom` enum('No','Yes') NOT NULL DEFAULT 'No',
  `entPhysicalIndex` varchar(16) DEFAULT NULL,
  `entPhysicalIndex_measured` varchar(16) DEFAULT NULL,
  `lastupdate` timestamp NOT NULL DEFAULT current_timestamp(),
  `sensor_oids` text NOT NULL,
  `access_point_id` int(10) unsigned DEFAULT NULL,
  `rrd_type` enum('GAUGE','COUNTER','DERIVE','DCOUNTER','DDERIVE','ABSOLUTE') NOT NULL DEFAULT 'GAUGE',
  PRIMARY KEY (`sensor_id`),
  KEY `wireless_sensors_sensor_class_index` (`sensor_class`),
  KEY `wireless_sensors_device_id_index` (`device_id`),
  KEY `wireless_sensors_sensor_type_index` (`sensor_type`),
  CONSTRAINT `wireless_sensors_device_id_foreign` FOREIGN KEY (`device_id`) REFERENCES `devices` (`device_id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

/*M!999999\- enable the sandbox mode */ 
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (1,'2018_07_03_091314_create_access_points_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (2,'2018_07_03_091314_create_alert_device_map_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (3,'2018_07_03_091314_create_alert_group_map_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (4,'2018_07_03_091314_create_alert_log_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (5,'2018_07_03_091314_create_alert_rules_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (6,'2018_07_03_091314_create_alert_schedulables_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (7,'2018_07_03_091314_create_alert_schedule_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (8,'2018_07_03_091314_create_alert_template_map_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (9,'2018_07_03_091314_create_alert_templates_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (10,'2018_07_03_091314_create_alert_transport_groups_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (11,'2018_07_03_091314_create_alert_transport_map_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (12,'2018_07_03_091314_create_alert_transports_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (13,'2018_07_03_091314_create_alerts_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (14,'2018_07_03_091314_create_api_tokens_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (15,'2018_07_03_091314_create_application_metrics_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (16,'2018_07_03_091314_create_applications_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (17,'2018_07_03_091314_create_authlog_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (18,'2018_07_03_091314_create_bgpPeers_cbgp_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (19,'2018_07_03_091314_create_bgpPeers_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (20,'2018_07_03_091314_create_bill_data_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (21,'2018_07_03_091314_create_bill_history_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (22,'2018_07_03_091314_create_bill_perms_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (23,'2018_07_03_091314_create_bill_port_counters_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (24,'2018_07_03_091314_create_bill_ports_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (25,'2018_07_03_091314_create_bills_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (26,'2018_07_03_091314_create_callback_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (27,'2018_07_03_091314_create_cef_switching_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (28,'2018_07_03_091314_create_ciscoASA_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (29,'2018_07_03_091314_create_component_prefs_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (30,'2018_07_03_091314_create_component_statuslog_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (31,'2018_07_03_091314_create_component_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (32,'2018_07_03_091314_create_config_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (33,'2018_07_03_091314_create_customers_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (34,'2018_07_03_091314_create_dashboards_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (35,'2018_07_03_091314_create_dbSchema_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (36,'2018_07_03_091314_create_device_graphs_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (37,'2018_07_03_091314_create_device_group_device_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (38,'2018_07_03_091314_create_device_groups_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (39,'2018_07_03_091314_create_device_mibs_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (40,'2018_07_03_091314_create_device_oids_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (41,'2018_07_03_091314_create_device_perf_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (42,'2018_07_03_091314_create_device_relationships_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (43,'2018_07_03_091314_create_devices_attribs_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (44,'2018_07_03_091314_create_devices_perms_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (45,'2018_07_03_091314_create_devices_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (46,'2018_07_03_091314_create_entPhysical_state_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (47,'2018_07_03_091314_create_entPhysical_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (48,'2018_07_03_091314_create_entityState_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (49,'2018_07_03_091314_create_eventlog_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (50,'2018_07_03_091314_create_graph_types_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (51,'2018_07_03_091314_create_hrDevice_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (52,'2018_07_03_091314_create_ipsec_tunnels_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (53,'2018_07_03_091314_create_ipv4_addresses_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (54,'2018_07_03_091314_create_ipv4_mac_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (55,'2018_07_03_091314_create_ipv4_networks_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (56,'2018_07_03_091314_create_ipv6_addresses_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (57,'2018_07_03_091314_create_ipv6_networks_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (58,'2018_07_03_091314_create_juniAtmVp_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (59,'2018_07_03_091314_create_links_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (60,'2018_07_03_091314_create_loadbalancer_rservers_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (61,'2018_07_03_091314_create_loadbalancer_vservers_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (62,'2018_07_03_091314_create_locations_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (63,'2018_07_03_091314_create_mac_accounting_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (64,'2018_07_03_091314_create_mefinfo_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (65,'2018_07_03_091314_create_mempools_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (66,'2018_07_03_091314_create_mibdefs_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (67,'2018_07_03_091314_create_munin_plugins_ds_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (68,'2018_07_03_091314_create_munin_plugins_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (69,'2018_07_03_091314_create_netscaler_vservers_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (70,'2018_07_03_091314_create_notifications_attribs_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (71,'2018_07_03_091314_create_notifications_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (72,'2018_07_03_091314_create_ospf_areas_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (73,'2018_07_03_091314_create_ospf_instances_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (74,'2018_07_03_091314_create_ospf_nbrs_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (75,'2018_07_03_091314_create_ospf_ports_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (76,'2018_07_03_091314_create_packages_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (77,'2018_07_03_091314_create_pdb_ix_peers_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (78,'2018_07_03_091314_create_pdb_ix_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (79,'2018_07_03_091314_create_perf_times_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (80,'2018_07_03_091314_create_plugins_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (81,'2018_07_03_091314_create_poller_cluster_stats_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (82,'2018_07_03_091314_create_poller_cluster_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (83,'2018_07_03_091314_create_poller_groups_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (84,'2018_07_03_091314_create_pollers_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (85,'2018_07_03_091314_create_ports_adsl_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (86,'2018_07_03_091314_create_ports_fdb_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (87,'2018_07_03_091314_create_ports_nac_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (88,'2018_07_03_091314_create_ports_perms_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (89,'2018_07_03_091314_create_ports_stack_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (90,'2018_07_03_091314_create_ports_statistics_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (91,'2018_07_03_091314_create_ports_stp_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (92,'2018_07_03_091314_create_ports_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (93,'2018_07_03_091314_create_ports_vlans_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (94,'2018_07_03_091314_create_processes_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (95,'2018_07_03_091314_create_processors_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (96,'2018_07_03_091314_create_proxmox_ports_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (97,'2018_07_03_091314_create_proxmox_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (98,'2018_07_03_091314_create_pseudowires_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (99,'2018_07_03_091314_create_route_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (100,'2018_07_03_091314_create_sensors_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (101,'2018_07_03_091314_create_sensors_to_state_indexes_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (102,'2018_07_03_091314_create_services_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (103,'2018_07_03_091314_create_session_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (104,'2018_07_03_091314_create_slas_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (105,'2018_07_03_091314_create_state_indexes_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (106,'2018_07_03_091314_create_state_translations_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (107,'2018_07_03_091314_create_storage_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (108,'2018_07_03_091314_create_stp_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (109,'2018_07_03_091314_create_syslog_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (110,'2018_07_03_091314_create_tnmsneinfo_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (111,'2018_07_03_091314_create_toner_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (112,'2018_07_03_091314_create_transport_group_transport_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (113,'2018_07_03_091314_create_ucd_diskio_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (114,'2018_07_03_091314_create_users_prefs_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (115,'2018_07_03_091314_create_users_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (116,'2018_07_03_091314_create_users_widgets_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (117,'2018_07_03_091314_create_vlans_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (118,'2018_07_03_091314_create_vminfo_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (119,'2018_07_03_091314_create_vrf_lite_cisco_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (120,'2018_07_03_091314_create_vrfs_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (121,'2018_07_03_091314_create_widgets_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (122,'2018_07_03_091314_create_wireless_sensors_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (123,'2018_07_03_091322_add_foreign_keys_to_component_prefs_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (124,'2018_07_03_091322_add_foreign_keys_to_component_statuslog_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (125,'2018_07_03_091322_add_foreign_keys_to_device_group_device_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (126,'2018_07_03_091322_add_foreign_keys_to_device_relationships_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (127,'2018_07_03_091322_add_foreign_keys_to_sensors_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (128,'2018_07_03_091322_add_foreign_keys_to_sensors_to_state_indexes_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (129,'2018_07_03_091322_add_foreign_keys_to_wireless_sensors_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (130,'2019_01_16_132200_add_vlan_and_elapsed_to_nac',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (131,'2019_01_16_195644_add_vrf_id_and_bgpLocalAs',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (132,'2019_02_05_140857_remove_config_definition_from_db',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (133,'2019_02_10_220000_add_dates_to_fdb',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (134,'2019_04_22_220000_update_route_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (135,'2019_05_12_202407_create_mpls_lsps_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (136,'2019_05_12_202408_create_mpls_lsp_paths_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (137,'2019_05_30_225937_device_groups_rewrite',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (138,'2019_06_30_190400_create_mpls_sdps_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (139,'2019_06_30_190401_create_mpls_sdp_binds_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (140,'2019_06_30_190402_create_mpls_services_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (141,'2019_07_03_132417_create_mpls_saps_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (142,'2019_07_09_150217_update_users_widgets_settings',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (143,'2019_08_10_223200_add_enabled_to_users',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (144,'2019_08_28_105051_fix-template-linefeeds',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (145,'2019_09_05_153524_create_notifications_attribs_index',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (146,'2019_09_29_114433_change_default_mempool_perc_warn_in_mempools_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (147,'2019_10_03_211702_serialize_config',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (148,'2019_10_21_105350_devices_group_perms',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (149,'2019_11_30_191013_create_mpls_tunnel_ar_hops_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (150,'2019_11_30_191013_create_mpls_tunnel_c_hops_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (151,'2019_12_01_165514_add_indexes_to_mpls_lsp_paths_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (152,'2019_12_05_164700_alerts_disable_on_update_current_timestamp',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (153,'2019_12_16_140000_create_customoids_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (154,'2019_12_17_151314_add_invert_map_to_alert_rules',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (155,'2019_12_28_180000_add_overwrite_ip_to_devices',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (156,'2020_01_09_1300_migrate_devices_attribs_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (157,'2020_01_10_075852_alter_mpls_lsp_paths_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (158,'2020_02_05_093457_add_inserted_to_devices',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (159,'2020_02_05_224042_device_inserted_null',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (160,'2020_02_10_223323_create_alert_location_map_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (161,'2020_03_24_0844_add_primary_key_to_device_graphs',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (162,'2020_03_25_165300_add_column_to_ports',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (163,'2020_04_06_001048_the_great_index_rename',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (164,'2020_04_08_172357_alert_schedule_utc',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (165,'2020_04_13_150500_add_last_error_fields_to_bgp_peers',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (166,'2020_04_19_010532_eventlog_sensor_reference_cleanup',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (167,'2020_05_22_020303_alter_metric_column',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (168,'2020_05_24_212054_poller_cluster_settings',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (169,'2020_05_30_162638_remove_mib_polling_tables',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (170,'2020_06_06_222222_create_device_outages_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (171,'2020_06_23_00522_alter_availability_perc_column',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (172,'2020_06_24_155119_drop_ports_if_high_speed',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (173,'2020_07_27_00522_alter_devices_snmp_algo_columns',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (174,'2020_07_29_143221_add_device_perf_index',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (175,'2020_08_28_212054_drop_uptime_column_outages',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (176,'2020_09_18_223431_create_cache_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (177,'2020_09_18_230114_create_service_templates_device_group_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (178,'2020_09_18_230114_create_service_templates_device_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (179,'2020_09_18_230114_create_service_templates_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (180,'2020_09_18_230114_extend_services_table_for_service_templates_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (181,'2020_09_19_230114_add_foreign_keys_to_service_templates_device_group_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (182,'2020_09_19_230114_add_foreign_keys_to_service_templates_device_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (183,'2020_09_22_172321_add_alert_log_index',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (184,'2020_09_24_000500_create_cache_locks_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (185,'2020_10_03_1000_add_primary_key_bill_perms',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (186,'2020_10_03_1000_add_primary_key_bill_ports',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (187,'2020_10_03_1000_add_primary_key_devices_perms',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (188,'2020_10_03_1000_add_primary_key_entPhysical_state',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (189,'2020_10_03_1000_add_primary_key_ipv4_mac',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (190,'2020_10_03_1000_add_primary_key_juniAtmVp',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (191,'2020_10_03_1000_add_primary_key_loadbalancer_vservers',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (192,'2020_10_03_1000_add_primary_key_ports_perms',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (193,'2020_10_03_1000_add_primary_key_processes',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (194,'2020_10_03_1000_add_primary_key_transport_group_transport',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (195,'2020_10_12_095504_mempools_add_oids',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (196,'2020_10_21_124101_allow_nullable_ospf_columns',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (197,'2020_10_30_093601_add_tos_to_ospf_ports',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (198,'2020_11_02_164331_add_powerstate_enum_to_vminfo',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (199,'2020_12_14_091314_create_port_group_port_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (200,'2020_12_14_091314_create_port_groups_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (201,'2021_02_08_224355_fix_invalid_dates',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (202,'2021_02_09_084318_remove_perf_times',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (203,'2021_02_09_122930_migrate_to_utf8mb4',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (204,'2021_02_21_203415_location_add_fixed_coordinates_flag',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (205,'2021_03_11_003540_rename_toner_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (206,'2021_03_11_003713_rename_printer_columns',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (207,'2021_03_17_160729_service_templates_cleanup',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (208,'2021_03_26_014054_change_cache_to_mediumtext',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (209,'2021_04_08_151101_add_foreign_keys_to_port_group_port_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (210,'2021_06_07_123600_create_sessions_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (211,'2021_06_11_084830_slas_add_rtt_field',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (212,'2021_07_06_1845_alter_bill_history_max_min',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (213,'2021_07_28_102443_plugins_add_version_and_settings',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (214,'2021_08_04_102914_add_syslog_indexes',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (215,'2021_08_26_093522_config_value_to_medium_text',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (216,'2021_09_07_094310_create_push_subscriptions_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (217,'2021_09_26_164200_create_hrsystem_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (218,'2021_10_02_190310_add_device_outages_index',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (219,'2021_10_03_164200_update_hrsystem_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (220,'2021_10_20_072929_disable_example_plugin',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (221,'2021_10_20_224207_increase_length_of_attrib_type_column',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (222,'2021_11_12_123037_change_cpwVcID_to_unsignedInteger',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (223,'2021_11_17_105321_device_add_display_field',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (224,'2021_11_29_160744_change_ports_text_fields_to_varchar',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (225,'2021_11_29_165046_improve_devices_search_index',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (226,'2021_11_29_165436_improve_ports_search_index',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (227,'2021_12_02_100709_remove_ports_stp_unique_index',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (228,'2021_12_02_101739_add_vlan_field_to_stp_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (229,'2021_12_02_101810_add_vlan_and_port_index_fields_to_ports_stp_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (230,'2021_12_02_110154_update_ports_stp_unique_index',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (231,'2021_12_02_113537_ports_stp_designated_cost_change_to_int',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (232,'2021_25_01_0127_create_isis_adjacencies_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (233,'2021_25_01_0128_isis_adjacencies_add_admin_status',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (234,'2021_25_01_0129_isis_adjacencies_nullable',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (235,'2022_02_03_164059_increase_auth_id_length',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (236,'2022_02_21_073500_add_iface_field_to_bgp_peers',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (237,'2022_04_08_085504_isis_adjacencies_table_add_index',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (238,'2022_05_25_084506_add_widgets_column_to_users_widgets_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (239,'2022_05_25_084617_migrate_widget_ids',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (240,'2022_05_25_085715_remove_user_widgets_id',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (241,'2022_05_25_090027_drop_widgets_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (242,'2022_05_30_084932_update-app-status-length',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (243,'2022_07_03_1947_add_app_data',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (244,'2022_07_19_081224_plugins_unique_index',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (245,'2022_08_15_084506_add_rrd_type_to_sensors_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (246,'2022_08_15_084507_add_rrd_type_to_wireless_sensors_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (247,'2022_08_15_091314_create_ports_vdsl_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (248,'2022_09_03_091314_update_ports_adsl_table_with_defaults',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (249,'2023_03_14_130653_migrate_empty_user_funcs_to_null',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (250,'2023_04_12_174529_modify_ports_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (251,'2023_04_26_185850_change_vminfo_vmw_vm_guest_o_s_nullable',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (252,'2023_04_27_164904_update_slas_opstatus_tinyint',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (253,'2023_05_12_071412_devices_expand_timetaken_doubles',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (254,'2023_06_02_230406_create_vendor_oui_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (255,'2023_06_18_195618_create_bouncer_tables',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (256,'2023_06_18_201914_migrate_level_to_roles',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (257,'2023_08_02_090027_drop_dbschema_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (258,'2023_08_02_120455_vendor_ouis_unique_index',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (259,'2023_08_30_105156_add_applications_soft_deleted',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (260,'2023_09_01_084057_application_new_defaults',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (261,'2023_10_07_170735_increase_processes_cputime_length',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (262,'2023_10_07_231037_application_metrics_add_primary_key',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (263,'2023_10_12_183306_ports_statistics_table_unsigned_stats',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (264,'2023_10_12_184311_bgp_peers_cbgp_table_unsigned_stats',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (265,'2023_10_12_184652_bgp_peers_table_unsigned_stats',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (266,'2023_10_14_162039_restore_ports_delta_fields',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (267,'2023_10_14_162234_restore_bgp_peers_cbgp_delta_fields',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (268,'2023_10_20_075853_cisco_asa_add_default_limits',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (269,'2023_10_31_074547_ospf_areas_unsigned',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (270,'2023_10_31_074901_ospf_instances_unsigned',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (271,'2023_10_31_075239_ospf_nbrs_unsigned',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (272,'2023_10_31_080052_ospf_ports_unsigned',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (273,'2023_11_04_125846_packages_increase_name_column_length',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (274,'2023_11_21_172239_increase_vminfo.vmwvmguestos_column_length',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (275,'2023_12_08_080319_create_custom_map_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (276,'2023_12_08_081420_create_custom_map_node_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (277,'2023_12_08_082518_create_custom_map_edge_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (278,'2023_12_08_083319_create_custom_map_background_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (279,'2023_12_08_184652_mpls_addrtype_fix',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (280,'2023_12_10_130000_historical_data_to_ports_nac',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (281,'2023_12_12_171400_alert_rule_note',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (282,'2023_12_15_105529_access_points_nummonbssid_integer',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (283,'2023_12_19_082112_custom_map_grid_snap',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (284,'2023_12_21_085427_create_view_port_mac_link',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (285,'2024_01_04_195618_add_ignore_status_to_devices_tables',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (286,'2024_01_08_223812_custom_map_node_image',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (287,'2024_01_09_211518_custom_map_node_maplink',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (288,'2024_01_09_223917_bill_data_new_primary',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (289,'2024_01_09_223927_bill_data_updated_indexes',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (290,'2024_02_03_201014_custom_map_edge_additions',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (291,'2024_02_07_151845_custom_map_additions',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (292,'2024_03_27_123152_create_transceivers_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (293,'2024_04_10_093513_remove_device_perf',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (294,'2024_04_22_161711_custom_maps_add_group',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (295,'2024_04_29_180911_custom_maps_add_background_type_and_background_data',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (296,'2024_04_29_183605_custom_maps_drop_background_suffix_and_background_version',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (297,'2024_07_13_133839_modify_ent_physical_defaults',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (298,'2024_07_19_120719_update_ports_stack_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (299,'2024_07_28_162410_ent_physical_table_ifindex_unsigned',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (300,'2024_08_12_232009_ent_physical_table_rev_length',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (301,'2024_08_27_182000_ports_statistics_table_rev_length',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (302,'2024_10_06_002633_ports_vlans_table_add_port_id_index',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (303,'2024_10_12_164214_custom_map_edge_width',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (304,'2024_10_12_210114_custom_map_legend_colours',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (305,'2024_10_13_161616_create_custom_map_nodeimage_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (306,'2024_10_13_162920_add_custom_map_nodeimage_column',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (307,'2024_10_20_154356_create_qos_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (308,'2024_10_24_131715_mpls_sdp_bindings_enum_string',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (309,'2024_11_07_110342_custommap_edge_add_text_align',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (310,'2024_11_22_135845_alert_log_refactor_indexes',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (311,'2025_01_07_223946_drop_cisco_a_s_a_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (312,'2025_01_20_125000_create_ospfv3_areas_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (313,'2025_01_20_125000_create_ospfv3_instances_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (314,'2025_01_20_125000_create_ospfv3_nbrs_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (315,'2025_01_20_125000_create_ospfv3_ports_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (316,'2025_01_22_194300_add_storage_oids_to_storage_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (317,'2025_01_22_194342_drop_storage_deleted',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (318,'2025_01_28_135558_ports_drop_unique_ifindex',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (319,'2025_01_30_000121_add_ifindex_index_to_ports_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (320,'2025_01_30_214311_create_ipv6_nd_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (321,'2025_03_11_031114_drop_ospfv3ifinstid',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (322,'2025_03_17_144000_drop_ospfv3nbrifindex',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (323,'2025_03_17_222255_rename_existing_permissions_tables',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (324,'2025_03_17_222652_create_permission_tables',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (325,'2025_03_17_222734_migrate_bouncer_to_spatie',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (326,'2025_03_18_003446_drop_bouncer_tables',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (327,'2025_03_19_205644_fix_ospfv3_areas_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (328,'2025_03_19_205648_fix_ospfv3_instances_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (329,'2025_03_19_205655_fix_ospfv3_nbrs_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (330,'2025_03_19_205700_fix_ospfv3_ports_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (331,'2025_03_22_134124_fix_ipv6_addresses_id_type',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (332,'2025_03_27_125749_move_definition_files',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (333,'2025_04_15_122034_laravel_11_fix_types',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (334,'2025_04_29_133233_add_device_index_to_ipv4_mac_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (335,'2025_04_29_133533_add_indexes_to_ipv6_nd_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (336,'2025_04_29_150404_context_nullable_in_ipv4_mac_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (337,'2025_04_29_150423_context_nullable_in_ipv6_nd_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (338,'2025_05_02_133959_filter_empty_socialite_configs',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (339,'2025_05_03_152418_remove_invalid_sensor_classes',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (340,'2025_05_07_103301_fix_ipv4_addresses_id_type',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (341,'2025_05_20_084533_dashboard_admin_move_full_shared',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (342,'2025_05_25_183627_drop_view_port_mac_link',1);
