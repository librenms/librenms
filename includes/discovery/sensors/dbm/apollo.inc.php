<?php

/**
 * Ribbon/ECI Apollo Optical Power sensor discovery.
 *
 * Reads per-port RX/TX optical power (dBm) from ECI/RIBBON Apollo
 * 9603/9608 DWDM equipment via SNMP and registers them as dBm
 * sensors with the description of the corresponding port.
 */
use App\Models\Port;

echo 'Apollo Optical Power ';

$multiplier = 1;
$divisor = 1;

$ports = Port::where('device_id', $device['device_id'])
    ->orderBy('ifIndex')
    ->get(['port_id', 'ifIndex', 'ifName'])
    ->toArray();

$directions = [
    'rx' => ['oid' => '5', 'label' => 'RX'],
    'tx' => ['oid' => '6', 'label' => 'TX'],
];

foreach ($ports as $port) {
    $ifIndex = (int) $port['ifIndex'];
    $ifName = $port['ifName'];

    foreach ($directions as $dir => $meta) {
        $oid = ".1.3.6.1.4.1.5395.3.7.5.1.{$meta['oid']}.$ifIndex";

        $value = \SnmpQuery::options('-OQv')->get($oid)->value();

        if ($value === null || $value === '' || ! preg_match('/(-?\d+(?:\.\d+)?)/', (string) $value, $m)) {
            continue;
        }

        discover_sensor(
            null,
            'dbm',
            $device,
            $oid,
            "apollo-$dir-$ifIndex",
            'apollo',
            "$ifName {$meta['label']}",
            $divisor,
            $multiplier,
            null,
            null,
            null,
            null,
            (float) $m[1],
            'snmp',
            $ifIndex,
            'ports'
        );
    }
}
