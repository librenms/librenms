<?php
      echo("<div class=graphhead>ADSL Attainable Rate</div>");
      $graph_type = "adsl_rate";            include ("includes/print-device-graph.php");
      echo("<br />");
      echo("<div class=graphhead>ADSL Signal-to-Noise Margin</div>");
      $graph_type = "adsl_snr";            include ("includes/print-device-graph.php");
      echo("<br />");
      echo("<div class=graphhead>ADSL Attenuation</div>");
      $graph_type = "adsl_atn";         include ("includes/print-device-graph.php");
      echo("<br />");

      include("graphs/netstats.inc.php");
      include("graphs/uptime.inc.php");
?>
