<?php

if ($bg == $list_colour_b) { $bg = $list_colour_a; } else { $bg = $list_colour_b; }

if ($device['status'] == '0')
{
  $class = "list-device-down";
  $table_tab_colour = "#cc0000";
} else {
  $class = "list-device";
  $table_tab_colour = "#0000cc";
}
if ($device['ignore'] == '1')
{
  $class = "list-device-ignored";
  $table_tab_colour = "#aaaaaa";
  if ($device['status'] == '1')
  {
    $class = "list-device-ignored-up";
    $table_tab_colour = "#009900";
  }
}
if ($device['disabled'] == '1')
{
  $class = "list-device-disabled";
  $table_tab_colour = "#aaaaaa";
}

$type = strtolower($device['os']);

$image = getImage($device);
if ($device['os'] == "ios") { formatCiscoHardware($device, true); }
$device['os_text'] = $config['os'][$device['os']]['text'];

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

$graph_array = array();
$graph_array['height'] = "100";
$graph_array['width']  = "310";
$graph_array['to']     = $config['time']['now'];
$graph_array['device'] = $device['device_id'];
$graph_array['type']   = "device_bits";
$graph_array['from']   = $config['time']['day'];
$graph_array['legend'] = "no";

$graph_array['height'] = "45";
$graph_array['width']  = "175";
$graph_array['bg']     = "FFFFFFFF";


if (get_dev_attrib($device,'override_sysLocation_bool')) {  $device['location'] = get_dev_attrib($device,'override_sysLocation_string'); }

echo('  <tr class="'.$class.'" onmouseover="this.style.backgroundColor=\'#fdd\';" onmouseout="this.style.backgroundColor=\'' . $bg . '\';"
          onclick="location.href=\'device/device='.$device['device_id'].'/\'" style="cursor: pointer;">
          <td width="1" style="background-color: '.$table_tab_colour.';"></td>
          <td width="40" class="paddedcell" align="center" valign="middle">' . $image . '</td>
          <td width="300" class="paddedcell"><span style="font-size: 15px;">' . generate_device_link($device) . '</span>
          <br />' . truncate($device['location'],32, '') . '<br />');
echo(formatUptime($device['uptime'], 'short'));

echo('</td>');
echo('<td>');
echo('<div class="pull-right" style="height: 50px; padding: 2px; margin: 0;">');
foreach ($graphs as $entry)
{
  if ($entry['graph'])
  {
    $graph_array['type']   = $entry['graph'];
    $graph_array['popup_title'] = $entry['text'];

    print_graph_popup($graph_array);
    
  }
}

unset($graph_array);

echo("</div>");
echo('</td>');

echo(' </tr>');

?>