<?php

function rrdtool_graph($graph_file, $options)
{

  global $config, $debug;

  if ($debug) { echo("$options"); }

  $command = $config['rrdtool'] . " -";

  $descriptorspec = array(
     0 => array("pipe", "r"),  // stdin is a pipe that the child will read from
     1 => array("pipe", "w"),  // stdout is a pipe that the child will write to
     2 => array("file", "/tmp/error-output.txt", "a") // stderr is a file to write to
  );

  $cwd = $config['rrd_dir'];
  $env = array();

  $process = proc_open($command, $descriptorspec, $pipes, $cwd, $env);

  if (is_resource($process)) {
    // $pipes now looks like this:
    // 0 => writeable handle connected to child stdin
    // 1 => readable handle connected to child stdout
    // Any error output will be appended to /tmp/error-output.txt

    if ($config['rrdcached']) {
      fwrite($pipes[0], "graph --daemon " . $config['rrdcached'] . " $graph_file $options");
    } else {
      fwrite($pipes[0], "graph $graph_file $options");
    }

    fclose($pipes[0]);
    fclose($pipes[1]);

    // It is important that you close any pipes before calling
    // proc_close in order to avoid a deadlock
    $return_value = proc_close($process);

    if ($debug)
    {
        echo("<p>");
        if ($debug) { echo("graph $graph_file $options"); }
        echo("</p><p>");
        echo "command returned $return_value\n";
        echo("</p>");
    }
  }
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

  foreach($vars as $var => $value)
  {
    if ($value != "")
    {
      $url .= $var ."=".$value."/";
    }
  }

  return($url);

}

function generate_overlib_content($graph_array, $text)
{
    global $config;

    $overlib_content = '<div style="width: 580px;"><h2>'.$text."</h2>";
    foreach (array('day','week','month','year') as $period)
    {
      $graph_array['from']        = $config['time'][$period];
      $overlib_content .= str_replace('"', "\'", generate_graph_tag($graph_array));
    }
    $overlib_content .= "</div>";

    return $overlib_content;

}


function get_percentage_colours($percentage)
{

  if ($percentage > '90') { $background['left']='c4323f'; $background['right']='C96A73'; }
  elseif ($percentage > '75') { $background['left']='bf5d5b'; $background['right']='d39392'; }
  elseif ($percentage > '50') { $background['left']='bf875b'; $background['right']='d3ae92'; }
  elseif ($percentage > '25') { $background['left']='5b93bf'; $background['right']='92b7d3'; }
  else { $background['left']='9abf5b'; $background['right']='bbd392'; }

  return($background);

}

function generate_device_url($device, $vars=array())
{
  return generate_url(array('page' => 'device', 'device' => $device['device_id']), $vars);
}

function generate_device_link($device, $text=0, $vars=array(), $start=0, $end=0)
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
    $contents .= "<img src=\"graph.php?id=" . $device['device_id'] . "&amp;from=$start&amp;to=$end&amp;width=275&amp;height=100&amp;type=$graph&amp;legend=no" . '" style="margin: 2px;">';
    $contents .= "<img src=\"graph.php?id=" . $device['device_id'] . "&amp;from=".$config['time']['week']."&amp;to=$end&amp;width=275&amp;height=100&amp;type=$graph&amp;legend=no" . '" style="margin: 2px;">';
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
  $graph_array['from']     = $config['time']['day'];
  $content .= generate_graph_tag($graph_array);
  $graph_array['from']     = $config['time']['week'];
  $content .= generate_graph_tag($graph_array);
  $graph_array['from']     = $config['time']['month'];
  $content .= generate_graph_tag($graph_array);
  $graph_array['from']     = $config['time']['year'];
  $content .= generate_graph_tag($graph_array);
  $content .= "</div>";
  
  $graph_array['link'] = "graphs/type=" . $graph_array['type'] . "/id=" . $graph_array['id'];

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
    $permissions['port'][$port['interface_id']] = 1;
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

function port_permitted($interface_id, $device_id = NULL)
{
  global $permissions;

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
  global $permissions;

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
<div style="font-size:11px;">
  <div style=" width:'.$width.'px; height:'.$height.'px; background-color:#'.$right_background.';">
    <div style="width:'.$size_percent.'%; height:'.$height.'px; background-color:#'.$left_background.'; border-right:0px white solid;"></div>
    <div style="vertical-align: middle;height: '.$height.'px;margin-top:-'.($height).'px; color:#'.$left_colour .'; padding-left :4px;"><b>'.$left_text.'</b></div>
    <div style="vertical-align: middle;height: '.$height.'px;margin-top:-'.($height).'px; color:#'.$right_colour.'; padding-right:4px;text-align:right;"><b>'.$right_text.'</b></div>
  </div>
</div>';

  return $output;
}

function generate_port_link($args, $text = NULL, $type = NULL)
{
  global $config;

  $args = ifNameDescr($args);
  if (!$text) { $text = fixIfName($args['label']); }
  if ($type) { $args['graph_type'] = $type; }
  if (!isset($args['graph_type'])) { $args['graph_type'] = 'port_bits'; }

  $class = ifclass($args['ifOperStatus'], $args['ifAdminStatus']);

  if (!isset($args['hostname'])) { $args = array_merge($args, device_by_id_cache($args['device_id'])); }

  $content = "<div class=list-large>".$args['hostname']." - " . fixifName($args['label']) . "</div>";
  if ($args['ifAlias']) { $content .= $args['ifAlias']."<br />"; }
  $content .= "<div style=\'width: 850px\'>";
  $graph_array['type']     = $args['graph_type'];
  $graph_array['legend']   = "yes";
  $graph_array['height']   = "100";
  $graph_array['width']    = "340";
  $graph_array['to']           = $config['time']['now'];
  $graph_array['from']     = $config['time']['day'];
  $graph_array['id']       = $args['interface_id'];
  $content .= generate_graph_tag($graph_array);
  $graph_array['from']     = $config['time']['week'];
  $content .= generate_graph_tag($graph_array);
  $graph_array['from']     = $config['time']['month'];
  $content .= generate_graph_tag($graph_array);
  $graph_array['from']     = $config['time']['year'];
  $content .= generate_graph_tag($graph_array);
  $content .= "</div>";

  $url = generate_port_url($args);

  if (port_permitted($args['interface_id'], $args['device_id'])) {
    return overlib_link($url, $text, $content, $class);
  } else {
    return fixifName($text);
  }
}

function generate_port_url($port, $vars=array())
{
  return generate_url(array('page' => 'device', 'device' => $port['device_id'], 'tab' => 'port', 'port' => $port['interface_id']), $vars);
}

function generate_port_thumbnail($args)
{
  if (!$args['bg']) { $args['bg'] = "FFFFF"; }
  $args['content'] = "<img src='graph.php?type=".$args['graph_type']."&amp;id=".$args['interface_id']."&amp;from=".$args['from']."&amp;to=".$args['to']."&amp;width=".$args['width']."&amp;height=".$args['height']."&amp;legend=no&amp;bg=".$args['bg']."'>";
  echo(generate_port_link($args, $args['content']));
}

function print_optionbar_start ($height = 0, $width = 0, $marginbottom = 5)
{
  echo("
    <div class='rounded-5px' style='display: block; background: #e5e5e5; text-align: left; margin-top: 0px;
    margin-bottom: ".$marginbottom."px; " . ($width ? 'max-width: ' . $width . (strstr($width,'%') ? '' : 'px') . '; ' : '') . "
    padding: 7px 14px'>");
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

?>
