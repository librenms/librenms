<?php

if (is_numeric($vars['id']))
{
  $sensor = dbFetchRow("SELECT * FROM sensors WHERE sensor_id = ?", array($vars['id']));

  if (is_numeric($sensor['device_id']) && ($auth || device_permitted($sensor['device_id'])))
  {
    $device = device_by_id_cache($sensor['device_id']);

    // This doesn't quite work for all yet.
    #$rrd_filename  = $config['rrd_dir'] . "/".$device['hostname']."/" . safename($sensor['sensor_class']."-" . $sensor['sensor_type'] . "-".$sensor['sensor_index'].".rrd");
    $rrd_filename = get_sensor_rrd($device, $sensor);

    $title  = generate_device_link($device);
    $title .= " :: Sensor :: " . htmlentities($sensor['sensor_descr']);
    $auth = TRUE;
  }
}

?>
