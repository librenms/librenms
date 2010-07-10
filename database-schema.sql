-- phpMyAdmin SQL Dump
-- version 2.11.8.1deb1
-- http://www.phpmyadmin.net
--
-- Host: localhost
-- Generation Time: May 11, 2009 at 03:07 PM
-- Server version: 5.0.67
-- PHP Version: 5.2.6-2ubuntu4.2

SET SQL_MODE="NO_AUTO_VALUE_ON_ZERO";

--
-- Database: `observernms`
--

-- --------------------------------------------------------

--
-- Table structure for table `alerts`
--

CREATE TABLE `alerts` (
  `id` int(11) NOT NULL auto_increment,
  `importance` int(11) NOT NULL default '0',
  `device_id` int(11) NOT NULL,
  `message` text NOT NULL,
  `time_logged` timestamp NOT NULL default CURRENT_TIMESTAMP,
  `alerted` smallint(6) NOT NULL default '0',
  KEY `id` (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `authlog`
--

CREATE TABLE `authlog` (
  `id` int(11) NOT NULL auto_increment,
  `datetime` timestamp NOT NULL default CURRENT_TIMESTAMP,
  `user` text NOT NULL,
  `address` text NOT NULL,
  `result` text NOT NULL,
  PRIMARY KEY  (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `bgpPeers`
--

CREATE TABLE `bgpPeers` (
  `bgpPeer_id` int(11) NOT NULL auto_increment,
  `device_id` int(11) NOT NULL,
  `astext` varchar(64) NOT NULL,
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
) ENGINE=InnoDB  DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `bgpPeers_cbgp`
--

CREATE TABLE `bgpPeers_cbgp` (
  `device_id` int(11) NOT NULL,
  `bgpPeerIdentifier` varchar(64) NOT NULL,
  `afi` varchar(8) NOT NULL,
  `safi` varchar(8) NOT NULL,
  KEY `device_id` (`device_id`,`bgpPeerIdentifier`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `bills`
--

CREATE TABLE `bills` (
  `bill_id` int(11) NOT NULL auto_increment,
  `bill_name` text NOT NULL,
  `bill_type` text NOT NULL,
  `bill_cdr` int(11) default NULL,
  `bill_day` int(11) NOT NULL default '1',
  `bill_gb` int(11) default NULL,
  UNIQUE KEY `bill_id` (`bill_id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `bill_data`
--

CREATE TABLE `bill_data` (
  `bill_id` int(11) NOT NULL,
  `timestamp` datetime NOT NULL,
  `period` int(11) NOT NULL,
  `delta` bigint(11) NOT NULL,
  `in_delta` bigint(11) NOT NULL,
  `out_delta` bigint(11) NOT NULL,
  KEY `bill_id` (`bill_id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `bill_perms`
--

CREATE TABLE `bill_perms` (
  `user_id` int(11) NOT NULL,
  `bill_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `bill_ports`
--

CREATE TABLE `bill_ports` (
  `bill_id` int(11) NOT NULL,
  `port_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `cempMemPool`
--

CREATE TABLE `cempMemPool` (
  `cempMemPool_id` int(11) NOT NULL auto_increment,
  `Index` varchar(8) NOT NULL,
  `entPhysicalIndex` int(11) NOT NULL,
  `cempMemPoolType` varchar(32) NOT NULL,
  `cempMemPoolName` varchar(32) NOT NULL,
  `cempMemPoolValid` tinyint(4) NOT NULL,
  `device_id` int(11) NOT NULL,
  `cempMemPoolUsed` int(11) NOT NULL,
  `cempMemPoolFree` int(11) NOT NULL,
  `cempMemPoolLargestFree` int(11) NOT NULL,
  `cempMemPoolLowestFree` int(11) NOT NULL,
  PRIMARY KEY  (`cempMemPool_id`),
  KEY `device_id` (`device_id`)
) ENGINE=InnoDB  DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `cpmCPU`
--

CREATE TABLE `cpmCPU` (
  `cpmCPU_id` int(11) NOT NULL auto_increment,
  `entPhysicalIndex` int(11) NOT NULL,
  `device_id` int(11) NOT NULL,
  `cpmCPU_oid` int(11) NOT NULL,
  `cpmCPUTotal5minRev` int(11) NOT NULL,
  `entPhysicalDescr` varchar(64) NOT NULL,
  PRIMARY KEY  (`cpmCPU_id`),
  KEY `cpuCPU_id` (`cpmCPU_id`,`device_id`)
) ENGINE=InnoDB  DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `customers`
--

CREATE TABLE `customers` (
  `customer_id` int(11) NOT NULL auto_increment,
  `username` char(64) NOT NULL,
  `password` char(32) NOT NULL,
  `string` char(64) NOT NULL,
  `level` tinyint(4) NOT NULL default '0',
  PRIMARY KEY  (`customer_id`),
  UNIQUE KEY `username` (`username`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `devices`
--

CREATE TABLE `devices` (
  `device_id` int(11) NOT NULL auto_increment,
  `hostname` varchar(128) NOT NULL,
  `sysName` varchar(128) default NULL,
  `community` varchar(32) NOT NULL,
  `snmpver` varchar(4) NOT NULL default 'v2c',
  `port` smallint(5) NOT NULL default '161',
  `bgpLocalAs` varchar(16) default NULL,
  `sysDescr` text,
  `sysContact` text,
  `version` text,
  `hardware` text,
  `features` text,
  `location` text,
  `os` varchar(12) default NULL,
  `status` tinyint(4) NOT NULL default '0',
  `ignore` tinyint(4) NOT NULL default '0',
  `disabled` tinyint(1) NOT NULL default '0',
  `lastchange` timestamp NOT NULL default CURRENT_TIMESTAMP on update CURRENT_TIMESTAMP,
  `last_polled` timestamp NOT NULL default '0000-00-00 00:00:00',
  `purpose` varchar(64) default NULL,
  `type` varchar(8) NOT NULL default 'unknown',
  PRIMARY KEY  (`device_id`),
  KEY `status` (`status`),
  KEY `hostname` (`hostname`),
  KEY `sysName` (`sysName`),
  KEY `os` (`os`)
) ENGINE=InnoDB  DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `devices_attribs`
--

CREATE TABLE `devices_attribs` (
  `attrib_id` int(11) NOT NULL auto_increment,
  `device_id` int(11) NOT NULL,
  `attrib_type` varchar(32) NOT NULL,
  `attrib_value` text NOT NULL,
  `updated` timestamp NOT NULL default CURRENT_TIMESTAMP on update CURRENT_TIMESTAMP,
  PRIMARY KEY  (`attrib_id`)
) ENGINE=InnoDB  DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `devices_perms`
--

CREATE TABLE `devices_perms` (
  `user_id` int(11) NOT NULL,
  `device_id` int(11) NOT NULL,
  `access_level` int(4) NOT NULL default '0',
  KEY `user_id` (`user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `entPhysical`
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
  `entAliasMappingIdentifier` varchar(32) default NULL,
  `entSensorType` varchar(16) default NULL,
  `entSensorScale` varchar(16) default NULL,
  `entSensorPrecision` int(11) default NULL,
  `entSensorValue` int(11) default NULL,
  `entSensorStatus` varchar(8) default NULL,
  `entSensorMeasuredEntity` int(11) default NULL,
  `ifIndex` int(11) default NULL,
  PRIMARY KEY  (`entPhysical_id`),
  KEY `device_id` (`device_id`)
) ENGINE=InnoDB  DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `eventlog`
--

CREATE TABLE `eventlog` (
  `id` int(11) NOT NULL default '0',
  `host` int(11) NOT NULL default '0',
  `interface` int(11) default NULL,
  `datetime` datetime NOT NULL default '0000-00-00 00:00:00',
  `message` text NOT NULL,
  `type` int(11) NOT NULL,
  KEY `host` (`host`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `interfaces`
--

CREATE TABLE `interfaces` (
  `interface_id` int(11) NOT NULL auto_increment,
  `device_id` int(11) NOT NULL default '0',
  `ifDescr` varchar(128) NOT NULL,
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
  `deleted` tinyint(1) NOT NULL default '0',
  PRIMARY KEY  (`interface_id`),
  KEY `host` (`device_id`),
  KEY `snmpid` (`ifIndex`),
  KEY `if_2` (`ifDescr`)
) ENGINE=InnoDB  DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `interfaces_perms`
--

CREATE TABLE `interfaces_perms` (
  `user_id` int(11) NOT NULL,
  `interface_id` int(11) NOT NULL,
  `access_level` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `ipv4_addresses`
--

CREATE TABLE `ipv4_addresses` (
  `ipv4_address_id` int(11) NOT NULL auto_increment,
  `ipv4_address` varchar(32) NOT NULL,
  `ipv4_prefixlen` int(11) NOT NULL,
  `ipv4_network_id` varchar(32) NOT NULL,
  `interface_id` int(11) NOT NULL,
  PRIMARY KEY  (`ipv4_address_id`)
) ENGINE=InnoDB  DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `ipv4_mac`
--

CREATE TABLE `ipv4_mac` (
  `interface_id` int(11) NOT NULL,
  `mac_address` varchar(32) NOT NULL,
  `ipv4_address` varchar(32) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `ipv4_networks`
--

CREATE TABLE `ipv4_networks` (
  `ipv4_network_id` int(11) NOT NULL auto_increment,
  `ipv4_network` varchar(64) NOT NULL,
  PRIMARY KEY  (`ipv4_network_id`)
) ENGINE=InnoDB  DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `ipv6_addresses`
--

CREATE TABLE `ipv6_addresses` (
  `ipv6_address_id` int(11) NOT NULL auto_increment,
  `ipv6_address` varchar(128) NOT NULL,
  `ipv6_compressed` varchar(128) NOT NULL,
  `ipv6_prefixlen` int(11) NOT NULL,
  `ipv6_origin` varchar(16) NOT NULL,
  `ipv6_network_id` varchar(128) NOT NULL,
  `interface_id` int(11) NOT NULL,
  PRIMARY KEY  (`ipv6_address_id`)
) ENGINE=InnoDB  DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `ipv6_networks`
--

CREATE TABLE `ipv6_networks` (
  `ipv6_network_id` int(11) NOT NULL auto_increment,
  `ipv6_network` varchar(64) NOT NULL,
  PRIMARY KEY  (`ipv6_network_id`)
) ENGINE=InnoDB  DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `links`
--

CREATE TABLE `links` (
  `id` int(11) NOT NULL auto_increment,
  `src_if` int(11) default NULL,
  `dst_if` int(11) default NULL,
  `active` tinyint(4) NOT NULL default '1',
  `cdp` int(11) default NULL,
  PRIMARY KEY  (`id`),
  KEY `src_if` (`src_if`),
  KEY `dst_if` (`dst_if`)
) ENGINE=InnoDB  DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `mac_accounting`
--

CREATE TABLE `mac_accounting` (
  `ma_id` int(11) NOT NULL auto_increment,
  `interface_id` int(11) NOT NULL,
  `mac` varchar(32) NOT NULL,
  `bps_out` int(11) NOT NULL,
  `bps_in` int(11) NOT NULL,
  `pps_in` int(11) NOT NULL,
  `pps_out` int(11) NOT NULL,
  PRIMARY KEY  (`ma_id`)
) ENGINE=InnoDB  DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `port_in_measurements`
--

CREATE TABLE `port_in_measurements` (
  `port_id` int(11) NOT NULL,
  `timestamp` timestamp NOT NULL default CURRENT_TIMESTAMP on update CURRENT_TIMESTAMP,
  `counter` bigint(11) NOT NULL,
  `delta` bigint(11) NOT NULL,
  KEY `port_id` (`port_id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `port_out_measurements`
--

CREATE TABLE `port_out_measurements` (
  `port_id` int(11) NOT NULL,
  `timestamp` timestamp NOT NULL default CURRENT_TIMESTAMP on update CURRENT_TIMESTAMP,
  `counter` bigint(11) NOT NULL,
  `delta` bigint(11) NOT NULL,
  KEY `port_id` (`port_id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `pseudowires`
--

CREATE TABLE `pseudowires` (
  `pseudowire_id` int(11) NOT NULL auto_increment,
  `interface_id` int(11) NOT NULL,
  `peer_device_id` int(11) NOT NULL,
  `peer_ldp_id` int(11) NOT NULL,
  `cpwVcID` int(11) NOT NULL,
  `cpwOid` int(11) NOT NULL,
  PRIMARY KEY  (`pseudowire_id`)
) ENGINE=InnoDB  DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `services`
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
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `storage`
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
) ENGINE=InnoDB  DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `syslog`
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
) ENGINE=InnoDB  DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `temperature`
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
) ENGINE=InnoDB  DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `user_id` int(11) NOT NULL auto_increment,
  `username` char(30) NOT NULL,
  `password` char(32) NOT NULL,
  `realname` varchar(64) NOT NULL,
  `email` varchar(64) NOT NULL,
  `descr` char(30) NOT NULL,
  `level` tinyint(4) NOT NULL default '0',
  PRIMARY KEY  (`user_id`),
  UNIQUE KEY `username` (`username`)
) ENGINE=InnoDB  DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `users_prefs`
--

CREATE TABLE `users_prefs` (
  `user_id` int(16) NOT NULL,
  `pref` varchar(32) NOT NULL,
  `value` varchar(128) NOT NULL,
  PRIMARY KEY  (`user_id`),
  UNIQUE KEY `user_id.pref` (`user_id`,`pref`),
  KEY `pref` (`pref`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `vlans`
--

CREATE TABLE `vlans` (
  `vlan_id` int(11) NOT NULL auto_increment,
  `device_id` int(11) default NULL,
  `vlan_vlan` int(11) default NULL,
  `vlan_domain` text,
  `vlan_descr` text,
  PRIMARY KEY  (`vlan_id`),
  KEY `device_id` (`device_id`,`vlan_vlan`)
) ENGINE=InnoDB  DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `vrfs`
--

CREATE TABLE `vrfs` (
  `vrf_id` int(11) NOT NULL auto_increment,
  `vrf_oid` varchar(256) NOT NULL,
  `vrf_name` varchar(32) NOT NULL,
  `mplsVpnVrfRouteDistinguisher` varchar(16) NOT NULL,
  `mplsVpnVrfDescription` text NOT NULL,
  `device_id` int(11) NOT NULL,
  PRIMARY KEY  (`vrf_id`),
  KEY `device_id` (`device_id`)
) ENGINE=InnoDB  DEFAULT CHARSET=latin1;

