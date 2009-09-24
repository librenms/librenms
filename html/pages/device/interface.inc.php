<?php

$interface_query = mysql_query("select * from interfaces WHERE interface_id = '".$_GET['opta']."'");
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

   include("includes/print-interface.inc");

 echo("</table></div>");

 $pos = strpos(strtolower($ifname), "vlan");
 if( $pos !== false ) {
  $broke = yes;
 }
 $pos = strpos(strtolower($ifname), "loopback");
 if( $pos !== false ) {
   $broke = yes;
 }

echo("<div style='clear: both;'>");

echo("
<div style='margin:auto; text-align: center; margin-top: 0px; margin-bottom: 10px;'>
  <b class='rounded'>
  <b class='rounded1'><b></b></b>
  <b class='rounded2'><b></b></b>
  <b class='rounded3'></b>
  <b class='rounded4'></b>
  <b class='rounded5'></b></b>
  <div class='roundedfg' style='padding: 0px 5px;'>
  <div style='margin: auto; text-align: left; padding: 2px 5px; padding-left: 11px; clear: both; display:block; height:20px;'>

<a href='".$config['base_url']."/device/" . $device['device_id'] . "/interface/".$interface['interface_id']."/'>Graphs</a> | 
<a href='".$config['base_url']."/device/" . $device['device_id'] . "/interface/".$interface['interface_id']."/arp/'>ARP Table</a>");

 if(mysql_result(mysql_query("SELECT count(*) FROM mac_accounting WHERE interface_id = '".$interface['interface_id']."'"),0)){

   echo(" | Mac Accounting : 
   <a href='".$config['base_url']."/device/" . $device['device_id'] . "/interface/".$interface['interface_id']."/macaccounting/bits/'>Bits</a> 
   (<a href='".$config['base_url']."/device/" . $device['device_id'] . "/interface/".$interface['interface_id']."/macaccounting/bits/thumbs/'>Mini</a>|<a href='".$config['base_url']."/device/" . $device['device_id'] . "/interface/".$interface['interface_id']."/macaccounting/bits/top10/'>Top10</a>) | 
   <a href='".$config['base_url']."/device/" . $device['device_id'] . "/interface/".$interface['interface_id']."/macaccounting/pkts/'>Packets</a>
   (<a href='".$config['base_url']."/device/" . $device['device_id'] . "/interface/".$interface['interface_id']."/macaccounting/pkts/thumbs/'>Mini</a>)");
  }

echo("</div>
</div>
  <b class='rounded'>
  <b class='rounded5'></b>
  <b class='rounded4'></b>
  <b class='rounded3'></b>
  <b class='rounded2'><b></b></b>
  <b class='rounded1'><b></b></b></b>
</div>
");

if($_GET['optb']) {

include("pages/device/port/".mres($_GET['optb']).".inc.php");

} else {

  if(file_exists("rrd/" . $hostname . "/". $ifIndex . ".rrd")) {

    $iid = $id;
    echo("<div class=graphhead>Interface Traffic</div>");
    $graph_type = "bits";
    include("includes/print-interface-graphs.php");

    echo("<div class=graphhead>Interface Packets</div>");
    $graph_type = "pkts";
    include("includes/print-interface-graphs.php");

    echo("<div class=graphhead>Interface Non Unicast</div>");
    $graph_type = "nupkts";
    include("includes/print-interface-graphs.php");

    echo("<div class=graphhead>Interface Errors</div>");
    $graph_type = "errors";
    include("includes/print-interface-graphs.php");

  }
 
}


?>
