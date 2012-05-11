<?php

if (is_numeric($vars['id']))
{
  $toner = dbFetchRow("SELECT * FROM `toner` WHERE `toner_id` = ?", array($vars['id']));

  if (is_numeric($toner['device_id']) && ($config['allow_unauth_graphs'] || device_permitted($toner['device_id'])))
  {
    $device = device_by_id_cache($toner['device_id']);
    $rrd_filename  = $config['rrd_dir'] . "/" . $device['hostname'] . "/" . safename("toner-" . $toner['toner_index'] . ".rrd");

    $title  = generate_device_link($device);
    $title .= " :: Toner :: " . htmlentities($toner['toner_descr']);
    $auth = TRUE;
  }
}

?>
