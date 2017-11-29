<?php
use LibreNMS\RRD\RrdDefinition;
$name = 'freeradius';
$app_id = $app['app_id'];
if (!empty($agent_data['app'][$name])) {
    $rawdata = $agent_data['app'][$name];
    update_application($app, $rawdata);
} else {
    $options = '-O qv';
    $oid     = '.1.3.6.1.4.1.8072.1.3.2.3.1.2.10.102.114.101.101.114.97.100.105.117.115';
    $rawdata  = snmp_get($device, $oid, $options);
}
#Format Data
$lines = explode("\n", $rawdata);
$freeradius = array();
foreach ($lines as $line) {
    list($var,$value) = explode(' = ', $line);
    $freeradius[$var] = $value;
}

#FreeRADIUS-Total-Access
$rrd_name =  array('app', $name,'access',$app_id);
$rrd_def = RrdDefinition::make()
    ->addDataset('requests', 'DERIVE', 0, 125000000000)
    ->addDataset('accepts', 'DERIVE', 0, 125000000000)
    ->addDataset('rejects', 'DERIVE', 0, 125000000000)
    ->addDataset('challenges', 'DERIVE', 0, 125000000000);
$fields = array (
    'requests' => $freeradius['FreeRADIUS-Total-Access-Requests'],
    'accepts' => $freeradius['FreeRADIUS-Total-Access-Accepts'],
    'rejects' => $freeradius['FreeRADIUS-Total-Access-Rejects'],
    'challenges' => $freeradius['FreeRADIUS-Total-Access-Challenges']
    );
$tags = compact('name', 'app_id', 'rrd_name', 'rrd_def');
data_update($device, 'app', $tags, $fields);

#FreeRADIUS-Total-Auth
$rrd_name =  array('app', $name,'auth',$app_id);
$rrd_def = RrdDefinition::make()
    ->addDataset('responses', 'DERIVE', 0, 125000000000)
    ->addDataset('duplicate_requests', 'DERIVE', 0, 125000000000)
    ->addDataset('malformed_requests', 'DERIVE', 0, 125000000000)
    ->addDataset('invalid_requests', 'DERIVE', 0, 125000000000)
    ->addDataset('dropped_requests', 'DERIVE', 0, 125000000000)
    ->addDataset('unknown_types', 'DERIVE', 0, 125000000000);
$fields = array (
    'responses' => $freeradius['FreeRADIUS-Total-Auth-Responses'],
    'duplicate_requests' => $freeradius['FreeRADIUS-Total-Auth-Duplicate-Requests'],
    'malformed_requests' => $freeradius['FreeRADIUS-Total-Auth-Malformed-Requests'],
    'invalid_requests' => $freeradius['FreeRADIUS-Total-Auth-Invalid-Requests'],
    'dropped_requests' => $freeradius['FreeRADIUS-Total-Auth-Dropped-Requests'],
    'unknown_types' => $freeradius['FreeRADIUS-Total-Auth-Unknown-Types']
    );
$tags = compact('name', 'app_id', 'rrd_name', 'rrd_def');
data_update($device, 'app', $tags, $fields);

#FreeRADIUS-Total-Acct
$rrd_name =  array('app', $name,'acct',$app_id);
$rrd_def = RrdDefinition::make()
    ->addDataset('requests', 'DERIVE', 0, 125000000000)
    ->addDataset('responses', 'DERIVE', 0, 125000000000)
    ->addDataset('duplicate_requests', 'DERIVE', 0, 125000000000)
    ->addDataset('malformed_requests', 'DERIVE', 0, 125000000000)
    ->addDataset('invalid_requests', 'DERIVE', 0, 125000000000)
    ->addDataset('dropped_requests', 'DERIVE', 0, 125000000000)
    ->addDataset('unknown_types', 'DERIVE', 0, 125000000000);
$fields = array (
    'requests' => $freeradius['FreeRADIUS-Total-Accounting-Requests'],
    'responses' => $freeradius['FreeRADIUS-Total-Accounting-Responses'],
    'duplicate_requests' => $freeradius['FreeRADIUS-Total-Acct-Duplicate-Requests'],
    'malformed_requests' => $freeradius['FreeRADIUS-Total-Acct-Malformed-Requests'],
    'invalid_requests' => $freeradius['FreeRADIUS-Total-Acct-Invalid-Requests'],
    'dropped_requests' => $freeradius['FreeRADIUS-Total-Acct-Dropped-Requests'],
    'unknown_types' => $freeradius['FreeRADIUS-Total-Acct-Unknown-Types']
    );
$tags = compact('name', 'app_id', 'rrd_name', 'rrd_def');
data_update($device, 'app', $tags, $fields);

#FreeRADIUS-Total-Proxy-Access
$rrd_name =  array('app', $name,'proxy_access',$app_id);
$rrd_def = RrdDefinition::make()
    ->addDataset('requests', 'DERIVE', 0, 125000000000)
    ->addDataset('accepts', 'DERIVE', 0, 125000000000)
    ->addDataset('rejects', 'DERIVE', 0, 125000000000)
    ->addDataset('challenges', 'DERIVE', 0, 125000000000);
$fields = array (
    'requests' => $freeradius['FreeRADIUS-Total-Access-Requests'],
    'accepts' => $freeradius['FreeRADIUS-Total-Access-Accepts'],
    'rejects' => $freeradius['FreeRADIUS-Total-Access-Rejects'],
    'challenges' => $freeradius['FreeRADIUS-Total-Access-Challenges']
    );
$tags = compact('name', 'app_id', 'rrd_name', 'rrd_def');
data_update($device, 'app', $tags, $fields);

#FreeRADIUS-Total-Proxy-Auth
$rrd_name =  array('app', $name,'proxy_auth',$app_id);
$rrd_def = RrdDefinition::make()
    ->addDataset('responses', 'DERIVE', 0, 125000000000)
    ->addDataset('duplicate_requests', 'DERIVE', 0, 125000000000)
    ->addDataset('malformed_requests', 'DERIVE', 0, 125000000000)
    ->addDataset('invalid_requests', 'DERIVE', 0, 125000000000)
    ->addDataset('dropped_requests', 'DERIVE', 0, 125000000000)
    ->addDataset('unknown_types', 'DERIVE', 0, 125000000000);
$fields = array (
    'responses' => $freeradius['FreeRADIUS-Total-Auth-Responses'],
    'duplicate_requests' => $freeradius['FreeRADIUS-Total-Auth-Duplicate-Requests'],
    'malformed_requests' => $freeradius['FreeRADIUS-Total-Auth-Malformed-Requests'],
    'invalid_requests' => $freeradius['FreeRADIUS-Total-Auth-Invalid-Requests'],
    'dropped_requests' => $freeradius['FreeRADIUS-Total-Auth-Dropped-Requests'],
    'unknown_types' => $freeradius['FreeRADIUS-Total-Auth-Unknown-Types']
    );
$tags = compact('name', 'app_id', 'rrd_name', 'rrd_def');
data_update($device, 'app', $tags, $fields);

#FreeRADIUS-Total-Proxy-Acct
$rrd_name =  array('app', $name,'proxy_acct',$app_id);
$rrd_def = RrdDefinition::make()
    ->addDataset('requests', 'DERIVE', 0, 125000000000)
    ->addDataset('responses', 'DERIVE', 0, 125000000000)
    ->addDataset('duplicate_requests', 'DERIVE', 0, 125000000000)
    ->addDataset('malformed_requests', 'DERIVE', 0, 125000000000)
    ->addDataset('invalid_requests', 'DERIVE', 0, 125000000000)
    ->addDataset('dropped_requests', 'DERIVE', 0, 125000000000)
    ->addDataset('unknown_types', 'DERIVE', 0, 125000000000);
$fields = array (
    'requests' => $freeradius['FreeRADIUS-Total-Accounting-Requests'],
    'responses' => $freeradius['FreeRADIUS-Total-Accounting-Responses'],
    'duplicate_requests' => $freeradius['FreeRADIUS-Total-Acct-Duplicate-Requests'],
    'malformed_requests' => $freeradius['FreeRADIUS-Total-Acct-Malformed-Requests'],
    'invalid_requests' => $freeradius['FreeRADIUS-Total-Acct-Invalid-Requests'],
    'dropped_requests' => $freeradius['FreeRADIUS-Total-Acct-Dropped-Requests'],
    'unknown_types' => $freeradius['FreeRADIUS-Total-Acct-Unknown-Types']
    );
$tags = compact('name', 'app_id', 'rrd_name', 'rrd_def');
data_update($device, 'app', $tags, $fields);

#FreeRADIUS-Queue
$rrd_name =  array('app', $name,'queue',$app_id);
$rrd_def = RrdDefinition::make()
    ->addDataset('len_internal', 'DERIVE', 0, 125000000000)
    ->addDataset('len_proxy', 'DERIVE', 0, 125000000000)
    ->addDataset('len_auth', 'DERIVE', 0, 125000000000)
    ->addDataset('len_acct', 'DERIVE', 0, 125000000000)
    ->addDataset('pps_in', 'DERIVE', 0, 125000000000)
    ->addDataset('pps_out', 'DERIVE', 0, 125000000000);
$fields = array (
    'len_internal' => $freeradius['FreeRADIUS-Total-Accounting-Requests'],
    'len_proxy' => $freeradius['FreeRADIUS-Total-Accounting-Responses'],
    'len_auth' => $freeradius['FreeRADIUS-Total-Acct-Duplicate-Requests'],
    'len_acct' => $freeradius['FreeRADIUS-Total-Acct-Malformed-Requests'],
    'pps_in' => $freeradius['FreeRADIUS-Total-Acct-Invalid-Requests'],
    'pps_out' => $freeradius['FreeRADIUS-Total-Acct-Dropped-Requests']
    );
$tags = compact('name', 'app_id', 'rrd_name', 'rrd_def');
data_update($device, 'app', $tags, $fields);

unset($lines ,$freeradius, $rrd_name, $rrd_def, $fields, $tags);
