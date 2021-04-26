<?php

$vlans = dbFetchRows("SELECT * FROM `ports_vlans` AS PV, vlans AS V WHERE PV.`port_id` = '" . $port['port_id'] . "' and PV.`device_id` = '" . $device['device_id'] . "' AND V.`vlan_vlan` = PV.vlan AND V.device_id = PV.device_id");

echo '<table border="0" cellspacing="0" cellpadding="5" width="100%">';

echo '<tr><th>VLAN</th><th>Description</th><th>Cost</th><th>Priority</th><th>State</th><th>Other Ports</th></tr>';

$row = 0;
foreach ($vlans as $vlan) {
    $row++;
    if (is_integer($row / 2)) {
        $row_colour = \LibreNMS\Config::get('list_colour.even');
    } else {
        $row_colour = \LibreNMS\Config::get('list_colour.odd');
    }

    echo '<tr bgcolor="' . $row_colour . '">';

    echo '<td width=100 class=list-large> Vlan ' . $vlan['vlan'] . '</td>';
    echo '<td width=200 class=box-desc>' . $vlan['vlan_name'] . '</td>';

    if ($vlan['state'] == 'blocking') {
        $class = 'red';
    } elseif ($vlan['state'] == 'forwarding') {
        $class = 'green';
    } else {
        $class = 'none';
    }

    echo '<td>' . $vlan['cost'] . '</td><td>' . $vlan['priority'] . "</td><td class=$class>" . $vlan['state'] . '</td>';

    $traverse_ifvlan = true;
    $vlan_ports = [];
    $otherports = dbFetchRows('SELECT * FROM `ports_vlans` AS V, `ports` as P WHERE V.`device_id` = ? AND V.`vlan` = ? AND P.port_id = V.port_id', [$device['device_id'], $vlan['vlan']]);
    foreach ($otherports as $otherport) {
        if ($otherport['untagged']) {
            $traverse_ifvlan = false;
        }
        $vlan_ports[$otherport['ifIndex']] = $otherport;
    }

    if ($traverse_ifvlan) {
        $otherports = dbFetchRows(
            'SELECT * FROM ports WHERE `device_id` = ? AND `ifVlan` = ?',
            [$device['device_id'], $vlan['vlan']]
        );
        foreach ($otherports as $otherport) {
            $vlan_ports[$otherport['ifIndex']] = array_merge($otherport, ['untagged' => '1']);
        }
    }

    ksort($vlan_ports);

    echo '<td>';
    $vsep = '';
    foreach ($vlan_ports as $otherport) {
        $otherport = cleanPort($otherport);
        echo $vsep . generate_port_link($otherport, makeshortif($otherport['ifDescr']));
        if ($otherport['untagged']) {
            echo '(U)';
        }

        $vsep = ', ';
    }

    echo '</td>';
    echo '</tr>';
}//end foreach

echo '</table>';
