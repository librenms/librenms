<?php

if (is_numeric($vars['id']))
{

  $acc = dbFetchRow("SELECT * FROM `mac_accounting` AS M, `ports` AS I, `devices` AS D WHERE M.ma_id = ? AND I.port_id = M.port_id AND I.device_id = D.device_id", array($vars['id']));

  if ($debug) {
    echo("<pre>");
    print_r($acc);
    echo("</pre>");
  }

  if (is_array($acc))
  {

    if ($auth || port_permitted($acc['port_id']))
    {
      if ($debug) { echo($config['rrd_dir'] . "/" . $acc['hostname'] . "/" . safename("cip-" . $acc['ifIndex'] . "-" . $acc['mac'] . ".rrd")); }

      if (is_file($config['rrd_dir'] . "/" . $acc['hostname'] . "/" . safename("cip-" . $acc['ifIndex'] . "-" . $acc['mac'] . ".rrd")))
      {
        if ($debug) { echo("exists"); }
        $rrd_filename = $config['rrd_dir'] . "/" . $acc['hostname'] . "/" . safename("cip-" . $acc['ifIndex'] . "-" . $acc['mac'] . ".rrd");
        $port   = get_port_by_id($acc['port_id']);
        $device = device_by_id_cache($port['device_id']);
        $title  = generate_device_link($device);
        $title .= " :: Port  ".generate_port_link($port);
        $title .= " :: " . formatMac($acc['mac']);
        $auth   = TRUE;
      } else {
        graph_error("file not found");
      }
    } else {
      graph_error("unauthenticated");
    }
  } else {
    graph_error("entry not found");
  }
} else {
  graph_error("invalid id");
}
?>
