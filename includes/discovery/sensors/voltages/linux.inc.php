<?php
/*
 * voltages for raspberry pi
 * requires snmp extend agent script from librenms-agent
 */
$raspberry = snmp_get($device, 'HOST-RESOURCES-MIB::hrSystemInitialLoadParameters.0', '-Osqnv');
if (preg_match("/(bcm).+(boardrev)/", $raspberry)) {
    $sensor_type = "rasbperry_volts";
    $oid = '.1.3.6.1.4.1.8072.1.3.2.4.1.2.9.114.97.115.112.98.101.114.114.121.';
    for ($volt = 2; $volt < 6; $volt++) {
        switch ($volt) {
            case "2":
                $descr = "Core";
                break;
            case "3":
                $descr = "SDRAMc";
                break;
            case "4":
                $descr = "SDRAMi";
                break;
            case "5":
                $descr = "SDRAMp";
                break;
        }
        $value = snmp_get($device, $oid.$volt, '-Oqv');
        discover_sensor($valid['sensor'], 'voltage', $device, $oid.$volt, $volt, $sensor_type, $descr, '1', '1', null, null, null, null, $value);
    }
    /*
     * other linux os
     */
} elseif ($device['os'] == 'linux' || $device['os'] == 'pktj' || $device['os'] == 'cumulus') {
    $oids = snmp_walk($device, 'lmVoltSensorsDevice', '-OsqnU', 'LM-SENSORS-MIB');
    d_echo($oids."\n");

    if ($oids) {
        echo 'LM-SENSORS ';
    }

    $divisor = 1000;
    $type    = 'lmsensors';

    foreach (explode("\n", $oids) as $data) {
        $data = trim($data);
        if ($data) {
            list($oid,$descr) = explode(' ', $data, 2);
            $split_oid        = explode('.', $oid);
            $index            = $split_oid[(count($split_oid) - 1)];
            $oid              = '1.3.6.1.4.1.2021.13.16.4.1.3.'.$index;
            $current          = (snmp_get($device, $oid, '-Oqv', 'LM-SENSORS-MIB') / $divisor);

            discover_sensor($valid['sensor'], 'voltage', $device, $oid, $index, $type, $descr, $divisor, '1', null, null, null, null, $current);
        }
    }
}
