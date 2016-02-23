<?php

// Force10 C-Series
// F10-C-SERIES-CHASSIS-MIB::chSysCardType.1 = INTEGER: lc4802E48TB(1024)
// F10-C-SERIES-CHASSIS-MIB::chSysCardType.2 = INTEGER: lc0810EX8PB(2049)
// F10-C-SERIES-CHASSIS-MIB::chSysCardTemp.1 = Gauge32: 25
// F10-C-SERIES-CHASSIS-MIB::chSysCardTemp.2 = Gauge32: 26
if ($device['os'] == 'ftos' || $device['os_group'] == 'ftos') {
    echo 'FTOS C-Series ';
    $oids = snmpwalk_cache_oid($device, 'chSysCardTemp', array(), 'F10-C-SERIES-CHASSIS-MIB', $config['mib_dir'].':'.$config['mib_dir'].'/ftos');
    if (is_array($oids)) {
        foreach ($oids as $index => $entry) {
            $entry['descr']   = 'Slot '.$index;
            $entry['oid']     = '.1.3.6.1.4.1.6027.3.8.1.2.1.1.5.'.$index;
            $entry['current'] = $entry['chSysCardTemp'];
            discover_sensor($valid['sensor'], 'temperature', $device, $entry['oid'], $index, 'ftos-cseries', $entry['descr'], '1', '1', null, null, null, null, $entry['current']);
        }
    }
}
