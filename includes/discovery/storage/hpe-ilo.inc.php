<?php

if (in_array($device['os'], ['windows', 'hpe-ilo']) || $device['os_group'] == 'unix') {
    $ilo_storage = snmpwalk_group($device, 'cpqHoFileSysEntry', 'CPQHOST-MIB', 1, [], 'hp');
    $units = 1024 * 1024;

    if (is_array($ilo_storage)) {
        echo 'HPE ILO4 ';
        foreach ($ilo_storage as $index => $storage) {
            $type = $storage['cpqHoFileSysDesc'];
            preg_match_all('/\\[:(.*?)\\]/', $type, $matches);
            $fstype = $matches[1][0];
            $descr = $storage['cpqHoFileSysDesc'];
            $size = $storage['cpqHoFileSysSpaceTotal'];
            $used = $storage['cpqHoFileSysSpaceUsed'];
            if (is_int($index)) {
                discover_storage($valid_storage, $device, $index, $fstype, 'hpe-ilo', $descr, $size, $units, $used);
            }
            unset($deny, $fstype, $descr, $size, $used, $units, $storage_rrd, $old_storage_rrd, $hrstorage_array);
        }
    }
}
