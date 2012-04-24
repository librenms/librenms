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

$port_count   = dbFetchCell("SELECT COUNT(*) FROM `ports` WHERE `device_id` = ?", array($device['device_id']));
$sensor_count = dbFetchCell("SELECT COUNT(*) FROM `sensors` WHERE `device_id` = ?", array($device['device_id']));

echo('  <tr class="'.$class.'" onmouseover="this.style.backgroundColor=\'#fdd\';" onmouseout="this.style.backgroundColor=\'' . $bg . '\';"
          onclick="location.href=\'device/device='.$device['device_id'].'/\'" style="cursor: pointer;">
          <td width="1" style="background-color: '.$table_tab_colour.';"></td>
          <td width="40" class="paddedcell" align="center" valign="middle">' . $image . '</td>
          <td width="300" class="paddedcell"><span style="font-size: 15px;">' . generate_device_link($device) . '</span>
          <br />' . $device['sysName'] . '</td>'
        );

echo('<td width="55">');
if ($port_count) { echo(' <img src="images/icons/port.png" align=absmiddle /> '.$port_count); }
echo('<br />');
if ($sensor_count) { echo(' <img src="images/icons/sensors.png" align=absmiddle /> '.$sensor_count); }
echo('</td>');
echo('    <td class="paddedcell">' . $device['hardware'] . '<br />' . $device['features'] . '</td>');
echo('    <td class="paddedcell">' . $device['os_text'] . '<br />' . $device['version'] . '</td>');
echo('    <td class="paddedcell">' . formatUptime($device['uptime'], 'short') . ' <br />');

if (get_dev_attrib($device,'override_sysLocation_bool')) {  $device['location'] = get_dev_attrib($device,'override_sysLocation_string'); }
echo('    ' . truncate($device['location'],32, '') . '</td>');

echo(' </tr>');

?>
