<?php
<<<<<<< HEAD
echo 'CERAGON-NET-MIB-FIBEAIR-4800 ';
$divisor = 1;
$type    = 'ceragon';
$oids = snmp_walk($device, 'fibeAir4800OduAirRxPower', '-OsqnU', 'CERAGON-NET-MIB-FIBEAIR-4800');
=======

echo 'CERAGON-NET-MIB-FIBEAIR-4800 ';

$divisor = 1;
$type    = 'ceragon';

$oids = snmp_walk($device, 'fibeAir4800OduAirRxPower', '-OsqnU', 'CERAGON-NET-MIB-FIBEAIR-4800');

>>>>>>> 400d8ca205c5d285dc183a2e845597638ffc5fd1
foreach (explode("\n", $oids) as $data) {
    $data = trim($data);
    if ($data) {
        list($oid,$value) = explode(' ', $data, 2);
        $split_oid        = explode('.', $oid);
        $index            = $split_oid[(count($split_oid) - 1)];
        $descr            = trim(snmp_get($device, '..3.6.1.4.1.4458.1000.1.5.9.1.'.$index, '-Oqv'), '"') . ' Signal';
<<<<<<< HEAD
=======

>>>>>>> 400d8ca205c5d285dc183a2e845597638ffc5fd1
        discover_sensor($valid['sensor'], 'signal', $device, $oid, $index, $type, $descr, $divisor, '1', null, null, null, null, $value);
    }
}
