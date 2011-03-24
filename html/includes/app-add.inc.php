<?php

  $updated = '1';

  $sql = "INSERT INTO `applications` (`device_id`, `app_type`)
                          VALUES ('" . mres($_POST['device']). "','" . mres($_POST['type']) . "')";

  $query = mysql_query($sql);
  $affected = mysql_affected_rows() . "records affected";

  $message .= $message_break . "application added!";
  $message_break .= "<br />"


?>
