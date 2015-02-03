<?php

if ($bg == $list_colour_b) { $bg = $list_colour_a; } else { $bg = $list_colour_b; }

if ($device['status'] == '0')
{
  $class = "bg-danger";
} else {
  $class = "bg-primary";
}
if ($device['ignore'] == '1')
{
  $class = "bg-warning";
  if ($device['status'] == '1')
  {
    $class = "bg-success";
  }
}
if ($device['disabled'] == '1')
{
  $class = "bg-info";
}

$type = strtolower($device['os']);

if ($device['os'] == "ios") { formatCiscoHardware($device, true); }
$device['os_text'] = $config['os'][$device['os']]['text'];
$image = getImage($device);

echo('  <tr>
          <td class="'. $class .' " '. $cell_click .'></td>
          <td '. $cell_click .'>' . $image . '</td>
          <td '. $cell_click .'><span style="font-size: 15px;">' . generate_device_link($device) . '</span></td>'
        );

echo('<td '. $cell_click .'>');
if ($port_count) { echo(' <img src="images/icons/port.png" align=absmiddle /> '.$port_count); }
echo('<br />');
if ($sensor_count) { echo(' <img src="images/icons/sensors.png" align=absmiddle /> '.$sensor_count); }
echo('</td>');
echo('    <td '. $cell_click .'>' . $device['hardware'] . ' ' . $device['features'] . '</td>');
echo('    <td '. $cell_click .'>' . $device['os_text'] . ' ' . $device['version'] . '</td>');
echo('    <td '. $cell_click .'>' . formatUptime($device['uptime'], 'short') . ' <br />');

if (get_dev_attrib($device,'override_sysLocation_bool')) {  $device['location'] = get_dev_attrib($device,'override_sysLocation_string'); }
echo('    ' . truncate($device['location'],32, '') . '</td>');
require 'hostbox-menu.inc.php';

echo(' </tr>');

?>
