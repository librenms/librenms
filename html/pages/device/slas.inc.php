<?php

print_optionbar_start();

echo("<span style='font-weight: bold;'>SLA</span> &#187; ");

$slas = dbFetchRows("SELECT * FROM `slas` WHERE `device_id` = ? AND `deleted` = 0 ORDER BY `sla_nr`", array($device['device_id']));

// Collect types
$sla_types = array('all' => 'All');
foreach ($slas as $sla)
{
  $sla_type = $sla['rtt_type'];

  if (!in_array($sla_type, $sla_types))
    if (isset($config['sla_type_labels'][$sla_type]))
    {
      $text = $config['sla_type_labels'][$sla_type];
    }
    else
    {
      $text = ucfirst($sla_type);
    }

    $sla_types[$sla_type] = $text;
}
asort($sla_types);

$sep = "";
foreach ($sla_types as $sla_type => $text)
{
  if (!$vars['view']) { $vars['view'] = $sla_type; }

  echo($sep);
  if ($vars['view'] == $sla_type)
  {
    echo("<span class='pagemenu-selected'>");
  }
  echo(generate_link($text,$vars,array('view'=>$sla_type)));
  if ($vars['view'] == $sla_type)
  {
    echo("</span>");
  }
  $sep = " | ";
}
unset($sep);

print_optionbar_end();

echo('<table>');

foreach ($slas as $sla)
{
  if ($vars['view'] != 'all' && $vars['view'] != $sla['rtt_type'])
    continue;

  $name = "SLA #". $sla['sla_nr'];
  if ($sla['tag'])
    $name .= ": ".$sla['tag'];
  if ($sla['owner'])
    $name .= " (Owner: ". $sla['owner'] .")";

  $graph_array['type'] = "device_sla";
  $graph_array['id'] = $sla['sla_id'];
  echo('<tr><td>');
  echo('<h3>'.htmlentities($name).'</h3>');

  include("includes/print-quadgraphs.inc.php");

  echo('</td></tr>');
}

echo('</table>');

$pagetitle[] = "SLAs";

?>
