<?
echo("<table cellpadding=7 cellspacing=0 class=devicetable width=100%>");

$device_query = mysql_query("select location from devices GROUP BY location ORDER BY location");
while($device = mysql_fetch_array($device_query)) {

   if($bg == "#ffffff") { $bg = "#eeeeee"; } else { $bg="#ffffff"; }

   $num = mysql_result(mysql_query("SELECT COUNT(id) FROM devices WHERE location = '$device[location]'"),0);
   $net = mysql_result(mysql_query("SELECT COUNT(id) FROM devices WHERE location = '$device[location]' AND type = 'network'"),0);
   $srv = mysql_result(mysql_query("SELECT COUNT(id) FROM devices WHERE location = '$device[location]' AND type = 'server'"),0);
   $fwl = mysql_result(mysql_query("SELECT COUNT(id) FROM devices WHERE location = '$device[location]' AND type = 'firewall'"),0);

   $hostalerts = mysql_result(mysql_query("SELECT COUNT(id) FROM devices WHERE location = '$device[location]' AND status = '0'"),0);
   if($hostalerts) { $alert = "<img src='/images/16/flag_red.png'>"; }

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
