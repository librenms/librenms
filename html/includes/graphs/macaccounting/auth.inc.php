<?php

if (is_numeric($id))
{

  $query = mysql_query("SELECT * FROM `mac_accounting` AS M, `ports` AS I, `devices` AS D WHERE M.ma_id = '".mres($_GET['id'])."'
                        AND I.interface_id = M.interface_id AND I.device_id = D.device_id");

  $acc = mysql_fetch_assoc($query);

  if (($config['allow_unauth_graphs'] || port_permitted($acc['interface_id']))
       && is_file($config['rrd_dir'] . "/" . $acc['hostname'] . "/" . safename("cip-" . $acc['ifIndex'] . "-" . $acc['mac'] . ".rrd")))
  {
    $rrd_filename = $config['rrd_dir'] . "/" . $acc['hostname'] . "/" . safename("cip-" . $acc['ifIndex'] . "-" . $acc['mac'] . ".rrd");  

    $port   = get_port_by_id($acc['interface_id']);
    $device = device_by_id_cache($port['device_id']);
    $title  = generate_device_link($device);
    $title .= " :: Port  ".generate_port_link($port);
    $title .= " :: " . $acc['mac'];
    $auth   = TRUE;

  }

}
?>
