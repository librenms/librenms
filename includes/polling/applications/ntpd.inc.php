<?php

// Polls ntpd-server statistics from script via SNMP
$name = 'ntpdserver';
$app_id = $app['app_id'];

echo ' ntpd-server';

$rrd_name = array('app', $name, $app_id);
$rrd_def = array(
    'DS:stratum:GAUGE:600:-1000:1000',
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

if ($agent_data['app']['ntpd']) {
    $data = explode("\n", $agent_data['app']['ntpd']);
    $fields = array();
    foreach ($data as $line) {
        $split = explode(':', $line);
        $fields[$split[0]] = $split[1];
    }
} else {
    // NET-SNMP-EXTEND-MIB::nsExtendOutputFull."ntpdserver"
    $oid = '.1.3.6.1.4.1.8072.1.3.2.3.1.2.10.110.116.112.100.115.101.114.118.101.114';
    $data = explode("\n", snmp_get($device, $oid, '-Oqv'));

    $fields = array(
        'stratum'        => $data[0],
        'offset'         => $data[1],
        'frequency'      => $data[2],
        'jitter'         => $data[3],
        'noise'          => $data[4],
        'stability'      => $data[5],
        'uptime'         => $data[6],
        'buffer_recv'    => $data[7],
        'buffer_free'    => $data[8],
        'buffer_used'    => $data[9],
        'packets_drop'   => $data[10],
        'packets_ignore' => $data[11],
        'packets_recv'   => $data[12],
        'packets_sent'   => $data[13],
    );
}

$tags = compact('name', 'app_id', 'rrd_name', 'rrd_def');
data_update($device, 'app', $tags, $fields);
