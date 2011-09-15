<?php

if ($_GET['optc'] == 'graphs')
{
  if ($_GET['optd']) { $graph_type = "port_" . $_GET['optd']; } else { $graph_type = "port_bits"; }
}

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

if (!$_GET['optc']) { $_GET['optc'] = "basic"; }

$sep = "";
foreach ($menu_options as $option => $text)
{
  echo($sep);
  if ($_GET['optc'] == $option) { echo("<span class='pagemenu-selected'>"); }
  echo('<a href="'.$config['base_url'].'/device/' . $device['device_id'] . '/ports/' . $option . ($_GET['optd'] ? '/' . $_GET['optd'] : ''). '/">' . $text . '</a>');
  if ($_GET['optc'] == $option) { echo("</span>"); }
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
  if ($_GET['optd'] == $type && $_GET['opte'] != "thumbs") { echo("<span class='pagemenu-selected'>"); }
  echo('<a href="'.$config['base_url'].'/device/' . $device['device_id'] . '/ports/graphs/'.$type.'/">'.$descr.'</a>');
  if ($_GET['optd'] == $type && $_GET['opte'] != "thumbs") { echo("</span>"); }

  echo('(');
  if ($_GET['optd'] == $type && $_GET['opte'] == "thumbs") { echo("<span class='pagemenu-selected'>"); }
  echo('<a href="'.$config['base_url'].'/device/' . $device['device_id'] . '/ports/graphs/'.$type.'/thumbs/">Mini</a>');
  if ($_GET['optd'] == $type && $_GET['opte'] == "thumbs") { echo("</span>"); }
  echo(')');
  $type_sep = " | ";
}

print_optionbar_end();

if ($_GET['opte'] == thumbs)
{
  $timeperiods = array('-1day','-1week','-1month','-1year');
  $from = '-1day';
  echo("<div style='display: block; clear: both; margin: auto; min-height: 500px;'>");
  unset ($seperator);
  foreach (dbFetchRows("select * from ports WHERE device_id = ? ORDER BY ifIndex", array($device['device_id'])) as $interface)
  {
    echo("<div style='display: block; padding: 3px; margin: 3px; min-width: 183px; max-width:183px; min-height:90px; max-height:90px; text-align: center; float: left; background-color: #e9e9e9;'>
    <div style='font-weight: bold;'>".makeshortif($interface['ifDescr'])."</div>
    <a href='device/".$device['device_id']."/port/".$interface['interface_id']."/' onmouseover=\"return overlib('\
    <div style=\'font-size: 16px; padding:5px; font-weight: bold; color: #e5e5e5;\'>".$device['hostname']." - ".$interface['ifDescr']."</div>\
    ".$interface['ifAlias']." \
    <img src=\'graph.php?type=$graph_type&amp;id=".$interface['interface_id']."&amp;from=".$from."&amp;to=".$now."&amp;width=450&amp;height=150\'>\
    ', CENTER, LEFT, FGCOLOR, '#e5e5e5', BGCOLOR, '#e5e5e5', WIDTH, 400, HEIGHT, 150);\" onmouseout=\"return nd();\"  >".
    "<img src='graph.php?type=$graph_type&amp;id=".$interface['interface_id']."&amp;from=".$from."&amp;to=".$now."&amp;width=180&amp;height=45&amp;legend=no'>
    </a>
    <div style='font-size: 9px;'>".truncate(short_port_descr($interface['ifAlias']), 32, '')."</div>
    </div>");
  }
  echo("</div>");
} else {
  if ($_GET['optc'] == "arp" || $_GET['optc'] == "adsl" || $_GET['optc'] == "neighbours")
  {
    include("ports/".$_GET['optc'].".inc.php");
  } else {
    if ($_GET['optc'] == "details") { $port_details = 1; }
    echo("<div style='margin: 0px;'><table border=0 cellspacing=0 cellpadding=5 width=100%>");
    $i = "1";

    global $port_cache;
    global $port_index_cache;  

    $ports = dbFetchRows("SELECT * FROM `ports` WHERE `device_id` = ? AND `deleted` = '0' ORDER BY `ifIndex` ASC", array($device['device_id'])); 
    ### As we've dragged the whole database, lets pre-populate our caches :)
    ### FIXME - we should probably split the fetching of link/stack/etc into functions and cache them here too to cut down on single row queries.
    foreach($ports as $port)
    {
      $port_cache[$port['interface_id']] = $port;
      $port_index_cache[$port['device_id']][$port['ifIndex']] = $port; 
    }

    foreach ($ports as $interface)
    {
      include("includes/print-interface.inc.php");
      $i++;
    }
    echo("</table></div>");
  }
}

?>
