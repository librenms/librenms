<?php

use LibreNMS\RRD\RrdDefinition;

echo "Polling SNOM device...\n";

// Get SNOM specific version string from silly SNOM location. Silly SNOM!
$device['sysDescr'] = snmp_get($device, '1.3.6.1.2.1.7526.2.4', '-Oqv');
$device['sysDescr'] = str_replace('-', ' ', $device['sysDescr']);
$device['sysDescr'] = str_replace('"', '', $device['sysDescr']);
[$hardware, $features, $version] = explode(' ', $device['sysDescr']);

// Get data for calls and network from SNOM specific SNMP OIDs.
$snmpdata = snmp_get($device, ['1.3.6.1.2.1.7526.2.1.1', '1.3.6.1.2.1.7526.2.1.2', '1.3.6.1.2.1.7526.2.2.1', '1.3.6.1.2.1.7526.2.2.2'], '-Oqv');
$snmpdatab = snmp_get($device, ['1.3.6.1.2.1.7526.2.5', '1.3.6.1.2.1.7526.2.6'], '-Oqv');

[$rxbytes, $rxpkts, $txbytes, $txpkts] = explode("\n", $snmpdata);
[$calls, $registrations] = explode("\n", $snmpdatab);
$txbytes = (0 - $txbytes * 8);
$rxbytes = (0 - $rxbytes * 8);
echo "$rxbytes, $rxpkts, $txbytes, $txpkts, $calls, $registrations";

$rrd_name = 'data';
$rrd_def = RrdDefinition::make()
    ->addDataset('INOCTETS', 'COUNTER', null, 100000000000)
    ->addDataset('OUTOCTETS', 'COUNTER', null, 10000000000)
    ->addDataset('INPKTS', 'COUNTER', null, 10000000000)
    ->addDataset('OUTPKTS', 'COUNTER', null, 10000000000)
    ->addDataset('CALLS', 'COUNTER', null, 10000000000)
    ->addDataset('REGISTRATIONS', 'COUNTER', null, 10000000000);

$fields = [
    'INOCTETS'      => $rxbytes,
    'OUTOCTETS'     => $txbytes,
    'INPKTS'        => $rxpkts,
    'OUTPKTS'       => $rxbytes,
    'CALLS'         => $calls,
    'REGISTRATIONS' => $registrations,
];

$tags = compact('rrd_name', 'rrd_def');
data_update($device, 'snom-data', $tags, $fields);
