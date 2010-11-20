<?php

global $valid_sensor, $ipoman_array;

## IPOMANII-MIB
if ($device['os'] == "ipoman")
{
  echo(" IPOMANII-MIB ");

  if(!is_array($ipoman_array))
  { 
    echo("outletConfigDesc ");
    $ipoman_array['out'] = snmpwalk_cache_multi_oid($device, "outletConfigDesc", $ipoman_array['out'], "IPOMANII-MIB");
    echo("outletConfigLocation ");
    $ipoman_array['out'] = snmpwalk_cache_multi_oid($device, "outletConfigLocation", $ipoman_array['out'], "IPOMANII-MIB");
    echo("inletConfigDesc ");
    $ipoman_array['in'] = snmpwalk_cache_multi_oid($device, "inletConfigDesc", $ipoman_array['in'], "IPOMANII-MIB");
  }

  $oids = array();

  echo("inletConfigVoltageHigh ");
  $oids = snmpwalk_cache_multi_oid($device, "inletConfigVoltageHigh", $oids, "IPOMANII-MIB");
  echo("inletConfigVoltageLow ");
  $oids = snmpwalk_cache_multi_oid($device, "inletConfigVoltageLow", $oids, "IPOMANII-MIB");
  echo("inletStatusVoltage ");
  $oids = snmpwalk_cache_multi_oid($device, "inletStatusVoltage", $oids, "IPOMANII-MIB");

  if(is_array($oids))
  {
    foreach($oids as $index => $entry)
    {
      $volt_oid = '.1.3.6.1.4.1.2468.1.4.2.1.3.1.3.1.2.' . $index;
      $divisor = 10;
      $descr = (trim($ipoman_array['in'][$index]['inletConfigDesc'],'"') != '' ? trim($ipoman_array['in'][$index]['inletConfigDesc'],'"') : "Inlet $index");
      $current = $entry['inletStatusVoltage'] / 10;
      $low_limit = $entry['inletConfigVoltageLow'];
      $high_limit = $entry['inletConfigVoltageHigh'];
      echo(discover_sensor($valid_sensor, 'voltage', $device, $volt_oid, $index, 'ipoman', $descr, $divisor, '1', $low_limit, NULL, NULL, $high_limit, $current));
      # FIXME: iPoMan 1201 also says it has 2 inlets, at least until firmware 1.06 - wtf?
    }
  }
}
?>