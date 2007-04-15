-- MySQL dump 10.10
--
-- Host: localhost    Database: vostron_network
-- ------------------------------------------------------
-- Server version	5.0.22-Debian_0ubuntu6.06.2-log

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8 */;
/*!40103 SET @OLD_TIME_ZONE=@@TIME_ZONE */;
/*!40103 SET TIME_ZONE='+00:00' */;
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
-- Table structure for table `alerts`
--

DROP TABLE IF EXISTS `alerts`;
CREATE TABLE `alerts` (
  `id` int(11) NOT NULL auto_increment,
  `importance` int(11) NOT NULL default '0',
  `device_id` int(11) NOT NULL,
  `message` text NOT NULL,
  `time_logged` timestamp NOT NULL default CURRENT_TIMESTAMP,
  `alerted` smallint(6) NOT NULL default '0',
  KEY `id` (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

--
-- Table structure for table `customers`
--

DROP TABLE IF EXISTS `customers`;
CREATE TABLE `customers` (
  `username` char(64) NOT NULL,
  `password` char(32) NOT NULL,
  `string` char(64) NOT NULL,
  `level` tinyint(4) NOT NULL default '0',
  UNIQUE KEY `username` (`username`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

--
-- Table structure for table `device_uptime`
--

DROP TABLE IF EXISTS `device_uptime`;
CREATE TABLE `device_uptime` (
  `device_id` int(11) NOT NULL default '0',
  `device_uptime` int(11) NOT NULL default '0',
  UNIQUE KEY `device_id` (`device_id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

--
-- Table structure for table `devices`
--

DROP TABLE IF EXISTS `devices`;
CREATE TABLE `devices` (
  `device_id` int(11) NOT NULL auto_increment,
  `hostname` text NOT NULL,
  `ip` varchar(16) NOT NULL default '',
  `community` varchar(32) NOT NULL default 'v05tr0n82',
  `snmpver` varchar(4) NOT NULL default 'v2c',
  `sysDescr` text,
  `monowall` tinyint(4) NOT NULL default '0',
  `version` text NOT NULL,
  `hardware` text NOT NULL,
  `features` text NOT NULL,
  `location` text,
  `os` varchar(8) NOT NULL default '',
  `status` tinyint(4) NOT NULL default '0',
  `ignore` tinyint(4) NOT NULL default '0',
  `lastchange` timestamp NOT NULL default CURRENT_TIMESTAMP on update CURRENT_TIMESTAMP,
  `purpose` text NOT NULL,
  `apache` tinyint(4) NOT NULL default '0',
  `courier` tinyint(4) NOT NULL default '0',
  `postfix` tinyint(4) NOT NULL default '0',
  `type` varchar(8) NOT NULL default 'other',
  PRIMARY KEY  (`device_id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

--
-- Table structure for table `devices_attribs`
--

DROP TABLE IF EXISTS `devices_attribs`;
CREATE TABLE `devices_attribs` (
  `attrib_id` int(11) NOT NULL auto_increment,
  `device_id` int(11) NOT NULL,
  `attrib_type` varchar(32) NOT NULL,
  `attrib_value` varchar(256) NOT NULL,
  PRIMARY KEY  (`attrib_id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

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
  `type` int(11) NOT NULL,
  KEY `host` (`host`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

--
-- Table structure for table `interfaces`
--

DROP TABLE IF EXISTS `interfaces`;
CREATE TABLE `interfaces` (
  `interface_id` int(11) NOT NULL auto_increment,
  `device_id` int(11) NOT NULL default '0',
  `ifDescr` varchar(64) NOT NULL,
  `ifIndex` int(11) default '0',
  `ifSpeed` text,
  `ifOperStatus` varchar(12) default NULL,
  `ifAdminStatus` varchar(12) default NULL,
  `ifDuplex` varchar(12) default NULL,
  `ifMtu` int(11) default NULL,
  `ifType` text,
  `ifAlias` text,
  `ifPhysAddress` text,
  `ifHardType` varchar(64) default NULL,
  `ifLastChange` timestamp NOT NULL default CURRENT_TIMESTAMP on update CURRENT_TIMESTAMP,
  `ifVlan` int(11) default NULL,
  `ifTrunk` varchar(8) default '',
  `ignore` tinyint(1) NOT NULL default '0',
  PRIMARY KEY  (`interface_id`),
  KEY `host` (`device_id`),
  KEY `snmpid` (`ifIndex`),
  KEY `if_2` (`ifDescr`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

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
-- Table structure for table `logs`
--

DROP TABLE IF EXISTS `logs`;
CREATE TABLE `logs` (
  `host` varchar(32) default NULL,
  `facility` varchar(10) default NULL,
  `priority` varchar(10) default NULL,
  `level` varchar(10) default NULL,
  `tag` varchar(10) default NULL,
  `datetime` datetime default NULL,
  `program` varchar(15) default NULL,
  `msg` text,
  `seq` bigint(20) unsigned NOT NULL auto_increment,
  PRIMARY KEY  (`seq`),
  KEY `host` (`host`),
  KEY `program` (`program`),
  KEY `datetime` (`datetime`),
  KEY `priority` (`priority`),
  KEY `facility` (`facility`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

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
-- Table structure for table `services`
--

DROP TABLE IF EXISTS `services`;
CREATE TABLE `services` (
  `service_id` int(11) NOT NULL auto_increment,
  `service_host` int(11) NOT NULL,
  `service_ip` text NOT NULL,
  `service_type` varchar(16) NOT NULL,
  `service_desc` text NOT NULL,
  `service_param` text NOT NULL,
  `service_ignore` tinyint(1) NOT NULL,
  `service_status` tinyint(4) NOT NULL default '0',
  `service_checked` int(11) NOT NULL default '0',
  `service_changed` int(11) NOT NULL default '0',
  `service_message` text NOT NULL,
  PRIMARY KEY  (`service_id`),
  KEY `service_host` (`service_host`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

--
-- Table structure for table `storage`
--

DROP TABLE IF EXISTS `storage`;
CREATE TABLE `storage` (
  `storage_id` int(11) NOT NULL auto_increment,
  `host_id` int(11) NOT NULL,
  `hrStorageIndex` int(11) NOT NULL,
  `hrStorageDescr` text NOT NULL,
  `hrStorageSize` int(11) NOT NULL,
  `hrStorageAllocationUnits` int(11) NOT NULL,
  PRIMARY KEY  (`storage_id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

--
-- Table structure for table `syslog`
--

DROP TABLE IF EXISTS `syslog`;
CREATE TABLE `syslog` (
  `host` varchar(32) default NULL,
  `facility` varchar(10) default NULL,
  `priority` varchar(10) default NULL,
  `level` varchar(10) default NULL,
  `tag` varchar(10) default NULL,
  `datetime` datetime default NULL,
  `program` varchar(20) default NULL,
  `msg` text,
  `seq` bigint(20) unsigned NOT NULL auto_increment,
  PRIMARY KEY  (`seq`),
  KEY `host` (`host`),
  KEY `program` (`program`),
  KEY `datetime` (`datetime`),
  KEY `priority` (`priority`),
  KEY `facility` (`facility`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

--
-- Table structure for table `temperature`
--

DROP TABLE IF EXISTS `temperature`;
CREATE TABLE `temperature` (
  `temp_id` int(11) NOT NULL auto_increment,
  `temp_host` int(11) NOT NULL default '0',
  `temp_oid` varchar(32) NOT NULL default '',
  `temp_descr` varchar(32) NOT NULL default '',
  `temp_current` tinyint(4) NOT NULL default '0',
  `temp_limit` tinyint(4) NOT NULL default '70',
  PRIMARY KEY  (`temp_id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

--
-- Table structure for table `users`
--

DROP TABLE IF EXISTS `users`;
CREATE TABLE `users` (
  `username` char(30) NOT NULL,
  `password` char(32) NOT NULL,
  `descr` char(30) NOT NULL,
  `level` tinyint(4) NOT NULL default '0',
  UNIQUE KEY `username` (`username`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

--
-- Table structure for table `vlans`
--

DROP TABLE IF EXISTS `vlans`;
CREATE TABLE `vlans` (
  `vlan_id` int(11) NOT NULL auto_increment,
  `device_id` int(11) default NULL,
  `vlan_vlan` int(11) default NULL,
  `vlan_domain` text,
  `vlan_descr` text,
  PRIMARY KEY  (`vlan_id`),
  KEY `device_id` (`device_id`,`vlan_vlan`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

