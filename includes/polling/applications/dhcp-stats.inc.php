<?php
$rrd_filename = $config['rrd_dir'].'/'.$device['hostname'].'/app-dhcp-stats-'.$app['app_id'].'.rrd';
$options      = '-O qv';
$mib          = 'NET-SNMP-EXTEND-MIB';
$oid          = '.1.3.6.1.4.1.8072.1.3.2.4.1.2.9.100.104.99.112.115.116.97.116.115';
echo 'dhcp-stats';

$dhcpstats = snmp_walk($device, $oid, $options, $mib);
list($dhcp_total,$dhcp_active,$dhcp_backup,$dhcp_free) = explode("\n",$dhcpstats);

if(!is_file($rrd_filename))
{
    rrdtool_create(
        $rrd_filename,
        '--step 300
        DS:dhcp_total:GAUGE:600:0:U
        DS:dhcp_active:GAUGE:600:0:U
        DS:dhcp_backup:GAUGE:600:0:U
        DS:dhcp_free:GAUGE:600:0:U
        '.$config['rrd_rra']
    );
}

$fields = array(
    'dhcp_total' => $dhcp_total,
    'dhcp_active' => $dhcp_active,
    'dhcp_backup' => $dhcp_backup,
    'dhcp_free' => $dhcp_free,
);

rrdtool_update($rrd_filename, $fields);
$tags = array('name' => 'dhcp-stats', 'app_id' => $app['app_id']);
influx_update($device,'app',$tags,$fields);
