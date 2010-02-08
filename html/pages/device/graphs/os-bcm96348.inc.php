<?php
  $graph_title = "ADSL Attainable Rate";
  $graph_type = "adsl_rate";
  include ("includes/print-device-graph.php");

  $graph_title = "ADSL Signal to Noise Ratio";
  $graph_type = "adsl_snr";          
  include ("includes/print-device-graph.php");

  $graph_title = "ADSL Attenuation";
  $graph_type = "adsl_atn"; 
  include ("includes/print-device-graph.php");

  include("graphs/netstats.inc.php");
  include("graphs/uptime.inc.php");
?>
