<?php

if ($bg == $list_colour_b) { $bg = $list_colour_a; } else { $bg = $list_colour_b; }

if ($device['status'] == '0') 
{
  $tr = "row-alert";
  $class = "list-device-down"; 
} else { 
  $class = "list-device"; unset ($tr); 
}
if ($device['ignore'] == '1') 
{
  $tr = "bordercolor=#eeeeee";
  $class = "list-device-ignored";
  if ($device['status'] == '1') { $class = "list-device-ignored-up"; }
}

$type = strtolower($device['os']);
unset($image);

$image = getImage($device['device_id']);
if ($device['os'] == "ios") { formatCiscoHardware($device, true); }
$device['os_text'] = $config['os'][$device['os']]['text'];

$port_count = mysql_result(mysql_query("SELECT COUNT(*) FROM `ports` WHERE `device_id` = '".$device['device_id']."'"),0);
$sensor_count = mysql_result(mysql_query("SELECT COUNT(*) FROM `sensors` WHERE `device_id` = '".$device['device_id']."'"),0);

echo('  <tr class="'.$tr.'" bgcolor="' . $bg . '" onmouseover="this.style.backgroundColor=\'#fdd\';" onmouseout="this.style.backgroundColor=\'' . $bg . '\';"
          onclick="location.href=\'device/'.$device['device_id'].'/\'" style="cursor: pointer;">
          <td width="40" align="center" valign="middle">' . $image . '</td>
          <td width="300"><span style="font-weight: bold; font-size: 14px;">' . generate_device_link($device) . '</span>
          <br />' . $device['sysName'] . '</td>'
	);

echo ('<td width="55">');
if ($port_count) { echo(' <img src="images/icons/port.png" align=absmiddle /> '.$port_count); }
echo('<br />');
if ($sensor_count) { echo(' <img src="images/icons/sensors.png" align=absmiddle /> '.$sensor_count); }
echo ('</td>');

echo('    <td>' . $device['hardware'] . '<br />' . $device['features'] . '</td>');

echo('    <td>' . $device['os_text'] . '<br />' . $device['version'] . '</td>');

echo('    <td>' . formatUptime($device['uptime']) . ' <br />');

if (get_dev_attrib($device,'override_sysLocation_bool')) {  $device['location'] = get_dev_attrib($device,'override_sysLocation_string'); }
echo('    ' . truncate($device['location'],32, '') . '</td>');

echo (' </tr>');

?>
