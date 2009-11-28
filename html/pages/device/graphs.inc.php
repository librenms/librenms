<?php

$device_query = mysql_query("select * from devices WHERE `device_id` = '$_GET[id]'");
while($device = mysql_fetch_array($device_query)) {
   $hostname = $device[hostname];
   $bg="#ffffff";

   echo("<div style='clear: both;'>");

    if(is_file($config['install_dir'] . "/html/pages/device/graphs/os-$os.inc.php")) {
      include($config['install_dir'] . "/html/pages/device/graphs/os-$os.inc.php");
    }

    if($os_groups[$device[os]]) {$os_group = $os_groups[$device[os]];}

    if(is_file($config['install_dir'] . "/html/pages/device/graphs/os-".$device['os'].".inc.php")) {
      /// OS Specific
      include($config['install_dir'] . "/html/pages/device/graphs/os-".$device['os'].".inc.php");
    }elseif($os_group && is_file($config['install_dir'] . "/html/pages/device/graphs/os-".$os_group.".inc.php")) {
      /// OS Group Specific
      include($config['install_dir'] . "/html/pages/device/graphs/os-".$os_group.".inc.php");
    } else {



   switch ($device['os']) {
   case "fortigate":
      echo("<div class=graphhead>Processor Utilisation</div>");
      $graph_type = "fortigate_cpu";            include ("includes/print-device-graph.php");
      echo("<div class=graphhead>Memory Usage</div>");
      $graph_type = "fortigate_memory";         include ("includes/print-device-graph.php");
      echo("<div class=graphhead>Firewall Sessions</div>");
      $graph_type = "fortigate_sessions";       include ("includes/print-device-graph.php");

      include("graphs/netstats.inc.php");
      include("graphs/uptime.inc.php");

      break;

   case "bcm96348":
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

      break;

   case "screenos":
      echo("<div class=graphhead>Processor Utilisation</div>");
      $graph_type = "netscreen_cpu";            include ("includes/print-device-graph.php");
      echo("<div class=graphhead>Memory Usage</div>");
      $graph_type = "netscreen_memory";        	include ("includes/print-device-graph.php");
      echo("<div class=graphhead>Firewall Sessions</div>");
      $graph_type = "netscreen_sessions";       include ("includes/print-device-graph.php");


      include("graphs/netstats.inc.php");
      include("graphs/uptime.inc.php");

      break;

   case "procurve":
      echo("<div class=graphhead>Processor Utilisation</div>");
      $graph_type = "device_cpu";              include ("includes/print-device-graph.php");
      echo("<br />");
      echo("<div class=graphhead>Memory Usage</div>");
      $graph_type = "device_memory";              include ("includes/print-device-graph.php");
      echo("<br />");
      echo("<div class=graphhead>Device Uptime</div>");
      $graph_type = "device_uptime";         include ("includes/print-device-graph.php"); break;
      echo("<br />");

      break;
   case "Snom":
      echo("<div class=graphhead>Calls</div>");
      $graph_type = "snom_calls";              include ("includes/print-device-graph.php");
   }

}

   if($memgraph) {
     echo("<img src='$memgraph'> <img src='$memgraphm'>");
   }
   if($storagegraph) {
     echo("$storagegraph");
   }
   if($loadgraph) {
     echo("<img src='$loadgraph'> <img src='$loadgraphm'>");
   }
   if($tempgraph) {
     echo("<img src='$tempgraph'> <img src='$tempgraphm'>");
   }
   if($mailsgraph) {
     echo("<img src='$mailsgraph'> <img src='$mailsgraphm'>");
   }
   if($mailerrorgraph) {
     echo("<img src='$mailerrorgraph'> <img src='$mailerrorgraphm'>");
   }
   if($couriergraph) {
     echo("<img src='$couriergraph'> <img src='$couriergraphm'>");
   }
   if($ahitsgraph) {
     echo("<img src='$ahitsgraph'> <img src='$ahitsgraphm'>");
   }
   if($abitsgraph) {
     echo("<img src='$abitsgraph'> <img src='$abitsgraphm'>");
   }
   if($usersgraph) {
     echo("<img src='$usersgraph'> <img src='$usersgraphm'>");
   }
   if($procsgraph) {
     echo("<img src='$procsgraph'> <img src='$procsgraphm'>");
   }
   if($uptimegraph) {
     echo("<img src='$uptimegraph'> <img src='$uptimegraphm'>");
   }
   echo("</div>");
}

?>

