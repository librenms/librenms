<?php

### Force10 C-Series

#F10-C-SERIES-CHASSIS-MIB::chSysCardType.1 = INTEGER: lc4802E48TB(1024)
#F10-C-SERIES-CHASSIS-MIB::chSysCardType.2 = INTEGER: lc0810EX8PB(2049)
#F10-C-SERIES-CHASSIS-MIB::chSysCardTemp.1 = Gauge32: 25
#F10-C-SERIES-CHASSIS-MIB::chSysCardTemp.2 = Gauge32: 26



global $valid_temp;
  
if ($device['os'] == "ftos" || $device['os_group'] == "ftos") 
{
  echo("FTOS C-Series ");

  $oids = snmpwalk_cache_oid($device, "chSysCardTemp", array(), "F10-C-SERIES-CHASSIS-MIB", $config['mib_dir'].":".$config['mib_dir']."/ftos" );

  if(is_array($oids[$device['device_id']]))
  {
    foreach($oids[$device['device_id']] as $index => $entry)
    {

      $descr = "Slot ".$index;
      $oid = ".1.3.6.1.4.1.6027.3.8.1.2.1.1.5.".$index;
      $current = $entry['chSysCardTemp'];

      discover_temperature($valid_temp, $device, $oid, $index, "ftos-cseries", $descr, "1", NULL, NULL, $current);
    }
  }

  unset($oids);

}


?>
