<?php

$bg="#ffffff";

echo('<div style="clear: both;">');


print_optionbar_start('', '');

$sep = "";
$query = mysql_query("SELECT graph_section FROM device_graphs AS D, graph_types AS G WHERE D.device_id = '".$device['device_id']."' AND G.graph_subtype = D.graph AND G.graph_type = 'device' GROUP BY G.graph_section ORDER BY graph_section");
while($section = mysql_fetch_assoc($query))
{
  $type = strtolower($section['graph_section']);
  if(!$_GET['opta']) { $_GET['opta'] = $type; }
  echo($sep);
  if ($_GET['opta'] == $type)
  {
    echo("<strong>");
    echo('<img src="images/icons/'.$type.'.png" class="optionicon" />');
  }
  else
  {
    echo('<img src="images/icons/greyscale/'.$type.'.png" class="optionicon" />');
  }
  echo("<a href='".$config['base_url']."/device/".$device['device_id']."/graphs/" . $type . ($_GET['optb'] ? "/" . $_GET['optb'] : ''). "/'> " . $type ."</a>\n");
  if ($_GET['opta'] == $type) { echo("</strong>"); }
  $sep = " | ";
}
unset ($sep);
print_optionbar_end();

$sql  = "SELECT * FROM device_graphs AS D, graph_types AS G WHERE D.device_id = '".$device['device_id']."'";
$sql .=" AND G.graph_subtype = D.graph AND G.graph_type = 'device' AND G.graph_section = '".mres($_GET['opta'])."' ORDER BY graph_order, graph_subtype";
$query = mysql_query($sql);
while($graph = mysql_fetch_assoc($query))
{

  $graph_title = $graph['graph_descr'];
  $graph_type = "device_" . $graph['graph_subtype'];
  include ("includes/print-device-graph.php");

}

?>

