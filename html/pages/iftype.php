<?php
if($bg == "#ffffff") { $bg = "#e5e5e5"; } else { $bg="#ffffff"; }
echo("<table cellpadding=7 cellspacing=0 class=devicetable width=100%>");

if($_GET['type']) {
  $type = $_GET['type'];
  $sql  = "select * from interfaces as I, devices as D WHERE `ifAlias` like '$type: %' AND I.device_id = D.device_id ORDER BY I.ifAlias";
  $query = mysql_query($sql);
  while($interface = mysql_fetch_array($query)) {
    $done = "yes";
    unset($class);
    $interface['ifAlias'] = str_replace($type . ": ", "", $interface['ifAlias']);
    $interface['ifAlias'] = str_replace("[PNI]", "Private", $interface['ifAlias']);
    $ifclass = ifclass($interface['ifOperStatus'], $interface['ifAdminStatus']);
    if($bg == "#ffffff") { $bg = "#e5e5e5"; } else { $bg="#ffffff"; }
    echo("<tr bgcolor='$bg'>
             <td><span class=list-large>" . generateiflink($interface,$interface['ifAlias']) . "</span><br /> 
            <span class=interface-desc style='float: left;'>".generatedevicelink($interface)." ".generateiflink($interface)." </span>");

    if(mysql_result(mysql_query("SELECT count(*) FROM mac_accounting WHERE interface_id = '".$interface['interface_id']."'"),0)){
      echo("<span style='float: right;'><a href='/?page=mac-accounting&id=".$interface['interface_id']."'><img src='/images/16/chart_curve.png' align='absmiddle'> MAC Accounting</a></span>");
    }


    echo("</td></tr><tr bgcolor='$bg'><td>");

if(file_exists($rrd_dir . "/" . $interface['hostname'] . "/" . $interface['ifIndex'] . ".rrd")) {

    $graph_type = "bits";
    include("includes/print-interface-graphs.php");

}
      echo("</td></tr>");
  }
}

echo("</table>");

if(!$done) { echo("None found."); }

?>
