<?php

      if(is_file($config['rrd_dir'] . "/" . $device['hostname'] ."/ipSystemStats-ipv6.rrd")) {
        $graph_title = "IPv4/IPv6 Statistics";
        $graph_type = "device_ipSystemStats";        
        include ("includes/print-device-graph.php");
      }

      $graph_title = "IP Statistics";
      $graph_type = "device_ip";         
      include ("includes/print-device-graph.php");

      $graph_title = "IP Fragmented Statistics";
      $graph_type = "device_ip_fragmented";    
      include ("includes/print-device-graph.php");

      $graph_title = "TCP Statistics";
      $graph_type = "device_tcp";    
      include ("includes/print-device-graph.php");

      $graph_title = "UDP Statistics";
      $graph_type = "device_udp";       
      include ("includes/print-device-graph.php");

      $graph_title = "ICMP Statistics";
      $graph_type = "device_icmp";      
      include ("includes/print-device-graph.php");

      $graph_title = "ICMP Informational Statistics";
      $graph_type = "device_icmp_informational";      
      include ("includes/print-device-graph.php");

?>
