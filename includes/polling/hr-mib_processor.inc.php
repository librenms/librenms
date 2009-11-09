<?php

echo("hrDevice : ");

$hrDevice_oids = array('hrDeviceStatus','hrDeviceErrors','hrProcessorLoad');

foreach ($hrDevice_oids as $oid) { echo("$oid "); $hrDevice_array = snmp_cache_oid($oid, $device, $hrDevice_array, "HOST-RESOURCES-MIB:HOST-RESOURCES-TYPES"); }

$sql = "SELECT * FROM `hrDevice` WHERE `device_id`  = '".$device['device_id']."' AND `hrDeviceType` = 'hrDeviceProcessor'";
$query = mysql_query($sql);
while ($hrDevice = mysql_fetch_array($query)) {
  $this_hrDevice = $hrDevice_array[$device[device_id]][$hrDevice[hrDeviceIndex]];

  $update_query  = "UPDATE `hrDevice` SET";
  $update_query .= ", `hrDeviceStatus` = '".mres($this_hrDevice[hrDeviceStatus])."'";
  $update_query .= ", `hrDeviceErrors` = '".mres($this_hrDevice[hrDeviceErrors])."'";
  $update_query .= ", `hrProcessorLoad` = '".mres($this_hrDevice[hrProcessorLoad])."'";
  $update_query .= " WHERE device_id = '".$device['device_id']."' AND hrDeviceIndex = '".$hrDevice['hrDeviceIndex']."'";
  @mysql_query($update_query); $mysql++; echo(".");

  $procrrd  = addslashes($config['rrd_dir'] . "/" . $device['hostname'] . "/hrProcessor-" . $hrDevice['hrDeviceIndex'] . ".rrd");

  if (!is_file($procrrd)) {
    shell_exec($config['rrdtool'] . " create $procrrd \
     --step 300 \
     DS:usage:GAUGE:600:-273:1000 \
     RRA:AVERAGE:0.5:1:1200 \
     RRA:MIN:0.5:12:2400 \
     RRA:MAX:0.5:12:2400 \
     RRA:AVERAGE:0.5:12:2400");
  }

  echo($this_hrDevice['hrProcessorLoad'] . "% ");

  rrdtool_update ($procrrd, "N:".$this_hrDevice['hrProcessorLoad']);

}

echo("\n");

?>
