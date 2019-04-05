<?php

$valid_toner = array();

if ($device['os_group'] == 'printer') {
    echo 'Toner: ';

    $oids = snmpwalk_cache_oid($device, 'prtMarkerSuppliesLevel', [], 'Printer-MIB');
    if (!empty($oids)) {
        $oids = snmpwalk_cache_oid($device, 'prtMarkerSuppliesType', $oids, 'Printer-MIB');
        $oids = snmpwalk_cache_oid($device, 'prtMarkerSuppliesMaxCapacity', $oids, 'Printer-MIB');
        $oids = snmpwalk_cache_oid($device, 'prtMarkerSuppliesDescription', $oids, 'Printer-MIB', null, '-OQUsa');
    }

    foreach ($oids as $index => $data) {
        $last_index = substr($index, strrpos($index, '.') + 1);

        $raw_toner     = $data['prtMarkerSuppliesLevel'];
        $descr         = $data['prtMarkerSuppliesDescription'];
        $raw_capacity  = $data['prtMarkerSuppliesMaxCapacity'];
        $raw_toner     = $data['prtMarkerSuppliesLevel'];
        $toner_oid     = ".1.3.6.1.2.1.43.11.1.1.9.$index";
        $capacity_oid  = ".1.3.6.1.2.1.43.11.1.1.8.$index";

        if (empty($raw_toner)) {
            $toner_oid = ".1.3.6.1.4.1.367.3.2.1.2.24.1.1.5.$last_index";
            $raw_toner = snmp_get($device, $toner_oid, '-Oqv');
        }

        if (empty($raw_toner)) {
            $raw_toner = snmp_get($device, $toner_oid, '-Oqv');
        }

        if (empty($descr)) {
            $descr_oid = ".1.3.6.1.4.1.367.3.2.1.2.24.1.1.3.$last_index";
            $descr = snmp_get($device, $descr_oid, '-Oqva');
        }

        // trim part & serial number from devices that include it
        if (str_contains($descr, ', PN')) {
            $descr = explode(', PN', $descr)[0];
        }

        $capacity = get_toner_capacity($data['prtMarkerSuppliesMaxCapacity']);
        $current = get_toner_levels($device, $raw_toner, $capacity);

        if (is_numeric($current)) {
            discover_toner(
                $valid_toner,
                $device,
                $toner_oid,
                $last_index,
                $data['prtMarkerSuppliesType'] ?: 'markerSupply',
                $descr,
                $capacity_oid,
                $capacity,
                $current
            );
        }
    }
    echo PHP_EOL;

    echo 'Tray Paper Level: ';
    $tray_oids = snmpwalk_cache_oid($device, 'prtInputName', [], 'Printer-MIB');
    if (!empty($tray_oids)) {
        $tray_oids = snmpwalk_cache_oid($device, 'prtInputCurrentLevel', $tray_oids, 'Printer-MIB');
        $tray_oids = snmpwalk_cache_oid($device, 'prtInputMaxCapacity', $tray_oids, 'Printer-MIB');
    }

    d_echo($tray_oids);
    foreach ($tray_oids as $index => $data) {
        $last_index = substr($index, strrpos($index, '.') + 1);

        $capacity = $data['prtInputMaxCapacity'];
        $current = $data['prtInputCurrentLevel'];
        if (!is_numeric($current) || $current == -2) {
            // capacity unsupported
            d_echo('Input Capacity unsupported', 'X');
            continue;
        } elseif ($current == -3) {
            // at least one piece of paper in tray
            $current = 50;
        } else {
            $current = $current / $capacity * 100;
        }

        discover_toner(
            $valid_toner,
            $device,
            ".1.3.6.1.2.1.43.8.2.1.10.$index",
            $last_index,
            'input',
            $data['prtInputName'],
            ".1.3.6.1.2.1.43.8.2.1.9.$index",
            $capacity,
            $current
        );
    }
}

// Delete removed toners
d_echo("\n Checking valid toner ... \n");
d_echo($valid_toner);

$toners = dbFetchRows("SELECT * FROM toner WHERE device_id = ?", [$device['device_id']]);
//d_echo($toners);
foreach ($toners as $test_toner) {
    $toner_oid = $test_toner['toner_oid'];
    $toner_type = $test_toner['toner_type'];
    if (!$valid_toner[$toner_type][$toner_oid]) {
        echo '-';
        dbDelete('toner', '`toner_id` = ?', array($test_toner['toner_id']));
    }
}

unset($valid_toner);
echo PHP_EOL;
