<?php

function generateiflink($interface, $text=0, $type = NULL)
{

   ## Exists only to support older version of this function (i suck)

   if($type) { $interface['type'] = $type; }
   return generate_if_link($interface, $text);
}

function generatedevicelink($device, $text=0, $start=0, $end=0) 
{
  global $twoday; global $day; global $now; global $config; global $popgraph; global $popdescr;
  if (!$start) { $start = $day; }
  if (!$end) { $end = $now; }
  $class = devclass($device);
  if (!$text) { $text = $device['hostname']; }
  
  if (isset($popgraph[$device['os']]))
  {
    $graphs = $popgraph[$device['os']];
    $descr = $popdescr[$device['os']];
  }
  else
  {
    $graphs = $popgraph['default'];
    $descr = $popdescr['default'];
  }

  $url  = $config['base_url']."/device/" . $device['device_id'] . "/";
  $contents = "<div class=list-large>".$device['hostname'] . " - $descr</div>";
  if (isset($device['location'])) { $contents .= "" . htmlentities($device['location'])."<br />"; }
  foreach ($graphs as $graph)
  {
    $contents .= '<img src="' . $config['base_url'] . "/graph.php?device=" . $device['device_id'] . "&amp;from=$start&amp;to=$end&amp;width=400&amp;height=120&amp;type=$graph" . '"><br />';
  }
  $text = htmlentities($text);
  $link = overlib_link($url, $text, $contents, $class);
  if(devicepermitted($device['device_id'])) {
    return $link;
  } else {
    return $device['hostname'];
  }


  return $link;
}

function overlib_link($url, $text, $contents, $class) {
  global $config;
  $contents = str_replace("\"", "\'", $contents);
  $output = "<a class='".$class."' href='".$url."'";
  $output .= " onmouseover=\"return overlib('".$contents."'".$config['overlib_defaults'].");\" onmouseout=\"return nd();\">";
  $output .= $text."</a>";
  return $output;
}

function generate_graph_popup($graph_array) 
{
  global $config;
  ## Take $graph_array and print day,week,month,year graps in overlib, hovered over graph

  $graph = generate_graph_tag($graph_array);
  $content = "<div class=list-large>".$graph_array['popup_title']."</div>";
  $content .= "<div style=\'width: 850px\'>";
  $graph_array['legend']   = "yes";
  $graph_array['height']   = "100";
  $graph_array['width']    = "340";
  $graph_array['from']     = $config['day'];
  $content .= generate_graph_tag($graph_array);
  $graph_array['from']     = $config['week'];
  $content .= generate_graph_tag($graph_array);
  $graph_array['from']     = $config['month'];
  $content .= generate_graph_tag($graph_array);
  $graph_array['from']     = $config['year'];
  $content .= generate_graph_tag($graph_array);
  $content .= "</div>";
  return overlib_link($graph_array['link'], $graph, $content, NULL);

}

function print_graph_popup($graph_array)
{
  echo (generate_graph_popup($graph_array));
}


function permissions_cache($user_id) {
  $permissions = array();
  $query = mysql_query("SELECT * FROM devices_perms WHERE user_id = '".$user_id."'");
  while($device = mysql_fetch_assoc($query)) {
    $permissions['device'][$device['device_id']] = 1;    
  }
  $query = mysql_query("SELECT * FROM ports_perms WHERE user_id = '".$user_id."'");
  while($port = mysql_fetch_assoc($query)) {
    $permissions['port'][$port['interface_id']] = 1;
  }
  return $permissions;
}

function interfacepermitted($interface_id, $device_id = NULL)
{
  global $_SESSION; global $permissions;
  if(!$device_id) { $device_id = mysql_result(mysql_query("SELECT `device_id` from ports WHERE interface_id = '".$interface_id."'"),0); }
  if ($_SESSION['userlevel'] >= "5") {
    $allowed = TRUE;
  } elseif ( devicepermitted($device_id)) {
    $allowed = TRUE;
  } elseif ( $permissions['port'][$interface_id]) {
    $allowed = TRUE;
  } else {
    $allowed = FALSE;
  }
  return $allowed;
}

function devicepermitted($device_id)
{
  global $_SESSION; global $permissions;
  if ($_SESSION['userlevel'] >= "5") {
    $allowed = true;
  } elseif ( $permissions['device'][$device_id] ) {
    $allowed = true;
  } else {
    $allowed = false;
  }
  return $allowed;

}


function print_graph_tag ($args) 
{
  echo generate_graph_tag ($args);
}

function generate_graph_tag ($args) 
{
  global $config;
  $sep = "?";
  $url = $config['base_url'] . "/graph.php";
  foreach ($args as $key => $arg) 
  {
    $url .= $sep.$key."=".$arg;
    $sep="&";
  }
  return "<img src=\"".$url."\" border=0>";
}


function print_percentage_bar ($width, $height, $percent, $left_text, $left_colour, $left_background, $right_text, $right_colour, $right_background) 
{
  $output = '
<div style="font-size:11px;">
  <div style=" width:'.$width.'px; height:'.$height.'px; background-color:#'.$right_background.';">
    <div style="width:'.$percent.'%; height:'.$height.'px; background-color:#'.$left_background.'; border-right:0px white solid;"></div>
    <div style="vertical-align: center;height: '.$height.'px;margin-top:-'.($height).'px; color:#'.$left_colour .'; padding-left :4px;"><b>'.$left_text.'</b></div>
    <div style="vertical-align: center;height: '.$height.'px;margin-top:-'.($height).'px; color:#'.$right_colour.'; padding-right:4px;text-align:right;"><b>'.$right_text.'</b></div>
  </div>
</div>';
  return $output;
}

function generate_if_link($args, $text = NULL)
{
  global $twoday; global $now; global $config; global $day; global $month;
  $args = ifNameDescr($args);
  if(!$text) { $text = fixIfName($args['label']); }
  if(!$args['graph_type']) { $args['graph_type'] = 'port_bits'; }
  $class = ifclass($args['ifOperStatus'], $args['ifAdminStatus']);
  if(!isset($args['hostname'])) { $args = array_merge($args, device_by_id_cache($args['device_id'])); }

  $content = "<div class=list-large>".$args['hostname']." - " . fixifName($args['label']) . "</div>";
  if($args['ifAlias']) { $content .= $args['ifAlias']."<br />"; }
  $content .= "<div style=\'width: 850px\'>";
  $graph_array['type']     = $args['graph_type'];
  $graph_array['legend']   = "yes";
  $graph_array['height']   = "100";
  $graph_array['width']    = "340";
  $graph_array['from']     = $config['day'];
  $graph_array['port']     = $args['interface_id'];
  $content .= generate_graph_tag($graph_array);
  $graph_array['from']     = $config['week'];
  $content .= generate_graph_tag($graph_array);
  $graph_array['from']     = $config['month'];
  $content .= generate_graph_tag($graph_array);
  $graph_array['from']     = $config['year'];
  $content .= generate_graph_tag($graph_array);
  $content .= "</div>";
  
  $url = $config['base_url']."/device/".$args['device_id']."/interface/" . $args['interface_id'] . "/";

  if(interfacepermitted($args['interface_id'])) {
    return overlib_link($url, $text, $content, $class);
  } else {
    return fixifName($text);
  }

}

function generate_port_thumbnail($args) 
{
    if(!$args['bg']) { $args['bg'] = "FFFFF"; }
    $args['content'] = "<img src='graph.php?type=".$args['graph_type']."&if=".$args['interface_id']."&from=".$args['from']."&to=".$args['to']."&width=".$args['width']."&height=".$args['height']."&legend=no&bg=".$args['bg']."'>";
    $output = generate_if_link($args);
    echo $output;
}

function print_optionbar_start ($height = 20, $width = 0) 
{
  echo("
    <div style='text-align: center; margin-top: 0px; margin-bottom: 0px; " . ($width ? 'max-width: ' . $width . (strstr($width,'%') ? '' : 'px') . '; ' : '') . "'>
    <b class='rounded'>
    <b class='rounded1'><b></b></b>
    <b class='rounded2'><b></b></b>
    <b class='rounded3'></b>
    <b class='rounded4'></b>
    <b class='rounded5'></b></b>
    <div class='roundedfg' style='padding: 0px 5px;'>
    <div style='margin: auto; text-align: left; padding: 2px 5px; padding-left: 11px; clear: both; display:block; " . ($height ? 'height:' . $height . 'px;' : '') . "'>");
}


function print_optionbar_end () 
{
  echo("  </div>
    </div>
    <b class='rounded'>
    <b class='rounded5'></b>
    <b class='rounded4'></b>
    <b class='rounded3'></b>
    <b class='rounded2'><b></b></b>
    <b class='rounded1'><b></b></b></b>
  </div>");
}

?>
