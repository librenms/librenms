-- --------------------------------------------------------

-- 
-- Table structure for table `adjacencies`
-- 

CREATE TABLE `adjacencies` (
  `adj_id` int(11) NOT NULL auto_increment,
  `network_id` int(11) NOT NULL default '0',
  `interface_id` int(11) NOT NULL default '0',
  PRIMARY KEY  (`adj_id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1 ;

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
) ENGINE=MyISAM DEFAULT CHARSET=latin1 ;

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
) ENGINE=MyISAM DEFAULT CHARSET=latin1 ;

-- --------------------------------------------------------

-- 
-- Table structure for table `devices`
-- 

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
) ENGINE=MyISAM DEFAULT CHARSET=latin1 ;

-- --------------------------------------------------------

-- 
-- Table structure for table `devices_attribs`
-- 

CREATE TABLE `devices_attribs` (
  `attrib_id` int(11) NOT NULL auto_increment,
  `device_id` int(11) NOT NULL,
  `attrib_type` varchar(32) NOT NULL,
  `attrib_value` varchar(256) NOT NULL,
  PRIMARY KEY  (`attrib_id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1 ;

-- --------------------------------------------------------

-- 
-- Table structure for table `devices_perms`
-- 

CREATE TABLE `devices_perms` (
  `user_id` int(11) NOT NULL,
  `device_id` int(11) NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

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
-- Table structure for table `interface_measurements`
-- 

CREATE TABLE `interface_measurements` (
  `interface_id` int(11) NOT NULL,
  `timestamp` timestamp NOT NULL default CURRENT_TIMESTAMP on update CURRENT_TIMESTAMP,
  `period` int(11) NOT NULL,
  `delta_in` int(11) NOT NULL,
  `delta_out` int(11) NOT NULL,
  `rate_in` int(11) NOT NULL,
  `rate_out` int(11) NOT NULL
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
  `ifVlan` int(11) default NULL,
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
) ENGINE=MyISAM DEFAULT CHARSET=latin1 ;

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
) ENGINE=MyISAM DEFAULT CHARSET=latin1 ;

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
) ENGINE=MyISAM DEFAULT CHARSET=latin1 ;

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
  KEY `program` (`program`),
  KEY `datetime` (`datetime`),
  KEY `priority` (`priority`),
  KEY `facility` (`facility`),
  KEY `seq` (`seq`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1 ;

-- --------------------------------------------------------

-- 
-- Table structure for table `networks`
-- 

CREATE TABLE `networks` (
  `id` int(11) NOT NULL auto_increment,
  `cidr` varchar(32) NOT NULL default '',
  PRIMARY KEY  (`id`),
  UNIQUE KEY `cidr_2` (`cidr`),
  FULLTEXT KEY `cidr` (`cidr`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1 ;

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
) ENGINE=MyISAM DEFAULT CHARSET=latin1 ;

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
) ENGINE=MyISAM DEFAULT CHARSET=latin1 ;

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
  KEY `program` (`program`),
  KEY `datetime` (`datetime`),
  KEY `priority` (`priority`),
  KEY `facility` (`facility`),
  KEY `seq` (`seq`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1 ;

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
  `temp_limit` tinyint(4) NOT NULL default '70',
  PRIMARY KEY  (`temp_id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1 ;

-- --------------------------------------------------------

-- 
-- Table structure for table `users`
-- 

CREATE TABLE `users` (
  `user_id` int(11) NOT NULL auto_increment,
  `username` char(30) NOT NULL,
  `password` char(32) NOT NULL,
  `descr` char(30) NOT NULL,
  `level` tinyint(4) NOT NULL default '0',
  PRIMARY KEY  (`user_id`),
  UNIQUE KEY `username` (`username`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1 ;

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
) ENGINE=MyISAM DEFAULT CHARSET=latin1 ;

