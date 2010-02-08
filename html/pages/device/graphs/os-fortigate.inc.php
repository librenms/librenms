<?php
      $graph_title = "Processor Utilisation";
      $graph_type = "fortigate_cpu";            include ("includes/print-device-graph.php");

      $graph_title = "Memory Utilisation";
      $graph_type = "fortigate_memory";         include ("includes/print-device-graph.php");

      $graph_title = "Firewall Sessions";
      $graph_type = "fortigate_sessions";       include ("includes/print-device-graph.php");

      include("graphs/netstats.inc.php");
      include("graphs/uptime.inc.php");
?>
