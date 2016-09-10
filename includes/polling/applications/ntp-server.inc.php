<?php
//NET-SNMP-EXTEND-MIB::nsExtendOutputFull."ntp-server"
$name = 'ntp-server';
$app_id = $app['app_id'];
$oid = '.1.3.6.1.4.1.8072.1.3.2.3.1.2.10.110.116.112.45.115.101.114.118.101.114';
$ntpserver_data = snmp_get($device, $oid, '-Oqv');
list ($stratum, $offset, $frequency, $jitter, $noise, $stability, $uptime, $buffer_recv, $buffer_free, $buffer_used, $packets_drop, $packets_ignore, $packets_recv, $packets_sent) = explode("\n", $ntpserver_data);

echo ' '.$name;

$rrd_name = array('app', $name, $app_id);
$rrd_def = array(
    'DS:stratum:GAUGE:600:0:1000',
    'DS:offset:GAUGE:600:-1000:1000',
    'DS:frequency:GAUGE:600:-1000:1000',
    'DS:jitter:GAUGE:600:-1000:1000',
    'DS:noise:GAUGE:600:-1000:1000',
    'DS:stability:GAUGE:600:-1000:1000',
    'DS:uptime:GAUGE:600:0:125000000000',
    'DS:buffer_recv:GAUGE:600:0:100000',
    'DS:buffer_free:GAUGE:600:0:100000',
    'DS:buffer_used:GAUGE:600:0:100000',
    'DS:packets_drop:DERIVE:600:0:125000000000',
    'DS:packets_ignore:DERIVE:600:0:125000000000',
    'DS:packets_recv:DERIVE:600:0:125000000000',
    'DS:packets_sent:DERIVE:600:0:125000000000'
);


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


$tags = compact('name', 'app_id', 'rrd_name', 'rrd_def');
data_update($device, 'app', $tags, $fields);
