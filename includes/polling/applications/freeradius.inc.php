<?php
use LibreNMS\RRD\RrdDefinition;
$name = 'freeradius';
$app_id = $app['app_id'];
if (!empty($agent_data['app'][$name])) {
    $rawdata = $agent_data['app'][$name];
    update_application($app, $rawdata);
} else {
    $options = '-O qv';
    $oid     = '.1.3.6.1.4.1.8072.1.3.2.4.1.2.10.102.114.101.101.114.97.100.105.117.115';
    $rawdata  = snmp_get($device, $oid, $options);
}
#Format Data
$lines = explode("\n", $rawdata);
$unbound = array();
foreach ($lines as $line) {
    list($var,$value) = explode(' = ', $line);
    $unbound[$var] = $value;
}

#FreeRADIUS-Total-Access
$rrd_name =  array('app', $name,'access',$app_id);
$rrd_def = RrdDefinition::make()
    ->addDataset('requests', 'DERIVE', 0, 125000000000)
    ->addDataset('accepts', 'DERIVE', 0, 125000000000)
    ->addDataset('rejects', 'DERIVE', 0, 125000000000)
    ->addDataset('challenges', 'DERIVE', 0, 125000000000);
$fields = array (
    'access-requests' => $unbound['FreeRADIUS-Total-Access-Requests'],
    'access-accepts' => $unbound['FreeRADIUS-Total-Access-Accepts'],
    'access-rejects' => $unbound['FreeRADIUS-Total-Access-Rejects'],
    'access-challenges' => $unbound['FreeRADIUS-Total-Access-Challenges']
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
    'responses' => $unbound['FreeRADIUS-Total-Auth-Responses'],
    'duplicate_requests' => $unbound['FreeRADIUS-Total-Auth-Duplicate-Requests'],
    'access-rejects' => $unbound['FreeRADIUS-Total-Auth-Malformed-Requests'],
    'invalid_requests' => $unbound['FreeRADIUS-Total-Auth-Invalid-Requests'],
    'dropped_requests' => $unbound['FreeRADIUS-Total-Auth-Dropped-Requests'],
    'unknown_types' => $unbound['FreeRADIUS-Total-Auth-Unknown-Types']
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
    'requests' => $unbound['FreeRADIUS-Total-Accounting-Requests'],
    'responses' => $unbound['FreeRADIUS-Total-Accounting-Responses'],
    'duplicate_requests' => $unbound['FreeRADIUS-Total-Acct-Duplicate-Requests'],
    'malformed_requests' => $unbound['FreeRADIUS-Total-Acct-Malformed-Requests'],
    'invalid_requests' => $unbound['FreeRADIUS-Total-Acct-Invalid-Requests'],
    'dropped_requests' => $unbound['FreeRADIUS-Total-Acct-Dropped-Requests'],
    'unknown_types' => $unbound['FreeRADIUS-Total-Acct-Unknown-Types']
    );
$tags = compact('name', 'app_id', 'rrd_name', 'rrd_def');
data_update($device, 'app', $tags, $fields);
unset($lines , $unbound, $rrd_name, $rrd_def, $fields, $tags);

#FreeRADIUS-Total-Proxy-Access
$rrd_name =  array('app', $name,'proxy_access',$app_id);
$rrd_def = RrdDefinition::make()
    ->addDataset('requests', 'DERIVE', 0, 125000000000)
    ->addDataset('accepts', 'DERIVE', 0, 125000000000)
    ->addDataset('rejects', 'DERIVE', 0, 125000000000)
    ->addDataset('challenges', 'DERIVE', 0, 125000000000);
$fields = array (
    'access-requests' => $unbound['FreeRADIUS-Total-Access-Requests'],
    'access-accepts' => $unbound['FreeRADIUS-Total-Access-Accepts'],
    'access-rejects' => $unbound['FreeRADIUS-Total-Access-Rejects'],
    'access-challenges' => $unbound['FreeRADIUS-Total-Access-Challenges']
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
    'responses' => $unbound['FreeRADIUS-Total-Auth-Responses'],
    'duplicate_requests' => $unbound['FreeRADIUS-Total-Auth-Duplicate-Requests'],
    'access-rejects' => $unbound['FreeRADIUS-Total-Auth-Malformed-Requests'],
    'invalid_requests' => $unbound['FreeRADIUS-Total-Auth-Invalid-Requests'],
    'dropped_requests' => $unbound['FreeRADIUS-Total-Auth-Dropped-Requests'],
    'unknown_types' => $unbound['FreeRADIUS-Total-Auth-Unknown-Types']
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
    'requests' => $unbound['FreeRADIUS-Total-Accounting-Requests'],
    'responses' => $unbound['FreeRADIUS-Total-Accounting-Responses'],
    'duplicate_requests' => $unbound['FreeRADIUS-Total-Acct-Duplicate-Requests'],
    'malformed_requests' => $unbound['FreeRADIUS-Total-Acct-Malformed-Requests'],
    'invalid_requests' => $unbound['FreeRADIUS-Total-Acct-Invalid-Requests'],
    'dropped_requests' => $unbound['FreeRADIUS-Total-Acct-Dropped-Requests'],
    'unknown_types' => $unbound['FreeRADIUS-Total-Acct-Unknown-Types']
    );
$tags = compact('name', 'app_id', 'rrd_name', 'rrd_def');
data_update($device, 'app', $tags, $fields);
unset($lines , $unbound, $rrd_name, $rrd_def, $fields, $tags);

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
    'len_internal' => $unbound['FreeRADIUS-Total-Accounting-Requests'],
    'len_proxy' => $unbound['FreeRADIUS-Total-Accounting-Responses'],
    'len_auth' => $unbound['FreeRADIUS-Total-Acct-Duplicate-Requests'],
    'len_acct' => $unbound['FreeRADIUS-Total-Acct-Malformed-Requests'],
    'pps_in' => $unbound['FreeRADIUS-Total-Acct-Invalid-Requests'],
    'pps_out' => $unbound['FreeRADIUS-Total-Acct-Dropped-Requests']
    );
$tags = compact('name', 'app_id', 'rrd_name', 'rrd_def');
data_update($device, 'app', $tags, $fields);
unset($lines , $unbound, $rrd_name, $rrd_def, $fields, $tags);
