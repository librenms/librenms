<?php

$rpm = [];
$rpm_oid = '.1.3.6.1.4.1.6486.801.1.1.1.3.1.1.11.1'; // alaChasEntPhysFanTable
$data = snmp_walk($device, 'alaChasEntPhysFanTable', '-OQUn', 'ALCATEL-IND1-CHASSIS-MIB', ':mibs/nokia/aos7:mibs');

foreach (explode("\n", $data) as $entry) {
    [$oid,$value] = explode('=', $entry, 2);
    $oid = trim($oid);
    $value = trim($value, "\" \\\n\r");
    [$revindex, $revchass, $revdata,] = explode('.', strrev($oid), 4);
    if (! strstr($value, 'at this OID') && ! empty($oid)) {
        $chassis = strrev($revchass);
        $index = strrev($revindex);
        $data = strrev($revdata);
        $rpm[$chassis][$index][$data] = $value;
    }
}

foreach ($rpm as $chassis => $entry) {
    foreach ($entry as $index => $data) {
        //$descr = 'Chassis '.($chassis-450). " Fan $index";
        $descr_oid = '.1.3.6.1.2.1.47.1.1.1.1.7.' . $chassis;
        $chas_descr = (string) snmp_get($device, $descr_oid, '-Oqv');
        $descr = 'CHASSIS-' . substr($chas_descr, 0, strpos($chas_descr, '/')) . " Fan $index";
        $value = $data[4];
        $id = "$chassis.$index";
        $oid = "$rpm_oid.4.$chassis.$index";
        discover_sensor($valid['sensor'], 'fanspeed', $device, $oid, $id, 'alcatel-lucent', $descr, '1', '1', null, null, null, null, $value, 'snmp');
    }
}
