<?php

use LibreNMS\RRD\RrdDefinition;

if ($device['type'] == 'wireless' && $device['os'] == 'arubaos') {
    $polled = time();

    // Build SNMP Cache Array
    // stuff about the controller
    $switch_info_oids = [
        'wlsxSwitchRole',
        'wlsxSwitchMasterIp',
    ];
    $switch_counter_oids = [
        'wlsxSwitchTotalNumAccessPoints.0',
        'wlsxSwitchTotalNumStationsAssociated.0',
    ];

    $switch_apinfo_oids = [
        'wlsxWlanRadioEntry',
        'wlanAPChInterferenceIndex',
    ];
    $switch_apname_oids = ['wlsxWlanRadioEntry.16'];

    // initialize arrays to avoid overwriting them in foreach loops below
    $aruba_stats = [];
    $aruba_apstats = [];
    $aruba_apnames = [];

    $aruba_oids = array_merge($switch_info_oids, $switch_counter_oids);
    echo 'Caching Oids: ';
    foreach ($aruba_oids as $oid) {
        echo "$oid ";
        $aruba_stats = snmpwalk_cache_oid($device, $oid, $aruba_stats, 'WLSX-SWITCH-MIB');
    }

    foreach ($switch_apinfo_oids as $oid) {
        echo "$oid ";
        $aruba_apstats = snmpwalk_cache_numerical_oid($device, $oid, $aruba_apstats, 'WLSX-WLAN-MIB');
    }

    foreach ($switch_apname_oids as $oid) {
        echo "$oid ";
        $aruba_apnames = snmpwalk_cache_numerical_oid($device, $oid, $aruba_apnames, 'WLSX-WLAN-MIB');
    }

    echo "\n";

    $rrd_name = 'aruba-controller';
    $rrd_def = RrdDefinition::make()
        ->addDataset('NUMAPS', 'GAUGE', 0, 12500000000)
        ->addDataset('NUMCLIENTS', 'GAUGE', 0, 12500000000);

    $fields = [
        'NUMAPS'     => $aruba_stats[0]['wlsxSwitchTotalNumAccessPoints'],
        'NUMCLIENTS' => $aruba_stats[0]['wlsxSwitchTotalNumStationsAssociated'],
    ];

    $tags = compact('rrd_name', 'rrd_def');
    data_update($device, 'aruba-controller', $tags, $fields);

    $ap_db = dbFetchRows('SELECT * FROM `access_points` WHERE `device_id` = ?', [$device['device_id']]);

    foreach ($aruba_apnames as $key1 => $value1) {
        foreach ($value1 as $key => $value) {
            $radioid = str_replace('.1.3.6.1.4.1.14823.2.2.1.5.2.1.5.1.16.', '', $key);
            $name = $value;
            $type = $aruba_apstats[$key1][".1.3.6.1.4.1.14823.2.2.1.5.2.1.5.1.2.$radioid"];
            $channel = cast_number($aruba_apstats[$key1][".1.3.6.1.4.1.14823.2.2.1.5.2.1.5.1.3.$radioid"]);
            $txpow = cast_number($aruba_apstats[$key1][".1.3.6.1.4.1.14823.2.2.1.5.2.1.5.1.4.$radioid"]) / 2;
            $radioutil = cast_number($aruba_apstats[$key1][".1.3.6.1.4.1.14823.2.2.1.5.2.1.5.1.6.$radioid"]);
            $numasoclients = cast_number($aruba_apstats[$key1][".1.3.6.1.4.1.14823.2.2.1.5.2.1.5.1.7.$radioid"]);
            $nummonclients = cast_number($aruba_apstats[$key1][".1.3.6.1.4.1.14823.2.2.1.5.2.1.5.1.8.$radioid"]);
            $numactbssid = cast_number($aruba_apstats[$key1][".1.3.6.1.4.1.14823.2.2.1.5.2.1.5.1.9.$radioid"]);
            $nummonbssid = cast_number($aruba_apstats[$key1][".1.3.6.1.4.1.14823.2.2.1.5.2.1.5.1.10.$radioid"]);
            $interference = cast_number($aruba_apstats[$key1][".1.3.6.1.4.1.14823.2.2.1.5.3.1.6.1.11.$radioid"]);

            $radionum = substr($radioid, (strlen($radioid) - 1), 1);

            d_echo($key . PHP_EOL);
            d_echo($value . PHP_EOL);
            d_echo('* radioid:        ' . $radioid . PHP_EOL);
            d_echo('  radionum:       ' . $radionum . PHP_EOL);
            d_echo('  name:           ' . $name . PHP_EOL);
            d_echo('  type:           ' . $type . PHP_EOL);
            d_echo('  channel:        ' . $channel . PHP_EOL);
            d_echo('  txpow:          ' . $txpow . PHP_EOL);
            d_echo('  radioutil:      ' . $radioutil . PHP_EOL);
            d_echo('  numasoclients:  ' . $numasoclients . PHP_EOL);
            d_echo('  interference:   ' . $interference . PHP_EOL);
            d_echo(PHP_EOL);

            // if there is a numeric channel, assume the rest of the data is valid, I guess
            if (is_numeric($channel)) {
                $rrd_name = ['arubaap',  $name . $radionum];

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

                $tags = [
                    'name' => $name,
                    'radionum' => $radionum,
                    'rrd_name' => $rrd_name,
                    'rrd_def' => $rrd_def,
                ];

                data_update($device, 'aruba', $tags, $fields);
            }

            // generate the mac address
            $macparts = explode('.', $radioid, -1);
            $mac = '';
            foreach ($macparts as $part) {
                $mac .= sprintf('%02x', $part) . ':';
            }

            $mac = rtrim($mac, ':');

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
                        'channel'       => $channel,
                        'deleted'       => 0,
                        'device_id'     => $device['device_id'],
                        'interference'  => $interference,
                        'mac_addr'      => $mac,
                        'name'          => $name,
                        'numactbssid'   => $numactbssid,
                        'numasoclients' => $numasoclients,
                        'nummonbssid'   => $nummonbssid,
                        'nummonclients' => $nummonclients,
                        'radio_number'  => $radionum,
                        'radioutil'     => $radioutil,
                        'txpow'         => $txpow,
                        'type'          => $type,
                    ],
                    'access_points'
                );
            } else {
                dbUpdate(
                    [
                        'channel'       => $channel,
                        'deleted'       => 0,
                        'interference'  => $interference,
                        'mac_addr'      => $mac,
                        'name'          => $name,
                        'numactbssid'   => $numactbssid,
                        'numasoclients' => $numasoclients,
                        'nummonbssid'   => $nummonbssid,
                        'nummonclients' => $nummonclients,
                        'radio_number'  => $radionum,
                        'radioutil'     => $radioutil,
                        'txpow'         => $txpow,
                        'type'          => $type,
                    ],
                    'access_points',
                    '`accesspoint_id` = ?',
                    [$foundid]
                );
            }
        }//end foreach
    }//end foreach

    // mark APs which are not on this controller anymore as deleted
    for ($z = 0; $z < sizeof($ap_db); $z++) {
        if (! isset($ap_db[$z]['seen']) && $ap_db[$z]['deleted'] == 0) {
            dbUpdate(['deleted' => 1], 'access_points', '`accesspoint_id` = ?', [$ap_db[$z]['accesspoint_id']]);
        }
    }
}//end if
