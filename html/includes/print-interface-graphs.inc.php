<?php

  global $config;

  if(!$graph_type) { $graph_type = $_GET['type']; }

  if(!$device) { $device['device_id'] = getifhost($interface['interface_id']); }

  $graph_array['height'] = "100";
  $graph_array['width']  = "215";
  $graph_array['to']     = $now;
  $graph_array['port']   = $interface['interface_id'];
  $graph_array['type']   = $graph_type;

  $periods = array('day', 'week', 'month', 'year');

  foreach($periods as $period) {
    $graph_array['from']     = $$period;
    $graph_array_zoom 	= $graph_array; $graph_array_zoom['height'] = "150"; $graph_array_zoom['width'] = "400";
    echo(overlib_link($_SERVER['REQUEST_URI'], generate_graph_tag($graph_array), generate_graph_tag($graph_array_zoom),  NULL));
  }

?>
