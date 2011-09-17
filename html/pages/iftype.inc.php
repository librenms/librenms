<table cellpadding="7" cellspacing="0" class="devicetable" width="100%">

<?php

if ($bg == "#ffffff") { $bg = "#e5e5e5"; } else { $bg = "#ffffff"; }

$type_where = " (";
foreach (explode(",", $_GET['opta']) as $type)
{
  $type_where .= " $or `port_descr_type` = ?";
  $or = "OR";
  $type_param[] = $type; 
}

$type_where .= ") ";
$ports = dbFetchRows("SELECT * FROM `ports` as I, `devices` AS D WHERE $type_where AND I.device_id = D.device_id ORDER BY I.ifAlias", $type_param);


foreach ($ports as $interface)
{
  $if_list .= $seperator . $interface['interface_id'];
  $seperator = ",";
}
unset($seperator);

$types_array = explode(',',$_GET['opta']);
for ($i = 0; $i < count($types_array);$i++) $types_array[$i] = ucfirst($types_array[$i]);
$types = implode(' + ',$types_array);

echo("<tr bgcolor='$bg'>
             <td><span class=list-large>Total Graph for ports of type : ".$types."</span></td></tr>");

if ($if_list)
{
  echo("<tr bgcolor='$bg'><td>");
  $graph_type = "multiport_bits";
  $interface['interface_id'] = $if_list;
  include("includes/print-interface-graphs.inc.php");
  echo("</td></tr>");

  foreach ($ports as $interface)
  {
    $done = "yes";
    unset($class);
    $interface['ifAlias'] = str_ireplace($type . ": ", "", $interface['ifAlias']);
    $interface['ifAlias'] = str_ireplace("[PNI]", "Private", $interface['ifAlias']);
    $ifclass = ifclass($interface['ifOperStatus'], $interface['ifAdminStatus']);
    if ($bg == "#ffffff") { $bg = "#e5e5e5"; } else { $bg = "#ffffff"; }
    echo("<tr bgcolor='$bg'>
             <td><span class=list-large>" . generate_port_link($interface,$interface['port_descr_descr']) . "</span><br />
            <span class=interface-desc style='float: left;'>".generate_device_link($interface)." ".generate_port_link($interface)." </span>");

    if (dbFetchCell("SELECT count(*) FROM mac_accounting WHERE interface_id = ?", array($interface['interface_id'])))
    {
      echo("<span style='float: right;'><a href='device/".$interface['device_id']."/port/".$interface['interface_id']."/macaccounting/'><img src='/images/16/chart_curve.png' align='absmiddle'> MAC Accounting</a></span>");
    }

    echo("</td></tr><tr bgcolor='$bg'><td>");

    if (file_exists($config['rrd_dir'] . "/" . $interface['hostname'] . "/port-" . $interface['ifIndex'] . ".rrd"))
    {
      $graph_type = "port_bits";
      include("includes/print-interface-graphs.inc.php");
    }

    echo("</td></tr>");
  }

}
else
{
  echo("<tr><td>None found.</td></tr>");
}

?>
</table>
