<?php

// Force10 E-Series
// F10-CHASSIS-MIB::chSysCardType.1 = INTEGER: rpmCardEF3(206)
// F10-CHASSIS-MIB::chSysCardType.3 = INTEGER: lc2401E24PG3(69)
// F10-CHASSIS-MIB::chSysCardType.4 = INTEGER: lc2401E24PG3(69)
// F10-CHASSIS-MIB::chSysCardUpperTemp.1 = Gauge32: 34
// F10-CHASSIS-MIB::chSysCardUpperTemp.3 = Gauge32: 34
// F10-CHASSIS-MIB::chSysCardUpperTemp.4 = Gauge32: 34
if ($device['os'] == 'ftos' || $device['os_group'] == 'ftos') {
    echo 'FTOS E-Series ';

    $oids = snmpwalk_cache_oid($device, 'chSysCardUpperTemp', array(), 'F10-CHASSIS-MIB', $config['mib_dir'].':'.$config['mib_dir'].'/ftos');

    if (is_array($oids)) {
        foreach ($oids as $index => $entry) {
            $descr   = 'Slot '.$index;
            $oid     = '.1.3.6.1.4.1.6027.3.1.1.2.3.1.8.'.$index;
            $current = $entry['chSysCardUpperTemp'];

            discover_sensor($valid['sensor'], 'temperature', $device, $oid, $index, 'ftos-eseries', $descr, '1', '1', null, null, null, null, $current);
        }
    }
}
