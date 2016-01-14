<?php

echo '<tr bgcolor="#ffffff"><th>Port</th><th>Priority</th><th>State</th><th>Enable</th><th>Path cost</th><th>Designated root</th><th>Designated cost</th><th>Designated bridge</th><th>Designated port</th><th>Fwd trasitions</th></tr>';
//SELECT ps.*, p.ifIndex FROM `ports_stp` ps JOIN `ports` p ON ps.port_id=p.port_id WHERE ps.device_id = 67 ORDER BY ps.port_id
foreach (dbFetchRows("SELECT * FROM `ports_stp` WHERE `device_id` = ? ORDER BY 'port_id'", array($device['device_id'])) as $stp_ports_db) {

    $stp_ports = [
        $stp_ports_db['port_id'],
        $stp_ports_db['priority'],
        $stp_ports_db['state'],
        $stp_ports_db['enable'],
        $stp_ports_db['pathCost'],
        $stp_ports_db['designatedRoot'],
        $stp_ports_db['designatedCost'],
        $stp_ports_db['designatedBridge'],
        $stp_ports_db['designatedPort'],
        $stp_ports_db['forwardTransitions']
    ];
    echo '<tr>';

    foreach ($stp_ports as $value) {
       echo "<td>$value</td>";
    }

    echo '</tr>';
}
