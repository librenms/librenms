<?php

      echo("<div class=graphhead>IP Statistics</div>");
      $graph_type = "device_ip";           include ("includes/print-device-graph.php");
      echo("<br />");
      echo("<div class=graphhead>IP Fragmented Statistics</div>");
      $graph_type = "device_ip_fragmented";           include ("includes/print-device-graph.php");
      echo("<br />");
      echo("<div class=graphhead>TCP Statistics</div>");
      $graph_type = "device_tcp";          include ("includes/print-device-graph.php");
      echo("<br />");
      echo("<div class=graphhead>UDP Statistics</div>");
      $graph_type = "device_udp";          include ("includes/print-device-graph.php");
      echo("<br />");
      echo("<div class=graphhead>ICMP Statistics</div>");
      $graph_type = "device_icmp";          include ("includes/print-device-graph.php");
      echo("<br />");
      echo("<div class=graphhead>ICMP Informational Statistics</div>");
      $graph_type = "device_icmp_informational";          include ("includes/print-device-graph.php");
      echo("<br />");

?>
