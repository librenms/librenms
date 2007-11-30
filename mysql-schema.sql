-- phpMyAdmin SQL Dump
-- version 2.8.1-Debian-1~dapper1
-- http://www.phpmyadmin.net
-- 
-- Host: fennel.vostron.net
-- Generation Time: Nov 30, 2007 at 02:47 PM
-- Server version: 5.0.22
-- PHP Version: 5.1.2
-- 
-- Database: `vostron_network`
-- 

-- --------------------------------------------------------

-- 
-- Table structure for table `adjacencies`
-- 

CREATE TABLE `adjacencies` (
  `adj_id` int(11) NOT NULL auto_increment,
  `network_id` int(11) NOT NULL default '0',
  `interface_id` int(11) NOT NULL default '0',
  PRIMARY KEY  (`adj_id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1 AUTO_INCREMENT=316 ;

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
) ENGINE=MyISAM DEFAULT CHARSET=latin1 AUTO_INCREMENT=4765 ;

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
) ENGINE=MyISAM DEFAULT CHARSET=latin1 AUTO_INCREMENT=22 ;

-- --------------------------------------------------------

-- 
-- Table structure for table `devices`
-- 

CREATE TABLE `devices` (
  `device_id` int(11) NOT NULL auto_increment,
  `hostname` text NOT NULL,
  `community` varchar(32) NOT NULL default 'v05tr0n82',
  `snmpver` varchar(4) NOT NULL default 'v2c',
  `sysDescr` text,
  `sysContact` text NOT NULL,
  `monowall` tinyint(4) NOT NULL default '0',
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
  `apache` tinyint(4) NOT NULL default '0',
  `courier` tinyint(4) NOT NULL default '0',
  `postfix` tinyint(4) NOT NULL default '0',
  `type` varchar(8) NOT NULL default 'other',
  PRIMARY KEY  (`device_id`),
  KEY `status` (`status`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1 AUTO_INCREMENT=92 ;

-- --------------------------------------------------------

-- 
-- Table structure for table `devices_attribs`
-- 

CREATE TABLE `devices_attribs` (
  `attrib_id` int(11) NOT NULL auto_increment,
  `device_id` int(11) NOT NULL,
  `attrib_type` varchar(32) NOT NULL,
  `attrib_value` int(11) NOT NULL,
  PRIMARY KEY  (`attrib_id`),
  FULLTEXT KEY `attrib_type` (`attrib_type`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1 AUTO_INCREMENT=78 ;

-- --------------------------------------------------------

-- 
-- Table structure for table `devices_perms`
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
) ENGINE=MyISAM DEFAULT CHARSET=latin1 AUTO_INCREMENT=931 ;

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
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

-- 
-- Table structure for table `interfaces`
-- 

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
  `ifVlan` varchar(8) NOT NULL default '',
  `ifTrunk` varchar(8) default '',
  `in_rate` int(11) NOT NULL,
  `out_rate` int(11) NOT NULL,
  `counter_in` int(11) default NULL,
  `counter_out` int(11) default NULL,
  `in_errors` int(11) NOT NULL,
  `out_errors` int(11) NOT NULL,
  `ignore` tinyint(1) NOT NULL default '0',
  `detailed` tinyint(1) NOT NULL default '0',
  PRIMARY KEY  (`interface_id`),
  KEY `host` (`device_id`),
  KEY `snmpid` (`ifIndex`),
  KEY `if_2` (`ifDescr`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1 AUTO_INCREMENT=4784 ;

-- --------------------------------------------------------

-- 
-- Table structure for table `ipaddr`
-- 

CREATE TABLE `ipaddr` (
  `id` int(11) NOT NULL auto_increment,
  `addr` varchar(32) NOT NULL default '',
  `cidr` smallint(6) NOT NULL default '0',
  `network` varchar(64) NOT NULL default '',
  `interface_id` int(11) NOT NULL default '0',
  PRIMARY KEY  (`id`),
  KEY `addr` (`addr`,`cidr`,`interface_id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1 AUTO_INCREMENT=2578 ;

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
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1 AUTO_INCREMENT=303 ;

-- --------------------------------------------------------

-- 
-- Table structure for table `logs`
-- 

CREATE TABLE `logs` (
  `host` varchar(32) default NULL,
  `facility` varchar(10) default NULL,
  `priority` varchar(10) default NULL,
  `level` varchar(10) default NULL,
  `tag` varchar(10) default NULL,
  `datetime` datetime default NULL,
  `program` varchar(32) default NULL,
  `msg` text,
  `seq` bigint(20) unsigned NOT NULL auto_increment,
  PRIMARY KEY  (`seq`),
  KEY `host` (`host`),
  KEY `datetime` (`datetime`),
  KEY `seq` (`seq`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1 AUTO_INCREMENT=12203343 ;

-- --------------------------------------------------------

-- 
-- Table structure for table `networks`
-- 

CREATE TABLE `networks` (
  `id` int(11) NOT NULL auto_increment,
  `cidr` varchar(32) NOT NULL default '',
  PRIMARY KEY  (`id`),
  UNIQUE KEY `cidr` (`cidr`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1 AUTO_INCREMENT=542 ;

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
  PRIMARY KEY  (`service_id`),
  KEY `service_host` (`service_host`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1 AUTO_INCREMENT=61 ;

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
) ENGINE=MyISAM DEFAULT CHARSET=latin1 AUTO_INCREMENT=88 ;

-- --------------------------------------------------------

-- 
-- Table structure for table `syslog`
-- 

CREATE TABLE `syslog` (
  `host` int(11) default NULL,
  `facility` varchar(10) default NULL,
  `priority` varchar(10) default NULL,
  `level` varchar(10) default NULL,
  `tag` varchar(10) default NULL,
  `datetime` datetime default NULL,
  `program` varchar(32) default NULL,
  `msg` text,
  `seq` bigint(20) unsigned NOT NULL auto_increment,
  PRIMARY KEY  (`seq`),
  KEY `host` (`host`),
  KEY `datetime` (`datetime`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1 AUTO_INCREMENT=9375565 ;

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
) ENGINE=MyISAM DEFAULT CHARSET=latin1 AUTO_INCREMENT=2789 ;

-- --------------------------------------------------------

-- 
-- Table structure for table `users`
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
) ENGINE=MyISAM DEFAULT CHARSET=latin1 AUTO_INCREMENT=16 ;

INSERT into users (username, password, descr, level) values ('admin',MD5('observer'),'Admin',10);

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
) ENGINE=MyISAM DEFAULT CHARSET=latin1 AUTO_INCREMENT=1016 ;

