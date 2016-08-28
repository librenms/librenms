<?php

// Polls ntp-client statistics from script via SNMP
$options      = '-O qv';
$oid          = 'nsExtendOutputFull.9.110.116.112.99.108.105.101.110.116';
$ntpclient = snmp_get($device, $oid, $options);

$name = 'ntpclient';
$app_id = $app['app_id'];
echo ' ntp-client';

list ($offset, $frequency, $jitter, $noise, $stability) = explode("\n", $ntpclient);

$rrd_name = array('app', $name, $app_id);
$rrd_def = array(
    'DS:offset:GAUGE:600:-1000:1000',
    'DS:frequency:GAUGE:600:-1000:1000',
    'DS:jitter:GAUGE:600:-1000:1000',
    'DS:noise:GAUGE:600:-1000:1000',
    'DS:stability:GAUGE:600:-1000:1000'
);

$fields = array(
    'offset'    => $offset,
    'frequency' => $frequency,
    'jitter'    => $jitter,
    'noise'     => $noise,
    'stability' => $stability,
);

$tags = compact('name', 'app_id', 'rrd_name', 'rrd_def');
data_update($device, 'app', $tags, $fields);
