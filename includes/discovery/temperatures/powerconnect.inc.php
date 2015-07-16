<?php
if ($device['os'] == 'powerconnect') {
    $sysObjectId = snmp_get($device, 'SNMPv2-MIB::sysObjectID.0', '-Ovqn');
    switch ($sysObjectId) {
        /**
        * Dell Powerconnect 5548
        * Operating Temperature: 0º C to 45º C
        */
        case '.1.3.6.1.4.1.674.10895.3031':
        $temperature = trim(snmp_get($device, '.1.3.6.1.4.1.89.53.15.1.9.1', '-Ovq'));
        discover_sensor($valid['sensor'], 'temperature', $device, '.1.3.6.1.4.1.89.53.15.1.9.1', 0, 'powerconnect', 'Internal Temperature', '1', '1', '0', null, null, '45', $temperature);
        break;
        /**
        * Dell Powerconnect 3548
        * Operating Temperature: 0º C to 45º C
        */
        case '.1.3.6.1.4.1.674.10895.3017':
        $temperature = trim(snmp_get($device, ".1.3.6.1.4.1.89.53.15.1.9.1", "-Ovq"));
        discover_sensor($valid['sensor'], 'temperature', $device, '.1.3.6.1.4.1.89.53.15.1.9.1', 0, 'powerconnect', 'Internal Temperature', '1', '1', '0', null, null, '45', $temperature);
        break;
        default :
        /**
        * Default Temperature Discovery
        * Operating Temperature: 0º C to 45º C
        */
        $temperature = snmp_get($device, 'boxServicesTempSensorTemperature.0', '-Ovq','FASTPATH-BOXSERVICES-PRIVATE-MIB');
        if (is_numeric($temperature)) {
            discover_sensor($valid['sensor'], 'temperature', $device, '.1.3.6.1.4.1.674.10895.5000.2.6132.1.1.43.1.8.1.4.0', 0, 'powerconnect', 'Internal Temperature', '1', '1', '0', null, null, '45', $temperature);
        }
    }//end switch
}//end if
