<?php

use LibreNMS\RRD\RrdDefinition;

$name = 'asterisk';
$app_id = $app['app_id'];
if (!empty($agent_data[$name])) {
    $rawdata = $agent_data[$name];
} else {
    $options = '-Oqv';
    $oid = '.1.3.6.1.4.1.8072.1.3.2.4.1.2.8.97.115.116.101.114.105.115.107';
    $rawdata = snmp_walk($device, $oid, $options);
    $rawdata  = str_replace("<<<asterisk>>>\n", '', $rawdata);
}
# Format Data
$lines = explode("\n", $rawdata);
$asterisk = array();
foreach ($lines as $line) {
    list($var,$value) = explode('=', $line);
    $asterisk[$var] = $value;
}
# Asterisk stats
$rrd_name =  array('app', $name, 'stats', $app_id);
$rrd_def = RrdDefinition::make()
    ->addDataset('calls', 'GAUGE', 0, 10000)
    ->addDataset('channels', 'GAUGE', 0, 20000)
    ->addDataset('sippeers', 'GAUGE', 0, 10000)
    ->addDataset('sipmononline', 'GAUGE', 0, 10000)
    ->addDataset('sipmonoffline', 'GAUGE', 0, 10000)
    ->addDataset('sipunmononline', 'GAUGE', 0, 10000)
    ->addDataset('sipunmonoffline', 'GAUGE', 0, 10000);
$fields = array(
    'calls' => $asterisk['Calls'],
    'channels' => $asterisk['Channels'],
    'sipeers' => $asterisk['SipPeers'],
    'sipmononline' => $asterisk['SipMonOnline'],
    'sipmonoffline' => $asterisk['SipMonOffline'],
    'sipunmononline' => $asterisk['SipUnMonOnline'],
    'sipunmonoffline' => $asterisk['SipUnMonOffline']
);
$tags = compact('name', 'app_id', 'rrd_name', 'rrd_def');
data_update($device, 'app', $tags, $fields);
update_application($app, $rawdata, $fields);

unset($lines, $asterisk, $rrd_name, $rrd_def, $fields, $tags, $rawdata);
