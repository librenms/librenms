<?php

echo 'MWRM-RADIO-MIB ';

$divisor = 1;
$type    = 'ceraos';

$oids = snmp_walk($device, 'genEquipRfuStatusRxLevel', '-OsqnU', 'MWRM-RADIO-MIB');

foreach (explode("\n", $oids) as $data) {
    $data = trim($data);
    if ($data) {
        list($oid,$value) = explode(' ', $data, 2);
        $split_oid        = explode('.', $oid);
        $index            = $split_oid[(count($split_oid) - 1)];
        $descr            = trim(snmp_get($device, '.1.3.6.1.4.1.2281.10.5.1.1.2.'.$index, '-Oqv'), '"') . ' Signal';

        discover_sensor($valid['sensor'], 'signal', $device, $oid, $index, $type, $descr, $divisor, '1', null, null, null, null, $value);
    }
}
#"genEquipRfuStatusRxLevel"			"1.3.6.1.4.1.2281.10.5.1.1.2"

