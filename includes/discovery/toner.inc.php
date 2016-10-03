<?php

$valid_toner = array();

if ($device['os_group'] == 'printer') {
    $oids = trim(snmp_walk($device, 'SNMPv2-SMI::mib-2.43.12.1.1.2.1 ', '-OsqnU'));
    if (!$oids) {
        $oids = trim(snmp_walk($device, 'SNMPv2-SMI::mib-2.43.11.1.1.2.1 ', '-OsqnU'));
    }

    d_echo($oids . "\n");

    if ($oids) {
        echo 'Jetdirect ';
    }

    foreach (explode("\n", $oids) as $data) {
        $data = trim($data);
        if ($data) {
            list($oid, $role) = explode(' ', $data);
            $split_oid = explode('.', $oid);
            $index = $split_oid[(count($split_oid) - 1)];
            if (is_numeric($role)) {
                //ricoh using private oids to expose toner levels
                if ($os == 'ricoh' || $os == 'nrg' || $os == 'lanier') {
                    $toner_oid = ".1.3.6.1.4.1.367.3.2.1.2.24.1.1.5.$index";
                    $descr_oid = ".1.3.6.1.4.1.367.3.2.1.2.24.1.1.3.$index";
                    $capacity_oid = '';
                } else {
                    $toner_oid = ".1.3.6.1.2.1.43.11.1.1.9.1.$index";
                    $descr_oid = ".1.3.6.1.2.1.43.11.1.1.6.1.$index";
                    $capacity_oid = ".1.3.6.1.2.1.43.11.1.1.8.1.$index";
                }

                $descr = trim(str_replace("\n", '', preg_replace('/[^ \w]+/', '', snmp_get($device, $descr_oid, '-Oqv'))));

                if ($descr != '') {
                    $oid_toner = snmp_get($device, $toner_oid, '-Oqv');
                    $oid_capacity = snmp_get($device, $capacity_oid, '-Oqv');

                    $capacity = get_toner_capacity($device, $oid_capacity);
                    $current = get_toner_levels($device, $oid_toner, $oid_capacity);

                    $type = 'jetdirect';
                    if (isHexString($descr)) {
                        $descr = preg_replace('/[^ \w]+/', '', snmp_hexstring($descr));
                    }

                    discover_toner($valid_toner, $device, $toner_oid, $index, $type, $descr, $capacity_oid, $capacity, $current);
                }
            }
        }
    }
}

// Delete removed toners
d_echo("\n Checking ... \n");
d_echo($valid_toner);

$sql = "SELECT * FROM toner WHERE device_id = '" . $device['device_id'] . "'";
foreach (dbFetchRows($sql) as $test_toner) {
    $toner_index = $test_toner['toner_index'];
    $toner_type = $test_toner['toner_type'];
    if (!$valid_toner[$toner_type][$toner_index]) {
        echo '-';
        dbDelete('toner', '`toner_id` = ?', array($test_toner['toner_id']));
    }
}

unset($valid_toner);
echo "\n";
