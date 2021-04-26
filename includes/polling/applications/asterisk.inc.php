<?php

use LibreNMS\RRD\RrdDefinition;

$name = 'asterisk';
$app_id = $app['app_id'];

echo "$name, app_id=$app_id ";

if (! empty($agent_data[$name])) {
    $rawdata = $agent_data[$name];
} else {
    $options = '-Oqv';
    $oid = '.1.3.6.1.4.1.8072.1.3.2.4.1.2.8.97.115.116.101.114.105.115.107';
    $rawdata = snmp_walk($device, $oid, $options);
    $rawdata = str_replace("<<<asterisk>>>\n", '', $rawdata);
}

// Format Data
$lines = explode("\n", $rawdata);
$asterisk = [];
$asterisk_metrics = [];
foreach ($lines as $line) {
    [$var,$value] = explode('=', $line);
    $asterisk[$var] = $value;
}
unset($lines);

// Asterisk stats
$rrd_name = ['app', $name, 'stats', $app_id];
$rrd_def = RrdDefinition::make()
    ->addDataset('calls', 'GAUGE', 0, 10000)
    ->addDataset('channels', 'GAUGE', 0, 20000)
    ->addDataset('sippeers', 'GAUGE', 0, 10000)
    ->addDataset('sipmononline', 'GAUGE', 0, 10000)
    ->addDataset('sipmonoffline', 'GAUGE', 0, 10000)
    ->addDataset('sipunmononline', 'GAUGE', 0, 10000)
    ->addDataset('sipunmonoffline', 'GAUGE', 0, 10000);

$sip_fields = [
    'calls' => $asterisk['Calls'],
    'channels' => $asterisk['Channels'],
    'sippeers' => $asterisk['SipPeers'],
    'sipmononline' => $asterisk['SipMonOnline'],
    'sipmonoffline' => $asterisk['SipMonOffline'],
    'sipunmononline' => $asterisk['SipUnMonOnline'],
    'sipunmonoffline' => $asterisk['SipUnMonOffline'],
];

$asterisk_metrics['stats'] = $sip_fields;
$sip_tags = compact('name', 'app_id', 'rrd_name', 'rrd_def');
data_update($device, 'app', $sip_tags, $sip_fields);

unset($rrd_name, $rrd_def, $sip_fields, $sip_tags);

// Additional iax2 stats
$rrd_name = ['app', $name, 'iax2', $app_id];
$rrd_def = RrdDefinition::make()
    ->addDataset('iax2peers', 'GAUGE', 0, 10000)
    ->addDataset('iax2online', 'GAUGE', 0, 10000)
    ->addDataset('iax2offline', 'GAUGE', 0, 10000)
    ->addDataset('iax2unmonitored', 'GAUGE', 0, 10000);

$iax2_fields = [
    'iax2peers' => $asterisk['Iax2Peers'],
    'iax2online' => $asterisk['Iax2Online'],
    'iax2offline' => $asterisk['Iax2Offline'],
    'iax2unmonitored' => $asterisk['Iax2Unmonitored'],
];

$asterisk_metrics['iax2'] = $iax2_fields;
$iax2_tags = compact('name', 'app_id', 'rrd_name', 'rrd_def');
data_update($device, 'app', $iax2_tags, $iax2_fields);

update_application($app, $rawdata, $asterisk_metrics);

unset($rrd_name, $rrd_def, $iax2_fields, $iax2_tags);

unset($asterisk, $asterisk_metrics, $rawdata); // these are used for all rrds
