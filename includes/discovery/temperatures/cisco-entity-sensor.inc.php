<?php

global $valid_sensor;

if ($device['os'] == "ios" || $device['os_group'] == "ios") 
{
  echo("CISCO-ENTITY-SENSOR");

  $oids = array();  

  $oids = snmpwalk_cache_multi_oid($device, "entSensorType", $oids, "CISCO-ENTITY-SENSOR-MIB");
  $oids = snmpwalk_cache_multi_oid($device, "entSensorScale", $oids, "CISCO-ENTITY-SENSOR-MIB");
  $oids = snmpwalk_cache_multi_oid($device, "entSensorValue", $oids, "CISCO-ENTITY-SENSOR-MIB");
  $oids = snmpwalk_cache_multi_oid($device, "entSensorMeasuredEntity", $oids, "CISCO-ENTITY-SENSOR-MIB");

  if(is_array($oids[$device['device_id']]))
  {
    foreach($oids[$device['device_id']] as $index => $entry)
    {
      #echo("[" . $entry['entSensorType'] . "|" . $entry['entSensorValue']. "|" . $index . "]");

      if($entry['entSensorType'] == "celsius" && is_numeric($entry['entSensorValue']) && is_numeric($index) && $entry['entSensorValue'] > "0" && $entry['entSensorValue'] < "1000")
      {
        $entPhysicalIndex = $index;
	$entPhysicalIndex_measured = $entry['entSensorMeasuredEntity']; 
	$descr = snmp_get($device, "entPhysicalDescr.".$index, "-Oqv", "ENTITY-MIB");
        $oid = ".1.3.6.1.4.1.9.9.91.1.1.1.1.4.".$index;
        $current = $entry['entSensorValue'];

        ## FIXME this sucks
        if($entry['entSensorScale'] == "milli") { $divisor = "1000"; } else { $divisor = "1"; }        

        discover_sensor($valid_sensor, 'temperature', $device, $oid, $index, 'cisco-entity-sensor', $descr, '1', '1', NULL, NULL, NULL, NULL, $temperature);

        $cisco_entity_temperature = 1;
      }
    }
  }
}


?>
