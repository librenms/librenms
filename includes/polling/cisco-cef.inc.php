<?php

echo("Cisco CEF Switching Path: ");

$cefs = array();
$cefs = snmpwalk_cache_threepart_oid($device, "CISCO-CEF-MIB::cefSwitchingStatsEntry", $cefs);
$polled = time();

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
      foreach($paths as $path => $cef_stat)
      {
        echo(" | |-".$path.": ".$cef_stat['cefSwitchingPath']);


        if(mysql_result(mysql_query("SELECT COUNT(*) FROM `cef_switching` WHERE `device_id` = '".$device['device_id']."' AND `entPhysicalIndex` = '".$entity."' 
                                     AND `afi` = '".$afi."' AND `cef_index` = '".$path."'"),0) != "1")
        {
	  $sql = "INSERT INTO `cef_switching` (`device_id`, `entPhysicalIndex`, `afi`, `cef_index`, `cef_path`) 
                  VALUES ('".$device['device_id']."', '".$entity."', '".$afi."', '".$path."', '".$cef_stat['cefSwitchingPath']."')";
	  mysql_query($sql);
          echo("+");
        }

        $sql = "SELECT * FROM `cef_switching` WHERE `device_id` = '".$device['device_id']."' AND `entPhysicalIndex` = '".$entity."'
                                     AND `afi` = '".$afi."' AND `cef_index` = '".$path."'";

	$query = mysql_query($sql);
	$cef_entry = mysql_fetch_assoc($query);

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

        ### Copy HC to non-HC if they exist
        if (is_numeric($cef_stat['cefSwitchingHCDrop'])) { $cef_stat['cefSwitchingDrop'] = $cef_stat['cefSwitchingHCDrop']; }
        if (is_numeric($cef_stat['cefSwitchingHCPunt'])) { $cef_stat['cefSwitchingPunt'] = $cef_stat['cefSwitchingHCPunt']; }
        if (is_numeric($cef_stat['cefSwitchingHCPunt2Host'])) { $cef_stat['cefSwitchingPunt2Host'] = $cef_stat['cefSwitchingHCPunt2Host']; }

        $update =  " `drop` = '".$cef_stat['cefSwitchingDrop']."'";
        $update .= ", `punt` = '".$cef_stat['cefSwitchingPunt']."'";
        $update .= ", `punt2host` = '".$cef_stat['cefSwitchingPunt2Host']."'";

        $update .= ", `drop_prev` = '".$cef_entry['drop']."'";
        $update .= ", `punt_prev` = '".$cef_entry['punt']."'";
        $update .= ", `punt2host_prev` = '".$cef_entry['punt2host']."'";

        $update .= ",  `updated` = '".$polled."'";
        $update .= ", `updated_prev` = '".$cef_entry['updated']."'";

	$update_query  = "UPDATE `cef_switching` SET ".$update." WHERE `device_id` = '".$device['device_id']."' AND `entPhysicalIndex` = '".$entity."'
                                                                   AND `afi` = '".$afi."' AND `cef_index` = '".$path."'";
        @mysql_query($update_query);
        if ($debug) {echo("\nMYSQL : [ $update_query ]"); }
                 
        $rrd_update  = "N:".$cef_stat['cefSwitchingDrop'].":".$cef_stat['cefSwitchingPunt'].":".$cef_stat['cefSwitchingPunt2Host'];
        $ret = rrdtool_update("$filename", $rrd_update);

        if($debug) { echo(" Values: ".$cef_stat['cefSwitchingDrop'].":".$cef_stat['cefSwitchingPunt'].":".$cef_stat['cefSwitchingPunt2Host']); }

        echo("\n");

      }
    }
  }
}

## FIXME - need to delete old ones. FIXME REALLY.

echo("\n");

?>
