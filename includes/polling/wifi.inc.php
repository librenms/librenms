<?php

if ($device['type'] == 'network' || $device['type'] == 'firewall' || $device['type'] == 'wireless') {
    echo "Wireless: MAG\n";

    if ($device['os'] == 'airos') {
        echo "It Is Airos\n";
        include 'includes/polling/mib/ubnt-airmax-mib.inc.php';
    }

    if ($device['os'] == 'airos-af') {
        echo "It Is AirFIBER\n";
        include 'includes/polling/mib/ubnt-airfiber-mib.inc.php';
    }

    if ($device['os'] == 'siklu') {
        echo "It is Siklu\n";
        include 'includes/polling/mib/siklu-mib.inc.php';
    }

    if ($device['os'] == 'sub10') {
        echo "It is Sub10\n";
        include 'includes/polling/mib/sub10-mib.inc.php';
    }

    // # GENERIC FRAMEWORK, FILLING VARIABLES
    if ($device['os'] == 'airport') {
        echo 'Checking Airport Wireless clients... ';

        $wificlients1 = (snmp_get($device, 'wirelessNumber.0', '-OUqnv', 'AIRPORT-BASESTATION-3-MIB') + 0);

        echo $wificlients1." clients\n";

        // FIXME Also interesting to poll? dhcpNumber.0 for number of active dhcp leases
    }

    if ($device['os'] == 'ios' and substr($device['hardware'], 0, 4) == 'AIR-') {
        echo 'Checking Aironet Wireless clients... ';

        $wificlients1 = snmp_get($device, 'cDot11ActiveWirelessClients.1', '-OUqnv', 'CISCO-DOT11-ASSOCIATION-MIB');
        $wificlients2 = snmp_get($device, 'cDot11ActiveWirelessClients.2', '-OUqnv', 'CISCO-DOT11-ASSOCIATION-MIB');

        echo (($wificlients1 + 0).' clients on dot11Radio0, '.($wificlients2 + 0)." clients on dot11Radio1\n");
    }

    // MikroTik RouterOS
    if ($device['os'] == 'routeros') {
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
    }

    if ($device['os'] == 'symbol' and (stristr($device['hardware'], 'AP'))) {
        echo 'Checking Symbol Wireless clients... ';

        $wificlients1 = snmp_get($device, '.1.3.6.1.4.1.388.11.2.4.2.100.10.1.18.1', '-Ovq', '""');

        echo (($wificlients1 + 0).' clients on wireless connector, ');
    }

    // # RRD Filling Code
    if (isset($wificlients1) && $wificlients1 != '') {
        $wificlientsrrd = $config['rrd_dir'].'/'.$device['hostname'].'/'.safename('wificlients-radio1.rrd');

        if (!is_file($wificlientsrrd)) {
            rrdtool_create(
                $wificlientsrrd,
                '--step 300 
                DS:wificlients:GAUGE:600:-273:1000 '.$config['rrd_rra']
            );
        }

        $fields = array(
            'wificlients' => $wificlients1,
        );

        rrdtool_update($wificlientsrrd, $fields);

        $tags = array('radio' => '1');
        influx_update($device,'wificlients',$tags,$fields);

        $graphs['wifi_clients'] = true;
    }

    if (isset($wificlients2) && $wificlients2 != '') {
        $wificlientsrrd = $config['rrd_dir'].'/'.$device['hostname'].'/'.safename('wificlients-radio2.rrd');

        if (!is_file($wificlientsrrd)) {
            rrdtool_create(
                $wificlientsrrd,
                '--step 300 
                DS:wificlients:GAUGE:600:-273:1000 '.$config['rrd_rra']
            );
        }

        $fields = array(
            'wificlients' => $wificlients2,
        );

        rrdtool_update($wificlientsrrd, $fields);

        $tags = array('radio' => '2');
        influx_update($device,'wificlients',$tags,$fields);


        $graphs['wifi_clients'] = true;
    }

    unset($wificlients2, $wificlients1);
}//end if

echo "\n";
