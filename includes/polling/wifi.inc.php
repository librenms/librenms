<?php

if ($device['type'] == 'network' || $device['type'] == 'firewall' || $device['type'] == 'wireless') {
    if ($device['os'] == 'airos') {
        echo 'It Is Airos' . PHP_EOL;
        include 'includes/polling/mib/ubnt-airmax-mib.inc.php';
    } elseif ($device['os'] == 'airos-af') {
        echo 'It Is AirFIBER' . PHP_EOL;
        include 'includes/polling/mib/ubnt-airfiber-mib.inc.php';
    } elseif ($device['os'] == 'ceraos') {
        echo 'It is Ceragon CeroOS' . PHP_EOL;
        include 'includes/polling/mib/ceraos-mib.inc.php';
    } elseif ($device['os'] == 'siklu') {
        echo 'It is Siklu' . PHP_EOL;
        include 'includes/polling/mib/siklu-mib.inc.php';
    } elseif ($device['os'] == 'saf') {
        echo 'It is SAF Tehnika' . PHP_EOL;
        include 'includes/polling/mib/saf-mib.inc.php';
    } elseif ($device['os'] == 'sub10') {
        echo 'It is Sub10' . PHP_EOL;
        include 'includes/polling/mib/sub10-mib.inc.php';
    } elseif ($device['os'] == 'airport') {
        // # GENERIC FRAMEWORK, FILLING VARIABLES
        echo 'Checking Airport Wireless clients... ';

        $wificlients1 = (snmp_get($device, 'wirelessNumber.0', '-OUqnv', 'AIRPORT-BASESTATION-3-MIB') + 0);

        echo $wificlients1." clients\n";
    } elseif ($device['os'] == 'ios' && (starts_with($device['hardware'], 'AIR-') || str_contains($device['hardware'], 'ciscoAIR'))) {
        echo 'Checking Aironet Wireless clients... ';

        $wificlients1 = snmp_get($device, 'cDot11ActiveWirelessClients.1', '-OUqnv', 'CISCO-DOT11-ASSOCIATION-MIB');
        $wificlients2 = snmp_get($device, 'cDot11ActiveWirelessClients.2', '-OUqnv', 'CISCO-DOT11-ASSOCIATION-MIB');

        echo (($wificlients1 + 0).' clients on dot11Radio0, '.($wificlients2 + 0)." clients on dot11Radio1\n");
    } elseif ($device['os'] == 'hpmsm') {
        echo 'Checking HP MSM Wireless clients... ';
        $wificlients1 = snmp_get($device, '.1.3.6.1.4.1.8744.5.25.1.7.2.0', '-OUqnv');
        echo $wificlients1." clients\n";
    } elseif ($device['os'] == 'routeros') {
        // MikroTik RouterOS
        // Check inventory for wireless card in device. Valid types be here:
        $wirelesscards = array(
            'Wireless',
            'Atheros',
        );
        $sql = 'SELECT COUNT(*) FROM `entPhysical` WHERE `device_id` = ? AND `entPhysicalDescr` LIKE ?';
        foreach ($wirelesscards as $wirelesscheck) {
            if (dbFetchCell($sql, array($device['device_id'], '%'.$wirelesscheck.'%')) >= 1) {
                echo 'Checking RouterOS Wireless clients... ';

                $wificlients1 = snmp_get($device, 'mtxrWlApClientCount', '-OUqnv', 'MIKROTIK-MIB');

                echo (($wificlients1 + 0)." clients\n");
                break;
            }

            unset($wirelesscards);
        }
    } elseif ($device['os'] == 'symbol' && str_contains($device['hardware'], 'AP', true)) {
        echo 'Checking Symbol Wireless clients... ';

        $wificlients1 = snmp_get($device, '.1.3.6.1.4.1.388.11.2.4.2.100.10.1.18.1', '-Ovq', '""');

        echo (($wificlients1 + 0).' clients on wireless connector, ');
    } elseif ($device['os'] == 'unifi') {
        echo 'Checking Unifi Wireless clients... ';

        $clients = snmpwalk_cache_oid($device, 'UBNT-UniFi-MIB::unifiVapRadio', array());
        $clients = snmpwalk_cache_oid($device, 'UBNT-UniFi-MIB::unifiVapNumStations', $clients);

        if (!empty($clients)) {
            $wificlients1 = 0;
            $wificlients2 = 0;
        }

        foreach ($clients as $entry) {
            if ($entry['unifiVapRadio'] == 'ng') {
                $wificlients1 += $entry['unifiVapNumStations'];
            } else {
                $wificlients2 += $entry['unifiVapNumStations'];
            }
        }

        if (!empty($clients)) {
            echo $wificlients1 . ' clients on Radio0, ' . $wificlients2 . " clients on Radio1\n";
        } else {
            echo "AP does not supply client counts\n";
        }
        include 'includes/polling/mib/ubnt-unifi-mib.inc.php';
    } elseif ($device['os'] == 'deliberant' && str_contains($device['hardware'], "DLB APC Button")) {
        echo 'Checking Deliberant APC Button wireless clients... ';
        $wificlients1 = snmp_get($device, '.1.3.6.1.4.1.32761.3.5.1.2.1.1.16.7', '-OUqnv');
        echo $wificlients1." clients\n";
    } elseif ($device['os'] == 'deliberant' && $device['hardware'] == "\"DLB APC 2Mi\"") {
        echo 'Checking Deliberant APC 2Mi wireless clients... ';
        $wificlients1 = snmp_get($device, '.1.3.6.1.4.1.32761.3.5.1.2.1.1.16.5', '-OUqnv');
        echo $wificlients1." clients\n";
    }

    // Loop through all $wificlients# and data_update()
    $i = 1;
    while (is_numeric(${'wificlients'.$i})) {
        $tags = array(
            'rrd_def'   => 'DS:wificlients:GAUGE:600:-273:1000',
            'rrd_name'  => array('wificlients', "radio$i"),
            'radio'     => $i,
        );
        data_update($device, 'wificlients', $tags, ${'wificlients'.$i});
        $graphs['wifi_clients'] = true;
        unset(${'wificlients'.$i});
        $i++;
    }
    unset($i);
} else {
    echo 'Unsupported type: ' . $device['type'] . PHP_EOL;
}
