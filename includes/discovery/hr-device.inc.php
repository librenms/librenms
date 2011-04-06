<?php

echo("hrDevice : ");

$hrDevice_oids = array('hrDeviceEntry','hrProcessorEntry');
if ($debug) { print_r($hrDevices); }

$hrDevices = array();
foreach ($hrDevice_oids as $oid) { $hrDevices = snmpwalk_cache_oid($device, $oid, $hrDevices, "HOST-RESOURCES-MIB:HOST-RESOURCES-TYPES"); }
if ($debug) { print_r($hrDevices); }

if (is_array($hrDevices))
{
  $hrDevices = $hrDevices;
  foreach ($hrDevices as $hrDevice)
  {
    if (is_array($hrDevice) && is_numeric($hrDevice['hrDeviceIndex']))
    {
      if (mysql_result(mysql_query("SELECT COUNT(*) FROM `hrDevice` WHERE device_id = '".$device['device_id']."' AND hrDeviceIndex = '".$hrDevice['hrDeviceIndex']."'"),0))
      {
        $update_query  = "UPDATE `hrDevice` SET";
        $update_query .= "  `hrDeviceType` = '".mres($hrDevice[hrDeviceType])."'";
        $update_query .= ", `hrDeviceDescr` = '".mres($hrDevice[hrDeviceDescr])."'";
        $update_query .= ", `hrDeviceStatus` = '".mres($hrDevice[hrDeviceStatus])."'";
        $update_query .= ", `hrDeviceErrors` = '".mres($hrDevice[hrDeviceErrors])."'";
        if ($hrDevice['hrDeviceType'] == "hrDeviceProcessor")
        {
          $update_query .= ", `hrProcessorLoad` = '".mres($hrDevice[hrProcessorLoad])."'";
        }
        $update_query .= " WHERE device_id = '".$device['device_id']."' AND hrDeviceIndex = '".$hrDevice['hrDeviceIndex']."'";
        @mysql_query($update_query); echo(".");
      }
      else
      {
        $insert_query = "INSERT INTO `hrDevice` (`hrDeviceIndex`,`device_id`,`hrDeviceType`,`hrDeviceDescr`,`hrDeviceStatus`,`hrDeviceErrors`) ";
        $insert_query .= " VALUES ('".mres($hrDevice[hrDeviceIndex])."','".mres($device[device_id])."','".mres($hrDevice[hrDeviceType])."','".mres($hrDevice[hrDeviceDescr])."','".mres($hrDevice[hrDeviceStatus])."','".mres($hrDevice[hrDeviceErrors])."')";
        @mysql_query($insert_query); echo("+");
        if ($debug) { print_r($hrDevice); echo("$insert_query" . mysql_affected_rows() . " row inserted"); }
      }
      $valid_hrDevice[$hrDevice['hrDeviceIndex']] = 1;
    }
  }
}

$sql = "SELECT * FROM `hrDevice` WHERE `device_id`  = '".$device['device_id']."'";
$query = mysql_query($sql);

while ($test_hrDevice = mysql_fetch_assoc($query))
{
  if (!$valid_hrDevice[$test_hrDevice['hrDeviceIndex']])
  {
    echo("-");
    mysql_query("DELETE FROM `hrDevice` WHERE hrDevice_id = '" . $test_hrDevice['hrDevice_id'] . "'");
    if ($debug) { print_r($test_hrDevice); echo(mysql_affected_rows() . " row deleted"); }
  }
}

unset($valid_hrDevice);
echo("\n");

?>