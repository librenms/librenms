<?php

// We're discovering this MIB
// snmpwalk -v2c -c <community> <hostname> -M mibs/junose/ -m Juniper-UNI-ATM-MIB juniAtmVpStatsEntry
// JunOSe ATM vps
use LibreNMS\Config;

if ($device['os'] == 'junose' && Config::get('enable_ports_junoseatmvp')) {
    $vp_array = snmpwalk_cache_multi_oid($device, 'juniAtmVpStatsInCells', $vp_array, 'Juniper-UNI-ATM-MIB', 'junose');
    $valid_vp = [];
    d_echo($vp_array);

    if (is_array($vp_array)) {
        foreach ($vp_array as $index => $entry) {
            [$ifIndex,$vp_id] = explode('.', $index);

            $port_id = dbFetchCell('SELECT `port_id` FROM `ports` WHERE `device_id` = ? AND `ifIndex` = ?', [$device['device_id'], $ifIndex]);

            if (is_numeric($port_id) && is_numeric($vp_id)) {
                discover_juniAtmvp($valid_vp, $port_id, $vp_id, null);
            }
        } //end foreach
    } //end if

    unset($vp_array);

    // Remove ATM vps which weren't redetected here
    $sql = "SELECT * FROM `ports` AS P, `juniAtmVp` AS J WHERE P.`device_id`  = '" . $device['device_id'] . "' AND J.port_id = P.port_id";

    d_echo($valid_vp);

    foreach (dbFetchRows($sql) as $test) {
        $port_id = $test['port_id'];
        $vp_id = $test['vp_id'];
        d_echo($port_id . ' -> ' . $vp_id . "\n");

        if (! $valid_vp[$port_id][$vp_id]) {
            echo '-';
            dbDelete('juniAtmvp', '`juniAtmVp` = ?', [$test['juniAtmvp']]);
        }

        unset($port_id);
        unset($vp_id);
    }

    unset($valid_vp);
    echo "\n";
}//end if
