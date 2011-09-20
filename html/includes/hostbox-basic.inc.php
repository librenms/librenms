<?php

if ($bg == $list_colour_b) { $bg = $list_colour_a; } else { $bg = $list_colour_b; }

if ($device['status'] == '0')
{
  $class = "list-device-down";
} else {
  $class = "list-device";
}
if ($device['ignore'] == '1')
{
  $class = "list-device-ignored";
  if ($device['status'] == '1')
  {
    $class = "list-device-ignored-up";
  }
}
if ($device['disabled'] == '1')
{
  $class = "list-device-disabled";
}

$type = strtolower($device['os']);

if ($device['os'] == "ios") { formatCiscoHardware($device, true); }
$device['os_text'] = $config['os'][$device['os']]['text'];


echo('  <tr class="'.$class.'" bgcolor="' . $bg . '" onmouseover="this.style.backgroundColor=\'#fdd\';" onmouseout="this.style.backgroundColor=\'' . $bg . '\';"
          onclick="location.href=\'device/'.$device['device_id'].'/\'" style="cursor: pointer;">
          <td width="300"><span style="font-size: 15px;">' . generate_device_link($device) . '</span></td>'
        );

echo('    <td>' . $device['hardware'] . ' ' . $device['features'] . '</td>');
echo('    <td>' . $device['os_text'] . ' ' . $device['version'] . '</td>');
echo('    <td>' . formatUptime($device['uptime'], 'short') . ' <br />');

if (get_dev_attrib($device,'override_sysLocation_bool')) {  $device['location'] = get_dev_attrib($device,'override_sysLocation_string'); }
echo('    ' . truncate($device['location'],32, '') . '</td>');

echo (' </tr>');

?>
