<?php

echo('<table border="0" cellspacing="0" cellpadding="5" width="100%">');
$i = "1";

foreach (dbFetchRows("SELECT * FROM ipv4_mac WHERE interface_id = ?", array($port['interface_id'])) as $arp)
{
  if (!is_integer($i/2)) { $bg_colour = $list_colour_a; } else { $bg_colour = $list_colour_b; }
  $arp_host = dbFetchRow("SELECT * FROM ipv4_addresses AS A, ports AS I, devices AS D WHERE A.ipv4_address = ? AND I.interface_id = A.interface_id AND D.device_id = I.device_id", array($arp['ipv4_address']));

  if ($arp_host) { $arp_name = generate_device_link($arp_host); } else { unset($arp_name); }
  if ($arp_host) { $arp_if = generate_port_link($arp_host); } else { unset($arp_if); }

  if ($arp_host['device_id'] == $device['device_id']) { $arp_name = "Localhost"; }
  if ($arp_host['interface_id'] == $arp['interface_id']) { $arp_if = "Local Port"; }

  echo('
  <tr bgcolor="' . $bg_colour .'">
    <td width="160">'.formatmac($arp['mac_address']).'</td>
    <td width="140">'.$arp['ipv4_address'].'</td>
    <td width="200">'.$arp_name.'</td>
    <td>'.$arp_if.'</td>
  </tr>');
  $i++;
}

echo('</table>');

?>
