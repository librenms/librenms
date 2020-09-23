<?php

use LibreNMS\RRD\RrdDefinition;

// retrieving base data for version, hardware and serial
$mibs = 'F5-BIGIP-SYSTEM-MIB';
$oids = [
    'sysProductVersion.0',
    'sysPlatformInfoMarketingName.0',
    'sysGeneralChassisSerialNum.0',
];

$data = snmp_get_multi($device, $oids, '-OQUs', $mibs);
$version = $data[0]['sysProductVersion'];
$hardware = $data[0]['sysPlatformInfoMarketingName'];
$serial = $data[0]['sysGeneralChassisSerialNum'];
unset($data, $oids);

$oids = [
    'F5-BIGIP-APM-MIB::apmAccessStatCurrentActiveSessions.0',
    'F5-BIGIP-SYSTEM-MIB::sysStatClientTotConns.0',
    'F5-BIGIP-SYSTEM-MIB::sysStatServerTotConns.0',
    'F5-BIGIP-SYSTEM-MIB::sysStatClientCurConns.0',
    'F5-BIGIP-SYSTEM-MIB::sysStatServerCurConns.0',
];

$metadata = [
    'apmAccessStatCurrentActiveSessions' => [
        'dataset' => 'sessions',
        'type' => 'GAUGE',
        'name' => 'bigip_apm_sessions',
    ],
    'sysStatClientTotConns' => [
        'dataset' => 'ClientTotConns',
        'type' => 'COUNTER',
        'name' => 'bigip_system_client_connection_rate',
    ],
    'sysStatServerTotConns' => [
        'dataset' => 'ServerTotConns',
        'type' => 'COUNTER',
        'name' => 'bigip_system_server_connection_rate',
    ],
    'sysStatClientCurConns' => [
        'dataset' => 'ClientCurConns',
        'type' => 'GAUGE',
        'name' => 'bigip_system_client_concurrent_connections',
    ],
    'sysStatServerCurConns' => [
        'dataset' => 'ServerCurConns',
        'type' => 'GAUGE',
        'name' => 'bigip_system_server_concurrent_connections',
    ],
];

$data = snmp_get_multi($device, $oids, '-OQUs');

foreach ($metadata as $key => $info) {
    $value = $data[0][$key];
    if (is_numeric($value)) {
        $rrd_def = RrdDefinition::make()->addDataset($info['dataset'], $info['type'], 0);
        $fields = [
            $info['dataset'] => $value,
        ];
        $tags = compact('rrd_def');
        data_update($device, $info['name'], $tags, $fields);
        $graphs[$info['name']] = true;
    }
}

// SSL TPS
$oids = [
    'F5-BIGIP-SYSTEM-MIB::sysClientsslStatTotNativeConns.0',
    'F5-BIGIP-SYSTEM-MIB::sysClientsslStatTotCompatConns.0',
];
$data = snmp_get_multi($device, $oids, '-OQUs');

if (is_numeric($data[0]['sysClientsslStatTotNativeConns']) && is_numeric($data[0]['sysClientsslStatTotCompatConns'])) {
    $rrd_def = RrdDefinition::make()
      ->addDataset('TotNativeConns', 'COUNTER', 0)
      ->addDataset('TotCompatConns', 'COUNTER', 0);
    $fields = [
        'TotNativeConns' => $data[0]['sysClientsslStatTotNativeConns'],
        'TotCompatConns' => $data[0]['sysClientsslStatTotCompatConns'],
    ];
    $tags = compact('rrd_def');
    data_update($device, 'bigip_system_tps', $tags, $fields);
    $os->enableGraph('bigip_system_tps');
}

unset($data, $oids, $metadata);
