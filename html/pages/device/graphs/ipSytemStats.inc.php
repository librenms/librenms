<?

      if (is_file($config['rrd_dir'] . "/" . $device['hostname'] ."/ipSystemStats-ipv6.rrd")) {
        $graph_title = "IPv6 IP Packet Statistics";
        $graph_type = "device_ipSystemStats_v6";
        include ("includes/print-device-graph.php");

        $graph_title = "IPv6 IP Fragmentation Statistics";
        $graph_type = "device_ipSystemStats_v6_frag";
        include ("includes/print-device-graph.php");

      }

      if (is_file($config['rrd_dir'] . "/" . $device['hostname'] ."/ipSystemStats-ipv4.rrd")) {
        $graph_title = "IPv4 IP Packet Statistics";
        $graph_type = "device_ipSystemStats_v4";
        include ("includes/print-device-graph.php");
      }

?>
