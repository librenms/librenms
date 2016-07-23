<?php
$rrd_filename = $config['rrd_dir'].'/'.$device['hostname'].'/app-os-updates-'.$app['app_id'].'.rrd';
$options      = '-O qv';
$mib          = 'NET-SNMP-EXTEND-MIB';
$oid	      = '.1.3.6.1.4.1.8072.1.3.2.4.1.2.8.111.115.117.112.100.97.116.101.1';
echo 'os-updates';

$osupdates = snmp_get($device, $oid, $options, $mib);

if(!is_file($rrd_filename))
{
    rrdtool_create(
        $rrd_filename,
        '--step 300
        DS:packages:GAUGE:600:0:U
        '.$config['rrd_rra']
    );
}

$fields = array(
    'packages' => $osupdates,
);

rrdtool_update($rrd_filename, $fields);
$tags = array('name' => 'os-updates', 'app_id' => $app['app_id']);
influx_update($device,'app',$tags,$fields);
