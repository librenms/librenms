<?php

      if(mysql_result(mysql_query("SELECT count(storage_id) FROM storage WHERE device_id = '" . $device['device_id'] . "'"),0)) {
        $graph_title = "Storage";
        $graph_type = "device_hrstorage";      
        include ("includes/print-device-graph.php");
      }

?>
