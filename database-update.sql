## 0.10.7.1
ALTER TABLE `bills` ADD `bill_autoadded` BOOLEAN NOT NULL DEFAULT '0';
ALTER TABLE `bill_ports` ADD `bill_port_autoadded` BOOLEAN NOT NULL DEFAULT '0';
ALTER TABLE `sensors` CHANGE  `sensor_precision`  `sensor_divisor` INT( 11 ) NOT NULL DEFAULT  '1'
ALTER TABLE `sensors` ADD  `sensor_multiplier` INT( 11 ) NOT NULL AFTER  `sensor_divisor`;
CREATE TABLE IF NOT EXISTS `device_graphs` (  `device_id` int(11) NOT NULL,  `graph` varchar(32) COLLATE utf8_unicode_ci NOT NULL ) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
DROP TABLE IF EXISTS `graph_types`;
CREATE TABLE IF NOT EXISTS `graph_types` (  `graph_type` varchar(32) COLLATE utf8_unicode_ci NOT NULL,  `graph_subtype` varchar(32) COLLATE utf8_unicode_ci NOT NULL,  `graph_section` varchar(32) COLLATE utf8_unicode_ci NOT NULL,  `graph_descr` varchar(64) COLLATE utf8_unicode_ci NOT NULL,  `graph_order` int(11) NOT NULL,  KEY `graph_type` (`graph_type`),  KEY `graph_subtype` (`graph_subtype`),  KEY `graph_section` (`graph_section`)) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
INSERT INTO `graph_types` (`graph_type`, `graph_subtype`, `graph_section`, `graph_descr`, `graph_order`) VALUES('device', 'bits', 'netstats', 'Total Traffic', 0),('device', 'hr_users', 'system', 'Users Logged In', 0),('device', 'ucd_load', 'system', 'Load Averages', 0),('device', 'ucd_cpu', 'system', 'Detailed Processor Usage', 0),('device', 'ucd_memory', 'system', 'Detailed Memory Usage', 0),('device', 'netstat_tcp', 'netstats', 'TCP Statistics', 0),('device', 'netstat_icmp_info', 'netstats', 'ICMP Informational Statistics', 0),('device', 'netstat_icmp_stat', 'netstats', 'ICMP Statistics', 0),('device', 'netstat_ip', 'netstats', 'IP Statistics', 0),('device', 'netstat_ip_frag', 'netstats', 'IP Fragmentation Statistics', 0),('device', 'netstat_udp', 'netstats', 'UDP Statistics', 0),('device', 'netstat_snmp', 'netstats', 'SNMP Statistics', 0),('device', 'temperatures', 'system', 'Temperatures', 0),('device', 'mempools', 'system', 'Memory Pool Usage', 0),('device', 'processors', 'system', 'Processor Usage', 0),('device', 'storage', 'system', 'Filesystem Usage', 0),('device', 'hr_processes', 'system', 'Running Processes', 0),('device', 'uptime', 'system', 'System Uptime', ''),('device', 'ipsystemstats_ipv4', 'netstats', 'IPv4 Packet Statistics', 0),('device', 'ipsystemstats_ipv6_frag', 'netstats', 'IPv6 Fragmentation Statistics', 0),('device', 'ipsystemstats_ipv6', 'netstats', 'IPv6 Packet Statistics', 0),('device', 'ipsystemstats_ipv4_frag', 'netstats', 'IPv4 Fragmentation Statistics', 0),('device',  'fortigate_sessions',  'firewall',  'Active Sessions',  ''), ('device',  'screenos_sessions',  'firewall',  'Active Sessions',  ''), ('device',  'fdb_count',  'system',  'MAC Addresses Learnt',  '0'),('device', 'cras_sessions', 'firewall', 'Remote Access Sessions', 0);
DROP TABLE `frequency`;
ALTER TABLE  `mempools` CHANGE  `mempool_index`  `mempool_index` VARCHAR( 16 ) CHARACTER SET latin1 COLLATE latin1_swedish_ci NOT NULL;
ALTER TABLE `vrfs` CHANGE `mplsVpnVrfRouteDistinguisher` `mplsVpnVrfRouteDistinguisher` varchar(26) default NOT NULL;
## Change port rrds
ALTER TABLE  `devices` ADD  `timeout` INT NULL DEFAULT NULL AFTER  `port`;
ALTER TABLE `devices` ADD `retries` INT NULL DEFAULT NULL AFTER `timeout`;
ALTER TABLE  `perf_times` CHANGE  `duration`  `duration` DOUBLE( 8, 2 ) NOT NULL
ALTER TABLE `sensors` ADD `poller_type` VARCHAR(16) NOT NULL DEFAULT 'snmp' AFTER `device_id`;
## Add transport
ALTER TABLE `devices` ADD `transport` VARCHAR(16) NOT NULL DEFAULT 'udp' AFTER `port`;
## Extend port descriptions
ALTER TABLE ports MODIFY port_descr_circuit VARCHAR(255);
ALTER TABLE ports MODIFY port_descr_descr VARCHAR(255);
ALTER TABLE ports MODIFY port_descr_notes VARCHAR(255);
ALTER TABLE devices MODIFY community VARCHAR(255);
