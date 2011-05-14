<?php

$sql = "SELECT * FROM ipv4_mac AS M, ports AS I WHERE I.interface_id = M.interface_id AND I.device_id = '".$device['device_id']."'";
$query = mysql_query($sql);

echo('<table border="0" cellspacing="0" cellpadding="5" width="100%">');
echo('<tr><th>Port</th><th>MAC address</th><th>IPv4 address</th><th>Remote device</th><th>Remote port</th></tr>');

$i = "1";

while ($arp = mysql_fetch_assoc($query))
{
  if (!is_integer($i/2)) { $bg_colour = $list_colour_a; } else { $bg_colour = $list_colour_b; }

  $arp_host = mysql_fetch_assoc(mysql_query("SELECT * FROM ipv4_addresses AS A, ports AS I, devices AS D WHERE A.ipv4_address = '".$arp['ipv4_address']."' AND I.interface_id = A.interface_id AND D.device_id = I.device_id"));

  if ($arp_host) { $arp_name = generate_device_link($arp_host); } else { unset($arp_name); }
  if ($arp_host) { $arp_if = generate_port_link($arp_host); } else { unset($arp_if); }

  if ($arp_host['device_id'] == $device['device_id']) { $arp_name = "Localhost"; }
  if ($arp_host['interface_id'] == $arp['interface_id']) { $arp_if = "Local Port"; }

  echo("
  <tr bgcolor=$bg_colour>
    <td width=200><b>".generate_port_link(array_merge($arp, $device))."</b></td>
    <td width=160>".formatmac($arp['mac_address'])."</td>
    <td width=160>".$arp['ipv4_address']."</td>
    <td width=280>$arp_name</td>
    <td>$arp_if</td>
  </tr>");
  $i++;
}

echo("</table>");

?>
