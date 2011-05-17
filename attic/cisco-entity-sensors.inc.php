<?php

$query = "SELECT * FROM entPhysical WHERE device_id = '" . $device['device_id'] . "' AND entPhysicalClass = 'sensor'";
$sensors = mysql_query($query);
while ($sensor = mysql_fetch_assoc($sensors))
{
  echo("Checking Entity Sensor " . $sensor['entPhysicalName'] . " - " . $sensor['cempsensorName']);

  $oid = $sensor['entPhysicalIndex'];

  $sensor_cmd  = $config['snmpget'] . " -M ".$config['mibdir']." -m CISCO-ENTITY-SENSOR-MIB -O Uqnv -" . $device['snmpver'] . " -c " . $device['community'] . " " . $device['hostname'].":".$device['port'];
  $sensor_cmd .= " entSensorValue.$oid entSensorStatus.$oid";

  $sensor_data = trim(shell_exec($sensor_cmd));

#  echo("$sensor_data");

  list($entSensorValue, $entSensorStatus) = explode("\n", $sensor_data);

  $rrd  = $config['rrd_dir'] . "/" . $device['hostname'] . "/" . safename("ces-" . $oid . ".rrd");

  if (!is_file($rrd))
  {
    rrdtool_create($rrd,"--step 300 \
      DS:value:GAUGE:600:-1000:U \
      RRA:AVERAGE:0.5:1:2304 \
      RRA:AVERAGE:0.5:6:1536 \
      RRA:AVERAGE:0.5:24:2268 \
      RRA:AVERAGE:0.5:288:1890 \
      RRA:MAX:0.5:1:2304 \
      RRA:MAX:0.5:6:1536 \
      RRA:MAX:0.5:24:2268 \
      RRA:MAX:0.5:288:1890 \
      RRA:MIN:0.5:1:2304 \
      RRA:MIN:0.5:6:1536 \
      RRA:MIN:0.5:24:2268 \
      RRA:MIN:0.5:288:1890");
  }

  $entSensorValue = entPhysical_scale($entSensorValue, $sensor['entSensorScale']);

  $updatecmd = $config['rrdtool'] ." update $rrd N:$entSensorValue";
  shell_exec($updatecmd);

  $update_query = "UPDATE `entPhysical` SET entSensorValue='$entSensorValue', entSensorStatus='$entSensorStatus' WHERE `entPhysical_id` = '".$sensor['entPhysical_id']."'";
  mysql_query($update_query);

  echo($entSensorValue . " - " . $entSensorStatus . "\n");
}

?>