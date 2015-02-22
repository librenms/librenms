<?php

/**
 * LibreNMS
 *
 *   This file is part of LibreNMS
 *
 * @package    librenms
 * @subpackage functions
 * @author     LibreNMS Contributors <librenms-project@google.groups.com>
 * @copyright  (C) 2006 - 2012 Adam Armstrong (as Observium)
 * @copyright  (C) 2013 LibreNMS Group
 *
 */

include("../includes/alerts.inc.php");

function data_uri($file, $mime)
{
  $contents = file_get_contents($file);
  $base64   = base64_encode($contents);
  return ('data:' . $mime . ';base64,' . $base64);
}

function nicecase($item)
{
  switch ($item)
  {
    case "dbm":
      return "dBm";
    case "mysql":
      return" MySQL";
    case "powerdns":
      return "PowerDNS";
    case "bind":
      return "BIND";
    default:
      return ucfirst($item);
  }
}

function toner2colour($descr, $percent)
{
  $colour = get_percentage_colours(100-$percent);

  if (substr($descr,-1) == 'C' || stripos($descr,"cyan"   ) !== false) { $colour['left'] = "55D6D3"; $colour['right'] = "33B4B1"; }
  if (substr($descr,-1) == 'M' || stripos($descr,"magenta") !== false) { $colour['left'] = "F24AC8"; $colour['right'] = "D028A6"; }
  if (substr($descr,-1) == 'Y' || stripos($descr,"yellow" ) !== false
                               || stripos($descr,"giallo" ) !== false
                               || stripos($descr,"gul"    ) !== false) { $colour['left'] = "FFF200"; $colour['right'] = "DDD000"; }
  if (substr($descr,-1) == 'K' || stripos($descr,"black"  ) !== false
                               || stripos($descr,"nero"   ) !== false) { $colour['left'] = "000000"; $colour['right'] = "222222"; }

  return $colour;
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

function escape_quotes($text)
{
  return str_replace('"', "\'", str_replace("'", "\'", $text));
}

function generate_overlib_content($graph_array, $text)
{
    global $config;

    $overlib_content = '<div class=overlib><span class=overlib-text>'.$text.'</span><br />';
    foreach (array('day','week','month','year') as $period)
    {
      $graph_array['from']        = $config['time'][$period];
      $overlib_content .= escape_quotes(generate_graph_tag($graph_array));
    }
    $overlib_content .= '</div>';

    return $overlib_content;

}

function get_percentage_colours($percentage)
{
  $background = array();
  if ($percentage > '90') { $background['left']='c4323f'; $background['right']='C96A73'; }
  elseif ($percentage > '75') { $background['left']='bf5d5b'; $background['right']='d39392'; }
  elseif ($percentage > '50') { $background['left']='bf875b'; $background['right']='d3ae92'; }
  elseif ($percentage > '25') { $background['left']='5b93bf'; $background['right']='92b7d3'; }
  else { $background['left']='9abf5b'; $background['right']='bbd392'; }

  return($background);

}

function generate_minigraph_image($device, $start, $end, $type, $legend = 'no', $width = 275, $height = 100, $sep = '&amp;', $class = "minigraph-image")
{
  return '<img class="'.$class.'" src="graph.php?'.
    implode($sep, array('device='.$device['device_id'], "from=$start", "to=$end", "width=$width", "height=$height", "type=$type", "legend=$legend")).'">';
}

function generate_device_url($device, $vars=array())
{
  return generate_url(array('page' => 'device', 'device' => $device['device_id']), $vars);
}

function generate_device_link($device, $text=NULL, $vars=array(), $start=0, $end=0, $escape_text=1)
{
  global $config;

  if (!$start) { $start = $config['time']['day']; }
  if (!$end)   { $end   = $config['time']['now']; }

  $class = devclass($device);
  if (!$text) { $text = $device['hostname']; }

  if (isset($config['os'][$device['os']]['over']))
  {
    $graphs = $config['os'][$device['os']]['over'];
  }
  elseif (isset($device['os_group']) && isset($config['os'][$device['os_group']]['over']))
  {
    $graphs = $config['os'][$device['os_group']]['over'];
  }
  else
  {
    $graphs = $config['os']['default']['over'];
  }

  $url = generate_device_url($device, $vars);

  // beginning of overlib box contains large hostname followed by hardware & OS details
  $contents = "<div><span class=list-large>".$device['hostname']."</span>";
  if ($device['hardware']) { $contents .= " - ".$device['hardware']; }
  if ($device['os']) { $contents .= " - ".mres($config['os'][$device['os']]['text']); }
  if ($device['version']) { $contents .= " ".mres($device['version']); }
  if ($device['features']) { $contents .= " (".mres($device['features']).")"; }
  if (isset($device['location'])) { $contents .= " - " . htmlentities($device['location']); }
  $contents .= "</div>";

  foreach ($graphs as $entry)
  {
    $graph     = $entry['graph'];
    $graphhead = $entry['text'];
    $contents .= '<div class=overlib-box>';
    $contents .= '<span class=overlib-title>'.$graphhead.'</span><br />';
    $contents .= generate_minigraph_image($device, $start, $end, $graph);
    $contents .= generate_minigraph_image($device, $config['time']['week'], $end, $graph);
    $contents .= '</div>';
  }

  if ($escape_text) { $text = htmlentities($text); }
  $link = overlib_link($url, $text, escape_quotes($contents), $class);

  if (device_permitted($device['device_id']))
  {
    return $link;
  } else {
    return $device['hostname'];
  }
}

function overlib_link($url, $text, $contents, $class)
{
  global $config;

  $contents = str_replace("\"", "\'", $contents);
  $output = '<a class="'.$class.'" href="'.$url.'"';
  $output .= " onmouseover=\"return overlib('".$contents."'".$config['overlib_defaults'].", WRAP,HAUTO,VAUTO);\" onmouseout=\"return nd();\">";
  $output .= $text."</a>";

  return $output;
}

function generate_graph_popup($graph_array)
{
  global $config;

  // Take $graph_array and print day,week,month,year graps in overlib, hovered over graph

  $original_from = $graph_array['from'];

  $graph = generate_graph_tag($graph_array);
  $content = "<div class=list-large>".$graph_array['popup_title']."</div>";
  $content .= "<div style=\'width: 850px\'>";
  $graph_array['legend']   = "yes";
  $graph_array['height']   = "100";
  $graph_array['width']    = "340";
  $graph_array['from']     = $config['time']['day'];
  $content .= generate_graph_tag($graph_array);
  $graph_array['from']     = $config['time']['week'];
  $content .= generate_graph_tag($graph_array);
  $graph_array['from']     = $config['time']['month'];
  $content .= generate_graph_tag($graph_array);
  $graph_array['from']     = $config['time']['year'];
  $content .= generate_graph_tag($graph_array);
  $content .= "</div>";

  $graph_array['from'] = $original_from;

  $graph_array['link'] = generate_url($graph_array, array('page' => 'graphs', 'height' => NULL, 'width' => NULL, 'bg' => NULL));

#  $graph_array['link'] = "graphs/type=" . $graph_array['type'] . "/id=" . $graph_array['id'];

  return overlib_link($graph_array['link'], $graph, $content, NULL);
}

function print_graph_popup($graph_array)
{
  echo(generate_graph_popup($graph_array));
}

function permissions_cache($user_id)
{
  $permissions = array();
  foreach (dbFetchRows("SELECT * FROM devices_perms WHERE user_id = '".$user_id."'") as $device)
  {
    $permissions['device'][$device['device_id']] = 1;
  }
  foreach (dbFetchRows("SELECT * FROM ports_perms WHERE user_id = '".$user_id."'") as $port)
  {
    $permissions['port'][$port['port_id']] = 1;
  }
  foreach (dbFetchRows("SELECT * FROM bill_perms WHERE user_id = '".$user_id."'") as $bill)
  {
    $permissions['bill'][$bill['bill_id']] = 1;
  }

  return $permissions;
}

function bill_permitted($bill_id)
{
  global $permissions;

  if ($_SESSION['userlevel'] >= "5") {
    $allowed = TRUE;
  } elseif ($permissions['bill'][$bill_id]) {
    $allowed = TRUE;
  } else {
    $allowed = FALSE;
  }

  return $allowed;
}

function port_permitted($port_id, $device_id = NULL)
{
  global $permissions;

  if (!is_numeric($device_id)) { $device_id = get_device_id_by_port_id($port_id); }

  if ($_SESSION['userlevel'] >= "5")
  {
    $allowed = TRUE;
  } elseif (device_permitted($device_id)) {
    $allowed = TRUE;
  } elseif ($permissions['port'][$port_id]) {
    $allowed = TRUE;
  } else {
    $allowed = FALSE;
  }

  return $allowed;
}

function application_permitted($app_id, $device_id = NULL)
{
  global $permissions;

  if (is_numeric($app_id))
  {
    if (!$device_id) { $device_id = get_device_id_by_app_id ($app_id); }
    if ($_SESSION['userlevel'] >= "5") {
      $allowed = TRUE;
    } elseif (device_permitted($device_id)) {
      $allowed = TRUE;
    } elseif ($permissions['application'][$app_id]) {
      $allowed = TRUE;
    } else {
      $allowed = FALSE;
    }
  } else {
    $allowed = FALSE;
  }

  return $allowed;
}

function device_permitted($device_id)
{
  global $permissions;

  if ($_SESSION['userlevel'] >= "5")
  {
    $allowed = true;
  } elseif ($permissions['device'][$device_id]) {
    $allowed = true;
  } else {
    $allowed = false;
  }

  return $allowed;
}

function print_graph_tag($args)
{
  echo(generate_graph_tag($args));
}

function generate_graph_tag($args)
{
  $urlargs = array();
  foreach ($args as $key => $arg)
  {
    $urlargs[] = $key."=".$arg;
  }

  return '<img src="graph.php?' . implode('&amp;',$urlargs).'" border="0" />';
}

function generate_graph_js_state($args) {
  // we are going to assume we know roughly what the graph url looks like here.
  // TODO: Add sensible defaults
  $from   = (is_numeric($args['from'])   ? $args['from']   : 0);
  $to     = (is_numeric($args['to'])     ? $args['to']     : 0);
  $width  = (is_numeric($args['width'])  ? $args['width']  : 0);
  $height = (is_numeric($args['height']) ? $args['height'] : 0);
  $legend = str_replace("'", "", $args['legend']);

  $state = <<<STATE
<script type="text/javascript" language="JavaScript">
document.graphFrom = $from;
document.graphTo = $to;
document.graphWidth = $width;
document.graphHeight = $height;
document.graphLegend = '$legend';
</script>
STATE;

  return $state;
}

function print_percentage_bar($width, $height, $percent, $left_text, $left_colour, $left_background, $right_text, $right_colour, $right_background)
{

  if ($percent > "100") { $size_percent = "100"; } else { $size_percent = $percent; }

  $output = '
<div class="container" style="width:'.$width.'px; height:'.$height.'px;">
 <div class="progress" style="min-width: 2em; background-color:#'.$right_background.'; height:'.$height.'px;">
  <div class="progress-bar" role="progressbar" aria-valuenow="'.$size_percent.'" aria-valuemin="0" aria-valuemax="100" style="min-width: 2em; width:'.$size_percent.'%; background-color: #'.$left_background.';">
  </div>
 </div>
 <b class="pull-left" style="padding-left: 4px; height: '.$height.'px;margin-top:-'.($height*2).'px; color:#'.$left_colour.';">'.$left_text.'</b>
 <b class="pull-right" style="padding-right: 4px; height: '.$height.'px;margin-top:-'.($height*2).'px; color:#'.$right_colour.';">'.$right_text.'</b>
</div>
';

  return $output;
}

function generate_entity_link($type, $entity, $text = NULL, $graph_type=NULL)
{
  global $config, $entity_cache;

  if (is_numeric($entity))
  {
    $entity = get_entity_by_id_cache($type, $entity);
  }

  switch($type)
  {
    case "port":
      $link = generate_port_link($entity, $text, $graph_type);
      break;
    case "storage":
      if (empty($text)) { $text = $entity['storage_descr']; }
      $link = generate_link($text, array('page' => 'device', 'device' => $entity['device_id'], 'tab' => 'health', 'metric' => 'storage'));
      break;
    default:
      $link = $entity[$type.'_id'];
  }

  return($link);

}

function generate_port_link($port, $text = NULL, $type = NULL)
{
  global $config;

  $graph_array = array();
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

function generate_port_url($port, $vars=array())
{
  return generate_url(array('page' => 'device', 'device' => $port['device_id'], 'tab' => 'port', 'port' => $port['port_id']), $vars);
}

function generate_peer_url($peer, $vars=array())
{
  return generate_url(array('page' => 'device', 'device' => $peer['device_id'], 'tab' => 'routing', 'proto' => 'bgp'), $vars);
}

function generate_port_image($args)
{
  if (!$args['bg']) { $args['bg'] = "FFFFFF"; }
  return "<img src='graph.php?type=".$args['graph_type']."&amp;id=".$args['port_id']."&amp;from=".$args['from']."&amp;to=".$args['to']."&amp;width=".$args['width']."&amp;height=".$args['height']."&amp;bg=".$args['bg']."'>";
}

function generate_port_thumbnail($port)
{
  global $config;
  $port['graph_type'] = 'port_bits';
  $port['from']       = $config['time']['day'];
  $port['to']         = $config['time']['now'];
  $port['width']      = 150;
  $port['height']     = 21;
  return generate_port_image($port);
}

function print_port_thumbnail($args)
{
  echo(generate_port_link($args, generate_port_image($args)));
}

function print_optionbar_start ($height = 0, $width = 0, $marginbottom = 5)
{
  echo('
        <div class="well">
');
}

function print_optionbar_end()
{
  echo('  </div>');
}

function geteventicon($message)
{
  if ($message == "Device status changed to Down") { $icon = "server_connect.png"; }
  if ($message == "Device status changed to Up") { $icon = "server_go.png"; }
  if ($message == "Interface went down" || $message == "Interface changed state to Down") { $icon = "if-disconnect.png"; }
  if ($message == "Interface went up" || $message == "Interface changed state to Up") { $icon = "if-connect.png"; }
  if ($message == "Interface disabled") { $icon = "if-disable.png"; }
  if ($message == "Interface enabled") { $icon = "if-enable.png"; }
  if (isset($icon)) { return $icon; } else { return false; }
}

function overlibprint($text)
{
  return "onmouseover=\"return overlib('" . $text . "');\" onmouseout=\"return nd();\"";
}

function humanmedia($media)
{
  array_preg_replace($rewrite_iftype, $media);
  return $media;
}

function humanspeed($speed)
{
  $speed = formatRates($speed);
  if ($speed == "") { $speed = "-"; }
  return $speed;
}

function devclass($device)
{
  if (isset($device['status']) && $device['status'] == '0') { $class = "list-device-down"; } else { $class = "list-device"; }
  if (isset($device['ignore']) && $device['ignore'] == '1')
  {
     $class = "list-device-ignored";
     if (isset($device['status']) && $device['status'] == '1') { $class = "list-device-ignored-up"; }
  }
  if (isset($device['disabled']) && $device['disabled'] == '1') { $class = "list-device-disabled"; }

  return $class;
}

function getlocations()
{
  $ignore_dev_location = array();
  $locations = array();
  # Fetch override locations, not through get_dev_attrib, this would be a huge number of queries
  $rows = dbFetchRows("SELECT attrib_type,attrib_value,device_id FROM devices_attribs WHERE attrib_type LIKE 'override_sysLocation%' ORDER BY attrib_type");
  foreach ($rows as $row)
  {
    if ($row['attrib_type'] == 'override_sysLocation_bool' && $row['attrib_value'] == 1)
    {
      $ignore_dev_location[$row['device_id']] = 1;
    }
    # We can do this because of the ORDER BY, "bool" will be handled before "string"
    elseif ($row['attrib_type'] == 'override_sysLocation_string' && $ignore_dev_location[$row['device_id']] == 1)
    {
      if (!in_array($row['attrib_value'],$locations)) { $locations[] = $row['attrib_value']; }
    }
  }

  # Fetch regular locations
  if ($_SESSION['userlevel'] >= '5')
  {
    $rows = dbFetchRows("SELECT D.device_id,location FROM devices AS D GROUP BY location ORDER BY location");
  } else {
    $rows = dbFetchRows("SELECT D.device_id,location FROM devices AS D, devices_perms AS P WHERE D.device_id = P.device_id AND P.user_id = ? GROUP BY location ORDER BY location", array($_SESSION['user_id']));
  }

  foreach ($rows as $row)
  {
    # Only add it as a location if it wasn't overridden (and not already there)
    if ($row['location'] != '' && !$ignore_dev_location[$row['device_id']])
    {
      if (!in_array($row['location'],$locations)) { $locations[] = $row['location']; }
    }
  }

  sort($locations);
  return $locations;
}

function foldersize($path)
{
  $total_size = 0;
  $files = scandir($path);
  $total_files = 0;

  foreach ($files as $t)
  {
    if (is_dir(rtrim($path, '/') . '/' . $t))
    {
      if ($t<>"." && $t<>"..")
      {
        $size = foldersize(rtrim($path, '/') . '/' . $t);
        $total_size += $size;
      }
    } else {
      $size = filesize(rtrim($path, '/') . '/' . $t);
      $total_size += $size;
      $total_files++;
    }
  }

  return array($total_size, $total_files);
}

function generate_ap_link($args, $text = NULL, $type = NULL)
{
  global $config;

  $args = ifNameDescr($args);
  if (!$text) { $text = fixIfName($args['label']); }
  if ($type) { $args['graph_type'] = $type; }
  if (!isset($args['graph_type'])) { $args['graph_type'] = 'port_bits'; }

  if (!isset($args['hostname'])) { $args = array_merge($args, device_by_id_cache($args['device_id'])); }

  $content = "<div class=list-large>".$args['text']." - " . fixifName($args['label']) . "</div>";
  if ($args['ifAlias']) { $content .= $args['ifAlias']."<br />"; }
  $content .= "<div style=\'width: 850px\'>";
  $graph_array = array();
  $graph_array['type']     = $args['graph_type'];
  $graph_array['legend']   = "yes";
  $graph_array['height']   = "100";
  $graph_array['width']    = "340";
  $graph_array['to']           = $config['time']['now'];
  $graph_array['from']     = $config['time']['day'];
  $graph_array['id']       = $args['accesspoint_id'];
  $content .= generate_graph_tag($graph_array);
  $graph_array['from']     = $config['time']['week'];
  $content .= generate_graph_tag($graph_array);
  $graph_array['from']     = $config['time']['month'];
  $content .= generate_graph_tag($graph_array);
  $graph_array['from']     = $config['time']['year'];
  $content .= generate_graph_tag($graph_array);
  $content .= "</div>";


  $url = generate_ap_url($args);
  if (port_permitted($args['interface_id'], $args['device_id'])) {
    return overlib_link($url, $text, $content, $class);
  } else {
    return fixifName($text);
  }
}

function generate_ap_url($ap, $vars=array())
{
  return generate_url(array('page' => 'device', 'device' => $ap['device_id'], 'tab' => 'accesspoint', 'ap' => $ap['accesspoint_id']), $vars);
}

function report_this($message)
{
  global $config;
  return '<h2>'.$message.' Please <a href="'.$config['project_issues'].'">report this</a> to the '.$config['project_name'].' developers.</h2>';
}

function report_this_text($message)
{
  global $config;
  return $message.'\nPlease report this to the '.$config['project_name'].' developers at '.$config['project_issues'].'\n';
}

# Find all the files in the given directory that match the pattern
function get_matching_files($dir, $match = "/\.php$/")
{
  global $config;

  $list = array();
  if ($handle = opendir($dir))
  {
    while (false !== ($file = readdir($handle)))
    {
      if ($file != "." && $file != ".." && preg_match($match, $file) === 1)
      {
	$list[] = $file;
      }
    }
    closedir($handle);
  }
  return $list;
}

# Include all the files in the given directory that match the pattern
function include_matching_files($dir, $match = "/\.php$/")
{
  foreach (get_matching_files($dir, $match) as $file) {
    include_once($file);
  }
}

function generate_pagination($count,$limit,$page,$links = 2) {
    $end_page = ceil($count / $limit);
    $start = (($page - $links) > 0) ? $page - $links : 1;
    $end = (($page + $links) < $end_page) ? $page + $links : $end_page;
    $return = '<ul class="pagination">';
    $link_class = ($page == 1) ? "disabled" : "";
    $return .= "<li><a href='' onClick='changePage(1,event);'>&laquo;</a></li>";
    $return .= "<li class='$link_class'><a href='' onClick='changePage($page - 1,event);'>&lt;</a></li>";

    if($start > 1) {
        $return .= "<li><a href='' onClick='changePage(1,event);'>1</a></li>";
        $return .= "<li class='disabled'><span>...</span></li>";
    }

    for($x=$start;$x<=$end;$x++) {
        $link_class = ($page == $x) ? "active" : "";
        $return .= "<li class='$link_class'><a href='' onClick='changePage($x,event);'>$x </a></li>";
    }

    if($end < $end_page) {
        $return .= "<li class='disabled'><span>...</span></li>";
        $return .= "<li><a href='' onClick='changePage($end_page,event);'>$end_page</a></li>";
    }

    $link_class = ($page == $end_page) ? "disabled" : "";
    $return .= "<li class='$link_class'><a href='' onClick='changePage($page + 1,event);'>&gt;</a></li>";
    $return .= "<li class='$link_class'><a href='' onClick='changePage($end_page,event);'>&raquo;</a></li>";
    $return .= '</ul>';
    return($return);
}

function is_admin() {
    if ($_SESSION['userlevel'] >= '10') {
        $allowed = true;
    } else {
        $allowed = false;
    }
    return $allowed;
}

function demo_account() {
    print_error("You are logged in as a demo account, this page isn't accessible to you");
}

?>
