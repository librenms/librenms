<?php

$query = "SELECT * FROM sensors WHERE device_id = '" . $device['device_id'] . "' AND poller_type='ipmi'";
$ipmi_data = mysql_query($query);

if ($ipmi['host'] = get_dev_attrib($device,'ipmi_hostname'))
{
  $ipmi['user'] = get_dev_attrib($device,'ipmi_username');
  $ipmi['password'] = get_dev_attrib($device,'ipmi_password');
      
  echo("Fetching IPMI sensor data...");
  $results = shell_exec($config['ipmitool'] . " -c -H " . $ipmi['host'] . " -U " . $ipmi['user'] . " -P " . $ipmi['password'] . " sdr");
  echo(" done.\n");

  foreach (explode("\n",$results) as $row)
  {
    list($desc,$value,$type,$status) = explode(',',$row);
    $ipmi_sensor[$desc][$ipmi_unit[$type]]['value'] = $value;
    $ipmi_sensor[$desc][$ipmi_unit[$type]]['unit'] = $type;
  }

  while ($ipmisensors = mysql_fetch_array($ipmi_data)) 
  {
    echo("Updating IPMI sensor " . $ipmisensors['sensor_descr'] . "... ");

    $sensor = $ipmi_sensor[$ipmisensors['sensor_descr']][$ipmisensors['sensor_class']]['value'];
    $unit   = $ipmi_sensor[$ipmisensors['sensor_descr']][$ipmisensors['sensor_class']]['unit'];

    $sensorrrd  = $config['rrd_dir'] . "/" . $device['hostname'] . "/" . safename($ipmisensors['sensor_class'].'-'.$ipmisensors['sensor_type'].'-'.$ipmisensors['sensor_index'] . ".rrd");

    if (!is_file($sensorrrd))
    {
       `rrdtool create $sensorrrd \
      --step 300 \
      DS:sensor:GAUGE:600:-20000:20000 \
      RRA:AVERAGE:0.5:1:600 \
      RRA:AVERAGE:0.5:6:700 \
      RRA:AVERAGE:0.5:24:775 \
      RRA:AVERAGE:0.5:288:797 \
      RRA:MAX:0.5:1:600 \
      RRA:MAX:0.5:6:700 \
      RRA:MAX:0.5:24:775 \
      RRA:MAX:0.5:288:797\
      RRA:MIN:0.5:1:600 \
      RRA:MIN:0.5:6:700 \
      RRA:MIN:0.5:24:775 \
      RRA:MIN:0.5:288:797`;
    }

    echo($sensor . " $unit\n");

    rrdtool_update($sensorrrd,"N:$sensor");

    ## FIXME warnings in event & mail not done here yet!

    mysql_query("UPDATE sensors SET sensor_current = '$sensor' WHERE poller_type='ipmi' AND sensor_class= AND sensor_id = '" . $ipmisensors['sensor_id'] . "'");
  }

  unset($ipmi_sensor);
}

?>
