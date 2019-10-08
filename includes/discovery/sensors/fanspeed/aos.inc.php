<?php
echo "Checking Fan Speed...\n";
    $rpm=[];
    $rpm_oid = '.1.3.6.1.4.1.6486.801.1.1.1.3.1.1.11.1'; // alaChasEntPhysFanTable
    $data = snmp_walk($device, 'alaChasEntPhysFanTable', "-OQUn", 'ALCATEL-IND1-CHASSIS-MIB', ':mibs/nokia/aos7:mibs');
foreach (explode("\n", $data) as $entry) {
    list($oid,$value)  = explode('=', $entry, 2);
    $oid               = trim($oid);
    $value             = trim($value, "\" \\\n\r");
    list($revindex, $revchass, $revdata,)  = explode('.', strrev($oid), 4);
    if (!strstr($value, 'at this OID') && !empty($oid)) {
        $chassis=strrev($revchass);
        $index=strrev($revindex);
        $data=strrev($revdata);
        $rpm[$chassis][$index][$data] = $value;
    }
}
if (is_array($rpm)) {
    foreach ($rpm as $chassis => $entry) {
        foreach ($entry as $index => $data) {
            $descr = 'Chassis '.($chassis-450). " Fan $index";
            $value = $data[4];
            $id= "$chassis.$index";
            $oid= "$rpm_oid.4.$chassis.$index";
            echo "$descr: $value\n";
            discover_sensor($valid['sensor'], 'fanspeed', $device, $oid, $id, 'alcatel-lucent', $descr, '1', '1', null, null, null, null, $value, 'snmp');
        }
    }
}
