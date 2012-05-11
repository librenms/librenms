<?php

$proc = dbFetchRow("SELECT * FROM `processors` where `processor_id` = ?", array($vars['id']));

if (is_numeric($proc['device_id']) && ($config['allow_unauth_graphs'] || device_permitted($proc['device_id'])))
{
  $device = device_by_id_cache($proc['device_id']);
  $rrd_filename  = $config['rrd_dir'] . "/".$device['hostname']."/" . safename("processor-" . $proc['processor_type'] . "-" . $proc['processor_index'] . ".rrd");
  $title  = generate_device_link($device);
  $title .= " :: Processor :: " . htmlentities($proc['processor_descr']);
  $auth = TRUE;
}

?>
