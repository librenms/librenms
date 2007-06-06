<?php
if($bg == "#ffffff") { $bg = "#e5e5e5"; } else { $bg="#ffffff"; }
echo("<table cellpadding=7 cellspacing=0 class=devicetable width=100%>");

if($_GET['type']) {
  $type = $_GET['type'];
  $sql  = "select * from interfaces as I, devices as D WHERE `ifAlias` like '$type: %' AND I.device_id = D.device_id ORDER BY I.ifAlias";
  $query = mysql_query($sql);
  while($data = mysql_fetch_array($query)) {
    $done = "yes";
    unset($class);
    $data['ifAlias'] = str_replace($type . ": ", "", $data['ifAlias']);
    $data['ifAlias'] = str_replace("[PNI]", "Private", $data['ifAlias']);
    $ifclass = ifclass($data['ifOperStatus'], $data['ifAdminStatus']);
    if($bg == "#ffffff") { $bg = "#e5e5e5"; } else { $bg="#ffffff"; }
    echo("<tr bgcolor='$bg'>
             <td><span class=interface><a href='?page=interface&id=" . $data['interface_id'] . "'>" . $data['ifAlias'] . "</a></span><br /> 
            <span class=interface-desc><a href='?page=device&id=" . $data['device_id'] . "'>" . $data['hostname'] . "</a> <a href='?page=interface&id=" . $data['interface_id'] . "'>" . $data['ifDescr'] . "</a></span></td></tr><tr bgcolor='$bg'><td>");

if(file_exists("rrd/" . $data['hostname'] . "." . $data['ifIndex'] . ".rrd")) {

    $graph_type = "bits";
    $iid = $data['interface_id'];
    include("includes/print-interface-graphs.php");

}
      echo("</td></tr>");
  }
}

echo("</table>");

if(!$done) { echo("None found."); }

?>
