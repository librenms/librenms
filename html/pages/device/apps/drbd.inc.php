<?php

echo('<h2>'.$app['app_instance'].'</h2>');

$graphs = array('drbd_network_bits' => 'Network Traffic',
                'drbd_disk_bits' => 'Disk Traffic',
                'drbd_unsynced' => 'Unsynced Data',
                'drbd_queue' => 'Queues');

foreach ($graphs as $key => $text)
{

  $graph_array['height'] = "100";
  $graph_array['width']  = "215";
  $graph_array['to']     = $now;
  $graph_array['id']     = $app['app_id'];
  $graph_array['type']   = "application_".$key;

  echo('<h3>'.$text.'</h3>');

  echo("<tr bgcolor='$row_colour'><td colspan=5>");

  include("includes/print-quadgraphs.inc.php");

  echo("</td></tr>");
}

?>
