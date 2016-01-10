<?php

// Polls shoutcast statistics from script via SNMP
$options = '-O qv';
$oid     = 'nsExtendOutputFull.9.115.104.111.117.116.99.97.115.116';

$shoutcast = snmp_get($device, $oid, $options);

echo ' shoutcast';

$servers = explode("\n", $shoutcast);

foreach ($servers as $item => $server) {
    $server = trim($server);

    if (!empty($server)) {
        $data              = explode(';', $server);
        list($host, $port) = split(':', $data['0'], 2);
        $bitrate           = $data['1'];
        $traf_in           = $data['2'];
        $traf_out          = $data['3'];
        $current           = $data['4'];
        $status            = $data['5'];
        $peak              = $data['6'];
        $max               = $data['7'];
        $unique            = $data['8'];
        $rrdfile           = $config['rrd_dir'].'/'.$device['hostname'].'/app-shoutcast-'.$app['app_id'].'-'.$host.'_'.$port.'.rrd';

        if (!is_file($rrdfile)) {
            rrdtool_create(
                $rrdfile,
                '--step 300 
                DS:bitrate:GAUGE:600:0:125000000000 
                DS:traf_in:GAUGE:600:0:125000000000 
                DS:traf_out:GAUGE:600:0:125000000000 
                DS:current:GAUGE:600:0:125000000000 
                DS:status:GAUGE:600:0:125000000000 
                DS:peak:GAUGE:600:0:125000000000 
                DS:max:GAUGE:600:0:125000000000 
                DS:unique:GAUGE:600:0:125000000000 '.$config['rrd_rra']
            );
        }

        $fields = array(
                        'bitrate'    => $bitrate,
                        'traf_in'    => $traf_in,
                        'traf_out'   => $traf_out,
                        'current'    => $current,
                        'status'     => $status,
                        'peak'       => $peak,
                        'max'        => $max,
                        'unique'     => $unique,
        );

        rrdtool_update($rrdfile, $fields);

        $tags = array('name' => 'shoutcast', 'app_id' => $app['app_id'], 'host' => $host, 'port' => $port);
        influx_update($device,'app',$tags,$fields);

    }//end if
}//end foreach
