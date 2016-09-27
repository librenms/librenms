<?php

if ($device['os'] == 'f5') {
    $f5 = null;
    // Get the Chassis Temperature values
    //Pull the sysChassisTempTable table from the snmpwalk
    $f5 = snmpwalk_cache_oid($device, 'sysChassisTempTable', array(), 'F5-BIGIP-SYSTEM-MIB');

    if (is_array($f5)) {
      echo "sysChassisTempTable ";

      foreach (array_keys($f5) as $index) {
          $descr           = "sysChassisTempTemperature.".$f5[$index]['sysChassisTempIndex'];
          $current         = $f5[$index]['sysChassisTempTemperature'];
          $sensorType      = 'f5';
          $oid             = '.1.3.6.1.4.1.3375.2.1.3.2.3.2.1.2.'.$index;
          $low_limit       =  null;
          $low_warn_limit  =  null;
          $high_warn_limit =  null;
          $high_limit      =  null;

          discover_sensor($valid['sensor'], 'temperature', $device, $oid, $index, $sensorType, $descr, '1', '1', $low_limit, $low_warn_limit, $high_warn_limit, $high_limit, $current);
      }
    }

    // Get the CPU Temperature values
    $f5cpu = null;
//    $f5cpu = snmpwalk_cache_oid($device, 'sysCpuSensorTemperature', array(), 'F5-BIGIP-SYSTEM-MIB');
    $f5cpu = snmpwalk_cache_multi_oid($device, 'sysCpuSensorTemperature', array(), 'F5-BIGIP-SYSTEM-MIB');

    if (is_array($f5cpu)) {
      echo "sysCpuSensorTemperature ";
  
      foreach (array_keys($f5cpu) as $index) {
          $cpuname_oid     = "1.3.6.1.4.1.3375.2.1.3.6.2.1.4.$index";
          $slot_oid        = "1.3.6.1.4.1.3375.2.1.3.6.2.1.5.$index";
          $slotnum         = snmp_get($device, $slot_oid, '-Oqv', 'F5-BIGIP-SYSTEM-MIB');
          $cpuname         = snmp_get($device, $cpuname_oid, '-Oqv', 'F5-BIGIP-SYSTEM-MIB');

          $descr           = "Cpu Temperature slot".$slotnum."/".$cpuname;
          $current         = $f5cpu[$index]['sysCpuSensorTemperature'];
          $sensorType      = 'f5';
          $oid             = '1.3.6.1.4.1.3375.2.1.3.6.2.1.2.'.$index;
          $low_limit       =  null;
          $low_warn_limit  =  null;
          $high_warn_limit =  null;
          $high_limit      =  null;

          discover_sensor($valid['sensor'], 'temperature', $device, $oid, $index, $sensorType, $descr, '1', '1', $low_limit, $low_warn_limit, $high_warn_limit, $high_limit, $current);
      }
    }

}//end if
