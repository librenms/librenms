<?php

global $config;

$graphs = array('powerdns_latency'  => 'PowerDNS - Latency',
                'powerdns_fail' => 'PowerDNS - Corrupt / Failed / Timed out',
                'powerdns_packetcache' => 'PowerDNS - Packet Cache',
                'powerdns_querycache' => 'PowerDNS - Query Cache',
                'powerdns_recursing' => 'PowerDNS - Recursing Queries and Answers',
                'powerdns_queries' => 'PowerDNS - Total UDP/TCP Queries and Answers',
                'powerdns_queries_udp' => 'PowerDNS - Detail UDP IPv4/IPv6 Queries and Answers');

foreach ($graphs as $key => $text)
{
  $graph_type            = $key;
  $graph_array['height'] = "100";
  $graph_array['width']  = "215";
  $graph_array['to']     = $config['time']['now'];
  $graph_array['id']     = $app['app_id'];
  $graph_array['type']   = "application_".$key;

  echo('<h3>'.$text.'</h3>');

  echo("<tr bgcolor='$row_colour'><td colspan=5>");

  include("includes/print-graphrow.inc.php");

  echo("</td></tr>");
}

?>