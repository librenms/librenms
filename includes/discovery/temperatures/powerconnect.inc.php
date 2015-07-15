<?php

if ($device['os'] == 'powerconnect') {
    $sysObjectId = snmp_get($device, 'SNMPv2-MIB::sysObjectID.0', '-Ovqn');
    switch ($sysObjectId) {
    case '.1.3.6.1.4.1.674.10895.3031':
        /*
         * Devices supported:
         * Dell Powerconnect 55xx
         * Operating Temperature: 0º C to 40º C
         */
        $temperature = trim(snmp_get($device, '.1.3.6.1.4.1.89.53.15.1.9.1', '-Ovq'));
        discover_sensor($valid['sensor'], 'temperature', $device, '.1.3.6.1.4.1.89.53.15.1.9.1', 0, 'powerconnect', 'Internal Temperature', '1', '1', '0', null, null, '40', $temperature);
        break;

    default:
        /*
         * Defaul Discovery for powerconnect series
         *  Dell-Vendor-MIB::dellLanExtension.6132.1.1.1.1.4.4.0 = STRING: "5 Sec (6.99%),    1 Min (6.72%),   5 Min (9.06%)"
         */
        $temps = snmp_walk($device, 'boxServicesTempSensorTemperature', '-OsqnU', 'FASTPATH-BOXSERVICES-PRIVATE-MIB');
        if ($debug) {
            echo $temps."\n";
        }

        $index = 0;
        foreach (explode("\n", $temps) as $oids) {
            echo 'Powerconnect ';
            list($oid,$current) = explode(' ', $oids);
            $divisor            = '1';
            $multiplier         = '1';
            $type               = 'powerconnect';
            $index++;
            $descr = 'Internal Temperature';
            if (count(explode("\n", $temps)) > 1) {
                $descr .= " $index";
            }

            discover_sensor($valid['sensor'], 'temperature', $device, $oid, $index, $type, $descr, $divisor, $multiplier, null, null, null, null, $current);
        }
    }//end switch
}//end if
