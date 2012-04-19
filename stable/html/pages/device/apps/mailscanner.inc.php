<?php

global $config;

$graphs = array('mailscanner_sent' => 'Mailscanner - Sent / Received',
                'mailscanner_spam' => 'Mailscanner - Spam / Virus',
                'mailscanner_reject' => 'Mailscanner - Rejected / Waiting / Relayed');

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