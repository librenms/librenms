<table cellpadding="7" cellspacing="0" class="devicetable" width="100%">

<?php

if ($bg == "#ffffff") { $bg = "#e5e5e5"; } else { $bg = "#ffffff"; }

$type_where = " (";
foreach (explode(",", $vars['type']) as $type)
{
  $type_where .= " $or `port_descr_type` = ?";
  $or = "OR";
  $type_param[] = $type;
}

$type_where .= ") ";
$ports = dbFetchRows("SELECT * FROM `ports` as I, `devices` AS D WHERE $type_where AND I.device_id = D.device_id ORDER BY I.ifAlias", $type_param);

foreach ($ports as $port)
{
  $if_list .= $seperator . $port['port_id'];
  $seperator = ",";
}
unset($seperator);

$types_array = explode(',',$vars['type']);
for ($i = 0; $i < count($types_array);$i++) $types_array[$i] = ucfirst($types_array[$i]);
$types = implode(' + ',$types_array);

echo("<tr class='iftype'>
             <td><span class=list-large>Total Graph for ports of type : ".$types."</span><br />");

if ($if_list)
{
  $graph_type = "multiport_bits_separate";
  $port['port_id'] = $if_list;

  include("includes/print-interface-graphs.inc.php");

  echo("</td></tr>");

  foreach ($ports as $port)
  {
    $done = "yes";
    unset($class);
    $port['ifAlias'] = str_ireplace($type . ": ", "", $port['ifAlias']);
    $port['ifAlias'] = str_ireplace("[PNI]", "Private", $port['ifAlias']);
    $ifclass = ifclass($port['ifOperStatus'], $port['ifAdminStatus']);
    if ($bg == "#ffffff") { $bg = "#e5e5e5"; } else { $bg = "#ffffff"; }
    echo("<tr class='iftype'>
             <td><span class=list-large>" . generate_port_link($port,$port['port_descr_descr']) . "</span><br />
            <span class=interface-desc style='float: left;'>".generate_device_link($port)." ".generate_port_link($port)." </span>");

    if (dbFetchCell("SELECT count(*) FROM mac_accounting WHERE port_id = ?", array($port['port_id'])))
    {
      echo("<span style='float: right;'><a href='device/device=".$port['device_id']."/tab=port/port=".$port['port_id']."/view=macaccounting/'><img src='/images/16/chart_curve.png' align='absmiddle'> MAC Accounting</a></span>");
    }

    echo('<br />');

    if (file_exists($config['rrd_dir'] . "/" . $port['hostname'] . "/port-" . $port['ifIndex'] . ".rrd"))
    {
      $graph_type = "port_bits";

      include("includes/print-interface-graphs.inc.php");
    }

    echo("</td></tr>");
  }

}
else
{
  echo("None found.</td></tr>");
}

?>
</table>
