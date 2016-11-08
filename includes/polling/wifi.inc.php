<?php

if ($device['type'] == 'network' || $device['type'] == 'firewall' || $device['type'] == 'wireless') {
    if ($device['os'] == 'airos') {
        echo 'It Is Airos' . PHP_EOL;
        include 'includes/polling/mib/ubnt-airmax-mib.inc.php';
    } elseif ($device['os'] == 'airos-af') {
        echo 'It Is AirFIBER' . PHP_EOL;
        include 'includes/polling/mib/ubnt-airfiber-mib.inc.php';
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

        // FIXME Also interesting to poll? dhcpNumber.0 for number of active dhcp leases
    } elseif ($device['os'] == 'ios' and substr($device['hardware'], 0, 4) == 'AIR-' || ($device['os'] == 'ios' && strpos($device['hardware'], 'ciscoAIR') !== false)) {
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
        foreach ($wirelesscards as $wirelesscheck) {
            if (dbFetchCell('SELECT COUNT(*) FROM `entPhysical` WHERE `device_id` = ?AND `entPhysicalDescr` LIKE ?', array($device['device_id'], '%'.$wirelesscheck.'%')) >= '1') {
                echo 'Checking RouterOS Wireless clients... ';

                $wificlients1 = snmp_get($device, 'mtxrWlApClientCount', '-OUqnv', 'MIKROTIK-MIB');

                echo (($wificlients1 + 0)." clients\n");
                break;
            }

            unset($wirelesscards);
        }
    } elseif ($device['os'] == 'symbol' and (stristr($device['hardware'], 'AP'))) {
        echo 'Checking Symbol Wireless clients... ';

        $wificlients1 = snmp_get($device, '.1.3.6.1.4.1.388.11.2.4.2.100.10.1.18.1', '-Ovq', '""');

        echo (($wificlients1 + 0).' clients on wireless connector, ');
    } elseif ($device['os'] == 'unifi') {
        echo 'Checking Unifi Wireless clients... ';

        $clients = snmp_walk($device, 'unifiVapNumStations', '-Oqv', 'UBNT-UniFi-MIB');
        $bands = snmp_walk($device, 'unifiVapRadio', '-Oqv', 'UBNT-UniFi-MIB');
        $clients = explode("\n", $clients);
        $bands = explode("\n", $bands);
        foreach ($bands as $index => $band_index) {
            if ($band_index == "ng") {
                $wificlients1 = $wificlients1 + $clients[$index] + 0;
            } else {
                $wificlients2 = $wificlients2 + $clients[$index] + 0;
            }
        }

        echo (($wificlients1 + 0).' clients on Radio0, '.($wificlients2 + 0)." clients on Radio1\n");
        include 'includes/polling/mib/ubnt-unifi-mib.inc.php';
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
}//end if

echo "\n";
