<?php

// APC Voltages
if ($device['os'] == 'apc') {
    $three_phase = snmp_get($device, 'upsBasicOutputPhase.0', '-OsqvU', 'PowerNet-MIB');
    if ($three_phase == 3) {
        $phase_divisor = 1;
        for ($ph_vlin = 1; $ph_vlin <= 3;) {
            $phinvoltage = snmp_get($device, 'upsPhaseOutputVoltage.1.1.'.$ph_vlin, '-OsqvU', 'PowerNet-MIB');
            $phvlin_oid = '.1.3.6.1.4.1.318.1.1.1.9.2.3.1.3.1.1';
            discover_sensor($valid['sensor'], 'voltage', $device, $phvlin_oid.'.'.$ph_vlin, $phvlin_oid.'.'.$ph_vlin, apc, 'Phase '. $ph_vlin .' Input Voltage', $phase_divisor, 1, null, null, null, null, $phinvoltage);
            $ph_vlin++;
        }
        for ($ph_vlout = 1; $ph_vlout <= 3;) {
            $phoutvoltage = snmp_get($device, 'upsPhaseInputVoltage.1.1.'.$ph_vlout, '-OsqvU', 'PowerNet-MIB');
            $phvlout_oid = '.1.3.6.1.4.1.318.1.1.1.9.3.3.1.3.1.1';
            discover_sensor($valid['sensor'], 'voltage', $device, $phvlout_oid.'.'.$ph_vlout, $phvlout_oid.'.'.$ph_vlout, apc, 'Phase '. $ph_vlout .' Output Voltage', $phase_divisor, 1, null, null, null, null, $phoutvoltage);
            $ph_vlout++;
        }
    } else {
        $oids = snmp_walk($device, '.1.3.6.1.4.1.318.1.1.8.5.3.3.1.3', '-OsqnU');
        d_echo($oids."\n");

        if ($oids) {
            echo 'APC In ';
        }

        $divisor = 1;
        $type    = 'apc';
        foreach (explode("\n", $oids) as $data) {
            $data = trim($data);
            if ($data) {
                list($oid,$current) = explode(' ', $data, 2);
                $split_oid          = explode('.', $oid);
                $index              = $split_oid[(count($split_oid) - 3)];
                $oid                = '.1.3.6.1.4.1.318.1.1.8.5.3.3.1.3.'.$index.'.1.1';
                $descr              = 'Input Feed '.chr(64 + $index);

                discover_sensor($valid['sensor'], 'voltage', $device, $oid, "3.3.1.3.$index", $type, $descr, $divisor, '1', null, null, null, null, $current);
            }
        }

        $oids = snmp_walk($device, '.1.3.6.1.4.1.318.1.1.8.5.4.3.1.3', '-OsqnU');
        d_echo($oids."\n");

        if ($oids) {
            echo ' APC Out ';
        }

        $divisor = 1;
        $type    = 'apc';
        foreach (explode("\n", $oids) as $data) {
            $data = trim($data);
            if ($data) {
                list($oid,$current) = explode(' ', $data, 2);
                $split_oid          = explode('.', $oid);
                $index              = $split_oid[(count($split_oid) - 3)];
                $oid                = '.1.3.6.1.4.1.318.1.1.8.5.4.3.1.3.'.$index.'.1.1';
                $descr              = 'Output Feed';
                if (count(explode("\n", $oids)) > 1) {
                    $descr .= " $index";
                }

                discover_sensor($valid['sensor'], 'voltage', $device, $oid, "4.3.1.3.$index", $type, $descr, $divisor, '1', null, null, null, null, $current);
            }
        }

        $oids = snmp_get($device, '.1.3.6.1.4.1.318.1.1.1.3.2.1.0', '-OsqnU');
        d_echo($oids."\n");

        if ($oids) {
            echo ' APC In ';
            list($oid,$current) = explode(' ', $oids);
            $divisor            = 1;
            $type               = 'apc';
            $index              = '3.2.1.0';
            $descr              = 'Input';

            discover_sensor($valid['sensor'], 'voltage', $device, $oid, $index, $type, $descr, $divisor, '1', null, null, null, null, $current);
        }

        $oids = snmp_get($device, '.1.3.6.1.4.1.318.1.1.1.4.2.1.0', '-OsqnU');
        d_echo($oids."\n");

        if ($oids) {
            echo ' APC Out ';
            list($oid,$current) = explode(' ', $oids);
            $divisor            = 1;
            $type               = 'apc';
            $index              = '4.2.1.0';
            $descr              = 'Output';

            discover_sensor($valid['sensor'], 'voltage', $device, $oid, $index, $type, $descr, $divisor, '1', null, null, null, null, $current);
        }

        // rPDUIdentDeviceLinetoLineVoltage
        $oids = snmp_get($device, ".1.3.6.1.4.1.318.1.1.12.1.15.0", "-OsqnU");
        d_echo($oids."\n");

        if ($oids) {
            echo ' Voltage In ';
            list($oid,$current) = explode(' ', $oids);
            if ($current >= 0) { // Newer units using rPDU2 can return the following rPDUIdentDeviceLinetoLineVoltage.0; Value (Integer): -1 hence this check.
                $divisor            = 1;
                $type               = 'apc';
                $index              = '1';
                $descr              = 'Input';

                discover_sensor($valid['sensor'], 'voltage', $device, $oid, $index, $type, $descr, $divisor, '1', null, null, null, null, $current);
            }
        }

        // rPDU2PhaseStatusVoltage
        $oids = snmp_walk($device, '.1.3.6.1.4.1.318.1.1.26.6.3.1.6', '-OsqnU');
        d_echo($oids."\n");

        if ($oids) {
            echo ' Voltage In ';
            list($oid,$current) = explode(' ', $oids);
            $divisor            = 1;
            $type               = 'apc';
            $index              = '1';
            $descr              = 'Input';

            discover_sensor($valid['sensor'], 'voltage', $device, $oid, $index, $type, $descr, $divisor, '1', null, null, null, null, $current);
        }
    }
}//end if
