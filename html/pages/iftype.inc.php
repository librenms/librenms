<?php
if($bg == "#ffffff") { $bg = "#e5e5e5"; } else { $bg="#ffffff"; }
echo("<table cellpadding=7 cellspacing=0 class=devicetable width=100%>");

#if($_GET['type']) {

  $type_where = " (";
  foreach(split(",", $_GET['opta']) as $type) {
    $type_where .= " $or `ifAlias` like '$type: %' ";
    $or = "OR";
  }
  $type_where .= ") ";

  $sql  = "select * from ports as I, devices as D WHERE $type_where AND I.device_id = D.device_id ORDER BY I.ifAlias";
  $query = mysql_query($sql);
  while($interface = mysql_fetch_array($query)) {
    $if_list .= $seperator . $interface['interface_id'];
    $seperator = ",";
  }
  unset($seperator);

  $types_array = explode(',',$_GET['opta']);
  for ($i = 0; $i < count($types_array);$i++) $types_array[$i] = ucfirst($types_array[$i]);
  $types = implode(' + ',$types_array);
  
  echo("<tr bgcolor='$bg'>
             <td><span class=list-large>Total Graph for ports of type : ".$types."</span></td></tr>");

  echo("<tr bgcolor='$bg'><td>");
  $graph_type = "multi_bits";
  $interface['interface_id'] = $if_list;
  include("includes/print-interface-graphs.inc.php");
  echo("</td></tr>");


  $sql  = "select * from ports as I, devices as D WHERE $type_where AND I.device_id = D.device_id ORDER BY I.ifAlias";
  $query = mysql_query($sql);
  while($interface = mysql_fetch_array($query)) {
    $done = "yes";
    unset($class);
    $interface['ifAlias'] = str_ireplace($type . ": ", "", $interface['ifAlias']);
    $interface['ifAlias'] = str_ireplace("[PNI]", "Private", $interface['ifAlias']);
    $ifclass = ifclass($interface['ifOperStatus'], $interface['ifAdminStatus']);
    if($bg == "#ffffff") { $bg = "#e5e5e5"; } else { $bg="#ffffff"; }
    echo("<tr bgcolor='$bg'>
             <td><span class=list-large>" . generateiflink($interface,$interface['ifAlias']) . "</span><br /> 
            <span class=interface-desc style='float: left;'>".generatedevicelink($interface)." ".generateiflink($interface)." </span>");

    if(mysql_result(mysql_query("SELECT count(*) FROM mac_accounting WHERE interface_id = '".$interface['interface_id']."'"),0)){
      echo("<span style='float: right;'><a href='".$config['base_url']."/device/".$interface['device_id']."/interface/".$interface['interface_id']."/macaccounting/'><img src='/images/16/chart_curve.png' align='absmiddle'> MAC Accounting</a></span>");
    }


    echo("</td></tr><tr bgcolor='$bg'><td>");

if(file_exists($config['rrd_dir'] . "/" . $interface['hostname'] . "/" . $interface['ifIndex'] . ".rrd")) {

    $graph_type = "port_bits";
    include("includes/print-interface-graphs.inc.php");

}
      echo("</td></tr>");
  }
#}

echo("</table>");

if(!$done) { echo("None found."); }

?>
