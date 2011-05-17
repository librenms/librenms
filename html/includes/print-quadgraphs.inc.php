<?php

global $config;

if(!$graph_array['height']) { $graph_array['height'] = "100"; }
if(!$graph_array['width']) { $graph_array['width']  = "215"; }

$graph_array['to']     = $config['time']['now'];

$periods = array('day', 'week', 'month', 'year');

foreach ($periods as $period)
{
  $graph_array['from']        = $config['time'][$period];
  $graph_array_zoom           = $graph_array;
  $graph_array_zoom['height'] = "150";
  $graph_array_zoom['width']  = "400";

  $link = "graphs/" . $graph_array['id'] . "/" . $graph_array['type'] . "/" . $graph_array['from'] . "/" . $config['to'] . "/";

  echo(overlib_link($link, generate_graph_tag($graph_array), generate_graph_tag($graph_array_zoom),  NULL));
}

?>
