<?php
use LibreNMS\RRD\RrdDefinition;

# retrieving base data for version, hardware and serial
$mibs = 'F5-BIGIP-SYSTEM-MIB';
$oids = [
    'sysProductVersion.0',
    'sysPlatformInfoMarketingName.0',
    'sysGeneralChassisSerialNum.0',
];

$data = snmp_get_multi($device, $oids, '-OQUs', $mibs);
$version    = $data[0]['sysProductVersion'];
$hardware   = $data[0]['sysPlatformInfoMarketingName'];
$serial     = $data[0]['sysProductVersion'];
unset($data, $oids);


# active APM sessions
$oids['apmAccessStatCurrentActiveSessions']         = 'F5-BIGIP-APM-MIB::apmAccessStatCurrentActiveSessions.0';
$dataset['apmAccessStatCurrentActiveSessions']      = 'sessions';
$type['apmAccessStatCurrentActiveSessions']         = 'GAUGE';
$graphName['apmAccessStatCurrentActiveSessions']    = 'bigip_apm_sessions';

# total client connections
$oids['sysStatClientTotConns']                      = 'F5-BIGIP-SYSTEM-MIB::sysStatClientTotConns.0';
$dataset['sysStatClientTotConns']                   = 'ClientTotConns';
$type['sysStatClientTotConns']                      = 'COUNTER';
$graphName['sysStatClientTotConns']                 = 'bigip_system_client_connection_rate';

# total server connections
$oids['sysStatServerTotConns']                      = 'F5-BIGIP-SYSTEM-MIB::sysStatServerTotConns.0';
$dataset['sysStatServerTotConns']                   = 'ServerTotConns';
$type['sysStatServerTotConns']                      = 'COUNTER';
$graphName['sysStatServerTotConns']                 = 'bigip_system_server_connection_rate';

# current client connections
$oids['sysStatClientCurConns']                      = 'F5-BIGIP-SYSTEM-MIB::sysStatClientCurConns.0';
$dataset['sysStatClientCurConns']                   = 'ClientCurConns';
$type['sysStatClientCurConns']                      = 'GAUGE';
$graphName['sysStatClientCurConns']                 = 'bigip_system_client_concurrent_connections';

# current server connections
$oids['sysStatServerCurConns']                      = 'F5-BIGIP-SYSTEM-MIB::sysStatServerCurConns.0';
$dataset['sysStatServerCurConns']                   = 'ServerCurConns';
$type['sysStatServerCurConns']                      = 'GAUGE';
$graphName['sysStatServerCurConns']                 = 'bigip_system_server_concurrent_connections';


$data = snmp_get_multi($device, $oids, '-OQUs');

foreach ($oids as $key => $oid) {
    $value[$key] = $data[0][$key];
    if (is_numeric($value[$key])) {
        $rrd_def = RrdDefinition::make()->addDataset($dataset[$key], $type[$key], 0);
        $fields = array(
                $dataset[$key] => $value[$key],
        );
        $tags = compact('rrd_def');
        data_update($device, $graphName[$key], $tags, $fields);
        $graphs[$graphName[$key]] = true;
    }
}


# SSL TPS
$oids = [
  'F5-BIGIP-SYSTEM-MIB::sysClientsslStatTotNativeConns.0',
  'F5-BIGIP-SYSTEM-MIB::sysClientsslStatTotCompatConns.0',
];
$data = snmp_get_multi($device, $oids, '-OQUs');

if (is_numeric($data[0]['sysClientsslStatTotNativeConns']) && is_numeric($data[0]['sysClientsslStatTotCompatConns'])) {
    $rrd_def = RrdDefinition::make()
      ->addDataset('TotNativeConns', 'COUNTER', 0)
      ->addDataset('TotCompatConns', 'COUNTER', 0);
    $fields = array(
      'TotNativeConns' => $data[0]['sysClientsslStatTotNativeConns'],
      'TotCompatConns' => $data[0]['sysClientsslStatTotCompatConns'],
    );
    $tags = compact('rrd_def');
    data_update($device, 'bigip_system_tps', $tags, $fields);
    $graphs['bigip_system_tps'] = true;
}


unset($data, $oids);
