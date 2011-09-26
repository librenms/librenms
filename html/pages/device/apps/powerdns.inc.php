<?php

global $config;

$graphs = array('powerdns_latency'  => 'Latency',
                'powerdns_fail' => 'Corrupt - Failed - Timedout',
                'powerdns_packetcache' => 'Packet Cache',
                'powerdns_querycache' => 'Query Cache',
                'powerdns_recursing' => 'Recursing Queries and Answers',
                'powerdns_queries' => 'Total UDP/TCP Queries and Answers',
                'powerdns_queries_udp' => 'Detail UDP IPv4/IPv6 Queries and Answers');

foreach ($graphs as $key => $text)
{
  $graph_type            = $key;
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