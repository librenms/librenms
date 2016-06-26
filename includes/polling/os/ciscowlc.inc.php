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

global $config;

$oids = 'entPhysicalModelName.1 entPhysicalSoftwareRev.1 entPhysicalSerialNum.1';

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

$stats      = snmpwalk_cache_oid($device, "bsnAPEntry", $stats, 'AIRESPACE-WIRELESS-MIB', null, '-OQUsb');
$radios     = snmpwalk_cache_oid($device, "bsnAPIfEntry", $radios, 'AIRESPACE-WIRELESS-MIB', null, '-OQUsb');
$APstats    = snmpwalk_cache_oid($device, 'bsnApIfNoOfUsers', $APstats, 'AIRESPACE-WIRELESS-MIB', null, '-OQUsxb');
$loadParams = snmpwalk_cache_oid($device, "bsnAPIfLoadChannelUtilization", $loadParams, 'AIRESPACE-WIRELESS-MIB', null, '-OQUsb');
$interferences = snmpwalk_cache_oid($device, "bsnAPIfInterferencePower", $interferences, 'AIRESPACE-WIRELESS-MIB', null, '-OQUsb');

$numAccessPoints = count($stats);
$numClients = 0;

foreach ($APstats as $key => $value) {
    $numClients += $value['bsnApIfNoOfUsers'];
}

$rrdfile = $host_rrd.'/ciscowlc'.safename('.rrd');
if (!is_file($rrdfile)) {
    rrdtool_create($rrdfile, ' --step 300 DS:NUMAPS:GAUGE:600:0:12500000000 DS:NUMCLIENTS:GAUGE:600:0:12500000000 '.$config['rrd_rra']);
}

$fields = array(
    'NUMAPS'     => $numAccessPoints,
    'NUMCLIENTS' => $numClients
);
$ret = rrdtool_update($rrdfile, $fields);

// also save the info about how many clients in the same place as the wireless module
$wificlientsrrd = $config['rrd_dir'].'/'.$device['hostname'].'/'.safename('wificlients-radio1.rrd');

if (!is_file($wificlientsrrd)) {
        rrdtool_create($wificlientsrrd, '--step 300 DS:wificlients:GAUGE:600:-273:10000 '.$config['rrd_rra']);
}

$fields = array(
    'wificlients' => $numClients
);

rrdtool_update($wificlientsrrd, $fields);
$graphs['wifi_clients'] = true;


$ap_db = dbFetchRows('SELECT * FROM `access_points` WHERE `device_id` = ?', array($device['device_id']));

foreach ($radios as $key => $value) {
 
    $indexName = substr($key, 0, -2);

    $channel       = str_replace('ch', '', $value['bsnAPIfPhyChannelNumber']);
    $mac           = str_replace(' ', ':', $stats[$indexName]['bsnAPDot3MacAddress']);
    $name          = $stats[$indexName]['bsnAPName'];
    $numasoclients = $value['bsnApIfNoOfUsers'];
    $radionum      = array_pop(explode('.',$key));
    $txpow         = $value['bsnAPIfPhyTxPowerLevel'];
    $type          = $value['bsnAPIfType'];
    $interference  = 128 + $interferences[$key . '.' . $channel]['bsnAPIfInterferencePower'];
    $radioutil     = $loadParams[$key]['bsnAPIfLoadChannelUtilization'];

    // TODO
    $numactbssid   = 0;
    $nummonbssid   = 0;
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
    if (!is_numeric($channel)) {
        continue;
    }

    $rrd_file = $config['rrd_dir'].'/'.$device['hostname'].'/'.safename("arubaap-$name.$radionum.rrd");
    if (!is_file($rrd_file)) {
        $dslist  = 'DS:channel:GAUGE:600:0:200 ';
        $dslist .= 'DS:txpow:GAUGE:600:0:200 ';
        $dslist .= 'DS:radioutil:GAUGE:600:0:100 ';
        $dslist .= 'DS:nummonclients:GAUGE:600:0:500 ';
        $dslist .= 'DS:nummonbssid:GAUGE:600:0:200 ';
        $dslist .= 'DS:numasoclients:GAUGE:600:0:500 ';
        $dslist .= 'DS:interference:GAUGE:600:0:2000 ';
        rrdtool_create($rrd_file, "--step 300 $dslist ".$config['rrd_rra']);
    }

    $fields = array(
        'channel'         => $channel,
        'txpow'           => $txpow,
        'radioutil'       => $radioutil,
        'nummonclients'   => $nummonclients,
        'nummonbssid'     => $nummonbssid,
        'numasoclients'   => $numasoclients,
        'interference'    => $interference,
    );

    rrdtool_update($rrd_file, $fields);

    $foundid = 0;

    for ($z = 0; $z < sizeof($ap_db); $z++) {
        if ($ap_db[$z]['name'] == $name && $ap_db[$z]['radio_number'] == $radionum) {
            $foundid           = $ap_db[$z]['accesspoint_id'];
            $ap_db[$z]['seen'] = 1;
            continue;
        }
    }

    if ($foundid == 0) {
        $ap_id = dbInsert(
        array(
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
            'interference' => $interference
         ),
        'access_points');
    } else {
        dbUpdate(
            array(
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
                'interference' => $interference
                 ),
            'access_points',
            '`accesspoint_id` = ?', array($foundid));
    }
}//end foreach

for ($z = 0; $z < sizeof($ap_db); $z++) {
    if (!isset($ap_db[$z]['seen']) && $ap_db[$z]['deleted'] == 0) {
        dbUpdate(array('deleted' => 1), 'access_points', '`accesspoint_id` = ?', array($ap_db[$z]['accesspoint_id']));
    }
}
