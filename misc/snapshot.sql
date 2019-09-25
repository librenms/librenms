-- MariaDB dump 10.17  Distrib 10.4.8-MariaDB, for Linux (x86_64)
--
-- Host: localhost    Database: librenms_phpunit_78hunjuybybh
-- ------------------------------------------------------
-- Server version	10.4.8-MariaDB

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;
/*!40103 SET @OLD_TIME_ZONE=@@TIME_ZONE */;
/*!40103 SET TIME_ZONE='+00:00' */;
/*!40014 SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;

--
-- Table structure for table `access_points`
--

DROP TABLE IF EXISTS `access_points`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `access_points` (
  `accesspoint_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `device_id` int(10) unsigned NOT NULL,
  `name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `radio_number` tinyint(4) DEFAULT NULL,
  `type` varchar(16) COLLATE utf8_unicode_ci NOT NULL,
  `mac_addr` varchar(24) COLLATE utf8_unicode_ci NOT NULL,
  `deleted` tinyint(1) NOT NULL DEFAULT 0,
  `channel` tinyint(3) unsigned NOT NULL DEFAULT 0,
  `txpow` tinyint(4) NOT NULL DEFAULT 0,
  `radioutil` tinyint(4) NOT NULL DEFAULT 0,
  `numasoclients` smallint(6) NOT NULL DEFAULT 0,
  `nummonclients` smallint(6) NOT NULL DEFAULT 0,
  `numactbssid` tinyint(4) NOT NULL DEFAULT 0,
  `nummonbssid` tinyint(4) NOT NULL DEFAULT 0,
  `interference` tinyint(3) unsigned NOT NULL,
  PRIMARY KEY (`accesspoint_id`),
  KEY `name` (`name`,`radio_number`),
  KEY `deleted` (`deleted`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `access_points`
--

LOCK TABLES `access_points` WRITE;
/*!40000 ALTER TABLE `access_points` DISABLE KEYS */;
/*!40000 ALTER TABLE `access_points` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `alert_device_map`
--

DROP TABLE IF EXISTS `alert_device_map`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `alert_device_map` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `rule_id` int(10) unsigned NOT NULL,
  `device_id` int(10) unsigned NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `alert_device_map_rule_id_device_id_uindex` (`rule_id`,`device_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `alert_device_map`
--

LOCK TABLES `alert_device_map` WRITE;
/*!40000 ALTER TABLE `alert_device_map` DISABLE KEYS */;
/*!40000 ALTER TABLE `alert_device_map` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `alert_group_map`
--

DROP TABLE IF EXISTS `alert_group_map`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `alert_group_map` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `rule_id` int(10) unsigned NOT NULL,
  `group_id` int(10) unsigned NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `alert_group_map_rule_id_group_id_uindex` (`rule_id`,`group_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `alert_group_map`
--

LOCK TABLES `alert_group_map` WRITE;
/*!40000 ALTER TABLE `alert_group_map` DISABLE KEYS */;
/*!40000 ALTER TABLE `alert_group_map` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `alert_log`
--

DROP TABLE IF EXISTS `alert_log`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `alert_log` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `rule_id` int(10) unsigned NOT NULL,
  `device_id` int(10) unsigned NOT NULL,
  `state` int(11) NOT NULL,
  `details` longblob DEFAULT NULL,
  `time_logged` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `rule_id` (`rule_id`),
  KEY `device_id` (`device_id`),
  KEY `time_logged` (`time_logged`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `alert_log`
--

LOCK TABLES `alert_log` WRITE;
/*!40000 ALTER TABLE `alert_log` DISABLE KEYS */;
/*!40000 ALTER TABLE `alert_log` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `alert_rules`
--

DROP TABLE IF EXISTS `alert_rules`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `alert_rules` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `rule` text COLLATE utf8_unicode_ci NOT NULL,
  `severity` enum('ok','warning','critical') COLLATE utf8_unicode_ci NOT NULL,
  `extra` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `disabled` tinyint(1) NOT NULL,
  `name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `query` text COLLATE utf8_unicode_ci NOT NULL,
  `builder` text COLLATE utf8_unicode_ci NOT NULL,
  `proc` varchar(80) COLLATE utf8_unicode_ci DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `name` (`name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `alert_rules`
--

LOCK TABLES `alert_rules` WRITE;
/*!40000 ALTER TABLE `alert_rules` DISABLE KEYS */;
/*!40000 ALTER TABLE `alert_rules` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `alert_schedulables`
--

DROP TABLE IF EXISTS `alert_schedulables`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `alert_schedulables` (
  `item_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `schedule_id` int(10) unsigned NOT NULL,
  `alert_schedulable_id` int(10) unsigned NOT NULL,
  `alert_schedulable_type` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  PRIMARY KEY (`item_id`),
  KEY `schedulable_morph_index` (`alert_schedulable_type`,`alert_schedulable_id`),
  KEY `schedule_id` (`schedule_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `alert_schedulables`
--

LOCK TABLES `alert_schedulables` WRITE;
/*!40000 ALTER TABLE `alert_schedulables` DISABLE KEYS */;
/*!40000 ALTER TABLE `alert_schedulables` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `alert_schedule`
--

DROP TABLE IF EXISTS `alert_schedule`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `alert_schedule` (
  `schedule_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `recurring` tinyint(1) unsigned NOT NULL DEFAULT 0,
  `start` datetime NOT NULL DEFAULT '1970-01-02 00:00:01',
  `end` datetime NOT NULL DEFAULT '1970-01-02 00:00:01',
  `start_recurring_dt` date NOT NULL DEFAULT '1970-01-01',
  `end_recurring_dt` date DEFAULT NULL,
  `start_recurring_hr` time NOT NULL DEFAULT '00:00:00',
  `end_recurring_hr` time NOT NULL DEFAULT '00:00:00',
  `recurring_day` varchar(15) COLLATE utf8_unicode_ci DEFAULT NULL,
  `title` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `notes` text COLLATE utf8_unicode_ci NOT NULL,
  PRIMARY KEY (`schedule_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `alert_schedule`
--

LOCK TABLES `alert_schedule` WRITE;
/*!40000 ALTER TABLE `alert_schedule` DISABLE KEYS */;
/*!40000 ALTER TABLE `alert_schedule` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `alert_template_map`
--

DROP TABLE IF EXISTS `alert_template_map`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `alert_template_map` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `alert_templates_id` int(10) unsigned NOT NULL,
  `alert_rule_id` int(10) unsigned NOT NULL,
  PRIMARY KEY (`id`),
  KEY `alert_templates_id` (`alert_templates_id`,`alert_rule_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `alert_template_map`
--

LOCK TABLES `alert_template_map` WRITE;
/*!40000 ALTER TABLE `alert_template_map` DISABLE KEYS */;
/*!40000 ALTER TABLE `alert_template_map` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `alert_templates`
--

DROP TABLE IF EXISTS `alert_templates`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `alert_templates` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `template` longtext COLLATE utf8_unicode_ci NOT NULL,
  `title` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `title_rec` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `alert_templates`
--

LOCK TABLES `alert_templates` WRITE;
/*!40000 ALTER TABLE `alert_templates` DISABLE KEYS */;
/*!40000 ALTER TABLE `alert_templates` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `alert_transport_groups`
--

DROP TABLE IF EXISTS `alert_transport_groups`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `alert_transport_groups` (
  `transport_group_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `transport_group_name` varchar(30) COLLATE utf8_unicode_ci NOT NULL,
  PRIMARY KEY (`transport_group_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `alert_transport_groups`
--

LOCK TABLES `alert_transport_groups` WRITE;
/*!40000 ALTER TABLE `alert_transport_groups` DISABLE KEYS */;
/*!40000 ALTER TABLE `alert_transport_groups` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `alert_transport_map`
--

DROP TABLE IF EXISTS `alert_transport_map`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `alert_transport_map` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `rule_id` int(10) unsigned NOT NULL,
  `transport_or_group_id` int(10) unsigned NOT NULL,
  `target_type` varchar(16) COLLATE utf8_unicode_ci NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `alert_transport_map`
--

LOCK TABLES `alert_transport_map` WRITE;
/*!40000 ALTER TABLE `alert_transport_map` DISABLE KEYS */;
/*!40000 ALTER TABLE `alert_transport_map` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `alert_transports`
--

DROP TABLE IF EXISTS `alert_transports`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `alert_transports` (
  `transport_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `transport_name` varchar(30) COLLATE utf8_unicode_ci NOT NULL,
  `transport_type` varchar(20) COLLATE utf8_unicode_ci NOT NULL DEFAULT 'mail',
  `is_default` tinyint(1) NOT NULL DEFAULT 0,
  `transport_config` text COLLATE utf8_unicode_ci DEFAULT NULL,
  PRIMARY KEY (`transport_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `alert_transports`
--

LOCK TABLES `alert_transports` WRITE;
/*!40000 ALTER TABLE `alert_transports` DISABLE KEYS */;
/*!40000 ALTER TABLE `alert_transports` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `alerts`
--

DROP TABLE IF EXISTS `alerts`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `alerts` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `device_id` int(10) unsigned NOT NULL,
  `rule_id` int(10) unsigned NOT NULL,
  `state` int(11) NOT NULL,
  `alerted` int(11) NOT NULL,
  `open` int(11) NOT NULL,
  `note` text COLLATE utf8_unicode_ci DEFAULT NULL,
  `timestamp` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `info` text COLLATE utf8_unicode_ci NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `unique_alert` (`device_id`,`rule_id`),
  KEY `device_id` (`device_id`),
  KEY `rule_id` (`rule_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `alerts`
--

LOCK TABLES `alerts` WRITE;
/*!40000 ALTER TABLE `alerts` DISABLE KEYS */;
/*!40000 ALTER TABLE `alerts` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `api_tokens`
--

DROP TABLE IF EXISTS `api_tokens`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `api_tokens` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `user_id` int(10) unsigned NOT NULL,
  `token_hash` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `description` varchar(100) COLLATE utf8_unicode_ci NOT NULL,
  `disabled` tinyint(1) NOT NULL DEFAULT 0,
  PRIMARY KEY (`id`),
  UNIQUE KEY `token_hash` (`token_hash`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `api_tokens`
--

LOCK TABLES `api_tokens` WRITE;
/*!40000 ALTER TABLE `api_tokens` DISABLE KEYS */;
/*!40000 ALTER TABLE `api_tokens` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `application_metrics`
--

DROP TABLE IF EXISTS `application_metrics`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `application_metrics` (
  `app_id` int(10) unsigned NOT NULL,
  `metric` varchar(32) COLLATE utf8_unicode_ci NOT NULL,
  `value` double DEFAULT NULL,
  `value_prev` double DEFAULT NULL,
  UNIQUE KEY `application_metrics_app_id_metric_uindex` (`app_id`,`metric`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `application_metrics`
--

LOCK TABLES `application_metrics` WRITE;
/*!40000 ALTER TABLE `application_metrics` DISABLE KEYS */;
/*!40000 ALTER TABLE `application_metrics` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `applications`
--

DROP TABLE IF EXISTS `applications`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `applications` (
  `app_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `device_id` int(10) unsigned NOT NULL,
  `app_type` varchar(64) COLLATE utf8_unicode_ci NOT NULL,
  `app_state` varchar(32) COLLATE utf8_unicode_ci NOT NULL DEFAULT 'UNKNOWN',
  `discovered` tinyint(4) NOT NULL DEFAULT 0,
  `app_state_prev` varchar(32) COLLATE utf8_unicode_ci DEFAULT NULL,
  `app_status` varchar(8) COLLATE utf8_unicode_ci NOT NULL,
  `timestamp` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `app_instance` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  PRIMARY KEY (`app_id`),
  UNIQUE KEY `unique_index` (`device_id`,`app_type`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `applications`
--

LOCK TABLES `applications` WRITE;
/*!40000 ALTER TABLE `applications` DISABLE KEYS */;
/*!40000 ALTER TABLE `applications` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `authlog`
--

DROP TABLE IF EXISTS `authlog`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `authlog` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `datetime` timestamp NOT NULL DEFAULT current_timestamp(),
  `user` text COLLATE utf8_unicode_ci NOT NULL,
  `address` text COLLATE utf8_unicode_ci NOT NULL,
  `result` text COLLATE utf8_unicode_ci NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `authlog`
--

LOCK TABLES `authlog` WRITE;
/*!40000 ALTER TABLE `authlog` DISABLE KEYS */;
/*!40000 ALTER TABLE `authlog` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `bgpPeers`
--

DROP TABLE IF EXISTS `bgpPeers`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `bgpPeers` (
  `bgpPeer_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `device_id` int(10) unsigned NOT NULL,
  `vrf_id` int(10) unsigned DEFAULT NULL,
  `astext` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `bgpPeerIdentifier` text COLLATE utf8_unicode_ci NOT NULL,
  `bgpPeerRemoteAs` bigint(20) NOT NULL,
  `bgpPeerState` text COLLATE utf8_unicode_ci NOT NULL,
  `bgpPeerAdminStatus` text COLLATE utf8_unicode_ci NOT NULL,
  `bgpLocalAddr` text COLLATE utf8_unicode_ci NOT NULL,
  `bgpPeerRemoteAddr` text COLLATE utf8_unicode_ci NOT NULL,
  `bgpPeerDescr` varchar(255) COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
  `bgpPeerInUpdates` int(11) NOT NULL,
  `bgpPeerOutUpdates` int(11) NOT NULL,
  `bgpPeerInTotalMessages` int(11) NOT NULL,
  `bgpPeerOutTotalMessages` int(11) NOT NULL,
  `bgpPeerFsmEstablishedTime` int(11) NOT NULL,
  `bgpPeerInUpdateElapsedTime` int(11) NOT NULL,
  `context_name` varchar(128) COLLATE utf8_unicode_ci DEFAULT NULL,
  PRIMARY KEY (`bgpPeer_id`),
  KEY `device_id` (`device_id`,`context_name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `bgpPeers`
--

LOCK TABLES `bgpPeers` WRITE;
/*!40000 ALTER TABLE `bgpPeers` DISABLE KEYS */;
/*!40000 ALTER TABLE `bgpPeers` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `bgpPeers_cbgp`
--

DROP TABLE IF EXISTS `bgpPeers_cbgp`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `bgpPeers_cbgp` (
  `device_id` int(10) unsigned NOT NULL,
  `bgpPeerIdentifier` varchar(64) COLLATE utf8_unicode_ci NOT NULL,
  `afi` varchar(16) COLLATE utf8_unicode_ci NOT NULL,
  `safi` varchar(16) COLLATE utf8_unicode_ci NOT NULL,
  `AcceptedPrefixes` int(11) NOT NULL,
  `DeniedPrefixes` int(11) NOT NULL,
  `PrefixAdminLimit` int(11) NOT NULL,
  `PrefixThreshold` int(11) NOT NULL,
  `PrefixClearThreshold` int(11) NOT NULL,
  `AdvertisedPrefixes` int(11) NOT NULL,
  `SuppressedPrefixes` int(11) NOT NULL,
  `WithdrawnPrefixes` int(11) NOT NULL,
  `AcceptedPrefixes_delta` int(11) NOT NULL,
  `AcceptedPrefixes_prev` int(11) NOT NULL,
  `DeniedPrefixes_delta` int(11) NOT NULL,
  `DeniedPrefixes_prev` int(11) NOT NULL,
  `AdvertisedPrefixes_delta` int(11) NOT NULL,
  `AdvertisedPrefixes_prev` int(11) NOT NULL,
  `SuppressedPrefixes_delta` int(11) NOT NULL,
  `SuppressedPrefixes_prev` int(11) NOT NULL,
  `WithdrawnPrefixes_delta` int(11) NOT NULL,
  `WithdrawnPrefixes_prev` int(11) NOT NULL,
  `context_name` varchar(128) COLLATE utf8_unicode_ci DEFAULT NULL,
  UNIQUE KEY `unique_index` (`device_id`,`bgpPeerIdentifier`,`afi`,`safi`),
  KEY `device_id` (`device_id`,`bgpPeerIdentifier`,`context_name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `bgpPeers_cbgp`
--

LOCK TABLES `bgpPeers_cbgp` WRITE;
/*!40000 ALTER TABLE `bgpPeers_cbgp` DISABLE KEYS */;
/*!40000 ALTER TABLE `bgpPeers_cbgp` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `bill_data`
--

DROP TABLE IF EXISTS `bill_data`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `bill_data` (
  `bill_id` int(10) unsigned NOT NULL,
  `timestamp` datetime NOT NULL,
  `period` int(11) NOT NULL,
  `delta` bigint(20) NOT NULL,
  `in_delta` bigint(20) NOT NULL,
  `out_delta` bigint(20) NOT NULL,
  PRIMARY KEY (`bill_id`,`timestamp`),
  KEY `bill_id` (`bill_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `bill_data`
--

LOCK TABLES `bill_data` WRITE;
/*!40000 ALTER TABLE `bill_data` DISABLE KEYS */;
/*!40000 ALTER TABLE `bill_data` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `bill_history`
--

DROP TABLE IF EXISTS `bill_history`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `bill_history` (
  `bill_hist_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `bill_id` int(10) unsigned NOT NULL,
  `updated` timestamp NOT NULL DEFAULT current_timestamp(),
  `bill_datefrom` datetime NOT NULL,
  `bill_dateto` datetime NOT NULL,
  `bill_type` text COLLATE utf8_unicode_ci NOT NULL,
  `bill_allowed` bigint(20) NOT NULL,
  `bill_used` bigint(20) NOT NULL,
  `bill_overuse` bigint(20) NOT NULL,
  `bill_percent` decimal(10,2) NOT NULL,
  `rate_95th_in` bigint(20) NOT NULL,
  `rate_95th_out` bigint(20) NOT NULL,
  `rate_95th` bigint(20) NOT NULL,
  `dir_95th` varchar(3) COLLATE utf8_unicode_ci NOT NULL,
  `rate_average` bigint(20) NOT NULL,
  `rate_average_in` bigint(20) NOT NULL,
  `rate_average_out` bigint(20) NOT NULL,
  `traf_in` bigint(20) NOT NULL,
  `traf_out` bigint(20) NOT NULL,
  `traf_total` bigint(20) NOT NULL,
  `pdf` longblob DEFAULT NULL,
  PRIMARY KEY (`bill_hist_id`),
  UNIQUE KEY `unique_index` (`bill_id`,`bill_datefrom`,`bill_dateto`),
  KEY `bill_id` (`bill_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `bill_history`
--

LOCK TABLES `bill_history` WRITE;
/*!40000 ALTER TABLE `bill_history` DISABLE KEYS */;
/*!40000 ALTER TABLE `bill_history` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `bill_perms`
--

DROP TABLE IF EXISTS `bill_perms`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `bill_perms` (
  `user_id` int(10) unsigned NOT NULL,
  `bill_id` int(10) unsigned NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `bill_perms`
--

LOCK TABLES `bill_perms` WRITE;
/*!40000 ALTER TABLE `bill_perms` DISABLE KEYS */;
/*!40000 ALTER TABLE `bill_perms` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `bill_port_counters`
--

DROP TABLE IF EXISTS `bill_port_counters`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `bill_port_counters` (
  `port_id` int(10) unsigned NOT NULL,
  `timestamp` timestamp NOT NULL DEFAULT current_timestamp(),
  `in_counter` bigint(20) DEFAULT NULL,
  `in_delta` bigint(20) NOT NULL DEFAULT 0,
  `out_counter` bigint(20) DEFAULT NULL,
  `out_delta` bigint(20) NOT NULL DEFAULT 0,
  `bill_id` int(10) unsigned NOT NULL,
  PRIMARY KEY (`port_id`,`bill_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `bill_port_counters`
--

LOCK TABLES `bill_port_counters` WRITE;
/*!40000 ALTER TABLE `bill_port_counters` DISABLE KEYS */;
/*!40000 ALTER TABLE `bill_port_counters` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `bill_ports`
--

DROP TABLE IF EXISTS `bill_ports`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `bill_ports` (
  `bill_id` int(10) unsigned NOT NULL,
  `port_id` int(10) unsigned NOT NULL,
  `bill_port_autoadded` tinyint(1) NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `bill_ports`
--

LOCK TABLES `bill_ports` WRITE;
/*!40000 ALTER TABLE `bill_ports` DISABLE KEYS */;
/*!40000 ALTER TABLE `bill_ports` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `bills`
--

DROP TABLE IF EXISTS `bills`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `bills` (
  `bill_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `bill_name` text COLLATE utf8_unicode_ci NOT NULL,
  `bill_type` text COLLATE utf8_unicode_ci NOT NULL,
  `bill_cdr` bigint(20) DEFAULT NULL,
  `bill_day` int(11) NOT NULL DEFAULT 1,
  `bill_quota` bigint(20) DEFAULT NULL,
  `rate_95th_in` bigint(20) NOT NULL,
  `rate_95th_out` bigint(20) NOT NULL,
  `rate_95th` bigint(20) NOT NULL,
  `dir_95th` varchar(3) COLLATE utf8_unicode_ci NOT NULL,
  `total_data` bigint(20) NOT NULL,
  `total_data_in` bigint(20) NOT NULL,
  `total_data_out` bigint(20) NOT NULL,
  `rate_average_in` bigint(20) NOT NULL,
  `rate_average_out` bigint(20) NOT NULL,
  `rate_average` bigint(20) NOT NULL,
  `bill_last_calc` datetime NOT NULL,
  `bill_custid` varchar(64) COLLATE utf8_unicode_ci NOT NULL,
  `bill_ref` varchar(64) COLLATE utf8_unicode_ci NOT NULL,
  `bill_notes` varchar(256) COLLATE utf8_unicode_ci NOT NULL,
  `bill_autoadded` tinyint(1) NOT NULL,
  PRIMARY KEY (`bill_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `bills`
--

LOCK TABLES `bills` WRITE;
/*!40000 ALTER TABLE `bills` DISABLE KEYS */;
/*!40000 ALTER TABLE `bills` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `callback`
--

DROP TABLE IF EXISTS `callback`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `callback` (
  `callback_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `name` char(64) COLLATE utf8_unicode_ci NOT NULL,
  `value` char(64) COLLATE utf8_unicode_ci NOT NULL,
  PRIMARY KEY (`callback_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `callback`
--

LOCK TABLES `callback` WRITE;
/*!40000 ALTER TABLE `callback` DISABLE KEYS */;
/*!40000 ALTER TABLE `callback` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `cef_switching`
--

DROP TABLE IF EXISTS `cef_switching`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `cef_switching` (
  `cef_switching_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `device_id` int(10) unsigned NOT NULL,
  `entPhysicalIndex` int(11) NOT NULL,
  `afi` varchar(4) COLLATE utf8_unicode_ci NOT NULL,
  `cef_index` int(11) NOT NULL,
  `cef_path` varchar(16) COLLATE utf8_unicode_ci NOT NULL,
  `drop` int(11) NOT NULL,
  `punt` int(11) NOT NULL,
  `punt2host` int(11) NOT NULL,
  `drop_prev` int(11) NOT NULL,
  `punt_prev` int(11) NOT NULL,
  `punt2host_prev` int(11) NOT NULL,
  `updated` int(10) unsigned NOT NULL,
  `updated_prev` int(10) unsigned NOT NULL,
  PRIMARY KEY (`cef_switching_id`),
  UNIQUE KEY `device_id` (`device_id`,`entPhysicalIndex`,`afi`,`cef_index`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `cef_switching`
--

LOCK TABLES `cef_switching` WRITE;
/*!40000 ALTER TABLE `cef_switching` DISABLE KEYS */;
/*!40000 ALTER TABLE `cef_switching` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `ciscoASA`
--

DROP TABLE IF EXISTS `ciscoASA`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `ciscoASA` (
  `ciscoASA_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `device_id` int(10) unsigned NOT NULL,
  `oid` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `data` bigint(20) NOT NULL,
  `high_alert` bigint(20) NOT NULL,
  `low_alert` bigint(20) NOT NULL,
  `disabled` tinyint(4) NOT NULL DEFAULT 0,
  PRIMARY KEY (`ciscoASA_id`),
  KEY `device_id` (`device_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `ciscoASA`
--

LOCK TABLES `ciscoASA` WRITE;
/*!40000 ALTER TABLE `ciscoASA` DISABLE KEYS */;
/*!40000 ALTER TABLE `ciscoASA` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `component`
--

DROP TABLE IF EXISTS `component`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `component` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT 'ID for each component, unique index',
  `device_id` int(10) unsigned NOT NULL COMMENT 'device_id from the devices table',
  `type` varchar(50) COLLATE utf8_unicode_ci NOT NULL COMMENT 'name from the component_type table',
  `label` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL COMMENT 'Display label for the component',
  `status` tinyint(1) NOT NULL DEFAULT 0 COMMENT 'The status of the component, retreived from the device',
  `disabled` tinyint(1) NOT NULL DEFAULT 0 COMMENT 'Should this component be polled',
  `ignore` tinyint(1) NOT NULL DEFAULT 0 COMMENT 'Should this component be alerted on',
  `error` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL COMMENT 'Error message if in Alert state',
  PRIMARY KEY (`id`),
  KEY `device` (`device_id`),
  KEY `type` (`type`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `component`
--

LOCK TABLES `component` WRITE;
/*!40000 ALTER TABLE `component` DISABLE KEYS */;
/*!40000 ALTER TABLE `component` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `component_prefs`
--

DROP TABLE IF EXISTS `component_prefs`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `component_prefs` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT 'ID for each entry',
  `component` int(10) unsigned NOT NULL COMMENT 'id from the component table',
  `attribute` varchar(255) COLLATE utf8_unicode_ci NOT NULL COMMENT 'Attribute for the Component',
  `value` text COLLATE utf8_unicode_ci NOT NULL COMMENT 'Value for the Component',
  PRIMARY KEY (`id`),
  KEY `component` (`component`),
  CONSTRAINT `component_prefs_ibfk_1` FOREIGN KEY (`component`) REFERENCES `component` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `component_prefs`
--

LOCK TABLES `component_prefs` WRITE;
/*!40000 ALTER TABLE `component_prefs` DISABLE KEYS */;
/*!40000 ALTER TABLE `component_prefs` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `component_statuslog`
--

DROP TABLE IF EXISTS `component_statuslog`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `component_statuslog` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT 'ID for each log entry, unique index',
  `component_id` int(10) unsigned NOT NULL COMMENT 'id from the component table',
  `status` tinyint(1) NOT NULL DEFAULT 0 COMMENT 'The status that the component was changed TO',
  `message` text COLLATE utf8_unicode_ci DEFAULT NULL,
  `timestamp` timestamp NOT NULL DEFAULT current_timestamp() COMMENT 'When the status of the component was changed',
  PRIMARY KEY (`id`),
  KEY `device` (`component_id`),
  CONSTRAINT `component_statuslog_ibfk_1` FOREIGN KEY (`component_id`) REFERENCES `component` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `component_statuslog`
--

LOCK TABLES `component_statuslog` WRITE;
/*!40000 ALTER TABLE `component_statuslog` DISABLE KEYS */;
/*!40000 ALTER TABLE `component_statuslog` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `config`
--

DROP TABLE IF EXISTS `config`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `config` (
  `config_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `config_name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `config_value` varchar(512) COLLATE utf8_unicode_ci NOT NULL,
  `config_default` varchar(512) COLLATE utf8_unicode_ci NOT NULL,
  `config_descr` varchar(100) COLLATE utf8_unicode_ci NOT NULL,
  `config_group` varchar(50) COLLATE utf8_unicode_ci NOT NULL,
  `config_group_order` int(11) NOT NULL DEFAULT 0,
  `config_sub_group` varchar(50) COLLATE utf8_unicode_ci NOT NULL,
  `config_sub_group_order` int(11) NOT NULL DEFAULT 0,
  `config_hidden` enum('0','1') COLLATE utf8_unicode_ci NOT NULL DEFAULT '0',
  `config_disabled` enum('0','1') COLLATE utf8_unicode_ci NOT NULL DEFAULT '0',
  PRIMARY KEY (`config_id`),
  UNIQUE KEY `uniqueindex_configname` (`config_name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `config`
--

LOCK TABLES `config` WRITE;
/*!40000 ALTER TABLE `config` DISABLE KEYS */;
/*!40000 ALTER TABLE `config` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `customers`
--

DROP TABLE IF EXISTS `customers`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `customers` (
  `customer_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `username` char(64) COLLATE utf8_unicode_ci NOT NULL,
  `password` char(32) COLLATE utf8_unicode_ci NOT NULL,
  `string` char(64) COLLATE utf8_unicode_ci NOT NULL,
  `level` tinyint(4) NOT NULL DEFAULT 0,
  PRIMARY KEY (`customer_id`),
  UNIQUE KEY `username` (`username`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `customers`
--

LOCK TABLES `customers` WRITE;
/*!40000 ALTER TABLE `customers` DISABLE KEYS */;
/*!40000 ALTER TABLE `customers` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `dashboards`
--

DROP TABLE IF EXISTS `dashboards`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `dashboards` (
  `dashboard_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `user_id` int(10) unsigned NOT NULL DEFAULT 0,
  `dashboard_name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `access` tinyint(1) NOT NULL DEFAULT 0,
  PRIMARY KEY (`dashboard_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `dashboards`
--

LOCK TABLES `dashboards` WRITE;
/*!40000 ALTER TABLE `dashboards` DISABLE KEYS */;
/*!40000 ALTER TABLE `dashboards` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `dbSchema`
--

DROP TABLE IF EXISTS `dbSchema`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `dbSchema` (
  `version` int(11) NOT NULL DEFAULT 0,
  PRIMARY KEY (`version`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `dbSchema`
--

LOCK TABLES `dbSchema` WRITE;
/*!40000 ALTER TABLE `dbSchema` DISABLE KEYS */;
/*!40000 ALTER TABLE `dbSchema` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `device_graphs`
--

DROP TABLE IF EXISTS `device_graphs`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `device_graphs` (
  `device_id` int(10) unsigned NOT NULL,
  `graph` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  KEY `device_id` (`device_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `device_graphs`
--

LOCK TABLES `device_graphs` WRITE;
/*!40000 ALTER TABLE `device_graphs` DISABLE KEYS */;
/*!40000 ALTER TABLE `device_graphs` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `device_group_device`
--

DROP TABLE IF EXISTS `device_group_device`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `device_group_device` (
  `device_group_id` int(10) unsigned NOT NULL,
  `device_id` int(10) unsigned NOT NULL,
  PRIMARY KEY (`device_group_id`,`device_id`),
  KEY `device_group_device_device_group_id_index` (`device_group_id`),
  KEY `device_group_device_device_id_index` (`device_id`),
  CONSTRAINT `device_group_device_device_group_id_foreign` FOREIGN KEY (`device_group_id`) REFERENCES `device_groups` (`id`) ON DELETE CASCADE,
  CONSTRAINT `device_group_device_device_id_foreign` FOREIGN KEY (`device_id`) REFERENCES `devices` (`device_id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `device_group_device`
--

LOCK TABLES `device_group_device` WRITE;
/*!40000 ALTER TABLE `device_group_device` DISABLE KEYS */;
/*!40000 ALTER TABLE `device_group_device` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `device_groups`
--

DROP TABLE IF EXISTS `device_groups`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `device_groups` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
  `desc` varchar(255) COLLATE utf8_unicode_ci DEFAULT '',
  `type` varchar(16) COLLATE utf8_unicode_ci NOT NULL DEFAULT 'dynamic',
  `rules` text COLLATE utf8_unicode_ci DEFAULT NULL,
  `pattern` text COLLATE utf8_unicode_ci DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `name` (`name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `device_groups`
--

LOCK TABLES `device_groups` WRITE;
/*!40000 ALTER TABLE `device_groups` DISABLE KEYS */;
/*!40000 ALTER TABLE `device_groups` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `device_mibs`
--

DROP TABLE IF EXISTS `device_mibs`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `device_mibs` (
  `device_id` int(10) unsigned NOT NULL,
  `module` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `mib` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `included_by` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `last_modified` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`device_id`,`module`,`mib`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `device_mibs`
--

LOCK TABLES `device_mibs` WRITE;
/*!40000 ALTER TABLE `device_mibs` DISABLE KEYS */;
/*!40000 ALTER TABLE `device_mibs` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `device_oids`
--

DROP TABLE IF EXISTS `device_oids`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `device_oids` (
  `device_id` int(10) unsigned NOT NULL,
  `oid` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `module` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `mib` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `object_type` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `value` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `numvalue` bigint(20) DEFAULT NULL,
  `last_modified` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`device_id`,`oid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `device_oids`
--

LOCK TABLES `device_oids` WRITE;
/*!40000 ALTER TABLE `device_oids` DISABLE KEYS */;
/*!40000 ALTER TABLE `device_oids` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `device_perf`
--

DROP TABLE IF EXISTS `device_perf`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `device_perf` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `device_id` int(10) unsigned NOT NULL,
  `timestamp` datetime NOT NULL,
  `xmt` int(11) NOT NULL,
  `rcv` int(11) NOT NULL,
  `loss` int(11) NOT NULL,
  `min` double(8,2) NOT NULL,
  `max` double(8,2) NOT NULL,
  `avg` double(8,2) NOT NULL,
  `debug` text COLLATE utf8_unicode_ci DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `device_id` (`device_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `device_perf`
--

LOCK TABLES `device_perf` WRITE;
/*!40000 ALTER TABLE `device_perf` DISABLE KEYS */;
/*!40000 ALTER TABLE `device_perf` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `device_relationships`
--

DROP TABLE IF EXISTS `device_relationships`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `device_relationships` (
  `parent_device_id` int(10) unsigned NOT NULL DEFAULT 0,
  `child_device_id` int(10) unsigned NOT NULL,
  PRIMARY KEY (`parent_device_id`,`child_device_id`),
  KEY `device_relationship_child_device_id_fk` (`child_device_id`),
  CONSTRAINT `device_relationship_child_device_id_fk` FOREIGN KEY (`child_device_id`) REFERENCES `devices` (`device_id`) ON DELETE CASCADE,
  CONSTRAINT `device_relationship_parent_device_id_fk` FOREIGN KEY (`parent_device_id`) REFERENCES `devices` (`device_id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `device_relationships`
--

LOCK TABLES `device_relationships` WRITE;
/*!40000 ALTER TABLE `device_relationships` DISABLE KEYS */;
/*!40000 ALTER TABLE `device_relationships` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `devices`
--

DROP TABLE IF EXISTS `devices`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `devices` (
  `device_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `hostname` varchar(128) COLLATE utf8_unicode_ci NOT NULL,
  `sysName` varchar(128) COLLATE utf8_unicode_ci DEFAULT NULL,
  `ip` varbinary(16) DEFAULT NULL,
  `community` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `authlevel` enum('noAuthNoPriv','authNoPriv','authPriv') COLLATE utf8_unicode_ci DEFAULT NULL,
  `authname` varchar(64) COLLATE utf8_unicode_ci DEFAULT NULL,
  `authpass` varchar(64) COLLATE utf8_unicode_ci DEFAULT NULL,
  `authalgo` enum('MD5','SHA') COLLATE utf8_unicode_ci DEFAULT NULL,
  `cryptopass` varchar(64) COLLATE utf8_unicode_ci DEFAULT NULL,
  `cryptoalgo` enum('AES','DES','') COLLATE utf8_unicode_ci DEFAULT NULL,
  `snmpver` varchar(4) COLLATE utf8_unicode_ci NOT NULL DEFAULT 'v2c',
  `port` smallint(5) unsigned NOT NULL DEFAULT 161,
  `transport` varchar(16) COLLATE utf8_unicode_ci NOT NULL DEFAULT 'udp',
  `timeout` int(11) DEFAULT NULL,
  `retries` int(11) DEFAULT NULL,
  `snmp_disable` tinyint(1) NOT NULL DEFAULT 0,
  `bgpLocalAs` int(10) unsigned DEFAULT NULL,
  `sysObjectID` varchar(128) COLLATE utf8_unicode_ci DEFAULT NULL,
  `sysDescr` text COLLATE utf8_unicode_ci DEFAULT NULL,
  `sysContact` text COLLATE utf8_unicode_ci DEFAULT NULL,
  `version` text COLLATE utf8_unicode_ci DEFAULT NULL,
  `hardware` text COLLATE utf8_unicode_ci DEFAULT NULL,
  `features` text COLLATE utf8_unicode_ci DEFAULT NULL,
  `location_id` int(10) unsigned DEFAULT NULL,
  `os` varchar(32) COLLATE utf8_unicode_ci DEFAULT NULL,
  `status` tinyint(1) NOT NULL DEFAULT 0,
  `status_reason` varchar(50) COLLATE utf8_unicode_ci NOT NULL,
  `ignore` tinyint(1) NOT NULL DEFAULT 0,
  `disabled` tinyint(1) NOT NULL DEFAULT 0,
  `uptime` bigint(20) DEFAULT NULL,
  `agent_uptime` int(10) unsigned NOT NULL DEFAULT 0,
  `last_polled` timestamp NULL DEFAULT NULL,
  `last_poll_attempted` timestamp NULL DEFAULT NULL,
  `last_polled_timetaken` double(5,2) DEFAULT NULL,
  `last_discovered_timetaken` double(5,2) DEFAULT NULL,
  `last_discovered` timestamp NULL DEFAULT NULL,
  `last_ping` timestamp NULL DEFAULT NULL,
  `last_ping_timetaken` double(8,2) DEFAULT NULL,
  `purpose` text COLLATE utf8_unicode_ci DEFAULT NULL,
  `type` varchar(20) COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
  `serial` text COLLATE utf8_unicode_ci DEFAULT NULL,
  `icon` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `poller_group` int(11) NOT NULL DEFAULT 0,
  `override_sysLocation` tinyint(1) DEFAULT 0,
  `notes` text COLLATE utf8_unicode_ci DEFAULT NULL,
  `port_association_mode` int(11) NOT NULL DEFAULT 1,
  `max_depth` int(11) NOT NULL DEFAULT 0,
  PRIMARY KEY (`device_id`),
  KEY `hostname` (`hostname`),
  KEY `sysName` (`sysName`),
  KEY `os` (`os`),
  KEY `status` (`status`),
  KEY `last_polled` (`last_polled`),
  KEY `last_poll_attempted` (`last_poll_attempted`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `devices`
--

LOCK TABLES `devices` WRITE;
/*!40000 ALTER TABLE `devices` DISABLE KEYS */;
/*!40000 ALTER TABLE `devices` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `devices_attribs`
--

DROP TABLE IF EXISTS `devices_attribs`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `devices_attribs` (
  `attrib_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `device_id` int(10) unsigned NOT NULL,
  `attrib_type` varchar(32) COLLATE utf8_unicode_ci NOT NULL,
  `attrib_value` text COLLATE utf8_unicode_ci NOT NULL,
  `updated` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`attrib_id`),
  KEY `device_id` (`device_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `devices_attribs`
--

LOCK TABLES `devices_attribs` WRITE;
/*!40000 ALTER TABLE `devices_attribs` DISABLE KEYS */;
/*!40000 ALTER TABLE `devices_attribs` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `devices_perms`
--

DROP TABLE IF EXISTS `devices_perms`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `devices_perms` (
  `user_id` int(10) unsigned NOT NULL,
  `device_id` int(10) unsigned NOT NULL,
  KEY `user_id` (`user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `devices_perms`
--

LOCK TABLES `devices_perms` WRITE;
/*!40000 ALTER TABLE `devices_perms` DISABLE KEYS */;
/*!40000 ALTER TABLE `devices_perms` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `entPhysical`
--

DROP TABLE IF EXISTS `entPhysical`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `entPhysical` (
  `entPhysical_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `device_id` int(10) unsigned NOT NULL,
  `entPhysicalIndex` int(11) NOT NULL,
  `entPhysicalDescr` text COLLATE utf8_unicode_ci NOT NULL,
  `entPhysicalClass` text COLLATE utf8_unicode_ci NOT NULL,
  `entPhysicalName` text COLLATE utf8_unicode_ci NOT NULL,
  `entPhysicalHardwareRev` varchar(64) COLLATE utf8_unicode_ci DEFAULT NULL,
  `entPhysicalFirmwareRev` varchar(64) COLLATE utf8_unicode_ci DEFAULT NULL,
  `entPhysicalSoftwareRev` varchar(64) COLLATE utf8_unicode_ci DEFAULT NULL,
  `entPhysicalAlias` varchar(32) COLLATE utf8_unicode_ci DEFAULT NULL,
  `entPhysicalAssetID` varchar(32) COLLATE utf8_unicode_ci DEFAULT NULL,
  `entPhysicalIsFRU` varchar(8) COLLATE utf8_unicode_ci DEFAULT NULL,
  `entPhysicalModelName` text COLLATE utf8_unicode_ci NOT NULL,
  `entPhysicalVendorType` text COLLATE utf8_unicode_ci DEFAULT NULL,
  `entPhysicalSerialNum` text COLLATE utf8_unicode_ci NOT NULL,
  `entPhysicalContainedIn` int(11) NOT NULL,
  `entPhysicalParentRelPos` int(11) NOT NULL,
  `entPhysicalMfgName` text COLLATE utf8_unicode_ci NOT NULL,
  `ifIndex` int(11) DEFAULT NULL,
  PRIMARY KEY (`entPhysical_id`),
  KEY `device_id` (`device_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `entPhysical`
--

LOCK TABLES `entPhysical` WRITE;
/*!40000 ALTER TABLE `entPhysical` DISABLE KEYS */;
/*!40000 ALTER TABLE `entPhysical` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `entPhysical_state`
--

DROP TABLE IF EXISTS `entPhysical_state`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `entPhysical_state` (
  `device_id` int(10) unsigned NOT NULL,
  `entPhysicalIndex` varchar(64) COLLATE utf8_unicode_ci NOT NULL,
  `subindex` varchar(64) COLLATE utf8_unicode_ci DEFAULT NULL,
  `group` varchar(64) COLLATE utf8_unicode_ci NOT NULL,
  `key` varchar(64) COLLATE utf8_unicode_ci NOT NULL,
  `value` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  KEY `device_id_index` (`device_id`,`entPhysicalIndex`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `entPhysical_state`
--

LOCK TABLES `entPhysical_state` WRITE;
/*!40000 ALTER TABLE `entPhysical_state` DISABLE KEYS */;
/*!40000 ALTER TABLE `entPhysical_state` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `entityState`
--

DROP TABLE IF EXISTS `entityState`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `entityState` (
  `entity_state_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `device_id` int(10) unsigned DEFAULT NULL,
  `entPhysical_id` int(10) unsigned DEFAULT NULL,
  `entStateLastChanged` datetime DEFAULT NULL,
  `entStateAdmin` int(11) DEFAULT NULL,
  `entStateOper` int(11) DEFAULT NULL,
  `entStateUsage` int(11) DEFAULT NULL,
  `entStateAlarm` text COLLATE utf8_unicode_ci DEFAULT NULL,
  `entStateStandby` int(11) DEFAULT NULL,
  PRIMARY KEY (`entity_state_id`),
  KEY `entityState_device_id_index` (`device_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `entityState`
--

LOCK TABLES `entityState` WRITE;
/*!40000 ALTER TABLE `entityState` DISABLE KEYS */;
/*!40000 ALTER TABLE `entityState` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `eventlog`
--

DROP TABLE IF EXISTS `eventlog`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `eventlog` (
  `event_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `device_id` int(10) unsigned DEFAULT NULL,
  `datetime` datetime NOT NULL DEFAULT '1970-01-02 00:00:01',
  `message` text COLLATE utf8_unicode_ci DEFAULT NULL,
  `type` varchar(64) COLLATE utf8_unicode_ci DEFAULT NULL,
  `reference` varchar(64) COLLATE utf8_unicode_ci DEFAULT NULL,
  `username` varchar(128) COLLATE utf8_unicode_ci DEFAULT NULL,
  `severity` tinyint(4) NOT NULL DEFAULT 2,
  PRIMARY KEY (`event_id`),
  KEY `device_id` (`device_id`),
  KEY `datetime` (`datetime`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `eventlog`
--

LOCK TABLES `eventlog` WRITE;
/*!40000 ALTER TABLE `eventlog` DISABLE KEYS */;
/*!40000 ALTER TABLE `eventlog` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `graph_types`
--

DROP TABLE IF EXISTS `graph_types`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `graph_types` (
  `graph_type` varchar(32) COLLATE utf8_unicode_ci NOT NULL,
  `graph_subtype` varchar(64) COLLATE utf8_unicode_ci NOT NULL,
  `graph_section` varchar(32) COLLATE utf8_unicode_ci NOT NULL,
  `graph_descr` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `graph_order` int(11) NOT NULL,
  PRIMARY KEY (`graph_type`,`graph_subtype`,`graph_section`),
  KEY `graph_type` (`graph_type`),
  KEY `graph_subtype` (`graph_subtype`),
  KEY `graph_section` (`graph_section`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `graph_types`
--

LOCK TABLES `graph_types` WRITE;
/*!40000 ALTER TABLE `graph_types` DISABLE KEYS */;
/*!40000 ALTER TABLE `graph_types` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `hrDevice`
--

DROP TABLE IF EXISTS `hrDevice`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `hrDevice` (
  `hrDevice_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `device_id` int(10) unsigned NOT NULL,
  `hrDeviceIndex` int(11) NOT NULL,
  `hrDeviceDescr` text COLLATE utf8_unicode_ci NOT NULL,
  `hrDeviceType` text COLLATE utf8_unicode_ci NOT NULL,
  `hrDeviceErrors` int(11) NOT NULL DEFAULT 0,
  `hrDeviceStatus` text COLLATE utf8_unicode_ci NOT NULL,
  `hrProcessorLoad` tinyint(4) DEFAULT NULL,
  PRIMARY KEY (`hrDevice_id`),
  KEY `device_id` (`device_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `hrDevice`
--

LOCK TABLES `hrDevice` WRITE;
/*!40000 ALTER TABLE `hrDevice` DISABLE KEYS */;
/*!40000 ALTER TABLE `hrDevice` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `ipsec_tunnels`
--

DROP TABLE IF EXISTS `ipsec_tunnels`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `ipsec_tunnels` (
  `tunnel_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `device_id` int(10) unsigned NOT NULL,
  `peer_port` int(10) unsigned NOT NULL,
  `peer_addr` varchar(64) COLLATE utf8_unicode_ci NOT NULL,
  `local_addr` varchar(64) COLLATE utf8_unicode_ci NOT NULL,
  `local_port` int(10) unsigned NOT NULL,
  `tunnel_name` varchar(96) COLLATE utf8_unicode_ci NOT NULL,
  `tunnel_status` varchar(11) COLLATE utf8_unicode_ci NOT NULL,
  PRIMARY KEY (`tunnel_id`),
  UNIQUE KEY `unique_index` (`device_id`,`peer_addr`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `ipsec_tunnels`
--

LOCK TABLES `ipsec_tunnels` WRITE;
/*!40000 ALTER TABLE `ipsec_tunnels` DISABLE KEYS */;
/*!40000 ALTER TABLE `ipsec_tunnels` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `ipv4_addresses`
--

DROP TABLE IF EXISTS `ipv4_addresses`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `ipv4_addresses` (
  `ipv4_address_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `ipv4_address` varchar(32) COLLATE utf8_unicode_ci NOT NULL,
  `ipv4_prefixlen` int(11) NOT NULL,
  `ipv4_network_id` varchar(32) COLLATE utf8_unicode_ci NOT NULL,
  `port_id` int(10) unsigned NOT NULL,
  `context_name` varchar(128) COLLATE utf8_unicode_ci DEFAULT NULL,
  PRIMARY KEY (`ipv4_address_id`),
  KEY `interface_id` (`port_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `ipv4_addresses`
--

LOCK TABLES `ipv4_addresses` WRITE;
/*!40000 ALTER TABLE `ipv4_addresses` DISABLE KEYS */;
/*!40000 ALTER TABLE `ipv4_addresses` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `ipv4_mac`
--

DROP TABLE IF EXISTS `ipv4_mac`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `ipv4_mac` (
  `port_id` int(10) unsigned NOT NULL,
  `device_id` int(10) unsigned DEFAULT NULL,
  `mac_address` varchar(32) COLLATE utf8_unicode_ci NOT NULL,
  `ipv4_address` varchar(32) COLLATE utf8_unicode_ci NOT NULL,
  `context_name` varchar(128) COLLATE utf8_unicode_ci NOT NULL,
  KEY `port_id` (`port_id`),
  KEY `mac_address` (`mac_address`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `ipv4_mac`
--

LOCK TABLES `ipv4_mac` WRITE;
/*!40000 ALTER TABLE `ipv4_mac` DISABLE KEYS */;
/*!40000 ALTER TABLE `ipv4_mac` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `ipv4_networks`
--

DROP TABLE IF EXISTS `ipv4_networks`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `ipv4_networks` (
  `ipv4_network_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `ipv4_network` varchar(64) COLLATE utf8_unicode_ci NOT NULL,
  `context_name` varchar(128) COLLATE utf8_unicode_ci DEFAULT NULL,
  PRIMARY KEY (`ipv4_network_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `ipv4_networks`
--

LOCK TABLES `ipv4_networks` WRITE;
/*!40000 ALTER TABLE `ipv4_networks` DISABLE KEYS */;
/*!40000 ALTER TABLE `ipv4_networks` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `ipv6_addresses`
--

DROP TABLE IF EXISTS `ipv6_addresses`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `ipv6_addresses` (
  `ipv6_address_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `ipv6_address` varchar(128) COLLATE utf8_unicode_ci NOT NULL,
  `ipv6_compressed` varchar(128) COLLATE utf8_unicode_ci NOT NULL,
  `ipv6_prefixlen` int(11) NOT NULL,
  `ipv6_origin` varchar(16) COLLATE utf8_unicode_ci NOT NULL,
  `ipv6_network_id` varchar(128) COLLATE utf8_unicode_ci NOT NULL,
  `port_id` int(10) unsigned NOT NULL,
  `context_name` varchar(128) COLLATE utf8_unicode_ci DEFAULT NULL,
  PRIMARY KEY (`ipv6_address_id`),
  KEY `interface_id` (`port_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `ipv6_addresses`
--

LOCK TABLES `ipv6_addresses` WRITE;
/*!40000 ALTER TABLE `ipv6_addresses` DISABLE KEYS */;
/*!40000 ALTER TABLE `ipv6_addresses` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `ipv6_networks`
--

DROP TABLE IF EXISTS `ipv6_networks`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `ipv6_networks` (
  `ipv6_network_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `ipv6_network` varchar(64) COLLATE utf8_unicode_ci NOT NULL,
  `context_name` varchar(128) COLLATE utf8_unicode_ci DEFAULT NULL,
  PRIMARY KEY (`ipv6_network_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `ipv6_networks`
--

LOCK TABLES `ipv6_networks` WRITE;
/*!40000 ALTER TABLE `ipv6_networks` DISABLE KEYS */;
/*!40000 ALTER TABLE `ipv6_networks` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `juniAtmVp`
--

DROP TABLE IF EXISTS `juniAtmVp`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `juniAtmVp` (
  `juniAtmVp_id` int(10) unsigned NOT NULL,
  `port_id` int(10) unsigned NOT NULL,
  `vp_id` int(10) unsigned NOT NULL,
  `vp_descr` varchar(32) COLLATE utf8_unicode_ci NOT NULL,
  KEY `port_id` (`port_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `juniAtmVp`
--

LOCK TABLES `juniAtmVp` WRITE;
/*!40000 ALTER TABLE `juniAtmVp` DISABLE KEYS */;
/*!40000 ALTER TABLE `juniAtmVp` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `links`
--

DROP TABLE IF EXISTS `links`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `links` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `local_port_id` int(10) unsigned DEFAULT NULL,
  `local_device_id` int(10) unsigned NOT NULL,
  `remote_port_id` int(10) unsigned DEFAULT NULL,
  `active` tinyint(1) NOT NULL DEFAULT 1,
  `protocol` varchar(11) COLLATE utf8_unicode_ci DEFAULT NULL,
  `remote_hostname` varchar(128) COLLATE utf8_unicode_ci NOT NULL,
  `remote_device_id` int(10) unsigned NOT NULL,
  `remote_port` varchar(128) COLLATE utf8_unicode_ci NOT NULL,
  `remote_platform` varchar(256) COLLATE utf8_unicode_ci DEFAULT NULL,
  `remote_version` varchar(256) COLLATE utf8_unicode_ci NOT NULL,
  PRIMARY KEY (`id`),
  KEY `local_device_id` (`local_device_id`,`remote_device_id`),
  KEY `src_if` (`local_port_id`),
  KEY `dst_if` (`remote_port_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `links`
--

LOCK TABLES `links` WRITE;
/*!40000 ALTER TABLE `links` DISABLE KEYS */;
/*!40000 ALTER TABLE `links` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `loadbalancer_rservers`
--

DROP TABLE IF EXISTS `loadbalancer_rservers`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `loadbalancer_rservers` (
  `rserver_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `farm_id` varchar(128) COLLATE utf8_unicode_ci NOT NULL,
  `device_id` int(10) unsigned NOT NULL,
  `StateDescr` varchar(64) COLLATE utf8_unicode_ci NOT NULL,
  PRIMARY KEY (`rserver_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `loadbalancer_rservers`
--

LOCK TABLES `loadbalancer_rservers` WRITE;
/*!40000 ALTER TABLE `loadbalancer_rservers` DISABLE KEYS */;
/*!40000 ALTER TABLE `loadbalancer_rservers` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `loadbalancer_vservers`
--

DROP TABLE IF EXISTS `loadbalancer_vservers`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `loadbalancer_vservers` (
  `classmap_id` int(10) unsigned NOT NULL,
  `classmap` varchar(128) COLLATE utf8_unicode_ci NOT NULL,
  `serverstate` varchar(64) COLLATE utf8_unicode_ci NOT NULL,
  `device_id` int(10) unsigned NOT NULL,
  KEY `device_id` (`device_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `loadbalancer_vservers`
--

LOCK TABLES `loadbalancer_vservers` WRITE;
/*!40000 ALTER TABLE `loadbalancer_vservers` DISABLE KEYS */;
/*!40000 ALTER TABLE `loadbalancer_vservers` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `locations`
--

DROP TABLE IF EXISTS `locations`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `locations` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `location` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `lat` double(10,6) DEFAULT NULL,
  `lng` double(10,6) DEFAULT NULL,
  `timestamp` datetime NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `locations_location_uindex` (`location`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `locations`
--

LOCK TABLES `locations` WRITE;
/*!40000 ALTER TABLE `locations` DISABLE KEYS */;
/*!40000 ALTER TABLE `locations` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `mac_accounting`
--

DROP TABLE IF EXISTS `mac_accounting`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `mac_accounting` (
  `ma_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `port_id` int(10) unsigned NOT NULL,
  `mac` varchar(32) COLLATE utf8_unicode_ci NOT NULL,
  `in_oid` varchar(128) COLLATE utf8_unicode_ci NOT NULL,
  `out_oid` varchar(128) COLLATE utf8_unicode_ci NOT NULL,
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
  KEY `interface_id` (`port_id`),
  KEY `interface_id_2` (`port_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `mac_accounting`
--

LOCK TABLES `mac_accounting` WRITE;
/*!40000 ALTER TABLE `mac_accounting` DISABLE KEYS */;
/*!40000 ALTER TABLE `mac_accounting` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `mefinfo`
--

DROP TABLE IF EXISTS `mefinfo`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `mefinfo` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `device_id` int(10) unsigned NOT NULL,
  `mefID` int(11) NOT NULL,
  `mefType` varchar(128) COLLATE utf8_unicode_ci NOT NULL,
  `mefIdent` varchar(128) COLLATE utf8_unicode_ci NOT NULL,
  `mefMTU` int(11) NOT NULL DEFAULT 1500,
  `mefAdmState` varchar(128) COLLATE utf8_unicode_ci NOT NULL,
  `mefRowState` varchar(128) COLLATE utf8_unicode_ci NOT NULL,
  PRIMARY KEY (`id`),
  KEY `device_id` (`device_id`),
  KEY `mefID` (`mefID`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `mefinfo`
--

LOCK TABLES `mefinfo` WRITE;
/*!40000 ALTER TABLE `mefinfo` DISABLE KEYS */;
/*!40000 ALTER TABLE `mefinfo` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `mempools`
--

DROP TABLE IF EXISTS `mempools`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `mempools` (
  `mempool_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `mempool_index` varchar(16) COLLATE utf8_unicode_ci NOT NULL,
  `entPhysicalIndex` int(11) DEFAULT NULL,
  `hrDeviceIndex` int(11) DEFAULT NULL,
  `mempool_type` varchar(32) COLLATE utf8_unicode_ci NOT NULL,
  `mempool_precision` int(11) NOT NULL DEFAULT 1,
  `mempool_descr` varchar(64) COLLATE utf8_unicode_ci NOT NULL,
  `device_id` int(10) unsigned NOT NULL,
  `mempool_perc` int(11) NOT NULL,
  `mempool_used` bigint(20) NOT NULL,
  `mempool_free` bigint(20) NOT NULL,
  `mempool_total` bigint(20) NOT NULL,
  `mempool_largestfree` bigint(20) DEFAULT NULL,
  `mempool_lowestfree` bigint(20) DEFAULT NULL,
  `mempool_deleted` tinyint(1) NOT NULL DEFAULT 0,
  `mempool_perc_warn` int(11) DEFAULT 75,
  PRIMARY KEY (`mempool_id`),
  KEY `device_id` (`device_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `mempools`
--

LOCK TABLES `mempools` WRITE;
/*!40000 ALTER TABLE `mempools` DISABLE KEYS */;
/*!40000 ALTER TABLE `mempools` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `mibdefs`
--

DROP TABLE IF EXISTS `mibdefs`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `mibdefs` (
  `module` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `mib` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `object_type` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `oid` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `syntax` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `description` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `max_access` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `status` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `included_by` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `last_modified` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`module`,`mib`,`object_type`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `mibdefs`
--

LOCK TABLES `mibdefs` WRITE;
/*!40000 ALTER TABLE `mibdefs` DISABLE KEYS */;
/*!40000 ALTER TABLE `mibdefs` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `migrations`
--

DROP TABLE IF EXISTS `migrations`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `migrations` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `migration` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `batch` int(11) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=142 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `migrations`
--

LOCK TABLES `migrations` WRITE;
/*!40000 ALTER TABLE `migrations` DISABLE KEYS */;
INSERT INTO `migrations` VALUES (1,'2018_07_03_091314_create_access_points_table',1),(2,'2018_07_03_091314_create_alert_device_map_table',1),(3,'2018_07_03_091314_create_alert_group_map_table',1),(4,'2018_07_03_091314_create_alert_log_table',1),(5,'2018_07_03_091314_create_alert_rules_table',1),(6,'2018_07_03_091314_create_alert_schedulables_table',1),(7,'2018_07_03_091314_create_alert_schedule_table',1),(8,'2018_07_03_091314_create_alert_template_map_table',1),(9,'2018_07_03_091314_create_alert_templates_table',1),(10,'2018_07_03_091314_create_alert_transport_groups_table',1),(11,'2018_07_03_091314_create_alert_transport_map_table',1),(12,'2018_07_03_091314_create_alert_transports_table',1),(13,'2018_07_03_091314_create_alerts_table',1),(14,'2018_07_03_091314_create_api_tokens_table',1),(15,'2018_07_03_091314_create_application_metrics_table',1),(16,'2018_07_03_091314_create_applications_table',1),(17,'2018_07_03_091314_create_authlog_table',1),(18,'2018_07_03_091314_create_bgpPeers_cbgp_table',1),(19,'2018_07_03_091314_create_bgpPeers_table',1),(20,'2018_07_03_091314_create_bill_data_table',1),(21,'2018_07_03_091314_create_bill_history_table',1),(22,'2018_07_03_091314_create_bill_perms_table',1),(23,'2018_07_03_091314_create_bill_port_counters_table',1),(24,'2018_07_03_091314_create_bill_ports_table',1),(25,'2018_07_03_091314_create_bills_table',1),(26,'2018_07_03_091314_create_callback_table',1),(27,'2018_07_03_091314_create_cef_switching_table',1),(28,'2018_07_03_091314_create_ciscoASA_table',1),(29,'2018_07_03_091314_create_component_prefs_table',1),(30,'2018_07_03_091314_create_component_statuslog_table',1),(31,'2018_07_03_091314_create_component_table',1),(32,'2018_07_03_091314_create_config_table',1),(33,'2018_07_03_091314_create_customers_table',1),(34,'2018_07_03_091314_create_dashboards_table',1),(35,'2018_07_03_091314_create_dbSchema_table',1),(36,'2018_07_03_091314_create_device_graphs_table',1),(37,'2018_07_03_091314_create_device_group_device_table',1),(38,'2018_07_03_091314_create_device_groups_table',1),(39,'2018_07_03_091314_create_device_mibs_table',1),(40,'2018_07_03_091314_create_device_oids_table',1),(41,'2018_07_03_091314_create_device_perf_table',1),(42,'2018_07_03_091314_create_device_relationships_table',1),(43,'2018_07_03_091314_create_devices_attribs_table',1),(44,'2018_07_03_091314_create_devices_perms_table',1),(45,'2018_07_03_091314_create_devices_table',1),(46,'2018_07_03_091314_create_entPhysical_state_table',1),(47,'2018_07_03_091314_create_entPhysical_table',1),(48,'2018_07_03_091314_create_entityState_table',1),(49,'2018_07_03_091314_create_eventlog_table',1),(50,'2018_07_03_091314_create_graph_types_table',1),(51,'2018_07_03_091314_create_hrDevice_table',1),(52,'2018_07_03_091314_create_ipsec_tunnels_table',1),(53,'2018_07_03_091314_create_ipv4_addresses_table',1),(54,'2018_07_03_091314_create_ipv4_mac_table',1),(55,'2018_07_03_091314_create_ipv4_networks_table',1),(56,'2018_07_03_091314_create_ipv6_addresses_table',1),(57,'2018_07_03_091314_create_ipv6_networks_table',1),(58,'2018_07_03_091314_create_juniAtmVp_table',1),(59,'2018_07_03_091314_create_links_table',1),(60,'2018_07_03_091314_create_loadbalancer_rservers_table',1),(61,'2018_07_03_091314_create_loadbalancer_vservers_table',1),(62,'2018_07_03_091314_create_locations_table',1),(63,'2018_07_03_091314_create_mac_accounting_table',1),(64,'2018_07_03_091314_create_mefinfo_table',1),(65,'2018_07_03_091314_create_mempools_table',1),(66,'2018_07_03_091314_create_mibdefs_table',1),(67,'2018_07_03_091314_create_munin_plugins_ds_table',1),(68,'2018_07_03_091314_create_munin_plugins_table',1),(69,'2018_07_03_091314_create_netscaler_vservers_table',1),(70,'2018_07_03_091314_create_notifications_attribs_table',1),(71,'2018_07_03_091314_create_notifications_table',1),(72,'2018_07_03_091314_create_ospf_areas_table',1),(73,'2018_07_03_091314_create_ospf_instances_table',1),(74,'2018_07_03_091314_create_ospf_nbrs_table',1),(75,'2018_07_03_091314_create_ospf_ports_table',1),(76,'2018_07_03_091314_create_packages_table',1),(77,'2018_07_03_091314_create_pdb_ix_peers_table',1),(78,'2018_07_03_091314_create_pdb_ix_table',1),(79,'2018_07_03_091314_create_perf_times_table',1),(80,'2018_07_03_091314_create_plugins_table',1),(81,'2018_07_03_091314_create_poller_cluster_stats_table',1),(82,'2018_07_03_091314_create_poller_cluster_table',1),(83,'2018_07_03_091314_create_poller_groups_table',1),(84,'2018_07_03_091314_create_pollers_table',1),(85,'2018_07_03_091314_create_ports_adsl_table',1),(86,'2018_07_03_091314_create_ports_fdb_table',1),(87,'2018_07_03_091314_create_ports_nac_table',1),(88,'2018_07_03_091314_create_ports_perms_table',1),(89,'2018_07_03_091314_create_ports_stack_table',1),(90,'2018_07_03_091314_create_ports_statistics_table',1),(91,'2018_07_03_091314_create_ports_stp_table',1),(92,'2018_07_03_091314_create_ports_table',1),(93,'2018_07_03_091314_create_ports_vlans_table',1),(94,'2018_07_03_091314_create_processes_table',1),(95,'2018_07_03_091314_create_processors_table',1),(96,'2018_07_03_091314_create_proxmox_ports_table',1),(97,'2018_07_03_091314_create_proxmox_table',1),(98,'2018_07_03_091314_create_pseudowires_table',1),(99,'2018_07_03_091314_create_route_table',1),(100,'2018_07_03_091314_create_sensors_table',1),(101,'2018_07_03_091314_create_sensors_to_state_indexes_table',1),(102,'2018_07_03_091314_create_services_table',1),(103,'2018_07_03_091314_create_session_table',1),(104,'2018_07_03_091314_create_slas_table',1),(105,'2018_07_03_091314_create_state_indexes_table',1),(106,'2018_07_03_091314_create_state_translations_table',1),(107,'2018_07_03_091314_create_storage_table',1),(108,'2018_07_03_091314_create_stp_table',1),(109,'2018_07_03_091314_create_syslog_table',1),(110,'2018_07_03_091314_create_tnmsneinfo_table',1),(111,'2018_07_03_091314_create_toner_table',1),(112,'2018_07_03_091314_create_transport_group_transport_table',1),(113,'2018_07_03_091314_create_ucd_diskio_table',1),(114,'2018_07_03_091314_create_users_prefs_table',1),(115,'2018_07_03_091314_create_users_table',1),(116,'2018_07_03_091314_create_users_widgets_table',1),(117,'2018_07_03_091314_create_vlans_table',1),(118,'2018_07_03_091314_create_vminfo_table',1),(119,'2018_07_03_091314_create_vrf_lite_cisco_table',1),(120,'2018_07_03_091314_create_vrfs_table',1),(121,'2018_07_03_091314_create_widgets_table',1),(122,'2018_07_03_091314_create_wireless_sensors_table',1),(123,'2018_07_03_091322_add_foreign_keys_to_component_prefs_table',1),(124,'2018_07_03_091322_add_foreign_keys_to_component_statuslog_table',1),(125,'2018_07_03_091322_add_foreign_keys_to_device_group_device_table',1),(126,'2018_07_03_091322_add_foreign_keys_to_device_relationships_table',1),(127,'2018_07_03_091322_add_foreign_keys_to_sensors_table',1),(128,'2018_07_03_091322_add_foreign_keys_to_sensors_to_state_indexes_table',1),(129,'2018_07_03_091322_add_foreign_keys_to_wireless_sensors_table',1),(130,'2019_01_16_132200_add_vlan_and_elapsed_to_nac',1),(131,'2019_01_16_195644_add_vrf_id_and_bgpLocalAs',1),(132,'2019_02_10_220000_add_dates_to_fdb',1),(133,'2019_05_12_202407_create_mpls_lsps_table',1),(134,'2019_05_12_202408_create_mpls_lsp_paths_table',1),(135,'2019_05_30_225937_device_groups_rewrite',1),(136,'2019_06_30_190400_create_mpls_sdps_table',1),(137,'2019_06_30_190401_create_mpls_sdp_binds_table',1),(138,'2019_06_30_190402_create_mpls_services_table',1),(139,'2019_07_03_132417_create_mpls_saps_table',1),(140,'2019_07_09_150217_update_users_widgets_settings',1),(141,'2019_09_05_153524_create_notifications_attribs_index',1);
/*!40000 ALTER TABLE `migrations` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `mpls_lsp_paths`
--

DROP TABLE IF EXISTS `mpls_lsp_paths`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `mpls_lsp_paths` (
  `lsp_path_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `lsp_id` int(10) unsigned NOT NULL,
  `path_oid` int(10) unsigned NOT NULL,
  `device_id` int(10) unsigned NOT NULL,
  `mplsLspPathRowStatus` enum('active','notInService','notReady','createAndGo','createAndWait','destroy') COLLATE utf8_unicode_ci NOT NULL,
  `mplsLspPathLastChange` bigint(20) NOT NULL,
  `mplsLspPathType` enum('other','primary','standby','secondary') COLLATE utf8_unicode_ci NOT NULL,
  `mplsLspPathBandwidth` int(10) unsigned NOT NULL,
  `mplsLspPathOperBandwidth` int(10) unsigned NOT NULL,
  `mplsLspPathAdminState` enum('noop','inService','outOfService') COLLATE utf8_unicode_ci NOT NULL,
  `mplsLspPathOperState` enum('unknown','inService','outOfService','transition') COLLATE utf8_unicode_ci NOT NULL,
  `mplsLspPathState` enum('unknown','active','inactive') COLLATE utf8_unicode_ci NOT NULL,
  `mplsLspPathFailCode` varchar(64) COLLATE utf8_unicode_ci NOT NULL,
  `mplsLspPathFailNodeAddr` varchar(32) COLLATE utf8_unicode_ci NOT NULL,
  `mplsLspPathMetric` int(10) unsigned NOT NULL,
  `mplsLspPathOperMetric` int(10) unsigned NOT NULL,
  `mplsLspPathTimeUp` bigint(20) DEFAULT NULL,
  `mplsLspPathTimeDown` bigint(20) DEFAULT NULL,
  `mplsLspPathTransitionCount` int(10) unsigned DEFAULT NULL,
  PRIMARY KEY (`lsp_path_id`),
  KEY `device_id` (`device_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `mpls_lsp_paths`
--

LOCK TABLES `mpls_lsp_paths` WRITE;
/*!40000 ALTER TABLE `mpls_lsp_paths` DISABLE KEYS */;
/*!40000 ALTER TABLE `mpls_lsp_paths` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `mpls_lsps`
--

DROP TABLE IF EXISTS `mpls_lsps`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `mpls_lsps` (
  `lsp_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `vrf_oid` int(10) unsigned NOT NULL,
  `lsp_oid` int(10) unsigned NOT NULL,
  `device_id` int(10) unsigned NOT NULL,
  `mplsLspRowStatus` enum('active','notInService','notReady','createAndGo','createAndWait','destroy') COLLATE utf8_unicode_ci NOT NULL,
  `mplsLspLastChange` bigint(20) DEFAULT NULL,
  `mplsLspName` varchar(64) COLLATE utf8_unicode_ci NOT NULL,
  `mplsLspAdminState` enum('noop','inService','outOfService') COLLATE utf8_unicode_ci NOT NULL,
  `mplsLspOperState` enum('unknown','inService','outOfService','transition') COLLATE utf8_unicode_ci NOT NULL,
  `mplsLspFromAddr` varchar(32) COLLATE utf8_unicode_ci NOT NULL,
  `mplsLspToAddr` varchar(32) COLLATE utf8_unicode_ci NOT NULL,
  `mplsLspType` enum('unknown','dynamic','static','bypassOnly','p2mpLsp','p2mpAuto','mplsTp','meshP2p','oneHopP2p','srTe','meshP2pSrTe','oneHopP2pSrTe') COLLATE utf8_unicode_ci NOT NULL,
  `mplsLspFastReroute` enum('true','false') COLLATE utf8_unicode_ci NOT NULL,
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
  KEY `device_id` (`device_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `mpls_lsps`
--

LOCK TABLES `mpls_lsps` WRITE;
/*!40000 ALTER TABLE `mpls_lsps` DISABLE KEYS */;
/*!40000 ALTER TABLE `mpls_lsps` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `mpls_saps`
--

DROP TABLE IF EXISTS `mpls_saps`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `mpls_saps` (
  `sap_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `svc_id` int(10) unsigned NOT NULL,
  `svc_oid` int(10) unsigned NOT NULL,
  `sapPortId` int(10) unsigned NOT NULL,
  `ifName` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `device_id` int(10) unsigned NOT NULL,
  `sapEncapValue` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `sapRowStatus` enum('active','notInService','notReady','createAndGo','createAndWait','destroy') COLLATE utf8_unicode_ci DEFAULT NULL,
  `sapType` enum('unknown','epipe','tls','vprn','ies','mirror','apipe','fpipe','ipipe','cpipe','intTls','evpnIsaTls') COLLATE utf8_unicode_ci DEFAULT NULL,
  `sapDescription` varchar(80) COLLATE utf8_unicode_ci DEFAULT NULL,
  `sapAdminStatus` enum('up','down') COLLATE utf8_unicode_ci DEFAULT NULL,
  `sapOperStatus` enum('up','down') COLLATE utf8_unicode_ci DEFAULT NULL,
  `sapLastMgmtChange` bigint(20) DEFAULT NULL,
  `sapLastStatusChange` bigint(20) DEFAULT NULL,
  PRIMARY KEY (`sap_id`),
  KEY `device_id` (`device_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `mpls_saps`
--

LOCK TABLES `mpls_saps` WRITE;
/*!40000 ALTER TABLE `mpls_saps` DISABLE KEYS */;
/*!40000 ALTER TABLE `mpls_saps` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `mpls_sdp_binds`
--

DROP TABLE IF EXISTS `mpls_sdp_binds`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `mpls_sdp_binds` (
  `bind_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `sdp_id` int(10) unsigned NOT NULL,
  `svc_id` int(10) unsigned NOT NULL,
  `sdp_oid` int(10) unsigned NOT NULL,
  `svc_oid` int(10) unsigned NOT NULL,
  `device_id` int(10) unsigned NOT NULL,
  `sdpBindRowStatus` enum('active','notInService','notReady','createAndGo','createAndWait','destroy') COLLATE utf8_unicode_ci DEFAULT NULL,
  `sdpBindAdminStatus` enum('up','down') COLLATE utf8_unicode_ci DEFAULT NULL,
  `sdpBindOperStatus` enum('up','down') COLLATE utf8_unicode_ci DEFAULT NULL,
  `sdpBindLastMgmtChange` bigint(20) DEFAULT NULL,
  `sdpBindLastStatusChange` bigint(20) DEFAULT NULL,
  `sdpBindType` enum('spoke','mesh') COLLATE utf8_unicode_ci DEFAULT NULL,
  `sdpBindVcType` enum('undef','ether','vlan','mirrior','atmSduatmCell','atmVcc','atmVpc','frDlci','ipipe','satopE1','satopT1','satopE3','satopT3','cesopsn','cesopsnCas') COLLATE utf8_unicode_ci DEFAULT NULL,
  `sdpBindBaseStatsIngFwdPackets` bigint(20) DEFAULT NULL,
  `sdpBindBaseStatsIngFwdOctets` bigint(20) DEFAULT NULL,
  `sdpBindBaseStatsEgrFwdPackets` bigint(20) DEFAULT NULL,
  `sdpBindBaseStatsEgrFwdOctets` bigint(20) DEFAULT NULL,
  PRIMARY KEY (`bind_id`),
  KEY `device_id` (`device_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `mpls_sdp_binds`
--

LOCK TABLES `mpls_sdp_binds` WRITE;
/*!40000 ALTER TABLE `mpls_sdp_binds` DISABLE KEYS */;
/*!40000 ALTER TABLE `mpls_sdp_binds` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `mpls_sdps`
--

DROP TABLE IF EXISTS `mpls_sdps`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `mpls_sdps` (
  `sdp_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `sdp_oid` int(10) unsigned NOT NULL,
  `device_id` int(10) unsigned NOT NULL,
  `sdpRowStatus` enum('active','notInService','notReady','createAndGo','createAndWait','destroy') COLLATE utf8_unicode_ci DEFAULT NULL,
  `sdpDelivery` enum('gre','mpls','l2tpv3','greethbridged') COLLATE utf8_unicode_ci DEFAULT NULL,
  `sdpDescription` varchar(80) COLLATE utf8_unicode_ci DEFAULT NULL,
  `sdpAdminStatus` enum('up','down') COLLATE utf8_unicode_ci DEFAULT NULL,
  `sdpOperStatus` enum('up','notAlive','notReady','invalidEgressInterface','transportTunnelDown','down') COLLATE utf8_unicode_ci DEFAULT NULL,
  `sdpAdminPathMtu` int(11) DEFAULT NULL,
  `sdpOperPathMtu` int(11) DEFAULT NULL,
  `sdpLastMgmtChange` bigint(20) DEFAULT NULL,
  `sdpLastStatusChange` bigint(20) DEFAULT NULL,
  `sdpActiveLspType` enum('not-applicable','rsvp','ldp','bgp','none','mplsTp','srIsis','srOspf','srTeLsp','fpe') COLLATE utf8_unicode_ci DEFAULT NULL,
  `sdpFarEndInetAddressType` enum('ipv4','ipv6') COLLATE utf8_unicode_ci DEFAULT NULL,
  `sdpFarEndInetAddress` varchar(46) COLLATE utf8_unicode_ci DEFAULT NULL,
  PRIMARY KEY (`sdp_id`),
  KEY `device_id` (`device_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `mpls_sdps`
--

LOCK TABLES `mpls_sdps` WRITE;
/*!40000 ALTER TABLE `mpls_sdps` DISABLE KEYS */;
/*!40000 ALTER TABLE `mpls_sdps` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `mpls_services`
--

DROP TABLE IF EXISTS `mpls_services`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `mpls_services` (
  `svc_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `svc_oid` int(10) unsigned NOT NULL,
  `device_id` int(10) unsigned NOT NULL,
  `svcRowStatus` enum('active','notInService','notReady','createAndGo','createAndWait','destroy') COLLATE utf8_unicode_ci DEFAULT NULL,
  `svcType` enum('unknown','epipe','tls','vprn','ies','mirror','apipe','fpipe','ipipe','cpipe','intTls','evpnIsaTls') COLLATE utf8_unicode_ci DEFAULT NULL,
  `svcCustId` int(10) unsigned DEFAULT NULL,
  `svcAdminStatus` enum('up','down') COLLATE utf8_unicode_ci DEFAULT NULL,
  `svcOperStatus` enum('up','down') COLLATE utf8_unicode_ci DEFAULT NULL,
  `svcDescription` varchar(80) COLLATE utf8_unicode_ci DEFAULT NULL,
  `svcMtu` int(11) DEFAULT NULL,
  `svcNumSaps` int(11) DEFAULT NULL,
  `svcNumSdps` int(11) DEFAULT NULL,
  `svcLastMgmtChange` bigint(20) DEFAULT NULL,
  `svcLastStatusChange` bigint(20) DEFAULT NULL,
  `svcVRouterId` int(11) DEFAULT NULL,
  `svcTlsMacLearning` enum('enabled','disabled') COLLATE utf8_unicode_ci DEFAULT NULL,
  `svcTlsStpAdminStatus` enum('enabled','disabled') COLLATE utf8_unicode_ci DEFAULT NULL,
  `svcTlsStpOperStatus` enum('up','down') COLLATE utf8_unicode_ci DEFAULT NULL,
  `svcTlsFdbTableSize` int(11) DEFAULT NULL,
  `svcTlsFdbNumEntries` int(11) DEFAULT NULL,
  PRIMARY KEY (`svc_id`),
  KEY `device_id` (`device_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `mpls_services`
--

LOCK TABLES `mpls_services` WRITE;
/*!40000 ALTER TABLE `mpls_services` DISABLE KEYS */;
/*!40000 ALTER TABLE `mpls_services` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `munin_plugins`
--

DROP TABLE IF EXISTS `munin_plugins`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `munin_plugins` (
  `mplug_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `device_id` int(10) unsigned NOT NULL,
  `mplug_type` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `mplug_instance` varchar(128) COLLATE utf8_unicode_ci DEFAULT NULL,
  `mplug_category` varchar(32) COLLATE utf8_unicode_ci DEFAULT NULL,
  `mplug_title` varchar(128) COLLATE utf8_unicode_ci DEFAULT NULL,
  `mplug_info` text COLLATE utf8_unicode_ci DEFAULT NULL,
  `mplug_vlabel` varchar(128) COLLATE utf8_unicode_ci DEFAULT NULL,
  `mplug_args` varchar(512) COLLATE utf8_unicode_ci DEFAULT NULL,
  `mplug_total` tinyint(1) NOT NULL DEFAULT 0,
  `mplug_graph` tinyint(1) NOT NULL DEFAULT 1,
  PRIMARY KEY (`mplug_id`),
  UNIQUE KEY `UNIQUE` (`device_id`,`mplug_type`),
  KEY `device_id` (`device_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `munin_plugins`
--

LOCK TABLES `munin_plugins` WRITE;
/*!40000 ALTER TABLE `munin_plugins` DISABLE KEYS */;
/*!40000 ALTER TABLE `munin_plugins` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `munin_plugins_ds`
--

DROP TABLE IF EXISTS `munin_plugins_ds`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `munin_plugins_ds` (
  `mplug_id` int(10) unsigned NOT NULL,
  `ds_name` varchar(32) COLLATE utf8_unicode_ci NOT NULL,
  `ds_type` enum('COUNTER','ABSOLUTE','DERIVE','GAUGE') COLLATE utf8_unicode_ci NOT NULL DEFAULT 'GAUGE',
  `ds_label` varchar(64) COLLATE utf8_unicode_ci NOT NULL,
  `ds_cdef` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `ds_draw` varchar(64) COLLATE utf8_unicode_ci NOT NULL,
  `ds_graph` enum('no','yes') COLLATE utf8_unicode_ci NOT NULL DEFAULT 'yes',
  `ds_info` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `ds_extinfo` text COLLATE utf8_unicode_ci NOT NULL,
  `ds_max` varchar(32) COLLATE utf8_unicode_ci NOT NULL,
  `ds_min` varchar(32) COLLATE utf8_unicode_ci NOT NULL,
  `ds_negative` varchar(32) COLLATE utf8_unicode_ci NOT NULL,
  `ds_warning` varchar(32) COLLATE utf8_unicode_ci NOT NULL,
  `ds_critical` varchar(32) COLLATE utf8_unicode_ci NOT NULL,
  `ds_colour` varchar(32) COLLATE utf8_unicode_ci NOT NULL,
  `ds_sum` text COLLATE utf8_unicode_ci NOT NULL,
  `ds_stack` text COLLATE utf8_unicode_ci NOT NULL,
  `ds_line` varchar(64) COLLATE utf8_unicode_ci NOT NULL,
  UNIQUE KEY `splug_id` (`mplug_id`,`ds_name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `munin_plugins_ds`
--

LOCK TABLES `munin_plugins_ds` WRITE;
/*!40000 ALTER TABLE `munin_plugins_ds` DISABLE KEYS */;
/*!40000 ALTER TABLE `munin_plugins_ds` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `netscaler_vservers`
--

DROP TABLE IF EXISTS `netscaler_vservers`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `netscaler_vservers` (
  `vsvr_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `device_id` int(10) unsigned NOT NULL,
  `vsvr_name` varchar(128) COLLATE utf8_unicode_ci NOT NULL,
  `vsvr_ip` varchar(128) COLLATE utf8_unicode_ci NOT NULL,
  `vsvr_port` int(11) NOT NULL,
  `vsvr_type` varchar(64) COLLATE utf8_unicode_ci NOT NULL,
  `vsvr_state` varchar(32) COLLATE utf8_unicode_ci NOT NULL,
  `vsvr_clients` int(11) NOT NULL,
  `vsvr_server` int(11) NOT NULL,
  `vsvr_req_rate` int(11) NOT NULL,
  `vsvr_bps_in` int(11) NOT NULL,
  `vsvr_bps_out` int(11) NOT NULL,
  PRIMARY KEY (`vsvr_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `netscaler_vservers`
--

LOCK TABLES `netscaler_vservers` WRITE;
/*!40000 ALTER TABLE `netscaler_vservers` DISABLE KEYS */;
/*!40000 ALTER TABLE `netscaler_vservers` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `notifications`
--

DROP TABLE IF EXISTS `notifications`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `notifications` (
  `notifications_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `title` varchar(255) COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
  `body` text COLLATE utf8_unicode_ci NOT NULL,
  `severity` int(11) DEFAULT 0 COMMENT '0=ok,1=warning,2=critical',
  `source` varchar(255) COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
  `checksum` varchar(128) COLLATE utf8_unicode_ci NOT NULL,
  `datetime` timestamp NOT NULL DEFAULT '1970-01-02 06:00:00',
  PRIMARY KEY (`notifications_id`),
  UNIQUE KEY `checksum` (`checksum`),
  KEY `notifications_severity_index` (`severity`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `notifications`
--

LOCK TABLES `notifications` WRITE;
/*!40000 ALTER TABLE `notifications` DISABLE KEYS */;
/*!40000 ALTER TABLE `notifications` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `notifications_attribs`
--

DROP TABLE IF EXISTS `notifications_attribs`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `notifications_attribs` (
  `attrib_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `notifications_id` int(10) unsigned NOT NULL,
  `user_id` int(10) unsigned NOT NULL,
  `key` varchar(255) COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
  `value` varchar(255) COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
  PRIMARY KEY (`attrib_id`),
  KEY `notifications_attribs_notifications_id_user_id_index` (`notifications_id`,`user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `notifications_attribs`
--

LOCK TABLES `notifications_attribs` WRITE;
/*!40000 ALTER TABLE `notifications_attribs` DISABLE KEYS */;
/*!40000 ALTER TABLE `notifications_attribs` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `ospf_areas`
--

DROP TABLE IF EXISTS `ospf_areas`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `ospf_areas` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `device_id` int(10) unsigned NOT NULL,
  `ospfAreaId` varchar(32) COLLATE utf8_unicode_ci NOT NULL,
  `ospfAuthType` varchar(64) COLLATE utf8_unicode_ci NOT NULL,
  `ospfImportAsExtern` varchar(128) COLLATE utf8_unicode_ci NOT NULL,
  `ospfSpfRuns` int(11) NOT NULL,
  `ospfAreaBdrRtrCount` int(11) NOT NULL,
  `ospfAsBdrRtrCount` int(11) NOT NULL,
  `ospfAreaLsaCount` int(11) NOT NULL,
  `ospfAreaLsaCksumSum` int(11) NOT NULL,
  `ospfAreaSummary` varchar(64) COLLATE utf8_unicode_ci NOT NULL,
  `ospfAreaStatus` varchar(64) COLLATE utf8_unicode_ci NOT NULL,
  `context_name` varchar(128) COLLATE utf8_unicode_ci DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `device_area` (`device_id`,`ospfAreaId`,`context_name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `ospf_areas`
--

LOCK TABLES `ospf_areas` WRITE;
/*!40000 ALTER TABLE `ospf_areas` DISABLE KEYS */;
/*!40000 ALTER TABLE `ospf_areas` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `ospf_instances`
--

DROP TABLE IF EXISTS `ospf_instances`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `ospf_instances` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `device_id` int(10) unsigned NOT NULL,
  `ospf_instance_id` int(10) unsigned NOT NULL,
  `ospfRouterId` varchar(32) COLLATE utf8_unicode_ci NOT NULL,
  `ospfAdminStat` varchar(32) COLLATE utf8_unicode_ci NOT NULL,
  `ospfVersionNumber` varchar(32) COLLATE utf8_unicode_ci NOT NULL,
  `ospfAreaBdrRtrStatus` varchar(32) COLLATE utf8_unicode_ci NOT NULL,
  `ospfASBdrRtrStatus` varchar(32) COLLATE utf8_unicode_ci NOT NULL,
  `ospfExternLsaCount` int(11) NOT NULL,
  `ospfExternLsaCksumSum` int(11) NOT NULL,
  `ospfTOSSupport` varchar(32) COLLATE utf8_unicode_ci NOT NULL,
  `ospfOriginateNewLsas` int(11) NOT NULL,
  `ospfRxNewLsas` int(11) NOT NULL,
  `ospfExtLsdbLimit` int(11) DEFAULT NULL,
  `ospfMulticastExtensions` int(11) DEFAULT NULL,
  `ospfExitOverflowInterval` int(11) DEFAULT NULL,
  `ospfDemandExtensions` varchar(32) COLLATE utf8_unicode_ci DEFAULT NULL,
  `context_name` varchar(128) COLLATE utf8_unicode_ci DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `device_id` (`device_id`,`ospf_instance_id`,`context_name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `ospf_instances`
--

LOCK TABLES `ospf_instances` WRITE;
/*!40000 ALTER TABLE `ospf_instances` DISABLE KEYS */;
/*!40000 ALTER TABLE `ospf_instances` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `ospf_nbrs`
--

DROP TABLE IF EXISTS `ospf_nbrs`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `ospf_nbrs` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `device_id` int(10) unsigned NOT NULL,
  `port_id` int(10) unsigned DEFAULT NULL,
  `ospf_nbr_id` varchar(32) COLLATE utf8_unicode_ci NOT NULL,
  `ospfNbrIpAddr` varchar(32) COLLATE utf8_unicode_ci NOT NULL,
  `ospfNbrAddressLessIndex` int(11) NOT NULL,
  `ospfNbrRtrId` varchar(32) COLLATE utf8_unicode_ci NOT NULL,
  `ospfNbrOptions` int(11) NOT NULL,
  `ospfNbrPriority` int(11) NOT NULL,
  `ospfNbrState` varchar(32) COLLATE utf8_unicode_ci NOT NULL,
  `ospfNbrEvents` int(11) NOT NULL,
  `ospfNbrLsRetransQLen` int(11) NOT NULL,
  `ospfNbmaNbrStatus` varchar(32) COLLATE utf8_unicode_ci NOT NULL,
  `ospfNbmaNbrPermanence` varchar(32) COLLATE utf8_unicode_ci NOT NULL,
  `ospfNbrHelloSuppressed` varchar(32) COLLATE utf8_unicode_ci NOT NULL,
  `context_name` varchar(128) COLLATE utf8_unicode_ci DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `device_id` (`device_id`,`ospf_nbr_id`,`context_name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `ospf_nbrs`
--

LOCK TABLES `ospf_nbrs` WRITE;
/*!40000 ALTER TABLE `ospf_nbrs` DISABLE KEYS */;
/*!40000 ALTER TABLE `ospf_nbrs` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `ospf_ports`
--

DROP TABLE IF EXISTS `ospf_ports`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `ospf_ports` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `device_id` int(10) unsigned NOT NULL,
  `port_id` int(10) unsigned NOT NULL,
  `ospf_port_id` varchar(32) COLLATE utf8_unicode_ci NOT NULL,
  `ospfIfIpAddress` varchar(32) COLLATE utf8_unicode_ci NOT NULL,
  `ospfAddressLessIf` int(11) NOT NULL,
  `ospfIfAreaId` varchar(32) COLLATE utf8_unicode_ci NOT NULL,
  `ospfIfType` varchar(32) COLLATE utf8_unicode_ci DEFAULT NULL,
  `ospfIfAdminStat` varchar(32) COLLATE utf8_unicode_ci DEFAULT NULL,
  `ospfIfRtrPriority` int(11) DEFAULT NULL,
  `ospfIfTransitDelay` int(11) DEFAULT NULL,
  `ospfIfRetransInterval` int(11) DEFAULT NULL,
  `ospfIfHelloInterval` int(11) DEFAULT NULL,
  `ospfIfRtrDeadInterval` int(11) DEFAULT NULL,
  `ospfIfPollInterval` int(11) DEFAULT NULL,
  `ospfIfState` varchar(32) COLLATE utf8_unicode_ci DEFAULT NULL,
  `ospfIfDesignatedRouter` varchar(32) COLLATE utf8_unicode_ci DEFAULT NULL,
  `ospfIfBackupDesignatedRouter` varchar(32) COLLATE utf8_unicode_ci DEFAULT NULL,
  `ospfIfEvents` int(11) DEFAULT NULL,
  `ospfIfAuthKey` varchar(128) COLLATE utf8_unicode_ci DEFAULT NULL,
  `ospfIfStatus` varchar(32) COLLATE utf8_unicode_ci DEFAULT NULL,
  `ospfIfMulticastForwarding` varchar(32) COLLATE utf8_unicode_ci DEFAULT NULL,
  `ospfIfDemand` varchar(32) COLLATE utf8_unicode_ci DEFAULT NULL,
  `ospfIfAuthType` varchar(32) COLLATE utf8_unicode_ci DEFAULT NULL,
  `context_name` varchar(128) COLLATE utf8_unicode_ci DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `device_id` (`device_id`,`ospf_port_id`,`context_name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `ospf_ports`
--

LOCK TABLES `ospf_ports` WRITE;
/*!40000 ALTER TABLE `ospf_ports` DISABLE KEYS */;
/*!40000 ALTER TABLE `ospf_ports` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `packages`
--

DROP TABLE IF EXISTS `packages`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `packages` (
  `pkg_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `device_id` int(10) unsigned NOT NULL,
  `name` varchar(64) COLLATE utf8_unicode_ci NOT NULL,
  `manager` varchar(16) COLLATE utf8_unicode_ci NOT NULL DEFAULT '1',
  `status` tinyint(1) NOT NULL,
  `version` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `build` varchar(64) COLLATE utf8_unicode_ci NOT NULL,
  `arch` varchar(16) COLLATE utf8_unicode_ci NOT NULL,
  `size` bigint(20) DEFAULT NULL,
  PRIMARY KEY (`pkg_id`),
  UNIQUE KEY `unique_key` (`device_id`,`name`,`manager`,`arch`,`version`,`build`),
  KEY `device_id` (`device_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `packages`
--

LOCK TABLES `packages` WRITE;
/*!40000 ALTER TABLE `packages` DISABLE KEYS */;
/*!40000 ALTER TABLE `packages` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `pdb_ix`
--

DROP TABLE IF EXISTS `pdb_ix`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `pdb_ix` (
  `pdb_ix_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `ix_id` int(10) unsigned NOT NULL,
  `name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `asn` int(10) unsigned NOT NULL,
  `timestamp` int(10) unsigned NOT NULL,
  PRIMARY KEY (`pdb_ix_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `pdb_ix`
--

LOCK TABLES `pdb_ix` WRITE;
/*!40000 ALTER TABLE `pdb_ix` DISABLE KEYS */;
/*!40000 ALTER TABLE `pdb_ix` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `pdb_ix_peers`
--

DROP TABLE IF EXISTS `pdb_ix_peers`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `pdb_ix_peers` (
  `pdb_ix_peers_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `ix_id` int(10) unsigned NOT NULL,
  `peer_id` int(10) unsigned NOT NULL,
  `remote_asn` int(10) unsigned NOT NULL,
  `remote_ipaddr4` varchar(15) COLLATE utf8_unicode_ci DEFAULT NULL,
  `remote_ipaddr6` varchar(128) COLLATE utf8_unicode_ci DEFAULT NULL,
  `name` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `timestamp` int(10) unsigned DEFAULT NULL,
  PRIMARY KEY (`pdb_ix_peers_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `pdb_ix_peers`
--

LOCK TABLES `pdb_ix_peers` WRITE;
/*!40000 ALTER TABLE `pdb_ix_peers` DISABLE KEYS */;
/*!40000 ALTER TABLE `pdb_ix_peers` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `perf_times`
--

DROP TABLE IF EXISTS `perf_times`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `perf_times` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `type` varchar(8) COLLATE utf8_unicode_ci NOT NULL,
  `doing` varchar(64) COLLATE utf8_unicode_ci NOT NULL,
  `start` int(10) unsigned NOT NULL,
  `duration` double(8,2) NOT NULL,
  `devices` int(10) unsigned NOT NULL,
  `poller` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  PRIMARY KEY (`id`),
  KEY `type` (`type`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `perf_times`
--

LOCK TABLES `perf_times` WRITE;
/*!40000 ALTER TABLE `perf_times` DISABLE KEYS */;
/*!40000 ALTER TABLE `perf_times` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `plugins`
--

DROP TABLE IF EXISTS `plugins`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `plugins` (
  `plugin_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `plugin_name` varchar(60) COLLATE utf8_unicode_ci NOT NULL,
  `plugin_active` int(11) NOT NULL,
  PRIMARY KEY (`plugin_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `plugins`
--

LOCK TABLES `plugins` WRITE;
/*!40000 ALTER TABLE `plugins` DISABLE KEYS */;
/*!40000 ALTER TABLE `plugins` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `poller_cluster`
--

DROP TABLE IF EXISTS `poller_cluster`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `poller_cluster` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `node_id` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `poller_name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `poller_version` varchar(255) COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
  `poller_groups` varchar(255) COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
  `last_report` datetime NOT NULL,
  `master` tinyint(1) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `poller_cluster_node_id_unique` (`node_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `poller_cluster`
--

LOCK TABLES `poller_cluster` WRITE;
/*!40000 ALTER TABLE `poller_cluster` DISABLE KEYS */;
/*!40000 ALTER TABLE `poller_cluster` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `poller_cluster_stats`
--

DROP TABLE IF EXISTS `poller_cluster_stats`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `poller_cluster_stats` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `parent_poller` int(10) unsigned NOT NULL DEFAULT 0,
  `poller_type` varchar(64) COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
  `depth` int(10) unsigned NOT NULL,
  `devices` int(10) unsigned NOT NULL,
  `worker_seconds` double unsigned NOT NULL,
  `workers` int(10) unsigned NOT NULL,
  `frequency` int(10) unsigned NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `parent_poller_poller_type` (`parent_poller`,`poller_type`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `poller_cluster_stats`
--

LOCK TABLES `poller_cluster_stats` WRITE;
/*!40000 ALTER TABLE `poller_cluster_stats` DISABLE KEYS */;
/*!40000 ALTER TABLE `poller_cluster_stats` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `poller_groups`
--

DROP TABLE IF EXISTS `poller_groups`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `poller_groups` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `group_name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `descr` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `poller_groups`
--

LOCK TABLES `poller_groups` WRITE;
/*!40000 ALTER TABLE `poller_groups` DISABLE KEYS */;
/*!40000 ALTER TABLE `poller_groups` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `pollers`
--

DROP TABLE IF EXISTS `pollers`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `pollers` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `poller_name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `last_polled` datetime NOT NULL,
  `devices` int(10) unsigned NOT NULL,
  `time_taken` double NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `poller_name` (`poller_name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `pollers`
--

LOCK TABLES `pollers` WRITE;
/*!40000 ALTER TABLE `pollers` DISABLE KEYS */;
/*!40000 ALTER TABLE `pollers` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `ports`
--

DROP TABLE IF EXISTS `ports`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `ports` (
  `port_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `device_id` int(10) unsigned NOT NULL DEFAULT 0,
  `port_descr_type` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `port_descr_descr` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `port_descr_circuit` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `port_descr_speed` varchar(32) COLLATE utf8_unicode_ci DEFAULT NULL,
  `port_descr_notes` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `ifDescr` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `ifName` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `portName` varchar(128) COLLATE utf8_unicode_ci DEFAULT NULL,
  `ifIndex` bigint(20) DEFAULT 0,
  `ifSpeed` bigint(20) DEFAULT NULL,
  `ifConnectorPresent` varchar(12) COLLATE utf8_unicode_ci DEFAULT NULL,
  `ifPromiscuousMode` varchar(12) COLLATE utf8_unicode_ci DEFAULT NULL,
  `ifHighSpeed` int(11) DEFAULT NULL,
  `ifOperStatus` varchar(16) COLLATE utf8_unicode_ci DEFAULT NULL,
  `ifOperStatus_prev` varchar(16) COLLATE utf8_unicode_ci DEFAULT NULL,
  `ifAdminStatus` varchar(16) COLLATE utf8_unicode_ci DEFAULT NULL,
  `ifAdminStatus_prev` varchar(16) COLLATE utf8_unicode_ci DEFAULT NULL,
  `ifDuplex` varchar(12) COLLATE utf8_unicode_ci DEFAULT NULL,
  `ifMtu` int(11) DEFAULT NULL,
  `ifType` text COLLATE utf8_unicode_ci DEFAULT NULL,
  `ifAlias` text COLLATE utf8_unicode_ci DEFAULT NULL,
  `ifPhysAddress` text COLLATE utf8_unicode_ci DEFAULT NULL,
  `ifHardType` varchar(64) COLLATE utf8_unicode_ci DEFAULT NULL,
  `ifLastChange` bigint(20) unsigned NOT NULL DEFAULT 0,
  `ifVlan` varchar(8) COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
  `ifTrunk` varchar(16) COLLATE utf8_unicode_ci DEFAULT NULL,
  `ifVrf` int(11) NOT NULL DEFAULT 0,
  `counter_in` int(11) DEFAULT NULL,
  `counter_out` int(11) DEFAULT NULL,
  `ignore` tinyint(1) NOT NULL DEFAULT 0,
  `disabled` tinyint(1) NOT NULL DEFAULT 0,
  `detailed` tinyint(1) NOT NULL DEFAULT 0,
  `deleted` tinyint(1) NOT NULL DEFAULT 0,
  `pagpOperationMode` varchar(32) COLLATE utf8_unicode_ci DEFAULT NULL,
  `pagpPortState` varchar(16) COLLATE utf8_unicode_ci DEFAULT NULL,
  `pagpPartnerDeviceId` varchar(48) COLLATE utf8_unicode_ci DEFAULT NULL,
  `pagpPartnerLearnMethod` varchar(16) COLLATE utf8_unicode_ci DEFAULT NULL,
  `pagpPartnerIfIndex` int(11) DEFAULT NULL,
  `pagpPartnerGroupIfIndex` int(11) DEFAULT NULL,
  `pagpPartnerDeviceName` varchar(128) COLLATE utf8_unicode_ci DEFAULT NULL,
  `pagpEthcOperationMode` varchar(16) COLLATE utf8_unicode_ci DEFAULT NULL,
  `pagpDeviceId` varchar(48) COLLATE utf8_unicode_ci DEFAULT NULL,
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
  UNIQUE KEY `device_ifIndex` (`device_id`,`ifIndex`),
  KEY `if_2` (`ifDescr`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `ports`
--

LOCK TABLES `ports` WRITE;
/*!40000 ALTER TABLE `ports` DISABLE KEYS */;
/*!40000 ALTER TABLE `ports` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `ports_adsl`
--

DROP TABLE IF EXISTS `ports_adsl`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `ports_adsl` (
  `port_id` int(10) unsigned NOT NULL,
  `port_adsl_updated` timestamp NOT NULL DEFAULT current_timestamp(),
  `adslLineCoding` varchar(8) COLLATE utf8_unicode_ci NOT NULL,
  `adslLineType` varchar(16) COLLATE utf8_unicode_ci NOT NULL,
  `adslAtucInvVendorID` varchar(8) COLLATE utf8_unicode_ci NOT NULL,
  `adslAtucInvVersionNumber` varchar(8) COLLATE utf8_unicode_ci NOT NULL,
  `adslAtucCurrSnrMgn` decimal(5,1) NOT NULL,
  `adslAtucCurrAtn` decimal(5,1) NOT NULL,
  `adslAtucCurrOutputPwr` decimal(5,1) NOT NULL,
  `adslAtucCurrAttainableRate` int(11) NOT NULL,
  `adslAtucChanCurrTxRate` int(11) NOT NULL,
  `adslAturInvSerialNumber` varchar(8) COLLATE utf8_unicode_ci NOT NULL,
  `adslAturInvVendorID` varchar(8) COLLATE utf8_unicode_ci NOT NULL,
  `adslAturInvVersionNumber` varchar(8) COLLATE utf8_unicode_ci NOT NULL,
  `adslAturChanCurrTxRate` int(11) NOT NULL,
  `adslAturCurrSnrMgn` decimal(5,1) NOT NULL,
  `adslAturCurrAtn` decimal(5,1) NOT NULL,
  `adslAturCurrOutputPwr` decimal(5,1) NOT NULL,
  `adslAturCurrAttainableRate` int(11) NOT NULL,
  UNIQUE KEY `interface_id` (`port_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `ports_adsl`
--

LOCK TABLES `ports_adsl` WRITE;
/*!40000 ALTER TABLE `ports_adsl` DISABLE KEYS */;
/*!40000 ALTER TABLE `ports_adsl` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `ports_fdb`
--

DROP TABLE IF EXISTS `ports_fdb`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `ports_fdb` (
  `ports_fdb_id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `port_id` int(10) unsigned NOT NULL,
  `mac_address` varchar(32) COLLATE utf8_unicode_ci NOT NULL,
  `vlan_id` int(10) unsigned NOT NULL,
  `device_id` int(10) unsigned NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`ports_fdb_id`),
  KEY `ports_fdb_port_id_index` (`port_id`),
  KEY `mac_address` (`mac_address`),
  KEY `ports_fdb_vlan_id_index` (`vlan_id`),
  KEY `ports_fdb_device_id_index` (`device_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `ports_fdb`
--

LOCK TABLES `ports_fdb` WRITE;
/*!40000 ALTER TABLE `ports_fdb` DISABLE KEYS */;
/*!40000 ALTER TABLE `ports_fdb` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `ports_nac`
--

DROP TABLE IF EXISTS `ports_nac`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `ports_nac` (
  `ports_nac_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `auth_id` varchar(50) COLLATE utf8_unicode_ci NOT NULL,
  `device_id` int(10) unsigned NOT NULL,
  `port_id` int(10) unsigned NOT NULL,
  `domain` varchar(50) COLLATE utf8_unicode_ci NOT NULL,
  `username` varchar(50) COLLATE utf8_unicode_ci NOT NULL,
  `mac_address` varchar(50) COLLATE utf8_unicode_ci NOT NULL,
  `ip_address` varchar(50) COLLATE utf8_unicode_ci NOT NULL,
  `host_mode` varchar(50) COLLATE utf8_unicode_ci NOT NULL,
  `authz_status` varchar(50) COLLATE utf8_unicode_ci NOT NULL,
  `authz_by` varchar(50) COLLATE utf8_unicode_ci NOT NULL,
  `authc_status` varchar(50) COLLATE utf8_unicode_ci NOT NULL,
  `method` varchar(50) COLLATE utf8_unicode_ci NOT NULL,
  `timeout` varchar(50) COLLATE utf8_unicode_ci NOT NULL,
  `time_left` varchar(50) COLLATE utf8_unicode_ci DEFAULT NULL,
  `vlan` int(10) unsigned DEFAULT NULL,
  `time_elapsed` varchar(50) COLLATE utf8_unicode_ci DEFAULT NULL,
  PRIMARY KEY (`ports_nac_id`),
  KEY `ports_nac_port_id_mac_address_index` (`port_id`,`mac_address`),
  KEY `ports_nac_device_id_index` (`device_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `ports_nac`
--

LOCK TABLES `ports_nac` WRITE;
/*!40000 ALTER TABLE `ports_nac` DISABLE KEYS */;
/*!40000 ALTER TABLE `ports_nac` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `ports_perms`
--

DROP TABLE IF EXISTS `ports_perms`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `ports_perms` (
  `user_id` int(10) unsigned NOT NULL,
  `port_id` int(10) unsigned NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `ports_perms`
--

LOCK TABLES `ports_perms` WRITE;
/*!40000 ALTER TABLE `ports_perms` DISABLE KEYS */;
/*!40000 ALTER TABLE `ports_perms` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `ports_stack`
--

DROP TABLE IF EXISTS `ports_stack`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `ports_stack` (
  `device_id` int(10) unsigned NOT NULL,
  `port_id_high` int(10) unsigned NOT NULL,
  `port_id_low` int(10) unsigned NOT NULL,
  `ifStackStatus` varchar(32) COLLATE utf8_unicode_ci NOT NULL,
  UNIQUE KEY `device_id` (`device_id`,`port_id_high`,`port_id_low`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `ports_stack`
--

LOCK TABLES `ports_stack` WRITE;
/*!40000 ALTER TABLE `ports_stack` DISABLE KEYS */;
/*!40000 ALTER TABLE `ports_stack` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `ports_statistics`
--

DROP TABLE IF EXISTS `ports_statistics`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `ports_statistics` (
  `port_id` int(10) unsigned NOT NULL,
  `ifInNUcastPkts` bigint(20) DEFAULT NULL,
  `ifInNUcastPkts_prev` bigint(20) DEFAULT NULL,
  `ifInNUcastPkts_delta` bigint(20) DEFAULT NULL,
  `ifInNUcastPkts_rate` int(11) DEFAULT NULL,
  `ifOutNUcastPkts` bigint(20) DEFAULT NULL,
  `ifOutNUcastPkts_prev` bigint(20) DEFAULT NULL,
  `ifOutNUcastPkts_delta` bigint(20) DEFAULT NULL,
  `ifOutNUcastPkts_rate` int(11) DEFAULT NULL,
  `ifInDiscards` bigint(20) DEFAULT NULL,
  `ifInDiscards_prev` bigint(20) DEFAULT NULL,
  `ifInDiscards_delta` bigint(20) DEFAULT NULL,
  `ifInDiscards_rate` int(11) DEFAULT NULL,
  `ifOutDiscards` bigint(20) DEFAULT NULL,
  `ifOutDiscards_prev` bigint(20) DEFAULT NULL,
  `ifOutDiscards_delta` bigint(20) DEFAULT NULL,
  `ifOutDiscards_rate` int(11) DEFAULT NULL,
  `ifInUnknownProtos` bigint(20) DEFAULT NULL,
  `ifInUnknownProtos_prev` bigint(20) DEFAULT NULL,
  `ifInUnknownProtos_delta` bigint(20) DEFAULT NULL,
  `ifInUnknownProtos_rate` int(11) DEFAULT NULL,
  `ifInBroadcastPkts` bigint(20) DEFAULT NULL,
  `ifInBroadcastPkts_prev` bigint(20) DEFAULT NULL,
  `ifInBroadcastPkts_delta` bigint(20) DEFAULT NULL,
  `ifInBroadcastPkts_rate` int(11) DEFAULT NULL,
  `ifOutBroadcastPkts` bigint(20) DEFAULT NULL,
  `ifOutBroadcastPkts_prev` bigint(20) DEFAULT NULL,
  `ifOutBroadcastPkts_delta` bigint(20) DEFAULT NULL,
  `ifOutBroadcastPkts_rate` int(11) DEFAULT NULL,
  `ifInMulticastPkts` bigint(20) DEFAULT NULL,
  `ifInMulticastPkts_prev` bigint(20) DEFAULT NULL,
  `ifInMulticastPkts_delta` bigint(20) DEFAULT NULL,
  `ifInMulticastPkts_rate` int(11) DEFAULT NULL,
  `ifOutMulticastPkts` bigint(20) DEFAULT NULL,
  `ifOutMulticastPkts_prev` bigint(20) DEFAULT NULL,
  `ifOutMulticastPkts_delta` bigint(20) DEFAULT NULL,
  `ifOutMulticastPkts_rate` int(11) DEFAULT NULL,
  PRIMARY KEY (`port_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `ports_statistics`
--

LOCK TABLES `ports_statistics` WRITE;
/*!40000 ALTER TABLE `ports_statistics` DISABLE KEYS */;
/*!40000 ALTER TABLE `ports_statistics` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `ports_stp`
--

DROP TABLE IF EXISTS `ports_stp`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `ports_stp` (
  `port_stp_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `device_id` int(10) unsigned NOT NULL,
  `port_id` int(10) unsigned NOT NULL,
  `priority` tinyint(3) unsigned NOT NULL,
  `state` varchar(11) COLLATE utf8_unicode_ci NOT NULL,
  `enable` varchar(8) COLLATE utf8_unicode_ci NOT NULL,
  `pathCost` int(10) unsigned NOT NULL,
  `designatedRoot` varchar(32) COLLATE utf8_unicode_ci NOT NULL,
  `designatedCost` smallint(5) unsigned NOT NULL,
  `designatedBridge` varchar(32) COLLATE utf8_unicode_ci NOT NULL,
  `designatedPort` mediumint(9) NOT NULL,
  `forwardTransitions` int(10) unsigned NOT NULL,
  PRIMARY KEY (`port_stp_id`),
  UNIQUE KEY `device_id` (`device_id`,`port_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `ports_stp`
--

LOCK TABLES `ports_stp` WRITE;
/*!40000 ALTER TABLE `ports_stp` DISABLE KEYS */;
/*!40000 ALTER TABLE `ports_stp` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `ports_vlans`
--

DROP TABLE IF EXISTS `ports_vlans`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `ports_vlans` (
  `port_vlan_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `device_id` int(10) unsigned NOT NULL,
  `port_id` int(10) unsigned NOT NULL,
  `vlan` int(11) NOT NULL,
  `baseport` int(11) NOT NULL,
  `priority` bigint(20) NOT NULL,
  `state` varchar(16) COLLATE utf8_unicode_ci NOT NULL,
  `cost` int(11) NOT NULL,
  `untagged` tinyint(1) NOT NULL DEFAULT 0,
  PRIMARY KEY (`port_vlan_id`),
  UNIQUE KEY `unique` (`device_id`,`port_id`,`vlan`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `ports_vlans`
--

LOCK TABLES `ports_vlans` WRITE;
/*!40000 ALTER TABLE `ports_vlans` DISABLE KEYS */;
/*!40000 ALTER TABLE `ports_vlans` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `processes`
--

DROP TABLE IF EXISTS `processes`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `processes` (
  `device_id` int(10) unsigned NOT NULL,
  `pid` int(11) NOT NULL,
  `vsz` int(11) NOT NULL,
  `rss` int(11) NOT NULL,
  `cputime` varchar(12) COLLATE utf8_unicode_ci NOT NULL,
  `user` varchar(50) COLLATE utf8_unicode_ci NOT NULL,
  `command` text COLLATE utf8_unicode_ci NOT NULL,
  KEY `device_id` (`device_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `processes`
--

LOCK TABLES `processes` WRITE;
/*!40000 ALTER TABLE `processes` DISABLE KEYS */;
/*!40000 ALTER TABLE `processes` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `processors`
--

DROP TABLE IF EXISTS `processors`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `processors` (
  `processor_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `entPhysicalIndex` int(11) NOT NULL DEFAULT 0,
  `hrDeviceIndex` int(11) DEFAULT NULL,
  `device_id` int(10) unsigned NOT NULL,
  `processor_oid` varchar(128) COLLATE utf8_unicode_ci NOT NULL,
  `processor_index` varchar(32) COLLATE utf8_unicode_ci NOT NULL,
  `processor_type` varchar(16) COLLATE utf8_unicode_ci NOT NULL,
  `processor_usage` int(11) NOT NULL,
  `processor_descr` varchar(64) COLLATE utf8_unicode_ci NOT NULL,
  `processor_precision` int(11) NOT NULL DEFAULT 1,
  `processor_perc_warn` int(11) DEFAULT 75,
  PRIMARY KEY (`processor_id`),
  KEY `device_id` (`device_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `processors`
--

LOCK TABLES `processors` WRITE;
/*!40000 ALTER TABLE `processors` DISABLE KEYS */;
/*!40000 ALTER TABLE `processors` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `proxmox`
--

DROP TABLE IF EXISTS `proxmox`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `proxmox` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `device_id` int(10) unsigned NOT NULL DEFAULT 0,
  `vmid` int(11) NOT NULL,
  `cluster` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `description` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `last_seen` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `cluster_vm` (`cluster`,`vmid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `proxmox`
--

LOCK TABLES `proxmox` WRITE;
/*!40000 ALTER TABLE `proxmox` DISABLE KEYS */;
/*!40000 ALTER TABLE `proxmox` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `proxmox_ports`
--

DROP TABLE IF EXISTS `proxmox_ports`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `proxmox_ports` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `vm_id` int(11) NOT NULL,
  `port` varchar(10) COLLATE utf8_unicode_ci NOT NULL,
  `last_seen` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `vm_port` (`vm_id`,`port`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `proxmox_ports`
--

LOCK TABLES `proxmox_ports` WRITE;
/*!40000 ALTER TABLE `proxmox_ports` DISABLE KEYS */;
/*!40000 ALTER TABLE `proxmox_ports` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `pseudowires`
--

DROP TABLE IF EXISTS `pseudowires`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `pseudowires` (
  `pseudowire_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `device_id` int(10) unsigned NOT NULL,
  `port_id` int(10) unsigned NOT NULL,
  `peer_device_id` int(10) unsigned NOT NULL,
  `peer_ldp_id` int(11) NOT NULL,
  `cpwVcID` int(11) NOT NULL,
  `cpwOid` int(11) NOT NULL,
  `pw_type` varchar(32) COLLATE utf8_unicode_ci NOT NULL,
  `pw_psntype` varchar(32) COLLATE utf8_unicode_ci NOT NULL,
  `pw_local_mtu` int(11) NOT NULL,
  `pw_peer_mtu` int(11) NOT NULL,
  `pw_descr` varchar(128) COLLATE utf8_unicode_ci NOT NULL,
  PRIMARY KEY (`pseudowire_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `pseudowires`
--

LOCK TABLES `pseudowires` WRITE;
/*!40000 ALTER TABLE `pseudowires` DISABLE KEYS */;
/*!40000 ALTER TABLE `pseudowires` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `route`
--

DROP TABLE IF EXISTS `route`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `route` (
  `device_id` int(10) unsigned NOT NULL,
  `context_name` varchar(128) COLLATE utf8_unicode_ci NOT NULL,
  `ipRouteDest` varchar(39) COLLATE utf8_unicode_ci NOT NULL,
  `ipRouteIfIndex` varchar(256) COLLATE utf8_unicode_ci DEFAULT NULL,
  `ipRouteMetric` varchar(256) COLLATE utf8_unicode_ci NOT NULL,
  `ipRouteNextHop` varchar(39) COLLATE utf8_unicode_ci NOT NULL,
  `ipRouteType` varchar(256) COLLATE utf8_unicode_ci NOT NULL,
  `ipRouteProto` varchar(256) COLLATE utf8_unicode_ci NOT NULL,
  `discoveredAt` int(10) unsigned NOT NULL,
  `ipRouteMask` varchar(256) COLLATE utf8_unicode_ci NOT NULL,
  KEY `device` (`device_id`,`context_name`,`ipRouteDest`,`ipRouteNextHop`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `route`
--

LOCK TABLES `route` WRITE;
/*!40000 ALTER TABLE `route` DISABLE KEYS */;
/*!40000 ALTER TABLE `route` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `sensors`
--

DROP TABLE IF EXISTS `sensors`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `sensors` (
  `sensor_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `sensor_deleted` tinyint(1) NOT NULL DEFAULT 0,
  `sensor_class` varchar(64) COLLATE utf8_unicode_ci NOT NULL,
  `device_id` int(10) unsigned NOT NULL DEFAULT 0,
  `poller_type` varchar(16) COLLATE utf8_unicode_ci NOT NULL DEFAULT 'snmp',
  `sensor_oid` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `sensor_index` varchar(128) COLLATE utf8_unicode_ci DEFAULT NULL,
  `sensor_type` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `sensor_descr` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `group` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `sensor_divisor` bigint(20) NOT NULL DEFAULT 1,
  `sensor_multiplier` int(11) NOT NULL DEFAULT 1,
  `sensor_current` double DEFAULT NULL,
  `sensor_limit` double DEFAULT NULL,
  `sensor_limit_warn` double DEFAULT NULL,
  `sensor_limit_low` double DEFAULT NULL,
  `sensor_limit_low_warn` double DEFAULT NULL,
  `sensor_alert` tinyint(1) NOT NULL DEFAULT 1,
  `sensor_custom` enum('No','Yes') COLLATE utf8_unicode_ci NOT NULL DEFAULT 'No',
  `entPhysicalIndex` varchar(16) COLLATE utf8_unicode_ci DEFAULT NULL,
  `entPhysicalIndex_measured` varchar(16) COLLATE utf8_unicode_ci DEFAULT NULL,
  `lastupdate` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `sensor_prev` double DEFAULT NULL,
  `user_func` varchar(100) COLLATE utf8_unicode_ci DEFAULT NULL,
  PRIMARY KEY (`sensor_id`),
  KEY `sensor_class` (`sensor_class`),
  KEY `sensor_host` (`device_id`),
  KEY `sensor_type` (`sensor_type`),
  CONSTRAINT `sensors_device_id_foreign` FOREIGN KEY (`device_id`) REFERENCES `devices` (`device_id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `sensors`
--

LOCK TABLES `sensors` WRITE;
/*!40000 ALTER TABLE `sensors` DISABLE KEYS */;
/*!40000 ALTER TABLE `sensors` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `sensors_to_state_indexes`
--

DROP TABLE IF EXISTS `sensors_to_state_indexes`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `sensors_to_state_indexes` (
  `sensors_to_state_translations_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `sensor_id` int(10) unsigned NOT NULL,
  `state_index_id` int(10) unsigned NOT NULL,
  PRIMARY KEY (`sensors_to_state_translations_id`),
  UNIQUE KEY `sensor_id_state_index_id` (`sensor_id`,`state_index_id`),
  KEY `state_index_id` (`state_index_id`),
  CONSTRAINT `sensors_to_state_indexes_ibfk_1` FOREIGN KEY (`state_index_id`) REFERENCES `state_indexes` (`state_index_id`),
  CONSTRAINT `sensors_to_state_indexes_sensor_id_foreign` FOREIGN KEY (`sensor_id`) REFERENCES `sensors` (`sensor_id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `sensors_to_state_indexes`
--

LOCK TABLES `sensors_to_state_indexes` WRITE;
/*!40000 ALTER TABLE `sensors_to_state_indexes` DISABLE KEYS */;
/*!40000 ALTER TABLE `sensors_to_state_indexes` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `services`
--

DROP TABLE IF EXISTS `services`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `services` (
  `service_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `device_id` int(10) unsigned NOT NULL,
  `service_ip` text COLLATE utf8_unicode_ci NOT NULL,
  `service_type` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `service_desc` text COLLATE utf8_unicode_ci NOT NULL,
  `service_param` text COLLATE utf8_unicode_ci NOT NULL,
  `service_ignore` tinyint(1) NOT NULL,
  `service_status` tinyint(4) NOT NULL DEFAULT 0,
  `service_changed` int(10) unsigned NOT NULL DEFAULT 0,
  `service_message` text COLLATE utf8_unicode_ci NOT NULL,
  `service_disabled` tinyint(1) NOT NULL DEFAULT 0,
  `service_ds` text COLLATE utf8_unicode_ci NOT NULL,
  PRIMARY KEY (`service_id`),
  KEY `service_host` (`device_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `services`
--

LOCK TABLES `services` WRITE;
/*!40000 ALTER TABLE `services` DISABLE KEYS */;
/*!40000 ALTER TABLE `services` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `session`
--

DROP TABLE IF EXISTS `session`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `session` (
  `session_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `session_username` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `session_value` varchar(60) COLLATE utf8_unicode_ci NOT NULL,
  `session_token` varchar(60) COLLATE utf8_unicode_ci NOT NULL,
  `session_auth` varchar(16) COLLATE utf8_unicode_ci NOT NULL,
  `session_expiry` int(11) NOT NULL,
  PRIMARY KEY (`session_id`),
  UNIQUE KEY `session_value` (`session_value`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `session`
--

LOCK TABLES `session` WRITE;
/*!40000 ALTER TABLE `session` DISABLE KEYS */;
/*!40000 ALTER TABLE `session` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `slas`
--

DROP TABLE IF EXISTS `slas`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `slas` (
  `sla_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `device_id` int(10) unsigned NOT NULL,
  `sla_nr` int(11) NOT NULL,
  `owner` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `tag` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `rtt_type` varchar(16) COLLATE utf8_unicode_ci NOT NULL,
  `status` tinyint(1) NOT NULL,
  `opstatus` tinyint(1) NOT NULL DEFAULT 0,
  `deleted` tinyint(1) NOT NULL DEFAULT 0,
  PRIMARY KEY (`sla_id`),
  UNIQUE KEY `unique_key` (`device_id`,`sla_nr`),
  KEY `device_id` (`device_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `slas`
--

LOCK TABLES `slas` WRITE;
/*!40000 ALTER TABLE `slas` DISABLE KEYS */;
/*!40000 ALTER TABLE `slas` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `state_indexes`
--

DROP TABLE IF EXISTS `state_indexes`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `state_indexes` (
  `state_index_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `state_name` varchar(64) COLLATE utf8_unicode_ci NOT NULL,
  PRIMARY KEY (`state_index_id`),
  UNIQUE KEY `state_name` (`state_name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `state_indexes`
--

LOCK TABLES `state_indexes` WRITE;
/*!40000 ALTER TABLE `state_indexes` DISABLE KEYS */;
/*!40000 ALTER TABLE `state_indexes` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `state_translations`
--

DROP TABLE IF EXISTS `state_translations`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `state_translations` (
  `state_translation_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `state_index_id` int(10) unsigned NOT NULL,
  `state_descr` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `state_draw_graph` tinyint(1) NOT NULL,
  `state_value` smallint(6) NOT NULL DEFAULT 0,
  `state_generic_value` tinyint(1) NOT NULL,
  `state_lastupdated` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`state_translation_id`),
  UNIQUE KEY `state_index_id_value` (`state_index_id`,`state_value`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `state_translations`
--

LOCK TABLES `state_translations` WRITE;
/*!40000 ALTER TABLE `state_translations` DISABLE KEYS */;
/*!40000 ALTER TABLE `state_translations` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `storage`
--

DROP TABLE IF EXISTS `storage`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `storage` (
  `storage_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `device_id` int(10) unsigned NOT NULL,
  `storage_mib` varchar(16) COLLATE utf8_unicode_ci NOT NULL,
  `storage_index` varchar(64) COLLATE utf8_unicode_ci DEFAULT NULL,
  `storage_type` varchar(32) COLLATE utf8_unicode_ci DEFAULT NULL,
  `storage_descr` text COLLATE utf8_unicode_ci NOT NULL,
  `storage_size` bigint(20) NOT NULL,
  `storage_units` int(11) NOT NULL,
  `storage_used` bigint(20) NOT NULL DEFAULT 0,
  `storage_free` bigint(20) NOT NULL DEFAULT 0,
  `storage_perc` int(11) NOT NULL DEFAULT 0,
  `storage_perc_warn` int(11) DEFAULT 60,
  `storage_deleted` tinyint(1) NOT NULL DEFAULT 0,
  PRIMARY KEY (`storage_id`),
  UNIQUE KEY `index_unique` (`device_id`,`storage_mib`,`storage_index`),
  KEY `device_id` (`device_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `storage`
--

LOCK TABLES `storage` WRITE;
/*!40000 ALTER TABLE `storage` DISABLE KEYS */;
/*!40000 ALTER TABLE `storage` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `stp`
--

DROP TABLE IF EXISTS `stp`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `stp` (
  `stp_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `device_id` int(10) unsigned NOT NULL,
  `rootBridge` tinyint(1) NOT NULL,
  `bridgeAddress` varchar(32) COLLATE utf8_unicode_ci NOT NULL,
  `protocolSpecification` varchar(16) COLLATE utf8_unicode_ci NOT NULL,
  `priority` mediumint(9) NOT NULL,
  `timeSinceTopologyChange` varchar(32) COLLATE utf8_unicode_ci NOT NULL,
  `topChanges` mediumint(9) NOT NULL,
  `designatedRoot` varchar(32) COLLATE utf8_unicode_ci NOT NULL,
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
  KEY `stp_host` (`device_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `stp`
--

LOCK TABLES `stp` WRITE;
/*!40000 ALTER TABLE `stp` DISABLE KEYS */;
/*!40000 ALTER TABLE `stp` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `syslog`
--

DROP TABLE IF EXISTS `syslog`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `syslog` (
  `device_id` int(10) unsigned DEFAULT NULL,
  `facility` varchar(10) COLLATE utf8_unicode_ci DEFAULT NULL,
  `priority` varchar(10) COLLATE utf8_unicode_ci DEFAULT NULL,
  `level` varchar(10) COLLATE utf8_unicode_ci DEFAULT NULL,
  `tag` varchar(10) COLLATE utf8_unicode_ci DEFAULT NULL,
  `timestamp` timestamp NOT NULL DEFAULT current_timestamp(),
  `program` varchar(32) COLLATE utf8_unicode_ci DEFAULT NULL,
  `msg` text COLLATE utf8_unicode_ci DEFAULT NULL,
  `seq` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  PRIMARY KEY (`seq`),
  KEY `priority_level` (`priority`,`level`),
  KEY `device_id-timestamp` (`device_id`,`timestamp`),
  KEY `device_id` (`device_id`),
  KEY `datetime` (`timestamp`),
  KEY `program` (`program`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `syslog`
--

LOCK TABLES `syslog` WRITE;
/*!40000 ALTER TABLE `syslog` DISABLE KEYS */;
/*!40000 ALTER TABLE `syslog` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `tnmsneinfo`
--

DROP TABLE IF EXISTS `tnmsneinfo`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `tnmsneinfo` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `device_id` int(10) unsigned NOT NULL,
  `neID` int(11) NOT NULL,
  `neType` varchar(128) COLLATE utf8_unicode_ci NOT NULL,
  `neName` varchar(128) COLLATE utf8_unicode_ci NOT NULL,
  `neLocation` varchar(128) COLLATE utf8_unicode_ci NOT NULL,
  `neAlarm` varchar(128) COLLATE utf8_unicode_ci NOT NULL,
  `neOpMode` varchar(128) COLLATE utf8_unicode_ci NOT NULL,
  `neOpState` varchar(128) COLLATE utf8_unicode_ci NOT NULL,
  PRIMARY KEY (`id`),
  KEY `device_id` (`device_id`),
  KEY `neID` (`neID`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `tnmsneinfo`
--

LOCK TABLES `tnmsneinfo` WRITE;
/*!40000 ALTER TABLE `tnmsneinfo` DISABLE KEYS */;
/*!40000 ALTER TABLE `tnmsneinfo` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `toner`
--

DROP TABLE IF EXISTS `toner`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `toner` (
  `toner_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `device_id` int(10) unsigned NOT NULL DEFAULT 0,
  `toner_index` int(11) NOT NULL,
  `toner_type` varchar(64) COLLATE utf8_unicode_ci NOT NULL,
  `toner_oid` varchar(64) COLLATE utf8_unicode_ci NOT NULL,
  `toner_descr` varchar(32) COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
  `toner_capacity` int(11) NOT NULL DEFAULT 0,
  `toner_current` int(11) NOT NULL DEFAULT 0,
  `toner_capacity_oid` varchar(64) COLLATE utf8_unicode_ci DEFAULT NULL,
  PRIMARY KEY (`toner_id`),
  KEY `device_id` (`device_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `toner`
--

LOCK TABLES `toner` WRITE;
/*!40000 ALTER TABLE `toner` DISABLE KEYS */;
/*!40000 ALTER TABLE `toner` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `transport_group_transport`
--

DROP TABLE IF EXISTS `transport_group_transport`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `transport_group_transport` (
  `transport_group_id` int(10) unsigned NOT NULL,
  `transport_id` int(10) unsigned NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `transport_group_transport`
--

LOCK TABLES `transport_group_transport` WRITE;
/*!40000 ALTER TABLE `transport_group_transport` DISABLE KEYS */;
/*!40000 ALTER TABLE `transport_group_transport` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `ucd_diskio`
--

DROP TABLE IF EXISTS `ucd_diskio`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `ucd_diskio` (
  `diskio_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `device_id` int(10) unsigned NOT NULL,
  `diskio_index` int(11) NOT NULL,
  `diskio_descr` varchar(32) COLLATE utf8_unicode_ci NOT NULL,
  PRIMARY KEY (`diskio_id`),
  KEY `device_id` (`device_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `ucd_diskio`
--

LOCK TABLES `ucd_diskio` WRITE;
/*!40000 ALTER TABLE `ucd_diskio` DISABLE KEYS */;
/*!40000 ALTER TABLE `ucd_diskio` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `users`
--

DROP TABLE IF EXISTS `users`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `users` (
  `user_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `auth_type` varchar(32) COLLATE utf8_unicode_ci DEFAULT NULL,
  `auth_id` int(11) DEFAULT NULL,
  `username` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `password` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `realname` varchar(64) COLLATE utf8_unicode_ci NOT NULL,
  `email` varchar(64) COLLATE utf8_unicode_ci NOT NULL,
  `descr` char(30) COLLATE utf8_unicode_ci NOT NULL,
  `level` tinyint(4) NOT NULL DEFAULT 0,
  `can_modify_passwd` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT '1970-01-02 06:00:01',
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `remember_token` varchar(100) COLLATE utf8_unicode_ci DEFAULT NULL,
  PRIMARY KEY (`user_id`),
  UNIQUE KEY `username` (`auth_type`,`username`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `users`
--

LOCK TABLES `users` WRITE;
/*!40000 ALTER TABLE `users` DISABLE KEYS */;
/*!40000 ALTER TABLE `users` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `users_prefs`
--

DROP TABLE IF EXISTS `users_prefs`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `users_prefs` (
  `user_id` int(10) unsigned NOT NULL,
  `pref` varchar(32) COLLATE utf8_unicode_ci NOT NULL,
  `value` varchar(128) COLLATE utf8_unicode_ci NOT NULL,
  UNIQUE KEY `users_prefs_user_id_pref_unique` (`user_id`,`pref`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `users_prefs`
--

LOCK TABLES `users_prefs` WRITE;
/*!40000 ALTER TABLE `users_prefs` DISABLE KEYS */;
/*!40000 ALTER TABLE `users_prefs` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `users_widgets`
--

DROP TABLE IF EXISTS `users_widgets`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `users_widgets` (
  `user_widget_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `user_id` int(10) unsigned NOT NULL,
  `widget_id` int(10) unsigned NOT NULL,
  `col` tinyint(4) NOT NULL,
  `row` tinyint(4) NOT NULL,
  `size_x` tinyint(4) NOT NULL,
  `size_y` tinyint(4) NOT NULL,
  `title` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `refresh` tinyint(4) NOT NULL DEFAULT 60,
  `settings` text COLLATE utf8_unicode_ci NOT NULL,
  `dashboard_id` int(10) unsigned NOT NULL,
  PRIMARY KEY (`user_widget_id`),
  KEY `user_id` (`user_id`,`widget_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `users_widgets`
--

LOCK TABLES `users_widgets` WRITE;
/*!40000 ALTER TABLE `users_widgets` DISABLE KEYS */;
/*!40000 ALTER TABLE `users_widgets` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `vlans`
--

DROP TABLE IF EXISTS `vlans`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `vlans` (
  `vlan_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `device_id` int(10) unsigned DEFAULT NULL,
  `vlan_vlan` int(11) DEFAULT NULL,
  `vlan_domain` int(11) DEFAULT NULL,
  `vlan_name` varchar(64) COLLATE utf8_unicode_ci DEFAULT NULL,
  `vlan_type` varchar(16) COLLATE utf8_unicode_ci DEFAULT NULL,
  `vlan_mtu` int(11) DEFAULT NULL,
  PRIMARY KEY (`vlan_id`),
  KEY `device_id` (`device_id`,`vlan_vlan`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `vlans`
--

LOCK TABLES `vlans` WRITE;
/*!40000 ALTER TABLE `vlans` DISABLE KEYS */;
/*!40000 ALTER TABLE `vlans` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `vminfo`
--

DROP TABLE IF EXISTS `vminfo`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `vminfo` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `device_id` int(10) unsigned NOT NULL,
  `vm_type` varchar(16) COLLATE utf8_unicode_ci NOT NULL DEFAULT 'vmware',
  `vmwVmVMID` int(11) NOT NULL,
  `vmwVmDisplayName` varchar(128) COLLATE utf8_unicode_ci NOT NULL,
  `vmwVmGuestOS` varchar(128) COLLATE utf8_unicode_ci NOT NULL,
  `vmwVmMemSize` int(11) NOT NULL,
  `vmwVmCpus` int(11) NOT NULL,
  `vmwVmState` varchar(128) COLLATE utf8_unicode_ci NOT NULL,
  PRIMARY KEY (`id`),
  KEY `device_id` (`device_id`),
  KEY `vmwVmVMID` (`vmwVmVMID`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `vminfo`
--

LOCK TABLES `vminfo` WRITE;
/*!40000 ALTER TABLE `vminfo` DISABLE KEYS */;
/*!40000 ALTER TABLE `vminfo` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `vrf_lite_cisco`
--

DROP TABLE IF EXISTS `vrf_lite_cisco`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `vrf_lite_cisco` (
  `vrf_lite_cisco_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `device_id` int(10) unsigned NOT NULL,
  `context_name` varchar(128) COLLATE utf8_unicode_ci NOT NULL,
  `intance_name` varchar(128) COLLATE utf8_unicode_ci DEFAULT '',
  `vrf_name` varchar(128) COLLATE utf8_unicode_ci DEFAULT 'Default',
  PRIMARY KEY (`vrf_lite_cisco_id`),
  KEY `mix` (`device_id`,`context_name`,`vrf_name`),
  KEY `device` (`device_id`),
  KEY `context` (`context_name`),
  KEY `vrf` (`vrf_name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `vrf_lite_cisco`
--

LOCK TABLES `vrf_lite_cisco` WRITE;
/*!40000 ALTER TABLE `vrf_lite_cisco` DISABLE KEYS */;
/*!40000 ALTER TABLE `vrf_lite_cisco` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `vrfs`
--

DROP TABLE IF EXISTS `vrfs`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `vrfs` (
  `vrf_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `vrf_oid` varchar(256) COLLATE utf8_unicode_ci NOT NULL,
  `vrf_name` varchar(128) COLLATE utf8_unicode_ci DEFAULT NULL,
  `bgpLocalAs` int(10) unsigned DEFAULT NULL,
  `mplsVpnVrfRouteDistinguisher` varchar(128) COLLATE utf8_unicode_ci DEFAULT NULL,
  `mplsVpnVrfDescription` text COLLATE utf8_unicode_ci NOT NULL,
  `device_id` int(10) unsigned NOT NULL,
  PRIMARY KEY (`vrf_id`),
  KEY `device_id` (`device_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `vrfs`
--

LOCK TABLES `vrfs` WRITE;
/*!40000 ALTER TABLE `vrfs` DISABLE KEYS */;
/*!40000 ALTER TABLE `vrfs` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `widgets`
--

DROP TABLE IF EXISTS `widgets`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `widgets` (
  `widget_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `widget_title` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `widget` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `base_dimensions` varchar(10) COLLATE utf8_unicode_ci NOT NULL,
  PRIMARY KEY (`widget_id`),
  UNIQUE KEY `widget` (`widget`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `widgets`
--

LOCK TABLES `widgets` WRITE;
/*!40000 ALTER TABLE `widgets` DISABLE KEYS */;
/*!40000 ALTER TABLE `widgets` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `wireless_sensors`
--

DROP TABLE IF EXISTS `wireless_sensors`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `wireless_sensors` (
  `sensor_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `sensor_deleted` tinyint(1) NOT NULL DEFAULT 0,
  `sensor_class` varchar(64) COLLATE utf8_unicode_ci NOT NULL,
  `device_id` int(10) unsigned NOT NULL DEFAULT 0,
  `sensor_index` varchar(64) COLLATE utf8_unicode_ci DEFAULT NULL,
  `sensor_type` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `sensor_descr` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `sensor_divisor` int(11) NOT NULL DEFAULT 1,
  `sensor_multiplier` int(11) NOT NULL DEFAULT 1,
  `sensor_aggregator` varchar(16) COLLATE utf8_unicode_ci NOT NULL DEFAULT 'sum',
  `sensor_current` double DEFAULT NULL,
  `sensor_prev` double DEFAULT NULL,
  `sensor_limit` double DEFAULT NULL,
  `sensor_limit_warn` double DEFAULT NULL,
  `sensor_limit_low` double DEFAULT NULL,
  `sensor_limit_low_warn` double DEFAULT NULL,
  `sensor_alert` tinyint(1) NOT NULL DEFAULT 1,
  `sensor_custom` enum('No','Yes') COLLATE utf8_unicode_ci NOT NULL DEFAULT 'No',
  `entPhysicalIndex` varchar(16) COLLATE utf8_unicode_ci DEFAULT NULL,
  `entPhysicalIndex_measured` varchar(16) COLLATE utf8_unicode_ci DEFAULT NULL,
  `lastupdate` timestamp NOT NULL DEFAULT current_timestamp(),
  `sensor_oids` text COLLATE utf8_unicode_ci NOT NULL,
  `access_point_id` int(10) unsigned DEFAULT NULL,
  PRIMARY KEY (`sensor_id`),
  KEY `sensor_class` (`sensor_class`),
  KEY `sensor_host` (`device_id`),
  KEY `sensor_type` (`sensor_type`),
  CONSTRAINT `wireless_sensors_device_id_foreign` FOREIGN KEY (`device_id`) REFERENCES `devices` (`device_id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `wireless_sensors`
--

LOCK TABLES `wireless_sensors` WRITE;
/*!40000 ALTER TABLE `wireless_sensors` DISABLE KEYS */;
/*!40000 ALTER TABLE `wireless_sensors` ENABLE KEYS */;
UNLOCK TABLES;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

-- Dump completed on 2019-09-25  1:30:42
