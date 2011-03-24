<?php

  $updated = '1';

  $sql = "DELETE FROM `applications` WHERE app_id = '" . mres($_POST['app']). "'";

  $query = mysql_query($sql);
  $rows = mysql_affected_rows();
  $affected = $rows . " records affected";

  $message .= $message_break . $rows .  " application deleted!";
  $message_break .= "<br />"


?>
