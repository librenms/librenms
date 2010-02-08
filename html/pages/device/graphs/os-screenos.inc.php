<?php

      $graph_title = "Processor Utilisation";
      $graph_type = "netscreen_cpu";            include ("includes/print-device-graph.php");

      $graph_title = "Memory Usage</div>";
      $graph_type = "netscreen_memory";        	include ("includes/print-device-graph.php");

      $graph_title = "Firewall Sessions";
      $graph_type = "netscreen_sessions";       include ("includes/print-device-graph.php");


      include("graphs/netstats.inc.php");
      include("graphs/uptime.inc.php");

?>
