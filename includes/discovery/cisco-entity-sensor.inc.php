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
  $oids = snmpwalk_cache_multi_oid($device, "entSensorPrecision", $oids, "CISCO-ENTITY-SENSOR-MIB");

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
	$descr = snmp_get($device, "entPhysicalName.".$index, "-Oqv", "ENTITY-MIB");
        if(!$descr) { snmp_get($device, "entPhysicalDescr.".$index, "-Oqv", "ENTITY-MIB"); }
        if(is_numeric($entry['entSensorMeasuredEntity']) && $entry['entSensorMeasuredEntity']) {
          $measured_descr = snmp_get($device, "entPhysicalName.".$entry['entSensorMeasuredEntity'],"-Oqv", "ENTITY-MIB");
          if(!measured_descr) {  $measured_descr = snmp_get($device, "entPhysicalDescr.".$entry['entSensorMeasuredEntity'],"-Oqv", "ENTITY-MIB");}
          $descr = $measured_descr . " - " . $descr;
        }

        ### Bit dirty also, clean later
	$descr = str_replace("Temp: ", "", $descr);
        $descr = str_replace("temperature ", "", $descr);
        $descr = str_replace("Temperature ", "", $descr);

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
        if(is_numeric($entry['entSensorPrecision']) && $entry['entSensorPrecision'] > "0") { $divisor = $divisor . str_pad('', $entry['entSensorPrecision'], "0"); }
        $current = $current * $multiplier / $divisor;

	$current = $current * $multiplier / $divisor;
        discover_sensor($valid_sensor, $type, $device, $oid, $index, 'cisco-entity-sensor', $descr, $divisor, $multiplier, NULL, NULL, NULL, NULL, $temperature);

        $cisco_entity_temperature = 1;
      }
    }
  }
}


?>
