<?php
$name = 'freeswitch';
$app_id = $app['app_id'];
if (!empty($agent_data[$name])) {
    $rawdata = $agent_data[$name];
} else {
    echo "Freeswitch Missing";
    return;
}
# Format Data
$lines = explode("\n", $rawdata);
$freeswitch = array();
foreach ($lines as $line) {
    list($var,$value) = explode('=', $line);
    $freeswitch[$var] = $value;
}
# Freeswitch stats
$rrd_name =  array('app', $name, 'stats', $app_id);
$rrd_def = array(
    'DS:calls:GAUGE:600:0:10000',
    'DS:channels:GAUGE:600:0:10000',
    'DS:peak:GAUGE:600:0:10000',
    'DS:in_failed:COUNTER:600:0:4294967295',
    'DS:in_okay:COUNTER:600:0:4294967295',
    'DS:out_failed:COUNTER:600:0:4294967295',
    'DS:out_okay:COUNTER:600:0:4294967295'
);
$fields = array (
        'calls' => $freeswitch['Calls'],
        'channels' => $freeswitch['Channels'],
        'peak' => $freeswitch['Peak'],
    'in_failed' => $freeswitch['InFailed'],
    'in_okay' => $freeswitch['InTotal']-$freeswitch['InFailed'],
    'out_failed' => $freeswitch['OutFailed'],
    'out_okay' => $freeswitch['OutTotal']-$freeswitch['OutFailed']
    );
$tags = compact('name', 'app_id', 'rrd_name', 'rrd_def');
data_update($device, 'app', $tags, $fields);
unset($lines , $freeswitch, $rrd_name, $rrd_def, $fields, $tags);
