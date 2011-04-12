<?php

echo("Cisco CEF Switching Path: ");

$cefs = array();
$cefs = snmpwalk_cache_threepart_oid($device, "CISCO-CEF-MIB::cefSwitchingStatsEntry", $cefs);
if ($debug) { print_r($cefs); }

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
    foreach($afis as $afi => $paths)
    {
      echo(" |- $afi\n");
      foreach($paths as $path => $path_name)
      {
        echo(" | |-".$path.": ".$path_name['cefSwitchingPath']);


        if(mysql_result(mysql_query("SELECT COUNT(*) FROM `cef_switching` WHERE `device_id` = '".$device['device_id']."' AND `entPhysicalIndex` = '".$entity."' 
                                     AND `afi` = '".$afi."' AND `cef_index` = '".$path."'"),0) != "1")
        {
	  $sql = "INSERT INTO `cef_switching` (`device_id`, `entPhysicalIndex`, `afi`, `cef_index`, `cef_path`) 
                  VALUES ('".$device['device_id']."', '".$entity."', '".$afi."', '".$path."', '".$path_name['cefSwitchingPath']."')";
	  mysql_query($sql);
          echo("+");
        }

        $filename = $config['rrd_dir'] . "/" . $device['hostname'] . "/" . safename("cefswitching-".$entity."-".$afi."-".$path.".rrd");

        if (!is_file($filename))
        {
          rrdtool_create($filename, "--step 300 \
          DS:drop:DERIVE:600:0:1000000 \
          DS:punt:DERIVE:600:0:1000000 \
          DS:hostpunt:DERIVE:600:0:1000000 \
          RRA:AVERAGE:0.5:1:600 RRA:AVERAGE:0.5:6:700 RRA:AVERAGE:0.5:24:775 RRA:AVERAGE:0.5:288:797 \
          RRA:MIN:0.5:1:600     RRA:MIN:0.5:6:700     RRA:MIN:0.5:24:775     RRA:MIN:0.5:288:797 \
          RRA:MAX:0.5:1:600     RRA:MAX:0.5:6:700     RRA:MAX:0.5:24:775     RRA:MAX:0.5:288:797 \
          RRA:LAST:0.5:1:600    RRA:LAST:0.5:6:700    RRA:LAST:0.5:24:775    RRA:LAST:0.5:288:797");
        }

        $path_name['cefSwitchingPath'];
        $path_name['cefSwitchingDrop'];        
        $path_name['cefSwitchingPunt'];
        $path_name['cefSwitchingPunt2Host'];

        ### Copy HC to non-HC if they exist
        if (is_numeric($this_port['cefSwitchingPath'])) { $this_port['cefSwitchingPath'] = $this_port['cefSwitchingHCDrop']; }
        if (is_numeric($this_port['cefSwitchingPunt'])) { $this_port['cefSwitchingPunt'] = $this_port['cefSwitchingHCPunt']; }
        if (is_numeric($this_port['cefSwitchingPunt2Host'])) { $this_port['cefSwitchingPunt2Host'] = $this_port['cefSwitchingHCPunt2Host']; }
                 
        $rrd_update  = "N:".$path_name['cefSwitchingDrop'].":".$path_name['cefSwitchingPunt'].":".$path_name['cefSwitchingPunt2Host'];
        $ret = rrdtool_update("$filename", $rrd_update);

        if($debug) { echo(" Values: ".$path_name['cefSwitchingDrop'].":".$path_name['cefSwitchingPunt'].":".$path_name['cefSwitchingPunt2Host']); }

        echo("\n");

      }
    }
  }
}

## FIXME - need to delete old ones. FIXME REALLY.

echo("\n");

?>
