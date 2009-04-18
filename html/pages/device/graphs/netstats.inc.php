<?php

      echo("<div class=graphhead>IP Statistics</div>");
      $graph_type = "ip_graph";           include ("includes/print-device-graph.php");
      echo("<br />");
      echo("<div class=graphhead>TCP Statistics</div>");
      $graph_type = "tcp_graph";          include ("includes/print-device-graph.php");
      echo("<br />");
      echo("<div class=graphhead>UDP Statistics</div>");
      $graph_type = "udp_graph";          include ("includes/print-device-graph.php");
      echo("<br />");
      echo("<div class=graphhead>ICMP Statistics</div>");
      $graph_type = "icmp_graph";          include ("includes/print-device-graph.php");
      echo("<br />");


?>
