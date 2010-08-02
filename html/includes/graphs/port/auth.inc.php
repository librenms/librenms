<?php

if(is_numeric($id) && port_permitted($id)) {
  $port   = get_port_by_id($id);
  $device = device_by_id_cache($port['device_id']);
  $title  = generatedevicelink($device);
  $title .= " :: Port  ".generateiflink($port);
  $auth = TRUE;
}

?>
