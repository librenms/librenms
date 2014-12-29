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

if(dbFetchCell("SELECT COUNT(*) FROM links AS L, ports AS I WHERE I.device_id = ? AND I.port_id = L.local_port_id",array($device['device_id'])))
{
  $menu_options['neighbours'] = 'Neighbours';
}
if(dbFetchCell("SELECT COUNT(*) FROM `ports` WHERE `ifType` = 'adsl'"))
{
  $menu_options['adsl'] = 'ADSL';
}

$urlTmp=array('view'=>'');

if(!empty($vars['vrf-lite'])){
   $urlTmp= array_merge($urlTmp,array('vrf-lite'=>$vars['vrf-lite']));
}

$sep = "";
foreach ($menu_options as $option => $text)
{
  $urlTmp['view']=$option;
  echo($sep);
  if ($vars['view'] == $option) { echo("<span class='pagemenu-selected'>"); }
  echo(generate_link($text,$link_array,$urlTmp));
  if ($vars['view'] == $option) { echo("</span>"); }
  $sep = " | ";
}
unset($urlTmp);

unset($sep);

echo(' | Graphs: ');

$graph_types = array("bits" => "Bits",
                     "upkts" => "Unicast Packets",
                     "nupkts" => "Non-Unicast Packets",
                     "errors" => "Errors",
                     "etherlike" => "Etherlike");
$urlTmp=array('view'=>'graphs','graph'=>$type);
if(!empty($vars['vrf-lite'])){
   $urlTmp= array_merge($urlTmp,array('vrf-lite'=>$vars['vrf-lite']));
}

foreach ($graph_types as $type => $descr)
{
  echo("$type_sep");
  $urlTmp['view']='graphs';
  $urlTmp['graph']=$type;        
  if ($vars['graph'] == $type && $vars['view'] == "graphs") { echo("<span class='pagemenu-selected'>"); }
  echo(generate_link($descr,$link_array,$urlTmp));
  if ($vars['graph'] == $type && $vars['view'] == "graphs") { echo("</span>"); }
  $urlTmp['view']='minigraphs';
  echo(' (');
  if ($vars['graph'] == $type && $vars['view'] == "minigraphs") { echo("<span class='pagemenu-selected'>"); }
  echo(generate_link('Mini',$link_array,$urlTmp));
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

  if(!empty($vars['vrf-lite'])){
      $portsTmp = dbFetchRows("SELECT I.*, I4A.*, VR.vrf_name, VR.intance_name FROM `ports` I LEFT OUTER JOIN ipv4_addresses I4A on I4A.port_id=I.port_id LEFT OUTER JOIN vrf_lite_cisco VR on VR.context_name=I4A.context_name and VR.device_id=I.device_id  where VR.vrf_name=? AND I.device_id=? ORDER BY I.ifIndex ASC", array($device['device_id'],$vars['vrf-lite'],$device['device_id']));
  }else{
      $portsTmp = dbFetchRows("SELECT * FROM `ports` WHERE `device_id` = ? ORDER BY `ifIndex` ASC", array($device['device_id']));
  }
  
  // FIXME - FIX THIS. UGLY.
  foreach ($portsTmp as $port)
  {
    echo("<div style='display: block; padding: 3px; margin: 3px; min-width: 183px; max-width:183px; min-height:90px; max-height:90px; text-align: center; float: left; background-color: #e9e9e9;'>
    <div style='font-weight: bold;'>".makeshortif($port['ifDescr'])."</div>
    <a href=\"" . generate_port_url($port) . "\" onmouseover=\"return overlib('\
    <div style=\'font-size: 16px; padding:5px; font-weight: bold; color: #e5e5e5;\'>".$device['hostname']." - ".$port['ifDescr']."</div>\
    ".$port['ifAlias']." \
    <img src=\'graph.php?type=".$graph_type."&amp;id=".$port['port_id']."&amp;from=".$from."&amp;to=".$config['time']['now']."&amp;width=450&amp;height=150\'>\
    ', CENTER, LEFT, FGCOLOR, '#e5e5e5', BGCOLOR, '#e5e5e5', WIDTH, 400, HEIGHT, 150);\" onmouseout=\"return nd();\"  >".
    "<img src='graph.php?type=".$graph_type."&amp;id=".$port['port_id']."&amp;from=".$from."&amp;to=".$config['time']['now']."&amp;width=180&amp;height=45&amp;legend=no'>
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

  if(!empty($vars['vrf-lite'])){
      $portsTmp = dbFetchRows("SELECT I.*, I4A.*, VR.vrf_name, VR.intance_name FROM `ports` I join ipv4_addresses I4A on I4A.port_id=I.port_id join vrf_lite_cisco VR on VR.context_name=I4A.context_name and VR.device_id=I.device_id  where  I.deleted = '0'  AND I.device_id = ? AND VR.vrf_name = ? group by I.ifIndex ORDER BY I.ifIndex ASC ", array($device['device_id'],$vars['vrf-lite']));
  }else{
      $portsTmp = dbFetchRows("SELECT * FROM `ports` WHERE `device_id` = ? AND `deleted` = '0' ORDER BY `ifIndex` ASC", array($device['device_id']));
  }
  
  // As we've dragged the whole database, lets pre-populate our caches :)
  // FIXME - we should probably split the fetching of link/stack/etc into functions and cache them here too to cut down on single row queries.
  foreach ($portsTmp as $port)
  {
    $port_cache[$port['port_id']] = $port;
    $port_index_cache[$port['device_id']][$port['ifIndex']] = $port;
  }

  foreach ($portsTmp as $port)
  {
    if ($config['memcached']['enable'])
    {
      $state = $memcache->get('port-'.$port['port_id'].'-state');
      if($debug) { print_r($state); }
      if(is_array($state)) { $port = array_merge($port, $state); }
      unset($state);
    }

    include("includes/print-interface.inc.php");

    $i++;
  }
  echo("</table></div>");
}

unset($portsTmp);

$pagetitle[] = "Ports";

?>
