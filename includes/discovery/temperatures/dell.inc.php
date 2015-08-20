<?php

/*
 * Observium
 *
 *   This file is part of Observium.
 *
 * @package    observium
 * @subpackage discovery
 * @author     Adam Armstrong <adama@memetic.org>
 * @copyright  (C) 2006 - 2012 Adam Armstrong
 */

// MIB-Dell-10892::temperatureProbechassisIndex.1.1 = INTEGER: 1
// MIB-Dell-10892::temperatureProbeIndex.1.1 = INTEGER: 1
// MIB-Dell-10892::temperatureProbeStateCapabilities.1.1 = INTEGER: 0
// MIB-Dell-10892::temperatureProbeStateSettings.1.1 = INTEGER: enabled(2)
// MIB-Dell-10892::temperatureProbeStatus.1.1 = INTEGER: ok(3)
// MIB-Dell-10892::temperatureProbeReading.1.1 = INTEGER: 320
// MIB-Dell-10892::temperatureProbeType.1.1 = INTEGER: temperatureProbeTypeIsAmbientESM(3)
// MIB-Dell-10892::temperatureProbeLocationName.1.1 = STRING: "BMC Planar Temp"
// MIB-Dell-10892::temperatureProbeUpperCriticalThreshold.1.1 = INTEGER: 530
// MIB-Dell-10892::temperatureProbeUpperNonCriticalThreshold.1.1 = INTEGER: 480
// MIB-Dell-10892::temperatureProbeLowerNonCriticalThreshold.1.1 = INTEGER: 70
// MIB-Dell-10892::temperatureProbeLowerCriticalThreshold.1.1 = INTEGER: 30
// MIB-Dell-10892::temperatureProbeProbeCapabilities.1.1 = INTEGER: 0
if (strstr($device['hardware'], 'Dell')) {
    // stuff partially copied from akcp sensor
    $oids = snmp_walk($device, 'temperatureProbeStatus', '-Osqn', 'MIB-Dell-10892');
    d_echo($oids."\n");

    $oids = trim($oids);
    if ($oids) {
        echo 'Dell OMSA ';
    }

    foreach (explode("\n", $oids) as $data) {
    $data = trim($data);
        if ($data) {
            list($oid,$status) = explode(' ', $data, 2);
            d_echo('status : '.$status."\n");

            if ($status == 'ok') {
                // 2 = normal, 0 = not connected
                $split_oid            = explode('.', $oid);
                $temperature_id   = $split_oid[(count($split_oid) - 2)].'.'.$split_oid[(count($split_oid) - 1)];
                $descr_oid        = ".1.3.6.1.4.1.674.10892.1.700.20.1.8.$temperature_id";
                $temperature_oid  = ".1.3.6.1.4.1.674.10892.1.700.20.1.6.$temperature_id";
                $limit_oid        = ".1.3.6.1.4.1.674.10892.1.700.20.1.10.$temperature_id";
                $warnlimit_oid    = ".1.3.6.1.4.1.674.10892.1.700.20.1.11.$temperature_id";
                $lowwarnlimit_oid = ".1.3.6.1.4.1.674.10892.1.700.20.1.12.$temperature_id";
                $lowlimit_oid     = ".1.3.6.1.4.1.674.10892.1.700.20.1.13.$temperature_id";

                $descr        = trim(snmp_get($device, $descr_oid, '-Oqv', 'MIB-Dell-10892'), '"');
                $temperature  = snmp_get($device, $temperature_oid, '-Oqv', 'MIB-Dell-10892');
                $lowwarnlimit = snmp_get($device, $lowwarnlimit_oid, '-Oqv', 'MIB-Dell-10892');
                $warnlimit    = snmp_get($device, $warnlimit_oid, '-Oqv', 'MIB-Dell-10892');
                $limit        = snmp_get($device, $limit_oid, '-Oqv', 'MIB-Dell-10892');
                $lowlimit     = snmp_get($device, $lowlimit_oid, '-Oqv', 'MIB-Dell-10892');

                discover_sensor($valid['sensor'], 'temperature', $device, $temperature_oid, $temperature_id, 'dell', $descr, '10', '1', ($lowlimit / 10), ($low_warn_limit / 10), ($warnlimit / 10), ($limit / 10), ($temperature / 10));
            }
        }//end if
    }//end foreach
}//end if
