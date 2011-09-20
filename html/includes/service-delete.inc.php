<?php

  $updated = '1';

  $affected = dbDelete('services', '`service_id` = ?', array($_POST['service']));

  if($affected)
  {
    $message .= $message_break . $rows .  " service deleted!";
    $message_break .= "<br />";
  }

?>
