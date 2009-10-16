<?php

function graph_mac_acc_bits ($id, $graph, $from, $to, $width, $height) {
  global $config;
  $imgfile = $config['install_dir'] . "/graphs/" . "$graph";
  $query = mysql_query("SELECT * FROM `mac_accounting` AS M, `interfaces` AS I, `devices` AS D WHERE M.ma_id = '".$id."' AND I.interface_id = M.interface_id AND I.device_id = D.device_id");
  $acc = mysql_fetch_array($query);
  $database = $acc['hostname'] . "/mac-accounting/" . $acc['ifIndex'] . "-" . $acc['mac'] . ".rrd";
  return graph_bits ($database, $graph, $from, $to, $width, $height, $title, $vertical);
}


?>
