<?php

  $updated = '1';

  $service_id = dbInsert(array('device_id' => $_POST['device'], 'service_ip' => $_POST['ip'], 'service_type' => $_POST['type'], 'service_desc' => $_POST['descr'], 'service_param' => $_POST['params'], 'service_ignore' => '0'), 'services');

  if ($service_id) {
    $message .= $message_break . "Service added (".$service_id.")!";
    $message_break .= "<br />";
  }

?>
