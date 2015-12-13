<?php

$tableArp=array('Port','MAC address','IPv4 address','Remote device','Remote port');

if(!empty($device['vrf_lite_cisco'])){
    $tableArp= array_merge($tableArp,array('VRF','Intance'));
}

if(!empty($vars['vrf-lite'])){
    $arpsTmp=dbFetchRows("SELECT * FROM ipv4_mac AS M, ports AS I WHERE I.port_id = M.port_id AND I.device_id = ? AND M.context_name in (select context_name from vrf_lite_cisco where vrf_name = ? AND device_id = ?", array($device['device_id'],$vars['vrf-lite'],$device['device_id']));
}
else{
    $arpsTmp=dbFetchRows("SELECT * FROM ipv4_mac AS M, ports AS I WHERE I.port_id = M.port_id AND I.device_id = ?", array($device['device_id']));
}
echo '<table class="table table-condensed">';
//echo('<table border="0" cellspacing="0" cellpadding="'.count($tableArp).'" width="100%">');
echo('<tr>');

foreach ($tableArp as $value) {
    echo "<th>$value</th>";
}
echo '</tr>';

$i = '1';

foreach ($arpsTmp as $arp) {
    if (!is_integer($i / 2)) {
        $bg_colour = $list_colour_a;
    }
    else {
        $bg_colour = $list_colour_b;
    }

     $arp_host = dbFetchRow('SELECT * FROM ipv4_addresses AS A, ports AS I, devices AS D WHERE A.ipv4_address = ? AND I.port_id = A.port_id AND D.device_id = I.device_id  AND A.context_name = ?', array($arp['ipv4_address'],$arp['context_name']));

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
    <td>$arp_if</td>";
    if(!empty($device['vrf_lite_cisco'])){
        if(!empty($arp['context_name'])){
            if(key_exists($arp['context_name'], $device['vrf_lite_cisco'])){
                echo '<td>'.$device['vrf_lite_cisco'][$arp['context_name']]['vrf_name'].'</td>';
                echo '<td>'.$device['vrf_lite_cisco'][$arp['context_name']]['intance_name'].'</td>';
            }
            else{
                echo '<td>ERROR vrf</td>';
                echo '<td>ERROR vrf</td>';
            }

        }
        else{
            echo '<td></td>';
            echo '<td></td>';
        }
    
    }
    unset($arpsTmp);
  echo "</tr>";
    $i++;
}//end foreach

echo '</table>';
