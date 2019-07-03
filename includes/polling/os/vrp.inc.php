<?php

//Huawei VRP devices are not providing the HW description in a unified way

preg_match("/Version [^\s]*/m", $device['sysDescr'], $matches);
$version = trim(str_replace('Version ', '', $matches[0]));

preg_match("/\(([^\s]*) (V[0-9]{3}R[0-9]{3}[0-9A-Z]+)/m", $device['sysDescr'], $matches);

if (!empty($matches[2])) {
    $version .= " (" . trim($matches[2]) . ")";
}

$patch = snmp_getnext($device, 'HUAWEI-SYS-MAN-MIB::hwPatchVersion', '-OQv');
if ($patch) {
    $version .= " [$patch]";
}

$oidList = [
    'HUAWEI-ENTITY-EXTENT-MIB::hwEntityExtentMIB.6.5.0',
    'HUAWEI-DEVICE-EXT-MIB::hwProductName.0',
    'HUAWEI-MIB::hwDatacomm.183.1.25.1.5.1',
    'HUAWEI-MIB::mlsr.20.1.1.1.3.0',
];

foreach ($oidList as $oid) {
    $hardware_tmp = snmp_get($device, $oid, '-OQv');

    if (!empty($hardware_tmp)) {
        $hardware = "Huawei " . $hardware_tmp;
        break;
    }
}

// Let's use sysDescr if nothing else is found in the OIDs. sysDescr is less detailled than OIDs most of the time
if (empty($hardware_tmp) && !empty($matches[1])) {
    $hardware = "Huawei " . trim($matches[1]);
}

// Polling the Wireless data

use LibreNMS\RRD\RrdDefinition;

// check for Wireless Capability
$apTable = snmpwalk_group($device, 'hwWlanApName', 'HUAWEI-WLAN-AP-MIB', 2);

//Check for exitence of at least 1 AP to continue the polling)
if (!empty($apTable)) {
    $apTableOids = [
        'hwWlanApSn',
        'hwWlanApTypeInfo',
    ];
    foreach ($apTableOids as $apTableOid) {
        $apTable = snmpwalk_group($device, $apTableOid, 'HUAWEI-WLAN-AP-MIB', 2, $apTable);
    }

    $apRadioTableOids = [ // hwWlanRadioInfoTable
        'hwWlanRadioMac',
        'hwWlanRadioChUtilizationRate',
        'hwWlanRadioChInterferenceRate',
        'hwWlanRadioActualEIRP',
        'hwWlanRadioFreqType',
        'hwWlanRadioWorkingChannel',
    ];

    $clientPerRadio = [];
    $radioTable = [];
    foreach ($apRadioTableOids as $apRadioTableOid) {
        $radioTable = snmpwalk_group($device, $apRadioTableOid, 'HUAWEI-WLAN-AP-RADIO-MIB', 2, $radioTable);
    }

    $numClients = 0;
    $vapInfoTable = snmpwalk_group($device, 'hwWlanVapStaOnlineCnt', 'HUAWEI-WLAN-VAP-MIB', 3);
    foreach ($vapInfoTable as $ap_id => $ap) {
        //Convert mac address (hh:hh:hh:hh:hh:hh) to dec OID (ddd.ddd.ddd.ddd.ddd.ddd)
        //$a_index_oid = implode(".", array_map("hexdec", explode(":", $ap_id)));
        foreach ($ap as $r_id => $radio) {
            foreach ($radio as $s_index => $ssid) {
                $clientPerRadio[$ap_id][$r_id] += $ssid['hwWlanVapStaOnlineCnt'];
                $numClients +=  $ssid['hwWlanVapStaOnlineCnt'];
            }
        }
    }

    $numRadios = count($radioTable);

    $rrd_def = RrdDefinition::make()
        ->addDataset('NUMAPS', 'GAUGE', 0, 12500000000)
        ->addDataset('NUMCLIENTS', 'GAUGE', 0, 12500000000);

    $fields = [
        'NUMAPS'     => $numRadios,
        'NUMCLIENTS' => $numClients,
    ];

    $tags = compact('rrd_def');
    data_update($device, 'vrp', $tags, $fields);

    $ap_db = dbFetchRows('SELECT * FROM `access_points` WHERE `device_id` = ?', [$device['device_id']]);

    foreach ($radioTable as $ap_id => $ap) {
        foreach ($ap as $r_id => $radio) {
            $channel       = $radio['hwWlanRadioWorkingChannel'];
            $mac           = $radio['hwWlanRadioMac'];
            $name          = $apTable[$ap_id]['hwWlanApName'] . " Radio " . $r_id;
            $radionum      = $r_id ;
            $txpow         = $radio['hwWlanRadioActualEIRP'];
            $interference  = $radio['hwWlanRadioChInterferenceRate'];
            $radioutil     = $radio['hwWlanRadioChUtilizationRate'];
            $numasoclients  = $clientPerRadio[$ap_id][$r_id];

            switch ($radio['hwWlanRadioFreqType']) {
                case 1:
                    $type = "2.4Ghz";
                    break;
                case 2:
                    $type = "5Ghz";
                    break;
                default:
                    $type = "unknown (huawei " . $radio['hwWlanRadioFreqType'] . ")";
            }

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

            $rrd_name = ['arubaap', $name.$radionum];
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
                    $foundid           = $ap_db[$z]['accesspoint_id'];
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
                        'interference' => $interference
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
                        'interference' => $interference
                    ],
                    'access_points',
                    '`accesspoint_id` = ?',
                    [$foundid]
                );
            }
        }//end foreach 1
    }//end foreach 2

    for ($z = 0; $z < sizeof($ap_db); $z++) {
        if (!isset($ap_db[$z]['seen']) && $ap_db[$z]['deleted'] == 0) {
            dbUpdate(['deleted' => 1], 'access_points', '`accesspoint_id` = ?', [$ap_db[$z]['accesspoint_id']]);
        }
    }
}
