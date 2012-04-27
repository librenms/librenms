<?php

global $config;

$graphs = array('memcached_bits' => 'Traffic',
                'memcached_commands' => 'Commands',
                'memcached_data' => 'Data Size',
                'memcached_items' => 'Items',
	        'memcached_uptime' => 'Uptime',
                'memcached_threads' => 'Threads',

);

foreach ($graphs as $key => $text)
{

  $graph_array['height'] = "100";
  $graph_array['width']  = "215";
  $graph_array['to']     = $now;
  $graph_array['id']     = $app['app_id'];
  $graph_array['type']   = "application_".$key;

  echo('<h3>'.$text.'</h3>');

  echo("<tr bgcolor='$row_colour'><td colspan=5>");

  include("includes/print-graphrow.inc.php");

  echo("</td></tr>");
}

?>
