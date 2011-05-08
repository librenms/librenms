<?php

### Sections are printed in the order they exist in $config['graph_sections']
### Graphs are printed in the order they exist in $config['graph_types']

$bg="#ffffff";

echo('<div style="clear: both;">');

print_optionbar_start('', '');

echo("<span style='font-weight: bold;'>Graphs</span> &#187; ");

$sep = "";
$query = mysql_query("SELECT * FROM device_graphs WHERE device_id = '".$device['device_id']."'");

while ($graph = mysql_fetch_assoc($query))
{
  echo($graph['graph']."</br>");
  $section = $config['graph_types']['device'][$graph['graph']]['section'];
  $graph_enable[$section][$graph['graph']] = $graph['graph'];
}

foreach ($config['graph_sections'] as $section)
{
  if (isset($graph_enable) && is_array($graph_enable[$section]))
  {
    $type = strtolower($section);
    if (!$_GET['opta']) { $_GET['opta'] = $type; }
    echo($sep);
    if ($_GET['opta'] == $type)
    {
      echo('<span class="pagemenu-selected">');
    }

    echo("<a href='device/".$device['device_id']."/graphs/" . $type . ($_GET['optb'] ? "/" . $_GET['optb'] : ''). "/'> " . ucfirst($type) ."</a>");
    if ($_GET['opta'] == $type) 
    { 
      echo("</span>"); 
    }
    $sep = " | ";
  }
}

unset ($sep);
print_optionbar_end();

$graph_enable = $graph_enable[$_GET['opta']];

foreach ($config['graph_types']['device'] as $graph => $entry)
{
  if ($graph_enable[$graph])
  {
    $graph_title = $config['graph_types']['device'][$graph]['descr'];
    $graph_type = "device_" . $graph;
    include("includes/print-device-graph.php");
  }
}

?>
