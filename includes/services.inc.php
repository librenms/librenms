<?php

  function add_service($service) {
    global $id;
    global $hostname;
    echo("$service ");
    $sql = "INSERT INTO `services` (`device_id`,`service_ip`,`service_type`,`service_desc`,`service_param`,`service_ignore`)
                          VALUES ('" . mres($id). "','" . mres($hostname) . "','" . mres($service) . "',
                                  '" . mres("auto discovered: $service") . "','" . mres("") . "','0')";

    $query = mysql_query($sql);
  }


?>
