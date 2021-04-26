<?php
/*
 * LibreNMS Cisco wireless controller information module
 *
 * Copyright (c) 2016 Tuomas Riihimäki <tuomari@iudex.fi>
 * This program is free software: you can redistribute it and/or modify it
 * under the terms of the GNU General Public License as published by the
 * Free Software Foundation, either version 3 of the License, or (at your
 * option) any later version.  Please see LICENSE.txt at the top level of
 * the source code distribution for details.
 */

use LibreNMS\RRD\RrdDefinition;

$oids = ['entPhysicalModelName.1', 'entPhysicalSoftwareRev.1', 'entPhysicalSerialNum.1'];

$data = snmp_get_multi($device, $oids, '-OQUs', 'ENTITY-MIB');

if (isset($data[1]['entPhysicalSoftwareRev']) && $data[1]['entPhysicalSoftwareRev'] != '') {
    $version = $data[1]['entPhysicalSoftwareRev'];
}

if (isset($data[1]['entPhysicalName']) && $data[1]['entPhysicalName'] != '') {
    $hardware = $data[1]['entPhysicalName'];
}

if (isset($data[1]['entPhysicalModelName']) && $data[1]['entPhysicalModelName'] != '') {
    $hardware = $data[1]['entPhysicalModelName'];
}

if (empty($hardware)) {
    $hardware = snmp_get($device, 'sysObjectID.0', '-Osqv', 'SNMPv2-MIB:CISCO-PRODUCTS-MIB');
}

$stats = snmpwalk_cache_oid($device, 'bsnAPEntry', $stats, 'AIRESPACE-WIRELESS-MIB', null, '-OQUsb');
$radios = snmpwalk_cache_oid($device, 'bsnAPIfEntry', $radios, 'AIRESPACE-WIRELESS-MIB', null, '-OQUsb');
$APstats = snmpwalk_cache_oid($device, 'bsnApIfNoOfUsers', $APstats, 'AIRESPACE-WIRELESS-MIB', null, '-OQUsxb');
$loadParams = snmpwalk_cache_oid($device, 'bsnAPIfLoadChannelUtilization', $loadParams, 'AIRESPACE-WIRELESS-MIB', null, '-OQUsb');
$interferences = snmpwalk_cache_oid($device, 'bsnAPIfInterferencePower', $interferences, 'AIRESPACE-WIRELESS-MIB', null, '-OQUsb');

$numAccessPoints = is_countable($stats) ? count($stats) : 0;
$numClients = 0;

foreach ($APstats as $key => $value) {
    $numClients += $value['bsnApIfNoOfUsers'];
}

$rrd_def = RrdDefinition::make()
    ->addDataset('NUMAPS', 'GAUGE', 0, 12500000000)
    ->addDataset('NUMCLIENTS', 'GAUGE', 0, 12500000000);

$fields = [
    'NUMAPS'     => $numAccessPoints,
    'NUMCLIENTS' => $numClients,
];

$tags = compact('rrd_def');
data_update($device, 'ciscowlc', $tags, $fields);

$ap_db = dbFetchRows('SELECT * FROM `access_points` WHERE `device_id` = ?', [$device['device_id']]);

foreach ($radios as $key => $value) {
    $indexName = substr($key, 0, -2);

    $channel = str_replace('ch', '', $value['bsnAPIfPhyChannelNumber']);
    $mac = str_replace(' ', ':', $stats[$indexName]['bsnAPDot3MacAddress']);
    $name = $stats[$indexName]['bsnAPName'];
    $numasoclients = $value['bsnApIfNoOfUsers'];
    $radioArray = explode('.', $key);
    $radionum = array_pop($radioArray);
    $txpow = $value['bsnAPIfPhyTxPowerLevel'];
    $type = $value['bsnAPIfType'];
    $interference = 128 + $interferences[$key . '.' . $channel]['bsnAPIfInterferencePower'];
    $radioutil = $loadParams[$key]['bsnAPIfLoadChannelUtilization'];

    // TODO
    $numactbssid = 0;
    $nummonbssid = 0;
    $nummonclients = 0;

    d_echo("  name: $name\n");
    d_echo("  radionum: $radionum\n");
    d_echo("  type: $type\n");
    d_echo("  channel: $channel\n");
    d_echo("  txpow: $txpow\n");
    d_echo("  radioutil: $radioutil\n");
    d_echo("  numasoclients: $numasoclients\n");
    d_echo("  interference: $interference\n");

    // if there is a numeric channel, assume the rest of the data is valid, I guess
    if (! is_numeric($channel)) {
        continue;
    }

    $rrd_name = ['arubaap', $name . $radionum];
    $rrd_def = RrdDefinition::make()
        ->addDataset('channel', 'GAUGE', 0, 200)
        ->addDataset('txpow', 'GAUGE', 0, 200)
        ->addDataset('radioutil', 'GAUGE', 0, 100)
        ->addDataset('nummonclients', 'GAUGE', 0, 500)
        ->addDataset('nummonbssid', 'GAUGE', 0, 200)
        ->addDataset('numasoclients', 'GAUGE', 0, 500)
        ->addDataset('interference', 'GAUGE', 0, 2000);

    $fields = [
        'channel'         => $channel,
        'txpow'           => $txpow,
        'radioutil'       => $radioutil,
        'nummonclients'   => $nummonclients,
        'nummonbssid'     => $nummonbssid,
        'numasoclients'   => $numasoclients,
        'interference'    => $interference,
    ];

    $tags = compact('name', 'radionum', 'rrd_name', 'rrd_def');
    data_update($device, 'arubaap', $tags, $fields);

    $foundid = 0;

    for ($z = 0; $z < sizeof($ap_db); $z++) {
        if ($ap_db[$z]['name'] == $name && $ap_db[$z]['radio_number'] == $radionum) {
            $foundid = $ap_db[$z]['accesspoint_id'];
            $ap_db[$z]['seen'] = 1;
            continue;
        }
    }

    if ($foundid == 0) {
        $ap_id = dbInsert(
            [
                'device_id' => $device['device_id'],
                'name' => $name,
                'radio_number' => $radionum,
                'type' => $type,
                'mac_addr' => $mac,
                'channel' => $channel,
                'txpow' => $txpow,
                'radioutil' => $radioutil,
                'numasoclients' => $numasoclients,
                'nummonclients' => $nummonclients,
                'numactbssid' => $numactbssid,
                'nummonbssid' => $nummonbssid,
                'interference' => $interference,
            ],
            'access_points'
        );
    } else {
        dbUpdate(
            [
                'mac_addr' => $mac,
                'type' => $type,
                'deleted' => 0,
                'channel' => $channel,
                'txpow' => $txpow,
                'radioutil' => $radioutil,
                'numasoclients' => $numasoclients,
                'nummonclients' => $nummonclients,
                'numactbssid' => $numactbssid,
                'nummonbssid' => $nummonbssid,
                'interference' => $interference,
            ],
            'access_points',
            '`accesspoint_id` = ?',
            [$foundid]
        );
    }
}//end foreach

for ($z = 0; $z < sizeof($ap_db); $z++) {
    if (! isset($ap_db[$z]['seen']) && $ap_db[$z]['deleted'] == 0) {
        dbUpdate(['deleted' => 1], 'access_points', '`accesspoint_id` = ?', [$ap_db[$z]['accesspoint_id']]);
    }
}
