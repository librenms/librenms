<?
echo("<table cellpadding=7 cellspacing=0 class=devicetable width=100%>");

if($_SESSION['userlevel'] == '10') {
  $sql = "SELECT `location` FROM `devices` GROUP BY `location` ORDER BY `location`";
} else {
  $sql = "SELECT `location` FROM `devices` AS D, devices_perms AS P WHERE D.device_id = P.device_id AND P.user_id = '" . $_SESSION['user_id'] . "' GROUP BY `location` ORDER BY `location`";
}


$device_query = mysql_query($sql);
while($device = mysql_fetch_array($device_query)) {

   if($bg == "#ffffff") { $bg = "#eeeeee"; } else { $bg="#ffffff"; }

   if($_SESSION['userlevel'] == '10') {
     $num = mysql_result(mysql_query("SELECT COUNT(device_id) FROM devices WHERE location = '" . $device['location'] . "'"),0);
     $net = mysql_result(mysql_query("SELECT COUNT(device_id) FROM devices WHERE location = '" . $device['location'] . "' AND type = 'network'"),0);
     $srv = mysql_result(mysql_query("SELECT COUNT(device_id) FROM devices WHERE location = '" . $device['location'] . "' AND type = 'server'"),0);
     $fwl = mysql_result(mysql_query("SELECT COUNT(device_id) FROM devices WHERE location = '" . $device['location'] . "' AND type = 'firewall'"),0);
     $hostalerts = mysql_result(mysql_query("SELECT COUNT(device_id) FROM devices WHERE location = '" . $device['location'] . "' AND status = '0'"),0);
   } else {
     $num = mysql_result(mysql_query("SELECT COUNT(D.device_id) FROM devices AS D, devices_perms AS P WHERE D.device_id = P.device_id AND P.user_id = '" . $_SESSION['user_id'] . "' AND location = '" . $device['location'] . "'"),0);
     $net = mysql_result(mysql_query("SELECT COUNT(D.device_id) FROM devices AS D, devices_perms AS P WHERE D.device_id = P.device_id AND P.user_id = '" . $_SESSION['user_id'] . "' AND location = '" . $device['location'] . "' AND D.type = 'network'"),0);
     $srv = mysql_result(mysql_query("SELECT COUNT(D.device_id) FROM devices AS D, devices_perms AS P WHERE D.device_id = P.device_id AND P.user_id = '" . $_SESSION['user_id'] . "' AND location = '" . $device['location'] . "' AND type = 'server'"),0);
     $fwl = mysql_result(mysql_query("SELECT COUNT(D.device_id) FROM devices AS D, devices_perms AS P WHERE D.device_id = P.device_id AND P.user_id = '" . $_SESSION['user_id'] . "' AND location = '" . $device['location'] . "' AND type = 'firewall'"),0);
     $hostalerts = mysql_result(mysql_query("SELECT COUNT(device_id) FROM devices AS D, devices_perms AS P WHERE location = '" . $device['location'] . "' AND status = '0'"),0); 
   }

   if($hostalerts) { $alert = "<img src='images/16/flag_red.png'>"; }

   $loc = $device[location];

   if($loc != "") { 
     echo("<table border=0 cellspacing=0 cellpadding=7 class=devicetable width=100%>
           <tr bgcolor='$bg'>
             <td class=interface width=300><a class='list-bold' href='?page=devices&location=$device[location]'>$loc</a></td>
             <td width='100'>$alert</td>
             <td width='100'>$num devices</td>
             <td width='100'>$net network</td>
	     <td width='100'>$srv servers</td>
             <td width='100'>$fwl firewalls</td>
           </tr>
         ");

    $done = "yes";
  }
}

echo("</table>");

?>
