<?php
/*
 * LibreNMS front page top ports graph
 * - Find most utilised ports that have been polled in the last N minutes
 *
 * Copyright (c) 2013 Gear Consulting Pty Ltd <http://libertysys.com.au/>
 *
 * This program is free software: you can redistribute it and/or modify it
 * under the terms of the GNU General Public License as published by the
 * Free Software Foundation, either version 3 of the License, or (at your
 * option) any later version.  Please see LICENSE.txt at the top level of
 * the source code distribution for details.
 */

$minutes = 15;
$seconds = $minutes * 60;
$top = $config['front_page_settings']['top']['ports'];
$query = "
  SELECT *, p.ifInOctets_rate + p.ifOutOctets_rate as total
  FROM ports as p, devices as d
  WHERE d.device_id = p.device_id
    AND unix_timestamp() - p.poll_time < $seconds
    AND ( p.ifInOctets_rate > 0
    OR p.ifOutOctets_rate > 0 )
  ORDER BY total desc
  LIMIT $top
";

echo("<strong>Top $top ports (last $minutes minutes)</strong>");
echo('<table class="simple">');
foreach (dbFetchRows($query) as $result) {
  echo('<tr><td>'.
    generate_device_link($result, shorthost($result['hostname'])).
    '</td><td>'.generate_port_link($result).
    '</td><td>'.generate_port_link($result, generate_port_thumbnail($result)).'</td></tr>');
}
echo('</table>');

/*
// FIXME: Change to port_rrd_exists($device, $port)
if (file_exists($config['rrd_dir'] . "/" . $device['hostname'] . "/port-". $port['ifIndex'] . ".rrd"))
{
  $iid = $id;
  echo("<div class=graphhead>Interface Traffic</div>");
  $graph_type = "port_bits";

  include("includes/print-interface-graphs.inc.php");

  echo("<div class=graphhead>Interface Packets</div>");
  $graph_type = "port_upkts";

  include("includes/print-interface-graphs.inc.php");

  echo("<div class=graphhead>Interface Non Unicast</div>");
  $graph_type = "port_nupkts";

  include("includes/print-interface-graphs.inc.php");

  echo("<div class=graphhead>Interface Errors</div>");
  $graph_type = "port_errors";

  include("includes/print-interface-graphs.inc.php");

  if (is_file($config['rrd_dir'] . "/" . $device['hostname'] . "/port-" . $port['ifIndex'] . "-dot3.rrd"))
  {
    echo("<div class=graphhead>Ethernet Errors</div>");
    $graph_type = "port_etherlike";

    include("includes/print-interface-graphs.inc.php");
  }
}
*/

/* from html/includes/print-interface.inc.php:

generate_port_link($port, $port['ifIndex'] . ". ".$port['label'])

*/

/* from html/includes/functions.inc.php:

function generate_port_link($port, $text = NULL, $type = NULL)
{
  global $config;

  $port = ifNameDescr($port);
  if (!$text) { $text = fixIfName($port['label']); }
  if ($type) { $port['graph_type'] = $type; }
  if (!isset($port['graph_type'])) { $port['graph_type'] = 'port_bits'; }

  $class = ifclass($port['ifOperStatus'], $port['ifAdminStatus']);

  if (!isset($port['hostname'])) { $port = array_merge($port, device_by_id_cache($port['device_id'])); }

  $content = "<div class=list-large>".$port['hostname']." - " . fixifName($port['label']) . "</div>";
  if ($port['ifAlias']) { $content .= $port['ifAlias']."<br />"; }
  $content .= "<div style=\'width: 850px\'>";
  $graph_array['type']     = $port['graph_type'];
  $graph_array['legend']   = "yes";
  $graph_array['height']   = "100";
  $graph_array['width']    = "340";
  $graph_array['to']           = $config['time']['now'];
  $graph_array['from']     = $config['time']['day'];
  $graph_array['id']       = $port['port_id'];
  $content .= generate_graph_tag($graph_array);
  $graph_array['from']     = $config['time']['week'];
  $content .= generate_graph_tag($graph_array);
  $graph_array['from']     = $config['time']['month'];
  $content .= generate_graph_tag($graph_array);
  $graph_array['from']     = $config['time']['year'];
  $content .= generate_graph_tag($graph_array);
  $content .= "</div>";

  $url = generate_port_url($port);

  if (port_permitted($port['port_id'], $port['device_id'])) {
    return overlib_link($url, $text, $content, $class);
  } else {
    return fixifName($text);
  }
}
*/

/* includes/print-interface-graphs.inc.php:

global $config;

$graph_array['height'] = "100";
$graph_array['width']  = "215";
$graph_array['to']     = $config['time']['now'];
$graph_array['id']     = $port['port_id'];
$graph_array['type']   = $graph_type;

include("includes/print-graphrow.inc.php");

*/

/* html/includes/print-graphrow.inc.php:
global $config;

if($_SESSION['widescreen'])
{
  if (!$graph_array['height']) { $graph_array['height'] = "110"; }
  if (!$graph_array['width']) { $graph_array['width']  = "215"; }
  $periods = array('sixhour', 'day', 'week', 'month', 'year', 'twoyear');
} else {
  if (!$graph_array['height']) { $graph_array['height'] = "100"; }
  if (!$graph_array['width']) { $graph_array['width']  = "215"; }
  $periods = array('day', 'week', 'month', 'year');
}

$graph_array['to']     = $config['time']['now'];

foreach ($periods as $period)
{
  $graph_array['from']        = $config['time'][$period];
  $graph_array_zoom           = $graph_array;
  $graph_array_zoom['height'] = "150";
  $graph_array_zoom['width']  = "400";

  $link_array = $graph_array;
  $link_array['page'] = "graphs";
  unset($link_array['height'], $link_array['width']);
  $link = generate_url($link_array);

  echo(overlib_link($link, generate_graph_tag($graph_array), generate_graph_tag($graph_array_zoom),  NULL));
}

function overlib_link($url, $text, $contents, $class)
{
  global $config;

  $contents = str_replace("\"", "\'", $contents);
  $output = '<a class="'.$class.'" href="'.$url.'"';
  $output .= " onmouseover=\"return overlib('".$contents."'".$config['overlib_defaults'].");\" onmouseout=\"return nd();\">";
  $output .= $text."</a>";

  return $output;
}

function print_graph_tag($args)
{
  echo(generate_graph_tag($args));
}

function generate_graph_tag($args)
{

  foreach ($args as $key => $arg)
  {
    $urlargs[] = $key."=".$arg;
  }

  return '<img src="graph.php?' . implode('&amp;',$urlargs).'" border="0" />';
}

function generate_port_url($port, $vars=array())
{
  return generate_url(array('page' => 'device', 'device' => $port['device_id'], 'tab' => 'port', 'port' => $port['port_id']), $vars);
}

function generate_link($text, $vars, $new_vars = array())
{
  return '<a href="'.generate_url($vars, $new_vars).'">'.$text.'</a>';
}

function generate_url($vars, $new_vars = array())
{

  $vars = array_merge($vars, $new_vars);

  $url = $vars['page']."/";
  unset($vars['page']);

  foreach ($vars as $var => $value)
  {
    if ($value == "0" || $value != "" && strstr($var, "opt") === FALSE && is_numeric($var) === FALSE)
    {
      $url .= $var ."=".$value."/";
    }
  }

  return($url);

}

*/

?>
