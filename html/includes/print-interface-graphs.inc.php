<?php

  global $config;

  #if(!$graph_type) { $graph_type = $_GET['type']; }
  #if(!$device) { $device['device_id'] = getifhost($interface['interface_id']); }

  $graph_array['height'] = "100";
  $graph_array['width']  = "215";
  $graph_array['to']     = $now;
  $graph_array['id']     = $interface['interface_id'];
  $graph_array['type']   = $graph_type;

  include("includes/print-quadgraphs.inc.php");

?>
