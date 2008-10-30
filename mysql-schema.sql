-- phpMyAdmin SQL Dump
-- version 2.11.3deb1ubuntu1.1
-- http://www.phpmyadmin.net
--
-- Host: localhost
-- Generation Time: Oct 30, 2008 at 09:09 PM
-- Server version: 5.0.51
-- PHP Version: 5.2.4-2ubuntu5.3

SET SQL_MODE="NO_AUTO_VALUE_ON_ZERO";

--
-- Database: `observer`
--

-- --------------------------------------------------------

--
-- Table structure for table `adjacencies`
--
-- Creation: Sep 03, 2008 at 08:42 PM
-- Last update: Oct 30, 2008 at 07:10 PM
--

CREATE TABLE `adjacencies` (
  `adj_id` int(11) NOT NULL auto_increment,
  `network_id` int(11) NOT NULL default '0',
  `interface_id` int(11) NOT NULL default '0',
  PRIMARY KEY  (`adj_id`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `alerts`
--
-- Creation: Sep 03, 2008 at 08:42 PM
-- Last update: Oct 29, 2008 at 03:46 PM
--

CREATE TABLE `alerts` (
  `id` int(11) NOT NULL auto_increment,
  `importance` int(11) NOT NULL default '0',
  `device_id` int(11) NOT NULL,
  `message` text NOT NULL,
  `time_logged` timestamp NOT NULL default CURRENT_TIMESTAMP,
  `alerted` smallint(6) NOT NULL default '0',
  KEY `id` (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `bgpPeers`
--
-- Creation: Sep 16, 2008 at 12:51 PM
-- Last update: Oct 30, 2008 at 09:08 PM
-- Last check: Sep 16, 2008 at 12:51 PM
--

CREATE TABLE `bgpPeers` (
  `bgpPeer_id` int(11) NOT NULL auto_increment,
  `device_id` int(11) NOT NULL,
  `astext` varchar(32) NOT NULL,
  `bgpPeerIdentifier` text NOT NULL,
  `bgpPeerRemoteAs` int(11) NOT NULL,
  `bgpPeerState` text NOT NULL,
  `bgpPeerAdminStatus` text NOT NULL,
  `bgpLocalAddr` text NOT NULL,
  `bgpPeerRemoteAddr` text NOT NULL,
  `bgpPeerInUpdates` int(11) NOT NULL,
  `bgpPeerOutUpdates` int(11) NOT NULL,
  `bgpPeerInTotalMessages` int(11) NOT NULL,
  `bgpPeerOutTotalMessages` int(11) NOT NULL,
  `bgpPeerFsmEstablishedTime` int(11) NOT NULL,
  `bgpPeerInUpdateElapsedTime` int(11) NOT NULL,
  PRIMARY KEY  (`bgpPeer_id`),
  KEY `device_id` (`device_id`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `bills`
--
-- Creation: Sep 03, 2008 at 08:42 PM
-- Last update: Sep 03, 2008 at 08:42 PM
--

CREATE TABLE `bills` (
  `bill_id` int(11) NOT NULL auto_increment,
  `bill_name` text NOT NULL,
  `bill_type` text NOT NULL,
  `bill_cdr` int(11) default NULL,
  `bill_day` int(11) NOT NULL default '1',
  `bill_gb` int(11) default NULL,
  UNIQUE KEY `bill_id` (`bill_id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `bill_data`
--
-- Creation: Sep 03, 2008 at 08:42 PM
-- Last update: Sep 03, 2008 at 08:42 PM
--

CREATE TABLE `bill_data` (
  `bill_id` int(11) NOT NULL,
  `timestamp` datetime NOT NULL,
  `period` int(11) NOT NULL,
  `delta` bigint(11) NOT NULL,
  `in_delta` bigint(11) NOT NULL,
  `out_delta` bigint(11) NOT NULL,
  KEY `bill_id` (`bill_id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `bill_perms`
--
-- Creation: Sep 03, 2008 at 08:42 PM
-- Last update: Sep 03, 2008 at 08:42 PM
--

CREATE TABLE `bill_perms` (
  `user_id` int(11) NOT NULL,
  `bill_id` int(11) NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `bill_ports`
--
-- Creation: Sep 03, 2008 at 08:42 PM
-- Last update: Sep 03, 2008 at 08:42 PM
--

CREATE TABLE `bill_ports` (
  `bill_id` int(11) NOT NULL,
  `port_id` int(11) NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `customers`
--
-- Creation: Sep 03, 2008 at 08:42 PM
-- Last update: Sep 03, 2008 at 08:42 PM
--

CREATE TABLE `customers` (
  `customer_id` int(11) NOT NULL auto_increment,
  `username` char(64) NOT NULL,
  `password` char(32) NOT NULL,
  `string` char(64) NOT NULL,
  `level` tinyint(4) NOT NULL default '0',
  PRIMARY KEY  (`customer_id`),
  UNIQUE KEY `username` (`username`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `devices`
--
-- Creation: Sep 03, 2008 at 08:42 PM
-- Last update: Oct 29, 2008 at 03:46 PM
--

CREATE TABLE `devices` (
  `device_id` int(11) NOT NULL auto_increment,
  `hostname` text NOT NULL,
  `community` varchar(32) NOT NULL,
  `snmpver` varchar(4) NOT NULL default 'v2c',
  `bgpLocalAs` varchar(16) default NULL,
  `sysDescr` text,
  `sysContact` text NOT NULL,
  `version` text NOT NULL,
  `hardware` text NOT NULL,
  `features` text NOT NULL,
  `location` text,
  `os` varchar(8) NOT NULL default '',
  `status` tinyint(4) NOT NULL default '0',
  `ignore` tinyint(4) NOT NULL default '0',
  `disabled` tinyint(1) NOT NULL default '0',
  `lastchange` timestamp NOT NULL default CURRENT_TIMESTAMP on update CURRENT_TIMESTAMP,
  `purpose` text NOT NULL,
  `type` varchar(8) NOT NULL default 'other',
  PRIMARY KEY  (`device_id`),
  KEY `status` (`status`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `devices_attribs`
--
-- Creation: Sep 03, 2008 at 08:42 PM
-- Last update: Oct 30, 2008 at 09:08 PM
--

CREATE TABLE `devices_attribs` (
  `attrib_id` int(11) NOT NULL auto_increment,
  `device_id` int(11) NOT NULL,
  `attrib_type` varchar(32) NOT NULL,
  `attrib_value` int(11) NOT NULL,
  `updated` timestamp NOT NULL default CURRENT_TIMESTAMP on update CURRENT_TIMESTAMP,
  PRIMARY KEY  (`attrib_id`),
  FULLTEXT KEY `attrib_type` (`attrib_type`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `devices_perms`
--
-- Creation: Sep 03, 2008 at 08:42 PM
-- Last update: Sep 03, 2008 at 08:42 PM
--

CREATE TABLE `devices_perms` (
  `user_id` int(11) NOT NULL,
  `device_id` int(11) NOT NULL,
  `access_level` int(4) NOT NULL default '0',
  KEY `user_id` (`user_id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `entPhysical`
--
-- Creation: Sep 07, 2008 at 10:26 PM
-- Last update: Oct 29, 2008 at 06:50 PM
--

CREATE TABLE `entPhysical` (
  `entPhysical_id` int(11) NOT NULL auto_increment,
  `device_id` int(11) NOT NULL,
  `entPhysicalIndex` int(11) NOT NULL,
  `entPhysicalDescr` text NOT NULL,
  `entPhysicalClass` text NOT NULL,
  `entPhysicalName` text NOT NULL,
  `entPhysicalModelName` text NOT NULL,
  `entPhysicalVendorType` text,
  `entPhysicalSerialNum` text NOT NULL,
  `entPhysicalContainedIn` int(11) NOT NULL,
  `entPhysicalParentRelPos` int(11) NOT NULL,
  `entPhysicalMfgName` text NOT NULL,
  `ifIndex` int(11) default NULL,
  PRIMARY KEY  (`entPhysical_id`),
  KEY `device_id` (`device_id`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `eventlog`
--
-- Creation: Sep 03, 2008 at 08:42 PM
-- Last update: Oct 30, 2008 at 06:51 PM
--

CREATE TABLE `eventlog` (
  `id` int(11) NOT NULL default '0',
  `host` int(11) NOT NULL default '0',
  `interface` int(11) default NULL,
  `datetime` datetime NOT NULL default '0000-00-00 00:00:00',
  `message` text NOT NULL,
  `type` int(11) NOT NULL,
  KEY `host` (`host`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `interfaces`
--
-- Creation: Sep 21, 2008 at 05:22 PM
-- Last update: Oct 30, 2008 at 09:08 PM
-- Last check: Sep 21, 2008 at 05:22 PM
--

CREATE TABLE `interfaces` (
  `interface_id` int(11) NOT NULL auto_increment,
  `device_id` int(11) NOT NULL default '0',
  `ifDescr` varchar(64) NOT NULL,
  `ifIndex` int(11) default '0',
  `ifSpeed` text,
  `ifOperStatus` varchar(16) default NULL,
  `ifAdminStatus` varchar(16) default NULL,
  `ifDuplex` varchar(12) default NULL,
  `ifMtu` int(11) default NULL,
  `ifType` text,
  `ifAlias` text,
  `ifPhysAddress` text,
  `ifHardType` varchar(64) default NULL,
  `ifLastChange` timestamp NOT NULL default CURRENT_TIMESTAMP on update CURRENT_TIMESTAMP,
  `ifVlan` varchar(8) NOT NULL default '',
  `ifTrunk` varchar(8) default '',
  `ifVrf` int(11) NOT NULL,
  `in_rate` int(11) NOT NULL,
  `out_rate` int(11) NOT NULL,
  `counter_in` int(11) default NULL,
  `counter_out` int(11) default NULL,
  `in_errors` int(11) NOT NULL,
  `out_errors` int(11) NOT NULL,
  `ignore` tinyint(1) NOT NULL default '0',
  `detailed` tinyint(1) NOT NULL default '0',
  `deleted` int(11) NOT NULL default '0',
  PRIMARY KEY  (`interface_id`),
  KEY `host` (`device_id`),
  KEY `snmpid` (`ifIndex`),
  KEY `if_2` (`ifDescr`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `interfaces_perms`
--
-- Creation: Sep 03, 2008 at 08:42 PM
-- Last update: Sep 03, 2008 at 08:42 PM
--

CREATE TABLE `interfaces_perms` (
  `user_id` int(11) NOT NULL,
  `interface_id` int(11) NOT NULL,
  `access_level` int(11) NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `ipaddr`
--
-- Creation: Sep 03, 2008 at 08:42 PM
-- Last update: Oct 29, 2008 at 07:09 PM
--

CREATE TABLE `ipaddr` (
  `id` int(11) NOT NULL auto_increment,
  `addr` varchar(32) NOT NULL default '',
  `cidr` smallint(6) NOT NULL default '0',
  `network` varchar(64) NOT NULL default '',
  `interface_id` int(11) NOT NULL default '0',
  PRIMARY KEY  (`id`),
  KEY `addr` (`addr`,`cidr`,`interface_id`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `links`
--
-- Creation: Sep 03, 2008 at 08:42 PM
-- Last update: Oct 30, 2008 at 12:55 AM
--

CREATE TABLE `links` (
  `id` int(11) NOT NULL auto_increment,
  `src_if` int(11) default NULL,
  `dst_if` int(11) default NULL,
  `active` tinyint(4) NOT NULL default '1',
  `cdp` int(11) default NULL,
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `networks`
--
-- Creation: Sep 03, 2008 at 08:42 PM
-- Last update: Oct 27, 2008 at 12:47 PM
--

CREATE TABLE `networks` (
  `id` int(11) NOT NULL auto_increment,
  `cidr` varchar(32) NOT NULL default '',
  PRIMARY KEY  (`id`),
  UNIQUE KEY `cidr` (`cidr`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `port_in_measurements`
--
-- Creation: Sep 03, 2008 at 08:42 PM
-- Last update: Sep 03, 2008 at 08:42 PM
--

CREATE TABLE `port_in_measurements` (
  `port_id` int(11) NOT NULL,
  `timestamp` timestamp NOT NULL default CURRENT_TIMESTAMP on update CURRENT_TIMESTAMP,
  `counter` bigint(11) NOT NULL,
  `delta` bigint(11) NOT NULL,
  KEY `port_id` (`port_id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `port_out_measurements`
--
-- Creation: Sep 03, 2008 at 08:42 PM
-- Last update: Sep 03, 2008 at 08:42 PM
--

CREATE TABLE `port_out_measurements` (
  `port_id` int(11) NOT NULL,
  `timestamp` timestamp NOT NULL default CURRENT_TIMESTAMP on update CURRENT_TIMESTAMP,
  `counter` bigint(11) NOT NULL,
  `delta` bigint(11) NOT NULL,
  KEY `port_id` (`port_id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `pseudowires`
--
-- Creation: Oct 28, 2008 at 03:44 PM
-- Last update: Oct 29, 2008 at 12:52 PM
--

CREATE TABLE `pseudowires` (
  `pseudowire_id` int(11) NOT NULL auto_increment,
  `interface_id` int(11) NOT NULL,
  `peer_device_id` int(11) NOT NULL,
  `peer_ldp_id` int(11) NOT NULL,
  `cpwVcID` int(11) NOT NULL,
  `cpwOid` int(11) NOT NULL,
  PRIMARY KEY  (`pseudowire_id`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `services`
--
-- Creation: Sep 03, 2008 at 08:42 PM
-- Last update: Sep 03, 2008 at 08:42 PM
--

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
  `service_disabled` tinyint(1) NOT NULL default '0',
  PRIMARY KEY  (`service_id`),
  KEY `service_host` (`service_host`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `storage`
--
-- Creation: Sep 03, 2008 at 08:42 PM
-- Last update: Oct 30, 2008 at 09:07 PM
--

CREATE TABLE `storage` (
  `storage_id` int(11) NOT NULL auto_increment,
  `host_id` int(11) NOT NULL,
  `hrStorageIndex` int(11) NOT NULL,
  `hrStorageDescr` text NOT NULL,
  `hrStorageSize` int(11) NOT NULL,
  `hrStorageAllocationUnits` int(11) NOT NULL,
  `hrStorageUsed` int(11) NOT NULL,
  `storage_perc` text NOT NULL,
  PRIMARY KEY  (`storage_id`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `syslog`
--
-- Creation: Sep 03, 2008 at 08:42 PM
-- Last update: Oct 30, 2008 at 11:46 AM
--

CREATE TABLE `syslog` (
  `host` varchar(64) NOT NULL,
  `device_id` int(11) default NULL,
  `facility` varchar(10) default NULL,
  `priority` varchar(10) default NULL,
  `level` varchar(10) default NULL,
  `tag` varchar(10) default NULL,
  `datetime` datetime default NULL,
  `program` varchar(32) default NULL,
  `msg` text,
  `seq` bigint(20) unsigned NOT NULL auto_increment,
  `processed` tinyint(1) NOT NULL default '0',
  PRIMARY KEY  (`seq`),
  KEY `datetime` (`datetime`),
  KEY `device_id` (`device_id`),
  KEY `processed` (`processed`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `temperature`
--
-- Creation: Sep 03, 2008 at 08:42 PM
-- Last update: Oct 30, 2008 at 09:08 PM
--

CREATE TABLE `temperature` (
  `temp_id` int(11) NOT NULL auto_increment,
  `temp_host` int(11) NOT NULL default '0',
  `temp_oid` varchar(64) NOT NULL,
  `temp_descr` varchar(32) NOT NULL default '',
  `temp_tenths` int(1) NOT NULL default '0',
  `temp_current` tinyint(4) NOT NULL default '0',
  `temp_limit` tinyint(4) NOT NULL default '60',
  PRIMARY KEY  (`temp_id`),
  KEY `temp_host` (`temp_host`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `users`
--
-- Creation: Sep 03, 2008 at 08:42 PM
-- Last update: Sep 22, 2008 at 03:46 PM
--

CREATE TABLE `users` (
  `user_id` int(11) NOT NULL auto_increment,
  `username` char(30) NOT NULL,
  `password` char(32) NOT NULL,
  `realname` text NOT NULL,
  `descr` char(30) NOT NULL,
  `level` tinyint(4) NOT NULL default '0',
  PRIMARY KEY  (`user_id`),
  UNIQUE KEY `username` (`username`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `vlans`
--
-- Creation: Sep 03, 2008 at 08:42 PM
-- Last update: Sep 08, 2008 at 06:39 PM
--

CREATE TABLE `vlans` (
  `vlan_id` int(11) NOT NULL auto_increment,
  `device_id` int(11) default NULL,
  `vlan_vlan` int(11) default NULL,
  `vlan_domain` text,
  `vlan_descr` text,
  PRIMARY KEY  (`vlan_id`),
  KEY `device_id` (`device_id`,`vlan_vlan`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `vrfs`
--
-- Creation: Oct 28, 2008 at 09:44 PM
-- Last update: Oct 29, 2008 at 06:50 PM
--

CREATE TABLE `vrfs` (
  `vrf_id` int(11) NOT NULL auto_increment,
  `vrf_oid` varchar(64) NOT NULL,
  `vrf_name` varchar(32) NOT NULL,
  `mplsVpnVrfRouteDistinguisher` varchar(16) NOT NULL,
  `device_id` int(11) NOT NULL,
  PRIMARY KEY  (`vrf_id`),
  KEY `device_id` (`device_id`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1;

