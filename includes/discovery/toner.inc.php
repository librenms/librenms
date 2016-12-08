<?php

$valid_toner = array();

if ($device['os_group'] == 'printer') {
    echo 'Toner: ';
    $oids = snmpwalk_cache_oid($device, 'prtMarkerColorantMarkerIndex', array(), 'Printer-MIB');
    if (empty($oids)) {
        $oids = snmpwalk_cache_oid($device, 'prtMarkerSuppliesMarkerIndex', $oids, 'Printer-MIB');
    }

    if (!empty($oids)) {
        $oids = snmpwalk_cache_oid($device, 'prtMarkerSuppliesLevel', $oids, 'Printer-MIB');
        $oids = snmpwalk_cache_oid($device, 'prtMarkerSuppliesMaxCapacity', $oids, 'Printer-MIB');
        $oids = snmpwalk_cache_oid($device, 'prtMarkerSuppliesDescription', $oids, 'Printer-MIB', null, '-OQUsa');
    }

    foreach ($oids as $index => $data) {
        $last_index = substr($index, strrpos($index, '.') + 1);
        if ($os == 'ricoh' || $os == 'nrg' || $os == 'lanier') {
            $toner_oid = ".1.3.6.1.4.1.367.3.2.1.2.24.1.1.5.$last_index";
            $descr_oid = ".1.3.6.1.4.1.367.3.2.1.2.24.1.1.3.$last_index";
            $capacity_oid = '';

            $descr = snmp_get($device, $descr_oid, '-Oqva');
            $raw_toner = snmp_get($device, $toner_oid, '-Oqv');
        } else {
            $toner_oid = ".1.3.6.1.2.1.43.11.1.1.9.$index";
            $capacity_oid = ".1.3.6.1.2.1.43.11.1.1.8.$index";

            $descr = $data['prtMarkerSuppliesDescription'];
            $raw_toner = $data['prtMarkerSuppliesLevel'];
        }

        $type = 'jetdirect';
        $capacity = get_toner_capacity($data['prtMarkerSuppliesMaxCapacity']);
        $current = get_toner_levels($device, $raw_toner, $capacity);

        discover_toner(
            $valid_toner,
            $device,
            $toner_oid,
            $last_index,
            $type,
            $descr,
            $capacity_oid,
            $capacity,
            $current
        );
    }
}

// Delete removed toners
d_echo("\n Checking valid toner ... \n");
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
echo PHP_EOL;
