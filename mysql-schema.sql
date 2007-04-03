-- 
-- Table structure for table `adjacencies`
-- 

CREATE TABLE IF NOT EXISTS `adjacencies` (
  `adj_id` int(11) NOT NULL auto_increment,
  `network_id` int(11) NOT NULL default '0',
  `interface_id` int(11) NOT NULL default '0',
  PRIMARY KEY  (`adj_id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1 AUTO_INCREMENT=125 ;

-- --------------------------------------------------------

-- 
-- Table structure for table `alerts`
-- 

CREATE TABLE IF NOT EXISTS `alerts` (
  `id` int(11) NOT NULL auto_increment,
  `importance` int(11) NOT NULL default '0',
  `device_id` int(11) NOT NULL,
  `message` text NOT NULL,
  `time_logged` timestamp NOT NULL default CURRENT_TIMESTAMP,
  `alerted` smallint(6) NOT NULL default '0',
  KEY `id` (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1 AUTO_INCREMENT=882 ;

-- --------------------------------------------------------

-- 
-- Table structure for table `customers`
-- 

CREATE TABLE IF NOT EXISTS `customers` (
  `username` char(64) NOT NULL,
  `password` char(32) NOT NULL,
  `string` char(64) NOT NULL,
  `level` tinyint(4) NOT NULL default '0',
  UNIQUE KEY `username` (`username`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

-- 
-- Table structure for table `devices`
-- 

CREATE TABLE IF NOT EXISTS `devices` (
  `id` int(11) NOT NULL auto_increment,
  `hostname` varchar(64) NOT NULL default '',
  `ip` varchar(16) NOT NULL default '',
  `community` varchar(32) NOT NULL default '',
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
  `uptime` int(11) NOT NULL default '0',
  `lastchange` timestamp NOT NULL default CURRENT_TIMESTAMP on update CURRENT_TIMESTAMP,
  `purpose` text NOT NULL,
  `apache` tinyint(4) NOT NULL default '0',
  `courier` tinyint(4) NOT NULL default '0',
  `postfix` tinyint(4) NOT NULL default '0',
  `type` varchar(8) NOT NULL default 'other',
  PRIMARY KEY  (`id`),
  UNIQUE KEY `hostname` (`hostname`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1 AUTO_INCREMENT=55 ;

-- --------------------------------------------------------

-- 
-- Table structure for table `eventlog`
-- 

CREATE TABLE IF NOT EXISTS `eventlog` (
  `id` int(11) NOT NULL default '0',
  `host` int(11) NOT NULL default '0',
  `interface` int(11) default NULL,
  `datetime` datetime NOT NULL default '0000-00-00 00:00:00',
  `message` text NOT NULL,
  `type` int(11) NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

-- 
-- Table structure for table `interfaces`
-- 

CREATE TABLE IF NOT EXISTS `interfaces` (
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
  `ignore` tinyint(1) NOT NULL default '0',
  PRIMARY KEY  (`id`),
  KEY `host` (`host`),
  KEY `snmpid` (`ifIndex`),
  KEY `if_2` (`if`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1 AUTO_INCREMENT=3516 ;

-- --------------------------------------------------------

-- 
-- Table structure for table `ipaddr`
-- 

CREATE TABLE IF NOT EXISTS `ipaddr` (
  `id` int(11) NOT NULL auto_increment,
  `addr` varchar(32) NOT NULL default '',
  `cidr` smallint(6) NOT NULL default '0',
  `network` varchar(64) NOT NULL default '',
  `interface_id` int(11) NOT NULL default '0',
  PRIMARY KEY  (`id`),
  KEY `addr` (`addr`,`cidr`,`interface_id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1 AUTO_INCREMENT=589 ;

-- --------------------------------------------------------

-- 
-- Table structure for table `links`
-- 

CREATE TABLE IF NOT EXISTS `links` (
  `id` int(11) NOT NULL auto_increment,
  `src_if` int(11) default NULL,
  `dst_if` int(11) default NULL,
  `active` tinyint(4) NOT NULL default '1',
  `cdp` int(11) default NULL,
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1 AUTO_INCREMENT=568 ;

-- --------------------------------------------------------

-- 
-- Table structure for table `logs`
-- 

CREATE TABLE IF NOT EXISTS `logs` (
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
) ENGINE=MyISAM DEFAULT CHARSET=latin1 AUTO_INCREMENT=1955040 ;

-- --------------------------------------------------------

-- 
-- Table structure for table `networks`
-- 

CREATE TABLE IF NOT EXISTS `networks` (
  `id` int(11) NOT NULL auto_increment,
  `cidr` varchar(32) NOT NULL default '',
  PRIMARY KEY  (`id`),
  UNIQUE KEY `cidr_2` (`cidr`),
  FULLTEXT KEY `cidr` (`cidr`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1 AUTO_INCREMENT=391 ;

-- --------------------------------------------------------

-- 
-- Table structure for table `services`
-- 

CREATE TABLE IF NOT EXISTS `services` (
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
) ENGINE=MyISAM DEFAULT CHARSET=latin1 AUTO_INCREMENT=45 ;

-- --------------------------------------------------------

-- 
-- Table structure for table `storage`
-- 

CREATE TABLE IF NOT EXISTS `storage` (
  `storage_id` int(11) NOT NULL auto_increment,
  `host_id` int(11) NOT NULL,
  `hrStorageIndex` int(11) NOT NULL,
  `hrStorageDescr` text NOT NULL,
  `hrStorageSize` int(11) NOT NULL,
  `hrStorageAllocationUnits` int(11) NOT NULL,
  PRIMARY KEY  (`storage_id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1 AUTO_INCREMENT=21 ;

-- --------------------------------------------------------

-- 
-- Table structure for table `syslog`
-- 

CREATE TABLE IF NOT EXISTS `syslog` (
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
) ENGINE=MyISAM DEFAULT CHARSET=latin1 AUTO_INCREMENT=1385390 ;

-- --------------------------------------------------------

-- 
-- Table structure for table `temperature`
-- 

CREATE TABLE IF NOT EXISTS `temperature` (
  `temp_id` int(11) NOT NULL auto_increment,
  `temp_host` int(11) NOT NULL default '0',
  `temp_oid` varchar(32) NOT NULL default '',
  `temp_descr` varchar(32) NOT NULL default '',
  `temp_current` tinyint(4) NOT NULL default '0',
  `temp_limit` tinyint(4) NOT NULL default '70',
  PRIMARY KEY  (`temp_id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1 AUTO_INCREMENT=91 ;

-- --------------------------------------------------------

-- 
-- Table structure for table `users`
-- 

CREATE TABLE IF NOT EXISTS `users` (
  `username` char(30) NOT NULL,
  `password` char(32) NOT NULL,
  `descr` char(30) NOT NULL,
  `level` tinyint(4) NOT NULL default '0',
  UNIQUE KEY `username` (`username`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

INSERT INTO `users` VALUES ('admin','6033c66e583283ac','Default User',10);
