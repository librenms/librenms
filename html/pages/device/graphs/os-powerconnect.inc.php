      echo("<div class=graphhead>Processor Utilisation</div>");
      $graph_type = "device_cpu";              include ("includes/print-device-graph.php");
      echo("<br />");
      /*
      echo("<div class=graphhead>Memory Usage</div>");
      $graph_type = "device_memory";              include ("includes/print-device-graph.php");
      echo("<br />");
      */
      echo("<div class=graphhead>Device Uptime</div>");
      $graph_type = "device_uptime";         include ("includes/print-device-graph.php"); break;
      echo("<br />");
