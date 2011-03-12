<?php

  $updated = '1';

  $sql = "INSERT INTO `services` (`device_id`,`service_ip`,`service_type`,`service_desc`,`service_param`,`service_ignore`)
                          VALUES ('" . mres($_POST['device']). "','" . mres($_POST['ip']) . "','" . mres($_POST['type']) . "',
                                  '" . mres($_POST['descr']) . "','" . mres($_POST['params']) . "','0')";

  $query = mysql_query($sql);
  $affected = mysql_affected_rows() . "records affected";

  $message .= $message_break . "Service added!";
  $message_break .= "<br />"


?>
