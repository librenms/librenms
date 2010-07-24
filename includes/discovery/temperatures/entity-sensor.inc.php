<?php

global $valid_temp;
  
echo("ENTITY-SENSOR ");

$oids = array();  

$oids = snmpwalk_cache_multi_oid($device, "entPhySensorType", $oids, "ENTITY-SENSOR-MIB");
$oids = snmpwalk_cache_multi_oid($device, "entPhySensorScale", $oids, "ENTITY-SENSOR-MIB");
$oids = snmpwalk_cache_multi_oid($device, "entPhySensorValue", $oids, "ENTITY-SENSOR-MIB");

if(is_array($oids[$device['device_id']]))
{
  foreach($oids[$device['device_id']] as $index => $entry)
  {
    #echo("[" . $entry['entPhySensorType'] . "|" . $entry['entPhySensorValue']. "|" . $index . "]");

    if($entry['entPhySensorType'] == "celsius" && is_numeric($entry['entPhySensorValue']) && is_numeric($index) && $entry['entPhySensorValue'] > "0" && $entry['entPhySensorValue'] < "1000")
    {
      $entPhysicalIndex = $index;
      $descr = snmp_get($device, "entPhysicalDescr.".$index, "-Oqv", "ENTITY-SENSOR-MIB");
      $oid = ".1.3.6.1.2.1.99.1.1.1.4.".$index;
      $current = $entry['entPhySensorValue'];
      #ENTITY-SENSOR-MIB::entPhySensorUnitsDisplay.11 = STRING: "C"

      $descr = str_replace("temperature", "", $descr);
      $descr = str_replace("temp", "", $descr);
      $descr = str_replace("sensor", "", $descr);
        
      if($entry['entPhySensorScale'] == "milli") { $divisor = "1000"; } else { $divisor = "1"; }        

     
      if(mysql_result(mysql_query("SELECT COUNT(*) FROM `sensors` WHERE `device_id` = '".$device['device_id']."' AND `sensor_class` = 'temperature' AND `sensor_type` = 'cisco-entity-sensor' AND `sensor_index` = '".$index."'"),0) == "0") 
      ## Check to make sure we've not already seen this sensor via cisco's entity sensor mib
      {
        discover_temperature($valid_temp, $device, $oid, $index, "entity-sensor", $descr, $divisor, NULL, NULL, $current);
      }
    }
  }
}

?>
