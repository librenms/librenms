<?php

echo '<table class="table table-hover table-condensed">
    <thead>
        <tr>
            <th>Local Port</th>
            <th>Remote Device</th>
            <th>Remote Port</th>
            <th>Protocol</th>
        </tr>
    </thead>';

foreach (dbFetchRows('SELECT * FROM links AS L, ports AS I WHERE I.device_id = ? AND I.port_id = L.local_port_id order by ifName', [$device['device_id']]) as $neighbour) {
    $neighbour = cleanPort($neighbour);
    echo '<td>' . generate_port_link($neighbour) . '<br>' . $neighbour['ifAlias'] . '</td>';
    if (is_numeric($neighbour['remote_port_id']) && $neighbour['remote_port_id']) {
        $remote_port = cleanPort(get_port_by_id($neighbour['remote_port_id']));
        $remote_device = device_by_id_cache($remote_port['device_id']);
        echo '<td>' . generate_device_link($remote_device) . '<br>' . $remote_device['hardware'] . '</td>
              <td>' . generate_port_link($remote_port) . '<br>' . $remote_port['ifAlias'] . '</td>';
    } else {
        echo '<td>' . $neighbour['remote_hostname'] . '<br>' . $neighbour['remote_platform'] . '</td>
              <td>' . $neighbour['remote_port'] . '</td>';
    }
    echo '<td>' . strtoupper($neighbour['protocol']) . '</td></tr>';
}
echo '</table>';
