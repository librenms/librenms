<?php

if ($device['os'] == 'ies52xxM') {
    $array = snmpwalk_cache_oid($device, 'memStatsTable', null, 'IES5206-MIB', 'zyxel');

    if (is_array($array)) {
        echo 'IES52XXM: ';
        foreach ($array as $key => $value) {
            if (is_numeric($key) && is_numeric($value['memStatsCurrent'])) {
                $perc_warn = $value['memStatsHighThreshold'];
                discover_mempool($valid_mempool, $device, $key, 'ies52xxM-mem', 'Memory ' . $key, null, null, null, $perc_warn);
            }
        }

        unset($array);
    }
}
