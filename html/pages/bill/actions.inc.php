<?php

if($_POST['action'] == "delete_bill" && $_POST['confirm'] == "confirm") {

  $port_query = mysql_query("SELECT * FROM `bill_ports` WHERE `bill_id` = '$bill_id'");
  while($port_data = mysql_fetch_array($port_query)) {
    mysql_query("DELETE FROM `port_in_measurements` WHERE `port_id` = '".mres($port_data['bill_id'])."'");
    mysql_query("DELETE FROM `port_out_measurements` WHERE `port_id` = '".mres($port_data['bill_id'])."'");
  }
  
  mysql_query("DELETE FROM `bill_ports` WHERE `bill_id` = '".mres($bill_id)."'");
  mysql_query("DELETE FROM `bill_data` WHERE `bill_id` = '".mres($bill_id)."'");
  mysql_query("DELETE FROM `bill_perms` WHERE `bill_id` = '".mres($bill_id)."'");
  mysql_query("DELETE FROM `bills` WHERE `bill_id` = '".mres($bill_id)."'");

  echo("<div class=infobox>Bill Deleted. Redirecting to Bills list.</div>");

  echo("<meta http-equiv='Refresh' content=\"2; url='bills/'\">");

}


if($_POST['action'] == "add_bill_port") { mysql_query("INSERT INTO `bill_ports` (`bill_id`, `port_id`) VALUES ('".mres($_POST['bill_id'])."','".mres($_POST['interface_id'])."')"); }
if($_POST['action'] == "delete_bill_port") { mysql_query("DELETE FROM `bill_ports` WHERE `bill_id` = '".mres($bill_id)."' AND `port_id` = '".mres($_POST['interface_id'])."'"); }
if($_POST['action'] == "update_bill") {
  mysql_query("UPDATE `bills` SET `bill_name` = '".mres($_POST['bill_name'])."',
                                `bill_day` = '".mres($_POST['bill_day'])."',
                                `bill_gb` = '".mres($_POST['bill_gb'])."',
                                `bill_cdr` = '".mres($_POST['bill_cdr'])."',
                                `bill_type` = '".mres($_POST['bill_type'])."'
                                WHERE `bill_id` = '".mres($bill_id)."'");

  if(mysql_affected_rows())
  {
    echo("<div class=infobox>Bill Properties Updated</div>");
  }

}

?>
