<?php

  $updated = '1';

  $sql = "DELETE FROM `services` WHERE service_id = '" . mres($_POST['service']). "'";

  $query = mysql_query($sql);
  $rows = mysql_affected_rows();
  $affected = $rows . " records affected";

  $message .= $message_break . $rows .  " service deleted!";
  $message_break .= "<br />"


?>
