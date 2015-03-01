<?php

if (isset($bg) && $bg == $list_colour_b) { $bg = $list_colour_a; } else { $bg = $list_colour_b; }

if ($device['status'] == '0')
{
  $class = "list-device-down";
  $table_tab_colour = "bg-danger";
} else {
  $class = "list-device";
  $table_tab_colour = "bg-primary";
}
if ($device['ignore'] == '1')
{
  $class = "list-device-ignored";
  $table_tab_colour = "bg-warning";
  if ($device['status'] == '1')
  {
    $class = "list-device-ignored-up";
    $table_tab_colour = "bg-success";
  }
}
if ($device['disabled'] == '1')
{
  $class = "list-device-disabled";
  $table_tab_colour = "bg-info";
}

$type = strtolower($device['os']);

$image = getImage($device);
if ($device['os'] == "ios") { formatCiscoHardware($device, true); }
$device['os_text'] = $config['os'][$device['os']]['text'];

$port_count   = dbFetchCell("SELECT COUNT(*) FROM `ports` WHERE `device_id` = ?", array($device['device_id']));
$sensor_count = dbFetchCell("SELECT COUNT(*) FROM `sensors` WHERE `device_id` = ?", array($device['device_id']));

echo('  <tr>
          <td class="'. $table_tab_colour .' '. $cell_click .'"></td>
          <td '. $cell_click .'>' . $image . '</td>
          <td '. $cell_click .'><span style="font-size: 15px;">' . generate_device_link($device) . '</span>
          <br />' . $device['sysName'] . '</td>'
        );

echo('<td '. $cell_click .'>');
if ($port_count) { echo(' <img src="images/icons/port.png" align=absmiddle /> '.$port_count); }
echo('<br />');
if ($sensor_count) { echo(' <img src="images/icons/sensors.png" align=absmiddle /> '.$sensor_count); }
echo('</td>');
echo('    <td '. $cell_click .'>' . $device['hardware'] . '<br />' . $device['features'] . '</td>');
echo('    <td '. $cell_click .'>' . $device['os_text'] . '<br />' . $device['version'] . '</td>');
echo('    <td '. $cell_click .'>' . formatUptime($device['uptime'], 'short') . ' <br />');

if (get_dev_attrib($device,'override_sysLocation_bool')) {  $device['location'] = get_dev_attrib($device,'override_sysLocation_string'); }
echo('    ' . truncate($device['location'],32, '') . '</td>');
require 'hostbox-menu.inc.php';

echo(' </tr>');

?>
