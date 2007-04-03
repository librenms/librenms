-- MySQL dump 10.9
--
-- Host: localhost    Database: observer-dev
-- ------------------------------------------------------
-- Server version	4.1.15-Debian_1ubuntu5-log

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8 */;
/*!40014 SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;

--
-- Table structure for table `adjacencies`
--

DROP TABLE IF EXISTS `adjacencies`;
CREATE TABLE `adjacencies` (
  `adj_id` int(11) NOT NULL auto_increment,
  `network_id` int(11) NOT NULL default '0',
  `interface_id` int(11) NOT NULL default '0',
  PRIMARY KEY  (`adj_id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

--
-- Dumping data for table `adjacencies`
--


/*!40000 ALTER TABLE `adjacencies` DISABLE KEYS */;
LOCK TABLES `adjacencies` WRITE;
UNLOCK TABLES;
/*!40000 ALTER TABLE `adjacencies` ENABLE KEYS */;

--
-- Table structure for table `alerts`
--

DROP TABLE IF EXISTS `alerts`;
CREATE TABLE `alerts` (
  `id` int(11) NOT NULL auto_increment,
  `importance` int(11) NOT NULL default '0',
  `device_id` int(11) NOT NULL default '0',
  `message` text NOT NULL,
  `time_logged` timestamp NOT NULL default CURRENT_TIMESTAMP,
  `alerted` smallint(6) NOT NULL default '0',
  KEY `id` (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

--
-- Dumping data for table `alerts`
--


/*!40000 ALTER TABLE `alerts` DISABLE KEYS */;
LOCK TABLES `alerts` WRITE;
UNLOCK TABLES;
/*!40000 ALTER TABLE `alerts` ENABLE KEYS */;

--
-- Table structure for table `customers`
--

DROP TABLE IF EXISTS `customers`;
CREATE TABLE `customers` (
  `username` char(64) NOT NULL default '',
  `password` char(32) NOT NULL default '',
  `string` char(64) NOT NULL default '',
  `level` tinyint(4) NOT NULL default '0',
  UNIQUE KEY `username` (`username`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

--
-- Dumping data for table `customers`
--


/*!40000 ALTER TABLE `customers` DISABLE KEYS */;
LOCK TABLES `customers` WRITE;
UNLOCK TABLES;
/*!40000 ALTER TABLE `customers` ENABLE KEYS */;

--
-- Table structure for table `devices`
--

DROP TABLE IF EXISTS `devices`;
CREATE TABLE `devices` (
  `id` int(11) NOT NULL auto_increment,
  `hostname` varchar(64) NOT NULL default '',
  `ip` varchar(16) NOT NULL default '',
  `community` varchar(32) NOT NULL default 'public',
  `snmpver` char(3) NOT NULL default 'v2c',
  `monowall` tinyint(4) NOT NULL default '0',
  `version` text NOT NULL,
  `hardware` text NOT NULL,
  `features` text NOT NULL,
  `sysdesc` text,
  `location` text,
  `os` varchar(16) NOT NULL default '',
  `status` tinyint(4) NOT NULL default '0',
  `ignore` tinyint(4) NOT NULL default '0',
  `uptime` int(11) NOT NULL default '0',
  `lastchange` timestamp NOT NULL default CURRENT_TIMESTAMP on update CURRENT_TIMESTAMP,
  `purpose` text NOT NULL,
  `apache` tinyint(4) NOT NULL default '0',
  `courier` tinyint(4) NOT NULL default '0',
  `postfix` tinyint(4) NOT NULL default '0',
  `temp` tinyint(4) NOT NULL default '0',
  `type` varchar(16) default NULL,
  PRIMARY KEY  (`id`),
  UNIQUE KEY `hostname` (`hostname`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

--
-- Dumping data for table `devices`
--


/*!40000 ALTER TABLE `devices` DISABLE KEYS */;
LOCK TABLES `devices` WRITE;
UNLOCK TABLES;
/*!40000 ALTER TABLE `devices` ENABLE KEYS */;

--
-- Table structure for table `eventlog`
--

DROP TABLE IF EXISTS `eventlog`;
CREATE TABLE `eventlog` (
  `id` int(11) NOT NULL default '0',
  `host` int(11) NOT NULL default '0',
  `interface` int(11) default NULL,
  `datetime` datetime NOT NULL default '0000-00-00 00:00:00',
  `message` text NOT NULL,
  `type` int(11) NOT NULL default '0'
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

--
-- Dumping data for table `eventlog`
--


/*!40000 ALTER TABLE `eventlog` DISABLE KEYS */;
LOCK TABLES `eventlog` WRITE;
UNLOCK TABLES;
/*!40000 ALTER TABLE `eventlog` ENABLE KEYS */;

--
-- Table structure for table `interfaces`
--

DROP TABLE IF EXISTS `interfaces`;
CREATE TABLE `interfaces` (
  `id` int(11) NOT NULL auto_increment,
  `host` int(11) NOT NULL default '0',
  `if` varchar(64) NOT NULL default '',
  `ifIndex` int(11) NOT NULL default '0',
  `ifSpeed` text,
  `up` varchar(12) NOT NULL default '',
  `up_admin` varchar(12) default NULL,
  `ifDuplex` varchar(12) default NULL,
  `ifMtu` int(11) default NULL,
  `ifType` text,
  `name` text,
  `ifPhysAddress` text,
  `lastchange` timestamp NOT NULL default CURRENT_TIMESTAMP on update CURRENT_TIMESTAMP,
  PRIMARY KEY  (`id`),
  KEY `host` (`host`),
  KEY `snmpid` (`ifIndex`),
  KEY `if_2` (`if`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

--
-- Dumping data for table `interfaces`
--


/*!40000 ALTER TABLE `interfaces` DISABLE KEYS */;
LOCK TABLES `interfaces` WRITE;
UNLOCK TABLES;
/*!40000 ALTER TABLE `interfaces` ENABLE KEYS */;

--
-- Table structure for table `ipaddr`
--

DROP TABLE IF EXISTS `ipaddr`;
CREATE TABLE `ipaddr` (
  `id` int(11) NOT NULL auto_increment,
  `addr` varchar(32) NOT NULL default '',
  `cidr` smallint(6) NOT NULL default '0',
  `network` varchar(64) NOT NULL default '',
  `interface_id` int(11) NOT NULL default '0',
  PRIMARY KEY  (`id`),
  KEY `addr` (`addr`,`cidr`,`interface_id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

--
-- Dumping data for table `ipaddr`
--


/*!40000 ALTER TABLE `ipaddr` DISABLE KEYS */;
LOCK TABLES `ipaddr` WRITE;
UNLOCK TABLES;
/*!40000 ALTER TABLE `ipaddr` ENABLE KEYS */;

--
-- Table structure for table `links`
--

DROP TABLE IF EXISTS `links`;
CREATE TABLE `links` (
  `id` int(11) NOT NULL auto_increment,
  `src_if` int(11) default NULL,
  `dst_if` int(11) default NULL,
  `active` tinyint(4) NOT NULL default '1',
  `cdp` int(11) default NULL,
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

--
-- Dumping data for table `links`
--


/*!40000 ALTER TABLE `links` DISABLE KEYS */;
LOCK TABLES `links` WRITE;
UNLOCK TABLES;
/*!40000 ALTER TABLE `links` ENABLE KEYS */;

--
-- Table structure for table `networks`
--

DROP TABLE IF EXISTS `networks`;
CREATE TABLE `networks` (
  `id` int(11) NOT NULL auto_increment,
  `cidr` varchar(32) NOT NULL default '',
  PRIMARY KEY  (`id`),
  UNIQUE KEY `cidr_2` (`cidr`),
  FULLTEXT KEY `cidr` (`cidr`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

--
-- Dumping data for table `networks`
--


/*!40000 ALTER TABLE `networks` DISABLE KEYS */;
LOCK TABLES `networks` WRITE;
UNLOCK TABLES;
/*!40000 ALTER TABLE `networks` ENABLE KEYS */;

--
-- Table structure for table `services`
--

DROP TABLE IF EXISTS `services`;
CREATE TABLE `services` (
  `service_id` int(11) NOT NULL auto_increment,
  `service_host` int(11) NOT NULL default '0',
  `service_ip` text,
  `service_type` text NOT NULL,
  `service_desc` text NOT NULL,
  `service_param` text NOT NULL,
  `service_port` text,
  `service_hostname` text,
  `service_status` tinyint(4) NOT NULL default '2',
  `service_message` text NOT NULL,
  `service_changed` int(11) NOT NULL default '0',
  `service_checked` int(11) NOT NULL default '0',
  `service_ignore` tinyint(4) NOT NULL default '0',
  PRIMARY KEY  (`service_id`),
  KEY `service_host` (`service_host`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

--
-- Dumping data for table `services`
--


/*!40000 ALTER TABLE `services` DISABLE KEYS */;
LOCK TABLES `services` WRITE;
UNLOCK TABLES;
/*!40000 ALTER TABLE `services` ENABLE KEYS */;

--
-- Table structure for table `storage`
--

DROP TABLE IF EXISTS `storage`;
CREATE TABLE `storage` (
  `storage_id` int(11) NOT NULL auto_increment,
  `host_id` int(11) NOT NULL default '0',
  `hrStorageIndex` int(11) NOT NULL default '0',
  `hrStorageDescr` text NOT NULL,
  `hrStorageSize` int(11) NOT NULL default '0',
  `hrStorageAllocationUnits` int(11) NOT NULL default '0',
  PRIMARY KEY  (`storage_id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

--
-- Dumping data for table `storage`
--


/*!40000 ALTER TABLE `storage` DISABLE KEYS */;
LOCK TABLES `storage` WRITE;
UNLOCK TABLES;
/*!40000 ALTER TABLE `storage` ENABLE KEYS */;

--
-- Table structure for table `users`
--

DROP TABLE IF EXISTS `users`;
CREATE TABLE `users` (
  `username` char(30) NOT NULL default '',
  `password` char(32) NOT NULL default '',
  `descr` char(30) NOT NULL default '',
  `level` tinyint(4) NOT NULL default '0',
  UNIQUE KEY `username` (`username`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

--
-- Dumping data for table `users`
--


/*!40000 ALTER TABLE `users` DISABLE KEYS */;
LOCK TABLES `users` WRITE;
INSERT INTO `users` VALUES ('admin','6033c66e583283ac','Default User',10);
UNLOCK TABLES;
/*!40000 ALTER TABLE `users` ENABLE KEYS */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

