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

  if($debug) { print_r($oids); } 

  $entitysensor['voltsDC']   = "voltage";
  $entitysensor['voltsAC']   = "voltage";
  $entitysensor['amperes']   = "current";
  $entitysensor['watt']      = "power";
  $entitysensor['hertz']     = "freq";
  $entitysensor['percentRH'] = "humidity";
  $entitysensor['rpm']       = "fanspeed";
  $entitysensor['celsius']   = "temperature";

  if(is_array($oids[$device['device_id']]))
  {
    foreach($oids[$device['device_id']] as $index => $entry)
    {
      #echo("[" . $entry['entSensorType'] . "|" . $entry['entSensorValue']. "|" . $index . "]");

      if($entitysensor[$entry['entSensorType']] && is_numeric($entry['entSensorValue']) && is_numeric($index))
      {
        $entPhysicalIndex = $index;
	$entPhysicalIndex_measured = $entry['entSensorMeasuredEntity']; 
	$descr = snmp_get($device, "entPhysicalDescr.".$index, "-Oqv", "ENTITY-MIB");
        $oid = ".1.3.6.1.4.1.9.9.91.1.1.1.1.4.".$index;
        $current = $entry['entSensorValue'];

        $type = $entitysensor[$entry['entSensorType']];

        #echo("$index : ".$entry['entSensorScale']."|");
        ### FIXME this stuff is foul
        if($entry['entSensorScale'] == "nano")  { $divisor = "1000000000"; $multiplier = "1";  }
        if($entry['entSensorScale'] == "micro") { $divisor = "1000000"; $multiplier = "1";  }
        if($entry['entSensorScale'] == "milli") { $divisor = "1000"; $multiplier = "1";  }
        if($entry['entSensorScale'] == "units") { $divisor = "1"; $multiplier = "1";  }
        if($entry['entSensorScale'] == "kilo")  { $divisor = "1"; $multiplier = "1000";  }
        if($entry['entSensorScale'] == "mega")  { $divisor = "1"; $multiplier = "1000000";  }
        if($entry['entSensorScale'] == "giga")  { $divisor = "1"; $multiplier = "1000000000";  }

	$current = $current * $multiplier / $divisor;

        discover_sensor($valid_sensor, $type, $device, $oid, $index, 'cisco-entity-sensor', $descr, $divisor, $multiplier, NULL, NULL, NULL, NULL, $temperature);

        $cisco_entity_temperature = 1;
      }
    }
  }
}


?>
