<?

$interface_query = mysql_query("select * from interfaces WHERE id = '$_GET[id]'");
$interface = mysql_fetch_array($interface_query);

 $device_query = mysql_query("select * from devices where id = '$interface[host]'");
 $device = mysql_fetch_array($device_query);

 $hostname = $device['hostname'];
 $hostid   = $device['id'];
 $ifname   = $interface['if'];
 $up = $interface['up'];
 $up_admin = $interface['up_admin'];
 $ifIndex   = $interface['ifIndex'];
 $speed = humanspeed($interface['ifSpeed']);

 $ifalias = $interface['name'];

 if($interface['ifPhysAddress']) { $mac = "$interface[ifPhysAddress]"; } 

 $color = "black";
 if ($up_admin == "down") { $status = "<span class='grey'>Disabled</span>"; }
 if ($up_admin == "up" && $up == "down") { $status = "<span class='red'>Enabled / Disconnected</span>"; }
 if ($up_admin == "up" && $up == "up") { $status = "<span class='green'>Enabled / Connected</span>"; }

 $i = 1; 
 $inf = fixifName($ifname);

# $bg="#ffffff";
 echo("<table cellpadding=7 cellspacing=0 class=devicetable width=100%>");
 include("includes/device-header.inc");
 echo("</table>");

 $show_all = 1;

 echo("<div class=ifcell style='margin: 5px;'><table width=100% cellpadding=10 border=0 cellspacing=0>");

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

if( !$broke)
{ }

echo("<div style='clear: both;'>");

if(file_exists("rrd/" . $hostname . ".". $ifIndex . ".rrd")) {

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

?>
