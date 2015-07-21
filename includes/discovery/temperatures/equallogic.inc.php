<?php

if ($device['os'] == 'equallogic') {
    $oids = snmp_walk($device, 'eqlMemberHealthDetailsTemperatureEntry', '-OQ', 'EQLMEMBER-MIB', $config['install_dir'].'/mibs/equallogic');

    d_echo($oids."\n");
    if (!empty($oids)) {
        echo 'EQLCONTROLLER-MIB ';
        $temps = [];
        foreach (explode("\n", $oids) as $data) {
            $data = trim($data);
            if (!empty($data)) {
                list($oid,$val) = explode(' = ', $data, 2);
                $oid = explode('::', $oid, 2)[1];
                $oid = explode('.', $oid);
                $key = $oid[0];
                $part = $oid[-1];

                if not array_key_exists($part, $temps) {
                    $temps[$part] = [];
                } //end if

                $temps[$part][$key] = $val;
            }//end if
        }//end foreach

        foreach ($temps as $part => $vals) {
            discover_sensor($valid['sensor'], 'temperature', $device, $vals['eqlMemberHealthDetailsTemperatureName']
        }// end foreach
    }//end if
}//end if

                $temperature      = $extra[$keys[0]]['eqlMemberHealthDetailsTemperatureValue'];
                $low_limit        = $extra[$keys[0]]['eqlMemberHealthDetailsTemperatureLowCriticalThreshold'];
                $low_warn         = $extra[$keys[0]]['eqlMemberHealthDetailsTemperatureLowWarningThreshold'];
                $high_limit       = $extra[$keys[0]]['eqlMemberHealthDetailsTemperatureHighCriticalThreshold'];
                $high_warn        = $extra[$keys[0]]['eqlMemberHealthDetailsTemperatureHighWarningThreshold'];

                if ($extra[$keys[0]]['eqlMemberHealthDetailsTemperatureCurrentState'] != 'unknown') {
                    discover_sensor($valid['sensor'], 'temperature', $device, $oid, $index, 'snmp', $descr, 1, 1, $low_limit, $low_warn, $high_limit, $high_warn, $temperature);

EqlMemberHealthDetailsTemperatureEntry
