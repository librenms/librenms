<?php

echo '<table class="table table-condensed">';
echo '<tr><th>Port</th><th>MAC address</th><th>IPv4 address</th><th>Remote device</th><th>Remote port</th></tr>';

$i = '1';

foreach (dbFetchRows('SELECT * FROM ipv4_mac AS M, ports AS I WHERE I.port_id = M.port_id AND I.device_id = ?', array($device['device_id'])) as $arp) {
    if (!is_integer($i / 2)) {
        $bg_colour = $list_colour_a;
    }
    else {
        $bg_colour = $list_colour_b;
    }

    $arp_host = dbFetchRow('SELECT * FROM ipv4_addresses AS A, ports AS I, devices AS D WHERE A.ipv4_address = ? AND I.port_id = A.port_id AND D.device_id = I.device_id', array($arp['ipv4_address']));

    if ($arp_host) {
        $arp_name = generate_device_link($arp_host);
    }
    else {
        unset($arp_name);
    }

    if ($arp_host) {
        $arp_if = generate_port_link($arp_host);
    }
    else {
        unset($arp_if);
    }

    if ($arp_host['device_id'] == $device['device_id']) {
        $arp_name = 'Localhost';
    }

    if ($arp_host['port_id'] == $arp['port_id']) {
        $arp_if = 'Local Port';
    }

    echo "
  <tr bgcolor=$bg_colour>
    <td width=200><b>".generate_port_link(array_merge($arp, $device)).'</b></td>
    <td width=160>'.formatmac($arp['mac_address']).'</td>
    <td width=160>'.$arp['ipv4_address']."</td>
    <td width=280>$arp_name</td>
    <td>$arp_if</td>
  </tr>";
    $i++;
}//end foreach

echo '</table>';
