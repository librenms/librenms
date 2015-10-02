<?php

// Polls ntpd-server statistics from script via SNMP
$rrd_filename = $config['rrd_dir'].'/'.$device['hostname'].'/app-ntpdserver-'.$app['app_id'].'.rrd';
$options      = '-O qv';
$oid          = 'nsExtendOutputFull.10.110.116.112.100.115.101.114.118.101.114';

$ntpdserver = snmp_get($device, $oid, $options);

echo ' ntpd-server';

list ($stratum, $offset, $frequency, $jitter, $noise, $stability, $uptime,
    $buffer_recv, $buffer_free, $buffer_used, $packets_drop, $packets_ignore,
    $packets_recv, $packets_sent) = explode("\n", $ntpdserver);

if (!is_file($rrd_filename)) {
    rrdtool_create(
        $rrd_filename,
        '--step 300 
        DS:stratum:GAUGE:600:-1000:1000 
        DS:offset:GAUGE:600:-1000:1000 
        DS:frequency:GAUGE:600:-1000:1000 
        DS:jitter:GAUGE:600:-1000:1000 
        DS:noise:GAUGE:600:-1000:1000 
        DS:stability:GAUGE:600:-1000:1000 
        DS:uptime:GAUGE:600:0:125000000000 
        DS:buffer_recv:GAUGE:600:0:100000 
        DS:buffer_free:GAUGE:600:0:100000 
        DS:buffer_used:GAUGE:600:0:100000 
        DS:packets_drop:DERIVE:600:0:125000000000 
        DS:packets_ignore:DERIVE:600:0:125000000000 
        DS:packets_recv:DERIVE:600:0:125000000000 
        DS:packets_sent:DERIVE:600:0:125000000000 '.$config['rrd_rra']
    );
}

$fields = array(
    'stratum'        => $stratum,
    'offset'         => $offset,
    'frequency'      => $frequency,
    'jitter'         => $jitter,
    'noise'          => $noise,
    'stability'      => $stability,
    'uptime'         => $uptime,
    'buffer_recv'    => $buffer_recv,
    'buffer_free'    => $buffer_free,
    'buffer_used'    => $buffer_used,
    'packets_drop'   => $packets_drop,
    'packets_ignore' => $packets_ignore,
    'packets_recv'   => $packets_recv,
    'packets_sent'   => $packets_sent,
);

rrdtool_update($rrd_filename, $fields);
