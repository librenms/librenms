<?php

use LibreNMS\RRD\RrdDefinition;

$name = 'freeradius';

if (! empty($agent_data['app'][$name])) {
    $rawdata = $agent_data['app'][$name];
} else {
    $options = '-Oqv';
    $oid = '.1.3.6.1.4.1.8072.1.3.2.3.1.2.10.102.114.101.101.114.97.100.105.117.115';
    $rawdata = snmp_get($device, $oid, $options);
}

// Format Data
$lines = explode("\n", $rawdata);
$freeradius = [];
$metrics = [];
foreach ($lines as $line) {
    [$var,$value] = explode(' = ', $line);
    $freeradius[$var] = $value;
}

// Check $freeradius array for missing keys, returns null when the key does not exist.
$get = function ($key) use ($freeradius) {
    if (! array_key_exists($key, $freeradius)) {
        return null;
    }

    $v = $freeradius[$key];
    if ($v === null) {
        return null;
    }

    // Trim whitespace and surrounding quotes
    $v = trim($v);
    $v = trim($v, '"');

    // Convert numeric strings to numbers
    if (is_numeric($v)) {
        // Preserve integer vs float
        if (strpos($v, '.') !== false) {
            return (float) $v;
        }
        return (int) $v;
    }

    return $v;
};

// FreeRADIUS-Total-Access
$rrd_def = RrdDefinition::make()
    ->addDataset('requests', 'DERIVE', 0, 125000000000)
    ->addDataset('accepts', 'DERIVE', 0, 125000000000)
    ->addDataset('rejects', 'DERIVE', 0, 125000000000)
    ->addDataset('challenges', 'DERIVE', 0, 125000000000);
$fields = [
    'requests' => $get('FreeRADIUS-Total-Access-Requests'),
    'accepts' => $get('FreeRADIUS-Total-Access-Accepts'),
    'rejects' => $get('FreeRADIUS-Total-Access-Rejects'),
    'challenges' => $get('FreeRADIUS-Total-Access-Challenges'),
];
$metrics['access'] = $fields;
$tags = [
    'name' => $name,
    'app_id' => $app->app_id,
    'type' => 'access',
    'rrd_name' => ['app', $name, 'access', $app->app_id],
    'rrd_def' => $rrd_def,
];
app('Datastore')->put($device, 'app', $tags, $fields);

// FreeRADIUS-Total-Auth
$rrd_def = RrdDefinition::make()
    ->addDataset('responses', 'DERIVE', 0, 125000000000)
    ->addDataset('duplicate_requests', 'DERIVE', 0, 125000000000)
    ->addDataset('malformed_requests', 'DERIVE', 0, 125000000000)
    ->addDataset('invalid_requests', 'DERIVE', 0, 125000000000)
    ->addDataset('dropped_requests', 'DERIVE', 0, 125000000000)
    ->addDataset('unknown_types', 'DERIVE', 0, 125000000000);
$fields = [
    'responses' => $get('FreeRADIUS-Total-Auth-Responses'),
    'duplicate_requests' => $get('FreeRADIUS-Total-Auth-Duplicate-Requests'),
    'malformed_requests' => $get('FreeRADIUS-Total-Auth-Malformed-Requests'),
    'invalid_requests' => $get('FreeRADIUS-Total-Auth-Invalid-Requests'),
    'dropped_requests' => $get('FreeRADIUS-Total-Auth-Dropped-Requests'),
    'unknown_types' => $get('FreeRADIUS-Total-Auth-Unknown-Types'),
];
$metrics['auth'] = $fields;
$tags = [
    'name' => $name,
    'app_id' => $app->app_id,
    'type' => 'auth',
    'rrd_name' => ['app', $name, 'auth', $app->app_id],
    'rrd_def' => $rrd_def,
];
app('Datastore')->put($device, 'app', $tags, $fields);

// FreeRADIUS-Total-Acct
$rrd_def = RrdDefinition::make()
    ->addDataset('requests', 'DERIVE', 0, 125000000000)
    ->addDataset('responses', 'DERIVE', 0, 125000000000)
    ->addDataset('duplicate_requests', 'DERIVE', 0, 125000000000)
    ->addDataset('malformed_requests', 'DERIVE', 0, 125000000000)
    ->addDataset('invalid_requests', 'DERIVE', 0, 125000000000)
    ->addDataset('dropped_requests', 'DERIVE', 0, 125000000000)
    ->addDataset('unknown_types', 'DERIVE', 0, 125000000000);
$fields = [
    'requests' => $get('FreeRADIUS-Total-Accounting-Requests'),
    'responses' => $get('FreeRADIUS-Total-Accounting-Responses'),
    'duplicate_requests' => $get('FreeRADIUS-Total-Acct-Duplicate-Requests'),
    'malformed_requests' => $get('FreeRADIUS-Total-Acct-Malformed-Requests'),
    'invalid_requests' => $get('FreeRADIUS-Total-Acct-Invalid-Requests'),
    'dropped_requests' => $get('FreeRADIUS-Total-Acct-Dropped-Requests'),
    'unknown_types' => $get('FreeRADIUS-Total-Acct-Unknown-Types'),
];
$metrics['acct'] = $fields;
$tags = [
    'name' => $name,
    'app_id' => $app->app_id,
    'type' => 'acct',
    'rrd_name' => ['app', $name, 'acct', $app->app_id],
    'rrd_def' => $rrd_def,
];
app('Datastore')->put($device, 'app', $tags, $fields);

// FreeRADIUS-Total-Proxy-Access
$rrd_def = RrdDefinition::make()
    ->addDataset('requests', 'DERIVE', 0, 125000000000)
    ->addDataset('accepts', 'DERIVE', 0, 125000000000)
    ->addDataset('rejects', 'DERIVE', 0, 125000000000)
    ->addDataset('challenges', 'DERIVE', 0, 125000000000);
$fields = [
    'requests' => $get('FreeRADIUS-Total-Proxy-Access-Requests'),
    'accepts' => $get('FreeRADIUS-Total-Proxy-Access-Accepts'),
    'rejects' => $get('FreeRADIUS-Total-Proxy-Access-Rejects'),
    'challenges' => $get('FreeRADIUS-Total-Proxy-Access-Challenges'),
];
$metrics['proxy_access'] = $fields;
$tags = [
    'name' => $name,
    'app_id' => $app->app_id,
    'type' => 'proxy_access',
    'rrd_name' => ['app', $name, 'proxy_access', $app->app_id],
    'rrd_def' => $rrd_def,
];
app('Datastore')->put($device, 'app', $tags, $fields);

// FreeRADIUS-Total-Proxy-Auth
$rrd_def = RrdDefinition::make()
    ->addDataset('responses', 'DERIVE', 0, 125000000000)
    ->addDataset('duplicate_requests', 'DERIVE', 0, 125000000000)
    ->addDataset('malformed_requests', 'DERIVE', 0, 125000000000)
    ->addDataset('invalid_requests', 'DERIVE', 0, 125000000000)
    ->addDataset('dropped_requests', 'DERIVE', 0, 125000000000)
    ->addDataset('unknown_types', 'DERIVE', 0, 125000000000);
$fields = [
    'responses' => $get('FreeRADIUS-Total-Proxy-Auth-Responses'),
    'duplicate_requests' => $get('FreeRADIUS-Total-Proxy-Auth-Duplicate-Requests'),
    'malformed_requests' => $get('FreeRADIUS-Total-Proxy-Auth-Malformed-Requests'),
    'invalid_requests' => $get('FreeRADIUS-Total-Proxy-Auth-Invalid-Requests'),
    'dropped_requests' => $get('FreeRADIUS-Total-Proxy-Auth-Dropped-Requests'),
    'unknown_types' => $get('FreeRADIUS-Total-Proxy-Auth-Unknown-Types'),
];
$metrics['proxy_auth'] = $fields;
$tags = [
    'name' => $name,
    'app_id' => $app->app_id,
    'type' => 'proxy_auth',
    'rrd_name' => ['app', $name, 'proxy_auth', $app->app_id],
    'rrd_def' => $rrd_def,
];
app('Datastore')->put($device, 'app', $tags, $fields);

// FreeRADIUS-Total-Proxy-Acct
$rrd_def = RrdDefinition::make()
    ->addDataset('requests', 'DERIVE', 0, 125000000000)
    ->addDataset('responses', 'DERIVE', 0, 125000000000)
    ->addDataset('duplicate_requests', 'DERIVE', 0, 125000000000)
    ->addDataset('malformed_requests', 'DERIVE', 0, 125000000000)
    ->addDataset('invalid_requests', 'DERIVE', 0, 125000000000)
    ->addDataset('dropped_requests', 'DERIVE', 0, 125000000000)
    ->addDataset('unknown_types', 'DERIVE', 0, 125000000000);
$fields = [
    'requests' => $get('FreeRADIUS-Total-Proxy-Accounting-Requests'),
    'responses' => $get('FreeRADIUS-Total-Proxy-Accounting-Responses'),
    'duplicate_requests' => $get('FreeRADIUS-Total-Proxy-Acct-Duplicate-Requests'),
    'malformed_requests' => $get('FreeRADIUS-Total-Proxy-Acct-Malformed-Requests'),
    'invalid_requests' => $get('FreeRADIUS-Total-Proxy-Acct-Invalid-Requests'),
    'dropped_requests' => $get('FreeRADIUS-Total-Proxy-Acct-Dropped-Requests'),
    'unknown_types' => $get('FreeRADIUS-Total-Proxy-Acct-Unknown-Types'),
];
$metrics['proxy_acct'] = $fields;
$tags = [
    'name' => $name,
    'app_id' => $app->app_id,
    'type' => 'proxy_acct',
    'rrd_name' => ['app', $name, 'proxy_acct', $app->app_id],
    'rrd_def' => $rrd_def,
];
app('Datastore')->put($device, 'app', $tags, $fields);

// FreeRADIUS-Queue
$rrd_name = ['app', $name, 'queue', $app->app_id];
$rrd_def = RrdDefinition::make()
    ->addDataset('len_internal', 'DERIVE', 0, 125000000000)
    ->addDataset('len_proxy', 'DERIVE', 0, 125000000000)
    ->addDataset('len_auth', 'DERIVE', 0, 125000000000)
    ->addDataset('len_acct', 'DERIVE', 0, 125000000000)
    ->addDataset('len_detail', 'DERIVE', 0, 125000000000)
    ->addDataset('pps_in', 'DERIVE', 0, 125000000000)
    ->addDataset('pps_out', 'DERIVE', 0, 125000000000);
$fields = [
    'len_internal' => $get('FreeRADIUS-Queue-Len-Internal'),
    'len_proxy' => $get('FreeRADIUS-Queue-Len-Proxy'),
    'len_auth' => $get('FreeRADIUS-Queue-Len-Auth'),
    'len_acct' => $get('FreeRADIUS-Queue-Len-Acct'),
    'len_detail' => $get('FreeRADIUS-Queue-Len-Detail'),
    'pps_in' => $get('FreeRADIUS-Queue-PPS-In'),
    'pps_out' => $get('FreeRADIUS-Queue-PPS-Out'),
];
$metrics['queue'] = $fields;
$tags = [
    'name' => $name,
    'app_id' => $app->app_id,
    'type' => 'queue',
    'rrd_name' => ['app', $name, 'queue', $app->app_id],
    'rrd_def' => $rrd_def,
];
app('Datastore')->put($device, 'app', $tags, $fields);
update_application($app, $rawdata, $metrics);

unset($lines, $freeradius, $rrd_name, $rrd_def, $fields, $tags);
