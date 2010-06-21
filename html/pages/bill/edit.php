<?php

include("includes/javascript-interfacepicker.inc.php");

### This needs more verification. Is it already added? Does it exist?

if($_POST['action'] == "add_bill_port") { mysql_query("INSERT INTO `bill_ports` (`bill_id`, `port_id`) VALUES ('".mres($_POST['bill_id'])."','".mres($_POST['interface_id'])."')"); }
if($_POST['action'] == "delete_bill_port") { mysql_query("DELETE FROM `bill_ports` WHERE `bill_id` = '".mres($bill_id)."' AND `port_id` = '".mres($_POST['interface_id'])."'"); }

#print_r($_POST);


$ports_array = mysql_query("SELECT * FROM `bill_ports` AS B, `ports` AS P, `devices` AS D
                            WHERE B.bill_id = '".$bill_id."' AND P.interface_id = B.port_id
                            AND D.device_id = P.device_id");

if(mysql_affected_rows()) 
{

  echo("<h3>Billed Ports</h3>");

  echo("<table cellpadding=5 cellspacing=0>");
  while($port = mysql_fetch_array($ports_array)) 
  {
    if($bg == $list_colour_a) { $bg = $list_colour_b; } else { $bg=$list_colour_a; }
    echo("<tr style=\"background-color: $bg\">");
    echo("<td>");
    echo(generatedevicelink($port) . " - " . generateiflink($port));
    if($port['ifAlias']) { echo(" - " . $port['ifAlias']); } 
    echo("</td><td>");
    echo("<form action='' method='post'><input type='hidden' name='action' value='delete_bill_port'>
          <input type=hidden name=interface_id value='".$port['interface_id']."'>
          <input type=submit value=' Delete ' name='Delete'></form>");
    echo("</td>");
  } 
  echo("</table>");
}

  echo("<h3>Add Billed Port</h3>");

  echo("<form action='' method='post'>
      <input type='hidden' name='action' value='add_bill_port'>
      <input type='hidden' name='bill_id' value='".$bill_id."'>

      <table><tr><td>Device: </td>
       <td><select id='device' class='selector' name='device' onchange='getInterfaceList(this)'>
        <option value=''>Select a device</option>");

  $device_list = mysql_query("SELECT * FROM `devices` ORDER BY hostname");
  while($device = mysql_fetch_array($device_list)) {
    unset($done);
    foreach($access_list as $ac) { if($ac == $device['device_id']) { $done = 1; } }
    if(!$done) { echo("<option value='" . $device['device_id']  . "'>" . $device['hostname'] . "</option>"); }
  }

  echo("</select></td></tr><tr>
     <td>Interface: </td><td><select class=selector id='interface_id' name='interface_id'>
     </select></td>
     </tr><tr></table><input type='submit' name='Submit' value=' Add '></form>");



?>
