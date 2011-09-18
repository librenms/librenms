<?php

### Sections are printed in the order they exist in $config['graph_tabs']
### Graphs are printed in the order they exist in $config['graph_types']

$link_array = array('page'    => 'device',
                    'device'  => $device['device_id'],
                    'tab' => 'graphs');

$bg="#ffffff";

echo('<div style="clear: both;">');

print_optionbar_start('', '');

echo("<span style='font-weight: bold;'>Graphs</span> &#187; ");

$sep = "";

foreach (dbFetchRows("SELECT * FROM device_graphs WHERE device_id = ?", array($device['device_id'])) as $graph)
{
  $tab = $config['graph_types']['device'][$graph['graph']]['tab'];
  $graph_enable[$tab][$graph['graph']] = $graph['graph'];
}

foreach ($config['graph_tabs'] as $tab)
{
  if (isset($graph_enable) && is_array($graph_enable[$tab]))
  {
    $type = strtolower($tab);
    if (!$_GET['optc']) { $_GET['optc'] = $type; }
    echo($sep);
    if ($_GET['optc'] == $type)
    {
      echo('<span class="pagemenu-selected">');
    }

    echo("<a href='device/".$device['device_id']."/graphs/" . $type . ($_GET['optd'] ? "/" . $_GET['optd'] : ''). "/'> " . ucfirst($type) ."</a>");
    if ($_GET['optc'] == $type) 
    { 
      echo("</span>"); 
    }
    $sep = " | ";
  }
}

unset ($sep);
print_optionbar_end();

$graph_enable = $graph_enable[$_GET['optc']];

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
