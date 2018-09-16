<?php
echo '<table class="table table-hover table-condensed">
    <thead>
        <tr>
            <th>Port</th>
            <th>MAC Address</th>
            <th>IP Address</th>
            <th>AuthZ</th>
            <th>Domain</th>
            <th>Mode</th>
            <th>Username</th>
            <th>By</th>
            <th>Time Out</th>
            <th>Time Left</th>
            <th>AuthN</th>
        </tr>
    </thead>';

foreach (dbFetchRows('SELECT * FROM ports_nac WHERE device_id = '.$device['device_id'].' ORDER BY `ports_nac`.`port_index` ASC') as $nac) {

    echo '<td>' . $nac['port_descr'] . '</td>';
    echo '<td>' . strtoupper($nac['PortAuthSessionMacAddress']) . '</td>';
    echo '<td>' . $nac['PortAuthSessionIPAddress'] . '</td>';
    echo '<td>' . $nac['PortAuthSessionAuthzStatus'] . '</td>';
    echo '<td>' . strtoupper($nac['PortAuthSessionDomain']) . '</td>';
    echo '<td>' . $nac['PortAuthSessionHostMode'] . '</td>';
    echo '<td>' . $nac['PortAuthSessionUserName'] . '</td>';
    echo '<td>' . $nac['PortAuthSessionAuthzBy'] . '</td>';
    echo '<td>' . $nac['PortAuthSessionTimeOut'] . '</td>';
    echo '<td>' . $nac['PortAuthSessionTimeLeft'] . '</td>';

    echo '<td>' . $nac['PortAuthSessionAuthnStatus'] . '</td></tr>';
}
echo '</table>';
