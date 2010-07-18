<?php

      if(is_file($config['rrd_dir'] . "/" . $device['hostname'] ."/wificlients-all.rrd")) {
        $graph_title = "Wireless clients";
        $graph_type = "device_wificlients";
        include ("includes/print-device-graph.php");
      }

?>
