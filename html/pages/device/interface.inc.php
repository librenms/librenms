<?php

if( !isset($_GET['optb']) ) { $_GET['optb'] = "graphs"; }

$interface_query = mysql_query("select * from ports WHERE interface_id = '".$_GET['opta']."'");
$interface = mysql_fetch_array($interface_query);

 $port_details = 1;

 $hostname = $device['hostname'];
 $hostid   = $device['interface_id'];
 $ifname   = $interface['ifDescr'];
 $ifIndex   = $interface['ifIndex'];
 $speed = humanspeed($interface['ifSpeed']);

 $ifalias = $interface['name'];

 if($interface['ifPhysAddress']) { $mac = "$interface[ifPhysAddress]"; } 

 $color = "black";
 if ($interface['ifAdminStatus'] == "down") { $status = "<span class='grey'>Disabled</span>"; }
 if ($interface['ifAdminStatus'] == "up" && $interface['ifOperStatus'] == "down") { $status = "<span class='red'>Enabled / Disconnected</span>"; }
 if ($interface['ifAdminStatus'] == "up" && $interface['ifOperStatus'] == "up") { $status = "<span class='green'>Enabled / Connected</span>"; }

 $i = 1; 
 $inf = fixifName($ifname);

 $bg="#ffffff";

 $show_all = 1;

 echo("<div class=ifcell style='margin: 0px;'><table width=100% cellpadding=10 cellspacing=0>");

   include("includes/print-interface.inc.php");

 echo("</table></div>");

 $pos = strpos(strtolower($ifname), "vlan");
 if( $pos !== false ) {
  $broke = yes;
 }
 $pos = strpos(strtolower($ifname), "loopback");
 if( $pos !== false ) {
   $broke = yes;
 }

 if(mysql_result(mysql_query("SELECT COUNT(*) FROM `ports` WHERE `pagpGroupIfIndex` = '".$interface['ifIndex']."' and `device_id` = '".$device['device_id']."'"),0)) {
  $pagp = " | <a href='".$config['base_url']."/device/" . $device['device_id'] . "/interface/".$interface['interface_id']."/pagp/'>PAgP</a>";
 }

echo("<div style='clear: both;'>");

print_optionbar_start();

echo ("<a href='".$config['base_url']."/device/" . $device['device_id'] . "/interface/".$interface['interface_id']."/'>Graphs</a> | 
<a href='".$config['base_url']."/device/" . $device['device_id'] . "/interface/".$interface['interface_id']."/arp/'>ARP Table</a>$pagp");



 if(mysql_result(mysql_query("SELECT count(*) FROM mac_accounting WHERE interface_id = '".$interface['interface_id']."'"),0) > "0"){
   echo(" | Mac Accounting : 
   <a href='".$config['base_url']."/device/" . $device['device_id'] . "/interface/".$interface['interface_id']."/macaccounting/bits/'>Bits</a> 
   (<a href='".$config['base_url']."/device/" . $device['device_id'] . "/interface/".$interface['interface_id']."/macaccounting/bits/thumbs/'>Mini</a>|<a href='".$config['base_url']."/device/" . $device['device_id'] . "/interface/".$interface['interface_id']."/macaccounting/bits/top10/'>Top10</a>) | 
   <a href='".$config['base_url']."/device/" . $device['device_id'] . "/interface/".$interface['interface_id']."/macaccounting/pkts/'>Packets</a>
   (<a href='".$config['base_url']."/device/" . $device['device_id'] . "/interface/".$interface['interface_id']."/macaccounting/pkts/thumbs/'>Mini</a>)");
  }


 if(mysql_result(mysql_query("SELECT COUNT(*) FROM juniAtmVp WHERE interface_id = '".$interface['interface_id']."'"),0) > "0"){
   echo(" | ATM VPs :");
   echo(" <a href='".$config['base_url']."/device/" . $device['device_id'] . "/interface/".$interface['interface_id']."/junose-atm-vp/bits/'>Bits</a>");
   echo(" | <a href='".$config['base_url']."/device/" . $device['device_id'] . "/interface/".$interface['interface_id']."/junose-atm-vp/packets/'>Packets</a>");
   echo(" | <a href='".$config['base_url']."/device/" . $device['device_id'] . "/interface/".$interface['interface_id']."/junose-atm-vp/cells/'>Cells</a>");
   echo(" | <a href='".$config['base_url']."/device/" . $device['device_id'] . "/interface/".$interface['interface_id']."/junose-atm-vp/errors/'>Errors</a>");

  }


print_optionbar_end();

include("pages/device/port/".mres($_GET['optb']).".inc.php");

?>
