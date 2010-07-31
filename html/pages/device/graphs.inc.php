<?php

### Sections are printed in the order they exist in $config['graph_sections']
### Graphs are printed in the order they exist in $config['graph_types']

$bg="#ffffff";

echo('<div style="clear: both;">');

print_optionbar_start('', '');

$sep = "";
$query = mysql_query("SELECT * FROM device_graphs WHERE device_id = '".$device['device_id']."'");
while($graph = mysql_fetch_assoc($query))
{
  $section = $config['graph_types']['device'][$graph['graph']]['section'];
  $graph_enable[$section][$graph['graph']] = $graph['graph'];
}

foreach($config['graph_sections'] as $section) 
{
  if(is_array($graph_enable[$section])) 
  {
    $type = strtolower($section);
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
}
unset ($sep);
print_optionbar_end();

#echo("<pre>");
#print_r($_GET['opta']);
#print_r($graph_enable);
#echo("</pre>");

$graph_enable = $graph_enable[$_GET['opta']];

foreach($config['graph_types']['device'] as $graph => $entry)
{
  if($graph_enable[$graph]) 
  {
    $graph_title = $config['graph_types']['device'][$graph]['descr'];
    $graph_type = "device_" . $graph;
    include ("includes/print-device-graph.php");
  }
}

?>

