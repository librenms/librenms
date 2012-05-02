<?php

if ($vars['view'] == 'graphs' || $vars['view'] == 'minigraphs')
{
  if (isset($vars['graph'])) { $graph_type = "port_" . $vars['graph']; } else { $graph_type = "port_bits"; }
}

if (!$vars['view']) { $vars['view'] = trim($config['ports_page_default'],'/'); }

$link_array = array('page'    => 'device',
                    'device'  => $device['device_id'],
                    'tab' => 'ports');

print_optionbar_start();

$menu_options['basic']   = 'Basic';
$menu_options['details'] = 'Details';
$menu_options['arp']     = 'ARP Table';

if(dbFetchCell("SELECT * FROM links AS L, ports AS I WHERE I.device_id = '".$device['device_id']."' AND I.interface_id = L.local_interface_id"))
{
  $menu_options['neighbours'] = 'Neighbours';
}
if(dbFetchCell("SELECT COUNT(*) FROM `ports` WHERE `ifType` = 'adsl'"))
{
  $menu_options['adsl'] = 'ADSL';
}

$sep = "";
foreach ($menu_options as $option => $text)
{
  echo($sep);
  if ($vars['view'] == $option) { echo("<span class='pagemenu-selected'>"); }
  echo(generate_link($text,$link_array,array('view'=>$option)));
  if ($vars['view'] == $option) { echo("</span>"); }
  $sep = " | ";
}

unset($sep);

echo(' | Graphs: ');

$graph_types = array("bits" => "Bits",
                     "upkts" => "Unicast Packets",
                     "nupkts" => "Non-Unicast Packets",
                     "errors" => "Errors",
                     "etherlike" => "Etherlike");

foreach ($graph_types as $type => $descr)
{
  echo("$type_sep");
  if ($vars['graph'] == $type && $vars['view'] == "graphs") { echo("<span class='pagemenu-selected'>"); }
  echo(generate_link($descr,$link_array,array('view'=>'graphs','graph'=>$type)));
  if ($vars['graph'] == $type && $vars['view'] == "graphs") { echo("</span>"); }

  echo(' (');
  if ($vars['graph'] == $type && $vars['view'] == "minigraphs") { echo("<span class='pagemenu-selected'>"); }
  echo(generate_link('Mini',$link_array,array('view'=>'minigraphs','graph'=>$type)));
  if ($vars['graph'] == $type && $vars['view'] == "minigraphs") { echo("</span>"); }
  echo(')');
  $type_sep = " | ";
}

print_optionbar_end();

if ($vars['view'] == 'minigraphs')
{
  $timeperiods = array('-1day','-1week','-1month','-1year');
  $from = '-1day';
  echo("<div style='display: block; clear: both; margin: auto; min-height: 500px;'>");
  unset ($seperator);

  ## FIX THIS. UGLY.

  foreach (dbFetchRows("select * from ports WHERE device_id = ? ORDER BY ifIndex", array($device['device_id'])) as $port)
  {
    echo("<div style='display: block; padding: 3px; margin: 3px; min-width: 183px; max-width:183px; min-height:90px; max-height:90px; text-align: center; float: left; background-color: #e9e9e9;'>
    <div style='font-weight: bold;'>".makeshortif($port['ifDescr'])."</div>
    <a href=\"" . generate_port_url($port) . "\" onmouseover=\"return overlib('\
    <div style=\'font-size: 16px; padding:5px; font-weight: bold; color: #e5e5e5;\'>".$device['hostname']." - ".$port['ifDescr']."</div>\
    ".$port['ifAlias']." \
    <img src=\'graph.php?type=".$graph_type."&amp;id=".$port['interface_id']."&amp;from=".$from."&amp;to=".$now."&amp;width=450&amp;height=150\'>\
    ', CENTER, LEFT, FGCOLOR, '#e5e5e5', BGCOLOR, '#e5e5e5', WIDTH, 400, HEIGHT, 150);\" onmouseout=\"return nd();\"  >".
    "<img src='graph.php?type=".$graph_type."&amp;id=".$port['interface_id']."&amp;from=".$from."&amp;to=".$now."&amp;width=180&amp;height=45&amp;legend=no'>
    </a>
    <div style='font-size: 9px;'>".truncate(short_port_descr($port['ifAlias']), 32, '')."</div>
    </div>");
  }
  echo("</div>");
} elseif ($vars['view'] == "arp" || $vars['view'] == "adsl" || $vars['view'] == "neighbours") {
  include("ports/".$vars['view'].".inc.php");
} else {
  if ($vars['view'] == "details") { $port_details = 1; }
  echo("<div style='margin: 0px;'><table border=0 cellspacing=0 cellpadding=5 width=100%>");
  $i = "1";

  global $port_cache, $port_index_cache;

  $ports = dbFetchRows("SELECT * FROM `ports` WHERE `device_id` = ? AND `deleted` = '0' ORDER BY `ifIndex` ASC", array($device['device_id']));
  ### As we've dragged the whole database, lets pre-populate our caches :)
  ### FIXME - we should probably split the fetching of link/stack/etc into functions and cache them here too to cut down on single row queries.
  foreach ($ports as $port)
  {
    $port_cache[$port['interface_id']] = $port;
    $port_index_cache[$port['device_id']][$port['ifIndex']] = $port;
  }

  foreach ($ports as $port)
  {

    if ($config['memcached']['enable'])
{
  $oids = array('ifInOctets', 'ifOutOctets', 'ifInUcastPkts', 'ifOutUcastPkts', 'ifInErrors', 'ifOutErrors');
  foreach($oids as $oid)
  {
    $port[$oid.'_rate'] = $memcache->get('port-'.$port['interface_id'].'-'.$oid.'_rate');
    if($debug) { echo("MC[".$oid."->".$port[$oid.'_rate']."]"); }
  }
}


    include("includes/print-interface.inc.php");
    $i++;
  }
  echo("</table></div>");
}

$pagetitle[] = "Ports";

?>
