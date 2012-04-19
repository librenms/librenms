<?php

if ($config['overview_show_sysDescr'])
{
  echo('<div style="font-family: courier, serif; margin: 10px"><strong>' . $device['sysDescr'] . "</strong></div>");
}

$uptime = $device['uptime'];

if ($device['os'] == "ios") { formatCiscoHardware($device); }
if ($device['features']) { $device['features'] = "(".$device['features'].")"; }
$device['os_text'] = $config['os'][$device['os']]['text'];

echo('<table width="100%">');

if ($device['hardware'])
{
  echo('<tr>
        <td class="list-bold">Hardware</td>
        <td>' . $device['hardware']. '</td>
      </tr>');
}

echo('<tr>
        <td class="list-bold">Operating System</td>
        <td>' . $device['os_text'] . ' ' . $device['version'] . ' ' . $device['features'] . ' </td>
      </tr>');

if ($device['serial'])
{
  echo('<tr>
        <td class="list-bold">Serial</td>
        <td>' . $device['serial']. '</td>
      </tr>');
}

if ($device['sysContact'])
{
  echo('<tr>
        <td class="list-bold">Contact</td>');
  if (get_dev_attrib($device,'override_sysContact_bool'))
  {
    echo('
        <td>' . htmlspecialchars(get_dev_attrib($device,'override_sysContact_string')) . '</td>
      </tr>
      <tr>
        <td class="list-bold">SNMP Contact</td>');
  }
  echo('
        <td>' . htmlspecialchars($device['sysContact']). '</td>
      </tr>');
}

if ($device['location'])
{
  echo('<tr>
        <td class="list-bold">Location</td>
        <td>' . $device['location']. '</td>
      </tr>');
  if (get_dev_attrib($device,'override_sysLocation_bool') && !empty($device['real_location']))
  {
    echo('<tr>
        <td class="list-bold">SNMP Location</td>
        <td>' . $device['real_location']. '</td>
      </tr>');
  }
}

if ($uptime)
{
  echo('<tr>
        <td class="list-bold">Uptime</td>
        <td>' . formatUptime($uptime) . '</td>
      </tr>');
}

echo('
    </table>');

?>
