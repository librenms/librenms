<?php
if ($device['type'] == 'wireless' && $device['os'] == 'arubaos') {
    global $config;

    echo 'Aruba Controller: ';
    $polled = time();

    // Build SNMP Cache Array
    // stuff about the controller
    $switch_info_oids    = array(
        'wlsxSwitchRole',
        'wlsxSwitchMasterIp',
    );
    $switch_counter_oids = array(
        'wlsxSwitchTotalNumAccessPoints.0',
        'wlsxSwitchTotalNumStationsAssociated.0',
    );


    $switch_apinfo_oids = array(
        'wlsxWlanRadioEntry',
        'wlanAPChInterferenceIndex',
    );
    $switch_apname_oids = array('wlsxWlanRadioEntry.16');



    $aruba_oids = array_merge($switch_info_oids, $switch_counter_oids);
    echo 'Caching Oids: ';
    foreach ($aruba_oids as $oid) {
        echo "$oid ";
        $aruba_stats = snmpwalk_cache_oid($device, $oid, $aruba_stats, 'WLSX-SWITCH-MIB');
    }

    foreach ($switch_apinfo_oids as $oid) {
        echo "$oid ";
        $aruba_apstats = snmpwalk_cache_oid_num($device, $oid, $aruba_apstats, 'WLSX-WLAN-MIB');
    }

    foreach ($switch_apname_oids as $oid) {
        echo "$oid ";
        $aruba_apnames = snmpwalk_cache_oid_num($device, $oid, $aruba_apnames, 'WLSX-WLAN-MIB');
    }


    echo "\n";

    $rrdfile = $host_rrd.'/aruba-controller'.safename('.rrd');
    if (!is_file($rrdfile)) {
        rrdtool_create($rrdfile, ' --step 300 DS:NUMAPS:GAUGE:600:0:12500000000 DS:NUMCLIENTS:GAUGE:600:0:12500000000 '.$config['rrd_rra']);
    }

    $fields = array(
        'NUMAPS'     => $aruba_stats[0]['wlsxSwitchTotalNumAccessPoints'],
        'NUMCLIENTS' => $aruba_stats[0]['wlsxSwitchTotalNumStationsAssociated'],
    );
    $ret             = rrdtool_update($rrdfile, $fields);

    $tags = array();
    influx_update($device,'aruba-controller',$tags,$fields);

    // also save the info about how many clients in the same place as the wireless module
    $wificlientsrrd = $config['rrd_dir'].'/'.$device['hostname'].'/'.safename('wificlients-radio1.rrd');

    if (!is_file($wificlientsrrd)) {
        rrdtool_create($wificlientsrrd, '--step 300 DS:wificlients:GAUGE:600:-273:10000 '.$config['rrd_rra']);
    }

    $fields = array(
        'wificlients' => $aruba_stats[0]['wlsxSwitchTotalNumStationsAssociated'],
    );
    rrdtool_update($wificlientsrrd, $fields);

    $tags = array('radio' => '1');
    influx_update($device,'wificlients',$tags,$fields);

    $graphs['wifi_clients'] = true;


    $ap_db = dbFetchRows('SELECT * FROM `access_points` WHERE `device_id` = ?', array($device['device_id']));


    foreach ($aruba_apnames as $key => $value) {
        $radioid       = str_replace('1.3.6.1.4.1.14823.2.2.1.5.2.1.5.1.16.', '', $key);
        $name          = $value[''];
        $type          = $aruba_apstats["1.3.6.1.4.1.14823.2.2.1.5.2.1.5.1.2.$radioid"][''];
        $channel       = ($aruba_apstats["1.3.6.1.4.1.14823.2.2.1.5.2.1.5.1.3.$radioid"][''] + 0);
        $txpow         = ($aruba_apstats["1.3.6.1.4.1.14823.2.2.1.5.2.1.5.1.4.$radioid"][''] + 0);
        $radioutil     = ($aruba_apstats["1.3.6.1.4.1.14823.2.2.1.5.2.1.5.1.6.$radioid"][''] + 0);
        $numasoclients = ($aruba_apstats["1.3.6.1.4.1.14823.2.2.1.5.2.1.5.1.7.$radioid"][''] + 0);
        $nummonclients = ($aruba_apstats["1.3.6.1.4.1.14823.2.2.1.5.2.1.5.1.8.$radioid"][''] + 0);
        $numactbssid   = ($aruba_apstats["1.3.6.1.4.1.14823.2.2.1.5.2.1.5.1.9.$radioid"][''] + 0);
        $nummonbssid   = ($aruba_apstats["1.3.6.1.4.1.14823.2.2.1.5.2.1.5.1.10.$radioid"][''] + 0);
        $interference  = ($aruba_apstats["1.3.6.1.4.1.14823.2.2.1.5.3.1.6.1.11.$radioid"][''] + 0);

        $radionum = substr($radioid, (strlen($radioid) - 1), 1);

        if ($debug) {
            echo "* radioid: $radioid\n";
            echo "  radionum: $radionum\n";
            echo "  name: $name\n";
            echo "  type: $type\n";
            echo "  channel: $channel\n";
            echo "  txpow: $txpow\n";
            echo "  radioutil: $radioutil\n";
            echo "  numasoclients: $numasoclients\n";
            echo "  interference: $interference\n";
        }

        // if there is a numeric channel, assume the rest of the data is valid, I guess
        if (is_numeric($channel)) {
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

            $tags = array('name' => $name, 'radionum' => $radionum);
            influx_update($device,'aruba',$tags,$fields);

        }

        // generate the mac address
        $macparts = explode('.', $radioid, -1);
        $mac      = '';
        foreach ($macparts as $part) {
            $mac .= sprintf('%02x', $part).':';
        }

        $mac = rtrim($mac, ':');


        $foundid = 0;

        for ($z = 0; $z < sizeof($ap_db); $z++) {
            if ($ap_db[$z]['name'] == $name && $ap_db[$z]['radio_number'] == $radionum) {
                $foundid           = $ap_db[$z]['accesspoint_id'];
                $ap_db[$z]['seen'] = 1;
                continue;
            }
        }



        if ($foundid == 0) {
            $ap_id = dbInsert(array('device_id' => $device['device_id'], 'name' => $name, 'radio_number' => $radionum, 'type' => $type, 'mac_addr' => $mac, 'channel' => $channel, 'txpow' => $txpow, 'radioutil' => $radioutil, 'numasoclients' => $numasoclients, 'nummonclients' => $nummonclients, 'numactbssid' => $numactbssid, 'nummonbssid' => $nummonbssid, 'interference' => $interference), 'access_points');
        }
        else {
            dbUpdate(array('mac_addr' => $mac, 'deleted' => 0, 'channel' => $channel, 'txpow' => $txpow, 'radioutil' => $radioutil, 'numasoclients' => $numasoclients, 'nummonclients' => $nummonclients, 'numactbssid' => $numactbssid, 'nummonbssid' => $nummonbssid, 'interference' => $interference), 'access_points', '`accesspoint_id` = ?', array($foundid));
        }
    }//end foreach

    // mark APs which are not on this controller anymore as deleted
    for ($z = 0; $z < sizeof($ap_db); $z++) {
        if (!isset($ap_db[$z]['seen']) && $ap_db[$z]['deleted'] == 0) {
            dbUpdate(array('deleted' => 1), 'access_points', '`accesspoint_id` = ?', array($ap_db[$z]['accesspoint_id']));
        }
    }
}//end if
