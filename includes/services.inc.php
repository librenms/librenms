<?php

  ## FIXME who wrote this? so ugly :)

  function add_service($service) {
    global $id;
    global $hostname;
    echo("$service ");

    #$sql = "INSERT INTO `services` (`device_id`,`service_ip`,`service_type`,`service_desc`,`service_param`,`service_ignore`)
    #                      VALUES ('" . mres($id). "','" . mres($hostname) . "','" . mres($service) . "',
    #                              '" . mres("auto discovered: $service") . "','" . mres("") . "','0')";

    $insert = array('device_id' => $id, 'service_ip' => $hostname, 'service_type' => $service, 'service_desc' => "auto discovered: $service", 'service_param' => "", 'service_ignore' => "0");

    return dbInsert($insert, 'services');    

  }


?>
