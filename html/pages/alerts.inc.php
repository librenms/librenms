<meta http-equiv="refresh" content="60">
<?php

# FIXME is this used anywhere??

if($_GET['del']) { 
  $id = mres($_GET['del']);
  $query = "DELETE FROM `alerts` WHERE `id` = '$id'";
  mysql_query($query); 
}

$sql = "select *,DATE_FORMAT(time_logged, '%D %M %Y %T') as time, A.id as id, D.id as device_id from alerts AS A, devices as D WHERE A.device_id = D.id ORDER BY time_logged DESC LIMIT 100";

echo("
<table cellspacing=0 cellpadding=2 width=100%>
");

$query = mysql_query($sql);
while($event = mysql_fetch_array($query)) 
{

if($bg == "#ffffff") { $bg = "#e5e5e5"; } else { $bg="#ffffff"; }

$type = $event[importance];

switch ($type) {
case "9":
   $type = "<img src='images/16/flag_red.png'>";
   break;
case "7":
   $type = "<img src='images/16/flag_pink.png'>";
   break;
case "2":
   $type = "<img src='images/16/flag_blue.png'>";
   break;
case "0":
   $type = "<img src='images/16/flag_green.png'>";
   break;
}

if(!$argh) {
   echo("
  <tr style=\"background-color: $bg;\">
    <td width=10></td>
    <td class=syslog width=200>
      $event[time]     
    </td>
    <td class=syslog width=200>
      $event[hostname]
    </td>
    <td width=20>
      $type
    </td>
    <td class=syslog align=left>
      $event[message]
    </td>
   <td>
   </td>
   <td width=40>
     <a href='?page=alerts&del=$event[id]'><img border=0 src='images/16/cross.png'" . overlibprint("Remove Alert") . "'></a>
     <a href='?page=alerts&ack=$event[id]'><img border=0 src='images/16/tick.png' onmouseover=\"return overlib('Acknowledge Alert');\" onmouseout=\"return nd();\"></a>
   </td>
  </tr>

");
}

}

?>
</table>
