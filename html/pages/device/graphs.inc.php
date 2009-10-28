<?php

$device_query = mysql_query("select * from devices WHERE `device_id` = '$_GET[id]'");
while($device = mysql_fetch_array($device_query)) {
   $hostname = $device[hostname];
   $bg="#ffffff";

   echo("<div style='clear: both;'>");

    $os = strtolower(str_replace(" ", "_", $device['os']));  
    if(is_file($config['install_dir'] . "/html/pages/device/graphs/os-$os.inc.php")) {
      include($config['install_dir'] . "/html/pages/device/graphs/os-$os.inc.php");
    }

   switch ($device['os']) {
   case "JunOS":
      echo("<div class=graphhead>Processor Utilisation</div>");
      $graph_type = "device_cpu";            include ("includes/print-device-graph.php");
      if(mysql_result(mysql_query("SELECT count(*) FROM temperature WHERE temp_host = '" . $device['device_id'] . "'"),0)) {
        echo("<div class=graphhead>Temperatures</div>");
        $graph_type = "dev_temperatures";             include ("includes/print-device-graph.php");
        echo("<br />");
      }

      include("graphs/netstats.inc.php");
      include("graphs/uptime.inc.php");

      break;


   case "Fortigate":
      echo("<div class=graphhead>Processor Utilisation</div>");
      $graph_type = "fortigate_cpu";            include ("includes/print-device-graph.php");
      echo("<div class=graphhead>Memory Usage</div>");
      $graph_type = "fortigate_memory";         include ("includes/print-device-graph.php");
      echo("<div class=graphhead>Firewall Sessions</div>");
      $graph_type = "fortigate_sessions";       include ("includes/print-device-graph.php");

      include("graphs/netstats.inc.php");
      include("graphs/uptime.inc.php");

      break;

   case "BCM96348":
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

   case "ScreenOS":
      echo("<div class=graphhead>Processor Utilisation</div>");
      $graph_type = "netscreen_cpu";            include ("includes/print-device-graph.php");
      echo("<div class=graphhead>Memory Usage</div>");
      $graph_type = "netscreen_memory";        	include ("includes/print-device-graph.php");
      echo("<div class=graphhead>Firewall Sessions</div>");
      $graph_type = "netscreen_sessions";       include ("includes/print-device-graph.php");


      include("graphs/netstats.inc.php");
      include("graphs/uptime.inc.php");

      break;

   case "ProCurve":
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
   case "Windows":
      $graph_type = "device_cpu";
      include ("includes/print-device-graph.php");

      $memgraph  =   memgraphWin   ($device[hostname] . "-mem.rrd",  $device[hostname] . "-mem.png", $day, $now, 335, 100);
      $loadgraph  =  loadgraphWin  ($device[hostname] . "-load.rrd", $device[hostname] . "-load.png", $day, $now, 335, 100);
      $cpugraphm  =  cpugraphWin   ($device[hostname] . "-cpu.rrd",  $device[hostname] . "-cpu-m.png", $month, $now, 335, 100);
      $memgraphm  =  memgraphWin   ($device[hostname] . "-mem.rrd",  $device[hostname] . "-mem-m.png", $month, $now, 335, 100);
      $loadgraphm  = loadgraphWin  ($device[hostname] . "-load.rrd", $device[hostname] . "-load-m.png", $month, $now, 335, 100);
      $usersgraph  = usersgraphWin ($device[hostname] . "-sys.rrd",  $device[hostname] . "-users.png", $day, $now, 335, 100);
      $usersgraphm = usersgraphWin ($device[hostname] . "-sys.rrd",  $device[hostname] . "-users-m.png", $month, $now, 335, 100);
      $procsgraph  = procsgraphWin ($device[hostname] . "-sys.rrd",  $device[hostname] . "-procs.png", $day, $now, 335, 100);
      $procsgraphm = procsgraphWin ($device[hostname] . "-sys.rrd",  $device[hostname] . "-procs-m.png", $month, $now, 335, 100);
      break;
   case "FreeBSD":
   case "NetBSD":
   case "Linux":
   case "m0n0wall":
   case "Voswall":
   case "DragonFly":
   case "OpenBSD":
   case "pfSense":
      echo("<div class=graphhead>Processor Utilisation</div>");
      $graph_type = "device_cpu";              include ("includes/print-device-graph.php");
      echo("<br />");
      if($device[os] == "m0n0wall" || $device[os] == "pfSense" || $device[os] == "Voswall" || $device[monowall]) {
        echo("<div class=graphhead>IP Statistics</div>");
        $graph_type = "device_ip";           include ("includes/print-device-graph.php");
        echo("<br />");
        echo("<div class=graphhead>Device Uptime</div>");
        $graph_type = "device_uptime";         include ("includes/print-device-graph.php");
        break;
      }
      if($device['os'] != "NetBSD") {
      echo("<div class=graphhead>Memory Utilisation</div>");
      $graph_type = "device_memory";              include ("includes/print-device-graph.php");
      echo("<br />");
      }

      if(mysql_result(mysql_query("SELECT count(storage_id) FROM storage WHERE host_id = '" . $device['device_id'] . "'"),0)) {
        echo("<div class=graphhead>Storage</div>");
        $graph_type = "device_unixfs";           include ("includes/print-device-graph.php");
        echo("<br />");
      }

      if(mysql_result(mysql_query("SELECT count(*) FROM temperature WHERE temp_host = '" . $device['device_id'] . "'"),0)) {
        echo("<div class=graphhead>Temperatures</div>");
        $graph_type = "device_temperatures";           include ("includes/print-device-graph.php");
        echo("<br />");
      }

      include("graphs/netstats.inc.php");
      include("graphs/uptime.inc.php");

      echo("<div class=graphhead>System Load</div>");
      $graph_type = "device_load";             include ("includes/print-device-graph.php");
      echo("<br />");
      echo("<div class=graphhead>Users Logged On</div>");
      $graph_type = "device_users";            include ("includes/print-device-graph.php");
      echo("<br />");
      echo("<div class=graphhead>Running Processes</div>");
      $graph_type = "device_procs";            include ("includes/print-device-graph.php");
      echo("<br />");
      break;

   case "CatOS":
      break;

   case "IOS":
   case "IOS XE":
      break;
   case "Snom":
      echo("<div class=graphhead>Calls</div>");
      $graph_type = "snom_calls";              include ("includes/print-device-graph.php");
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

