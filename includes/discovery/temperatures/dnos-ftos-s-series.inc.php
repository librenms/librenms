<?php

// Force10 S-Series
// F10-S-SERIES-CHASSIS-MIB::chStackUnitTemp.1 = Gauge32: 47
// F10-S-SERIES-CHASSIS-MIB::chStackUnitModelID.1 = STRING: S25-01-GE-24V
if ($device['os'] == 'ftos' || $device['os_group'] == 'ftos' || $device['os'] == 'dnos') {
    echo 'FTOS C-Series ';

    $oids = snmpwalk_cache_oid($device, 'chStackUnitTemp', array(), 'F10-S-SERIES-CHASSIS-MIB', $config['mib_dir'].':'.$config['mib_dir'].'/ftos');
    $oids = snmpwalk_cache_oid($device, 'chStackUnitSysType', $oids, 'F10-S-SERIES-CHASSIS-MIB', $config['mib_dir'].':'.$config['mib_dir'].'/ftos');

    if (is_array($oids)) {
        foreach ($oids as $index => $entry) {
            $descr   = 'Unit '.$index.' '.$entry['chStackUnitSysType'];
            $oid     = '.1.3.6.1.4.1.6027.3.10.1.2.2.1.14.'.$index;
            $current = $entry['chStackUnitTemp'];
            discover_sensor($valid['sensor'], 'temperature', $device, $oid, $index, 'ftos-sseries', $descr, '1', '1', null, null, null, null, $current);
        }
    }
}
