<?

  $updated = '1';

  $sql = "INSERT INTO `services` (`service_host`,`service_ip`,`service_type`,`service_desc`,`service_param`,`service_ignore`) 
                          VALUES ('" . $_POST['device']. "','" . $_POST['ip'] . "','" . $_POST['type'] . "',
                                  '" . $_POST['descr'] . "','" . $_POST['params'] . "','0')";

  $query = mysql_query($sql);
  $affected = mysql_affected_rows() . "records affected";

  $message .= $message_break . "Service added!";
  $message_break .= "<br />"


?>
