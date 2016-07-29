<?php
$filename = array('app', 'os-updates', $app['app_id']);
$rrd      = rrd_name($device['hostname'], $filename);
$options  = '-O qv';
$mib      = 'NET-SNMP-EXTEND-MIB';
$oid	  = '.1.3.6.1.4.1.8072.1.3.2.4.1.2.8.111.115.117.112.100.97.116.101.1';

$osupdates = snmp_get($device, $oid, $options, $mib);

if(!is_file($rrd))
{
    rrdtool_create(
        $rrd,
        '--step 300
        DS:packages:GAUGE:600:0:U
        '.$config['rrd_rra']
    );
}

$fields = array(
    'packages' => $osupdates,
);

$tags = array('name' => 'os-updates', 'app_id' => $app['app_id']);

data_update($device,$filename,$tags,$fields);
