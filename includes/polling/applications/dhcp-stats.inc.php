<?php
$name = 'dhcp-stats';
$app_id = $app['app_id'];
$options      = '-O qv';
$mib          = 'NET-SNMP-EXTEND-MIB';
$oid          = '.1.3.6.1.4.1.8072.1.3.2.4.1.2.9.100.104.99.112.115.116.97.116.115';

$dhcpstats = snmp_walk($device, $oid, $options, $mib);
list($dhcp_total,$dhcp_active,$dhcp_expired,$dhcp_released,$dhcp_abandoned,$dhcp_reset,$dhcp_bootp,$dhcp_backup,$dhcp_free) = explode("\n", $dhcpstats);

$rrd_name = array('app', $name, $app_id);
$rrd_def = array(
    'DS:dhcp_total:GAUGE:600:0:U',
    'DS:dhcp_active:GAUGE:600:0:U',
    'DS:dhcp_expired:GAUGE:600:0:U',
    'DS:dhcp_released:GAUGE:600:0:U',
    'DS:dhcp_abandoned:GAUGE:600:0:U',
    'DS:dhcp_reset:GAUGE:600:0:U',
    'DS:dhcp_bootp:GAUGE:600:0:U',
    'DS:dhcp_backup:GAUGE:600:0:U',
    'DS:dhcp_free:GAUGE:600:0:U',
);

$fields = array(
    'dhcp_total' => $dhcp_total,
    'dhcp_active' => $dhcp_active,
    'dhcp_expired' => $dhcp_expired,
    'dhcp_released' => $dhcp_released,
    'dhcp_abandoned' => $dhcp_abandoned,
    'dhcp_reset' => $dhcp_reset,
    'dhcp_bootp' => $dhcp_bootp,
    'dhcp_backup' => $dhcp_backup,
    'dhcp_free' => $dhcp_free,
);

$tags = array('name' => $name, 'app_id' => $app_id, 'rrd_def' => $rrd_def, 'rrd_name' => $rrd_name);
data_update($device, 'app', $tags, $fields);
