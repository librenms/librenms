<?php
echo("
<div style='float: right; text-align: right;'>
<a href='".$config['base_url']."/device/" . $device['device_id'] . "/'>Details</a> | Graphs:

<a href='".$config['base_url']."/device/" . $device['device_id'] . "/ports/graphs/bits/'>Bits</a> | 
<a href='".$config['base_url']."/device/" . $device['device_id'] . "/ports/graphs/pkts/'>Packets</a> | 
<a href='".$config['base_url']."/device/" . $device['device_id'] . "/ports/graphs/nupkts/'>NU Packets</a> |
<a href='".$config['base_url']."/device/" . $device['device_id'] . "/ports/graphs/errors/'>Errors</a>
</div>");

$dographs = 1;

if($_GET['opta'] == graphs ) { 
  if($_GET['optb']) {
    $graph_type = $_GET['optb']; 
  } else {
    $graph_type = "bits";
  }
  $dographs = 1;
}

$hostname = gethostbyid($device['device_id']);

echo("<div style='margin: 5px;'><table border=0 cellspacing=0 cellpadding=5 width=100%>");
$i = "1";
$interface_query = mysql_query("select * from interfaces WHERE device_id = '$_GET[id]' AND deleted = '0' ORDER BY `ifDescr` ASC");
while($interface = mysql_fetch_array($interface_query)) {
  include("includes/print-interface.inc");
}
echo("</table></div>");

?>
