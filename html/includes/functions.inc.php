<?php

function generate_device_link($device, $text=0, $linksuffix="", $start=0, $end=0)
{
  global $twoday; global $day; global $now; global $config;

  if (!$start) { $start = $day; }
  if (!$end) { $end = $now; }
  $class = devclass($device);
  if (!$text) { $text = $device['hostname']; }

  if (isset($config['os'][$device['os']]['over']))
  {
    $graphs = $config['os'][$device['os']]['over'];
  }
  elseif (isset($config['os'][$device['os_group']]['over']))
  {
    $graphs = $config['os'][$device['os_group']]['over'];
  }
  else
  {
    $graphs = $config['os']['default']['over'];
  }

  $url  = $config['base_url']."/device/" . $device['device_id'] . "/" . $linksuffix;
  $contents = "<div class=list-large>".$device['hostname'];
  if ($device['hardware']) { $contents .= " - ".$device['hardware']; }
  $contents .= "</div>";

  $contents .= "<div>";
  if ($device['os']) { $contents .= mres($config['os'][$device['os']]['text']); }
  if ($device['version']) { $contents .= " ".mres($device['version']); }
  if ($device['features']) { $contents .= " (".mres($device['features']).")"; }
#  if ($device['hardware']) { $contents .= " - ".$device['hardware']; }
  $contents .= "</div>";

#  if (isset($device['location'])) { $contents .= "" . htmlentities($device['location'])."<br />"; }
  foreach ($graphs as $entry)
  {
    $graph     = $entry['graph'];
    $graphhead = $entry['text'];
    $contents .= '<div style="width: 708px">';
    $contents .= '<span style="margin-left: 5px; font-size: 12px; font-weight: bold;">'.$graphhead.'</span><br />';
    $contents .= '<img src="' . $config['base_url'] . "/graph.php?id=" . $device['device_id'] . "&amp;from=$start&amp;to=$end&amp;width=275&amp;height=100&amp;type=$graph&amp;legend=no" . '" style="margin: 2px;">';
    $contents .= '<img src="' . $config['base_url'] . "/graph.php?id=" . $device['device_id'] . "&amp;from=".$config['week']."&amp;to=$end&amp;width=275&amp;height=100&amp;type=$graph&amp;legend=no" . '" style="margin: 2px;">';
    $contents .= '</div>';
  }

  $text = htmlentities($text);
  $link = overlib_link($url, $text, $contents, $class);

  if (device_permitted($device['device_id']))
  {
    return $link;
  } else {
    return $device['hostname'];
  }


  return $link;
}

function overlib_link($url, $text, $contents, $class)
{
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
  echo(generate_graph_popup($graph_array));
}



function permissions_cache($user_id)
{
  $permissions = array();
  $query = mysql_query("SELECT * FROM devices_perms WHERE user_id = '".$user_id."'");
  while ($device = mysql_fetch_assoc($query))
  {
    $permissions['device'][$device['device_id']] = 1;
  }
  $query = mysql_query("SELECT * FROM ports_perms WHERE user_id = '".$user_id."'");
  while ($port = mysql_fetch_assoc($query))
  {
    $permissions['port'][$port['interface_id']] = 1;
  }
  $query = mysql_query("SELECT * FROM bill_perms WHERE user_id = '".$user_id."'");
  while ($bill = mysql_fetch_assoc($query))
  {
    $permissions['bill'][$bill['bill_id']] = 1;
  }
  return $permissions;
}

function bill_permitted($bill_id)
{
  global $_SESSION; global $permissions;

  if ($_SESSION['userlevel'] >= "5") {
    $allowed = TRUE;
  } elseif ($permissions['bill'][$bill_id]) {
    $allowed = TRUE;
  } else {
    $allowed = FALSE;
  }

  return $allowed;
}

function port_permitted($interface_id, $device_id = NULL)
{
  global $_SESSION; global $permissions;

  if (!is_numeric($device_id)) { $device_id = get_device_id_by_interface_id($interface_id); }

  if ($_SESSION['userlevel'] >= "5")
  {
    $allowed = TRUE;
  } elseif (device_permitted($device_id)) {
    $allowed = TRUE;
  } elseif ($permissions['port'][$interface_id]) {
    $allowed = TRUE;
  } else {
    $allowed = FALSE;
  }

  return $allowed;
}

function application_permitted($app_id, $device_id = NULL)
{
  global $_SESSION; global $permissions;
  if (is_numeric($app_id))
  {
    if (!$device_id) { $device_id = device_by_id_cache ($app_id); }
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
  global $_SESSION; global $permissions;

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

function print_graph_tag ($args)
{
  echo(generate_graph_tag ($args));
}

function generate_graph_tag ($args)
{
  global $config;

  $sep = "?";
  $url = $config['base_url'] . "/graph.php";
  foreach ($args as $key => $arg)
  {
    $url .= $sep.$key."=".$arg;
    $sep="&amp;";
  }
  return "<img src=\"".$url."\" border=0>";
}


function print_percentage_bar ($width, $height, $percent, $left_text, $left_colour, $left_background, $right_text, $right_colour, $right_background)
{
  $output = '
<div style="font-size:11px;">
  <div style=" width:'.$width.'px; height:'.$height.'px; background-color:#'.$right_background.';">
    <div style="width:'.$percent.'%; height:'.$height.'px; background-color:#'.$left_background.'; border-right:0px white solid;"></div>
    <div style="vertical-align: middle;height: '.$height.'px;margin-top:-'.($height).'px; color:#'.$left_colour .'; padding-left :4px;"><b>'.$left_text.'</b></div>
    <div style="vertical-align: middle;height: '.$height.'px;margin-top:-'.($height).'px; color:#'.$right_colour.'; padding-right:4px;text-align:right;"><b>'.$right_text.'</b></div>
  </div>
</div>';
  return $output;
}

function generate_port_link($args, $text = NULL, $type = NULL)
{
  global $twoday; global $now; global $config; global $day; global $month;
  $args = ifNameDescr($args);
  if (!$text) { $text = fixIfName($args['label']); }
  if ($type) { $args['graph_type'] = $type; }
  if (!$args['graph_type']) { $args['graph_type'] = 'port_bits'; }

  $class = ifclass($args['ifOperStatus'], $args['ifAdminStatus']);
  if (!isset($args['hostname'])) { $args = array_merge($args, device_by_id_cache($args['device_id'])); }

  $content = "<div class=list-large>".$args['hostname']." - " . fixifName($args['label']) . "</div>";
  if ($args['ifAlias']) { $content .= $args['ifAlias']."<br />"; }
  $content .= "<div style=\'width: 850px\'>";
  $graph_array['type']     = $args['graph_type'];
  $graph_array['legend']   = "yes";
  $graph_array['height']   = "100";
  $graph_array['width']    = "340";
  $graph_array['to']	   = $config['now'];
  $graph_array['from']     = $config['day'];
  $graph_array['id']       = $args['interface_id'];
  $content .= generate_graph_tag($graph_array);
  $graph_array['from']     = $config['week'];
  $content .= generate_graph_tag($graph_array);
  $graph_array['from']     = $config['month'];
  $content .= generate_graph_tag($graph_array);
  $graph_array['from']     = $config['year'];
  $content .= generate_graph_tag($graph_array);
  $content .= "</div>";

  $url = $config['base_url']."/device/".$args['device_id']."/interface/" . $args['interface_id'] . "/";

  if (port_permitted($args['interface_id'])) {
    return overlib_link($url, $text, $content, $class);
  } else {
    return fixifName($text);
  }
}

function generate_port_thumbnail($args)
{
    if (!$args['bg']) { $args['bg'] = "FFFFF"; }
    $args['content'] = "<img src='graph.php?type=".$args['graph_type']."&amp;id=".$args['interface_id']."&amp;from=".$args['from']."&amp;to=".$args['to']."&amp;width=".$args['width']."&amp;height=".$args['height']."&amp;legend=no&amp;bg=".$args['bg']."'>";
    echo generate_port_link($args, $args['content']);
}

function print_optionbar_start ($height = 20, $width = 0, $marginbottom = 5)
{
  echo("
    <div style='text-align: center; margin-top: 0px; margin-bottom: ".$marginbottom."px; " . ($width ? 'max-width: ' . $width . (strstr($width,'%') ? '' : 'px') . '; ' : '') . "'>
    <b class='rounded'>
    <b class='rounded1'><b></b></b>
    <b class='rounded2'><b></b></b>
    <b class='rounded3'></b>
    <b class='rounded4'></b>
    <b class='rounded5'></b></b>
    <div class='roundedfg' style='padding: 0px 5px;'>
    <div style='margin: auto; text-align: left; padding: 2px 5px; padding-left: 11px; clear: both; display:block; " . ($height ? 'height:' . $height . 'px;' : '') . "'>");
}


function print_optionbar_end()
{
  echo('  </div>
    </div>
    <b class="rounded">
    <b class="rounded5"></b>
    <b class="rounded4"></b>
    <b class="rounded3"></b>
    <b class="rounded2"><b></b></b>
    <b class="rounded1"><b></b></b></b>
  </div>');
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

function print_error($text)
{
  echo('<div class="errorbox"><img src="/images/15/exclamation.png" align="absmiddle"> '.$text.'</div>');
}

function print_message($text)
{
  echo('<div class="messagebox"><img src="/images/16/tick.png" align="absmiddle"> '.$text.'</div>');
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

?>