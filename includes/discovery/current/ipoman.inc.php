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
    $ipoman_array['in'] = snmpwalk_cache_multi_oid($device, "inletConfigDesc", $ipoman_array, "IPOMANII-MIB");
  }

  $oids_in = array();
  $oids_out = array();

  echo("inletConfigCurrentHigh ");
  $oids_in = snmpwalk_cache_multi_oid($device, "inletConfigCurrentHigh", $oids_in, "IPOMANII-MIB");
  echo("inletStatusCurrent ");
  $oids_in = snmpwalk_cache_multi_oid($device, "inletStatusCurrent", $oids_in, "IPOMANII-MIB");
//  $oids_in = snmpwalk_cache_multi_oid($device, "inletStatusKwatt", $oids_in, "IPOMANII-MIB"); // Not implemented yet in Obs?
  echo("outletConfigCurrentHigh ");
  $oids_out = snmpwalk_cache_multi_oid($device, "outletConfigCurrentHigh", $oids_out, "IPOMANII-MIB");
  echo("outletStatusCurrent ");
  $oids_out = snmpwalk_cache_multi_oid($device, "outletStatusCurrent", $oids_out, "IPOMANII-MIB");
//  $oids_out = snmpwalk_cache_multi_oid($device, "outletStatusKwatt", $oids_out, "IPOMANII-MIB"); // See above

  if(is_array($oids_in))
  {
    foreach($oids_in as $index => $entry)
    {
      $cur_oid = '.1.3.6.1.4.1.2468.1.4.2.1.3.1.3.1.3.' . $index;
      $divisor = 1000;
      $descr = (trim($ipoman_array['in'][$index]['inletConfigDesc'],'"') != '' ? trim($ipoman_array['in'][$index]['inletConfigDesc'],'"') : "Inlet $index");
      $current = $entry['inletStatusCurrent'] / $divisor;
      $high_limit = $entry['inletConfigCurrentHigh'] / 10;
      echo(discover_sensor($valid_sensor, 'current', $device, $cur_oid, '1.3.1.3.'.$index, 'ipoman', $descr, $divisor, '1', NULL, NULL, NULL, $high_limit, $current));
      # FIXME: iPoMan 1201 also says it has 2 inlets, at least until firmware 1.06 - wtf?
    }
  }

  if(is_array($oids_out))
  {
    foreach($oids_out as $index => $entry)
    {
      $cur_oid = '.1.3.6.1.4.1.2468.1.4.2.1.3.2.3.1.3.' . $index;
      $divisor = 1000;
      $descr = (trim($ipoman_array['out'][$index]['outletConfigDesc'],'"') != '' ? trim($ipoman_array['out'][$index]['outletConfigDesc'],'"') : "Output $index");
      $current = $entry['outletStatusCurrent'] / $divisor;
      $high_limit = $entry['outletConfigCurrentHigh'] / 10;
      echo(discover_sensor($valid_sensor, 'current', $device, $cur_oid, '2.3.1.3.'.$index, $type, $descr, $divisor, '1', NULL, NULL, NULL, $high_limit, $current));
    }
  }
}
?>