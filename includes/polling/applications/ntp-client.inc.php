<?php

// Polls ntp-client statistics from script via SNMP
$rrd_filename = $config['rrd_dir'].'/'.$device['hostname'].'/app-ntpclient-'.$app['app_id'].'.rrd';
$options      = '-O qv';
$oid          = 'nsExtendOutputFull.9.110.116.112.99.108.105.101.110.116';

$ntpclient = snmp_get($device, $oid, $options);

echo ' ntp-client';

list ($offset, $frequency, $jitter, $noise, $stability) = explode("\n", $ntpclient);

if (!is_file($rrd_filename)) {
    rrdtool_create(
        $rrd_filename,
        '--step 300 
        DS:offset:GAUGE:600:-1000:1000 
        DS:frequency:GAUGE:600:-1000:1000 
        DS:jitter:GAUGE:600:-1000:1000 
        DS:noise:GAUGE:600:-1000:1000 
        DS:stability:GAUGE:600:-1000:1000 '.$config['rrd_rra']
    );
}

$fields = array(
                'offset'    => $offset,
                'frequency' => $frequency,
                'jitter'    => $jitter,
                'noise'     => $noise,
                'stability' => $stability,
);

rrdtool_update($rrd_filename, $fields);
