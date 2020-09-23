<?php

if ($device['os'] == 'ies5000') {
    $array = snmpwalk_cache_oid($device, 'memoryUsageTable', null, 'ZYXEL-IES5000-MIB', 'zyxel', ['-LE 3', '-OQUs', '-Pu']);
    if (is_array($array)) {
        echo 'IES5000: ';
        foreach ($array as $key => $value) {
            if (is_numeric($value['memoryCurValue'])) {
                $perc_warn = $value['memoryHighThresh'];
                $descr = $value['memoryDescr'];
                discover_mempool($valid_mempool, $device, $key, 'ies5000', $descr, null, null, null, $perc_warn);
            }
        }

        unset($array);
    }
}
