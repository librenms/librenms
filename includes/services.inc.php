<?php

## FIXME who wrote this? so ugly :)
# Not me! -TL

function add_service($device, $service)
{
  echo("$service ");

  $insert = array('device_id' => $device['device_id'], 'service_ip' => $device['hostname'], 'service_type' => $service,
                  'service_desc' => "auto discovered: $service", 'service_param' => "", 'service_ignore' => "0");

  return dbInsert($insert, 'services');
}

?>
