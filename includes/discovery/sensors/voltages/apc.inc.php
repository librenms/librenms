<?php

if ($device['os'] == 'apc') {
    echo 'APC ';

    $phasecount = snmp_get($device, 'upsBasicOutputPhase.0', '-OsqvU', 'PowerNet-MIB');
    // Check for three phase UPS devices - else skip to normal discovery
    if ($phasecount > 1) {
        $oids = snmpwalk_cache_oid($device, 'upsPhaseOutputVoltage', $oids, 'PowerNet-MIB');
        $in_oids = snmpwalk_cache_oid($device, 'upsPhaseInputVoltage', $in_oids, 'PowerNet-MIB');

        foreach ($oids as $index => $data) {
             $type = 'apcUPS';
             $descr = 'Phase ' . substr($index, -1) . ' Output';
             $voltage_oid = '.1.3.6.1.4.1.318.1.1.1.9.3.3.1.3.' . $index;
             $divisor = 1;
             $voltage = $data['upsPhaseOutputVoltage'] / $divisor;

              discover_sensor($valid['sensor'], 'voltage', $device, $voltage_oid, $index, $type, $descr, $divisor, 1, null, null, null, null, $voltage);
        }
        unset($index);
        unset($data);
        foreach ($in_oids as $index => $data) {
            $type = 'apcUPS';
            $voltage_oid = '.1.3.6.1.4.1.318.1.1.1.9.2.3.1.3.' . $index;
            $divisor = 1;
            $voltage = $data['upsPhaseInputVoltage'] / $divisor;
            $in_index = '3.1.3.' . $index;

            if (substr($index, 0, 1) == 2 && $data['upsPhaseInputVoltage'] != -1) {
                 $descr = 'Phase ' . substr($index, -1) . ' Bypass Input';
                 discover_sensor($valid['sensor'], 'voltage', $device, $voltage_oid, $in_index, $type, $descr, $divisor, 0, null, null, null, null, $voltage);
            } elseif (substr($index, 0, 1) == 1) {
                $descr = 'Phase ' . substr($index, -1) . ' Input';
                discover_sensor($valid['sensor'], 'voltage', $device, $voltage_oid, $in_index, $type, $descr, $divisor, 0, null, null, null, null, $voltage);
            }
        }
    } else {
        $oids = snmpwalk_cache_oid($device, 'atsInputVoltage', array(), 'PowerNet-MIB');
        foreach ($oids as $index => $data) {
            $type = 'apcInFeed';
            $divisor = 1;
            $oid = '.1.3.6.1.4.1.318.1.1.8.5.3.3.1.3.' . $index;
            $current = $data['atsInputVoltage'];
            $descr = 'Input Feed';
            if (count($oids) > 1) {
                $descr .= ' ' . chr(64 + $index);
            }
            discover_sensor($valid['sensor'], 'voltage', $device, $oid, $index, $type, $descr, $divisor, 1, null, null, null, null, $current);
        }
          $oids = snmpwalk_cache_oid($device, 'atsOutputVoltage', array(), 'PowerNet-MIB');
        foreach ($oids as $index => $data) {
             $type = 'apcOutFeed';
             $divisor = 1;
             $oid = '.1.3.6.1.4.1.318.1.1.8.5.4.3.1.3.' . $index;
             $current = $data['atsOutputVoltage'];
             $descr = 'Output Feed';
            if (count($oids) > 1) {
                $descr .= ' ' . chr(64 + $index);
            }
                discover_sensor($valid['sensor'], 'voltage', $device, $oid, $index, $type, $descr, $divisor, 1, null, null, null, null, $current);
        }
          $oids = snmpwalk_cache_oid($device, 'upsAdvInputLineVoltage', array(), 'PowerNet-MIB');
        foreach ($oids as $index => $data) {
             $type = 'apcIn';
             $divisor = 1;
             $oid = '.1.3.6.1.4.1.318.1.1.1.3.2.1.' . $index;
             $current = $data['upsAdvInputLineVoltage'];
             $descr = 'Input';
             discover_sensor($valid['sensor'], 'voltage', $device, $oid, $index, $type, $descr, $divisor, 1, null, null, null, null, $current);
        }
          $oids = snmpwalk_cache_oid($device, 'upsAdvOutputVoltage', array(), 'PowerNet-MIB');
        foreach ($oids as $index => $data) {
             $type = 'apcOut';
             $divisor = 1;
             $oid = '.1.3.6.1.4.1.318.1.1.1.4.2.1.' . $index;
             $current = $data['upsAdvOutputVoltage'];
             $descr = 'Output';
             discover_sensor($valid['sensor'], 'voltage', $device, $oid, $index, $type, $descr, $divisor, 1, null, null, null, null, $current);
        }
          // PDU
          $oids = snmpwalk_cache_oid($device, 'rPDUIdentDeviceLinetoLineVoltage', array(), 'PowerNet-MIB');
        foreach ($oids as $index => $data) {
             $type = 'apcPduIn';
             $divisor = 1;
             $oid = '.1.3.6.1.4.1.318.1.1.12.1.15.' . $index;
             $current = $data['rPDUIdentDeviceLinetoLineVoltage'];
             $descr = 'Input';
             discover_sensor($valid['sensor'], 'voltage', $device, $oid, $index, $type, $descr, $divisor, 1, null, null, null, null, $current);
        }
          $oids = snmpwalk_cache_oid($device, 'rPDU2PhaseStatusVoltage', array(), 'PowerNet-MIB');
        foreach ($oids as $index => $data) {
             $type = 'apcPduOut';
             $divisor = 1;
             $oid = '.1.3.6.1.4.1.318.1.1.26.6.3.1.6.' . $index;
             $current = $data['rPDU2PhaseStatusVoltage'];
             $descr = 'Output';
            if (count($oids) > 1) {
                $descr .= ' Phase ' . $index;
            }
                discover_sensor($valid['sensor'], 'voltage', $device, $oid, $index, $type, $descr, $divisor, 1, null, null, null, null, $current);
        }
    }
}//end if
