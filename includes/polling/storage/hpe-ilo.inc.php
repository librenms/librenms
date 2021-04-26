%
<?php

if (! is_array($storage_cache['hpe-ilo'])) {
    $storage_cache['hpe-ilo'] = snmpwalk_group($device, 'cpqHoFileSysEntry', 'CPQHOST-MIB');
    echo 'HPE ILO4 ';
}

$entry = $storage_cache['hpe-ilo'][$storage['storage_index']];

$storage['units'] = 1024 * 1024;
$storage['size'] = ($entry['cpqHoFileSysSpaceTotal'] * $storage['units']);
$storage['used'] = ($entry['cpqHoFileSysSpaceUsed'] * $storage['units']);
$storage['free'] = ($storage['size'] - $storage['used']);
