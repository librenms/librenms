<?php

echo("Cisco CEF Switching Path: ");

$cefs = array();
$cefs = snmpwalk_cache_threepart_oid($device, "CISCO-CEF-MIB::cefSwitchingPath", $cefs);
if (1||$debug) { print_r($cefs); }

if (is_array($cefs))
{
  if (!is_array($entity_array))
  {
    echo("Caching OIDs: ");
    $entity_array = array();
    echo(" entPhysicalDescr");
    $entity_array = snmpwalk_cache_multi_oid($device, "entPhysicalDescr", $entity_array, "ENTITY-MIB");
    echo(" entPhysicalName");
    $entity_array = snmpwalk_cache_multi_oid($device, "entPhysicalName", $entity_array, "ENTITY-MIB");
    echo(" entPhysicalModelName");
    $entity_array = snmpwalk_cache_multi_oid($device, "entPhysicalModelName", $entity_array, "ENTITY-MIB");
  }
    foreach ($cefs as $entity => $afis)
  {
    $entity_name = $entity_array[$entity]['entPhysicalName'] ." - ".$entity_array[$entity]['entPhysicalModelName'];
    echo("\n$entity $entity_name\n");
    foreach ($afis as $afi => $paths)
    {
      echo(" |- $afi\n");
      foreach ($paths as $path => $path_name)
      {
        echo(" | |-".$path.": ".$path_name['cefSwitchingPath']."\n");

        if (mysql_result(mysql_query("SELECT COUNT(*) FROM `cef` WHERE `device_id` = '".$device['device_id']."' AND `entPhysicalIndex` = '".$entity."'
                                     AND `afi` = '".$afi."' AND `cef_index` = '".$path."'"),0) != "1")
        {
          $sql = "INSERT INTO `cef` (`device_id`, `entPhysicalIndex`, `afi`, `cef_index`, `cef_path`)
                  VALUES ('".$device['device_id']."', '".$entity."', '".$afi."', '".$path."', '".$path_name['cefSwitchingPath']."')";
          mysql_query($sql);
          echo("+");
        }

      }
    }
  }
}

## FIXME - need to delete old ones. FIXME REALLY.

echo("\n");

?>
