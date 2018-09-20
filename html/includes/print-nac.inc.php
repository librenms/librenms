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
            <th>Time Out</th>
            <th>Time Left</th>
            <th>AuthC</th>
            <th>Method</th>
        </tr>
    </thead>';

foreach (dbFetchRows('SELECT * FROM ports_nac WHERE device_id = '.$device['device_id'].' ORDER BY `ports_nac`.`port_index` ASC') as $nac) {
    echo '<td>' . $nac['port_descr'] . '</td>';
    echo '<td>' . strtoupper($nac['PortAuthSessionMacAddress']) . '</td>';
    echo '<td>' . $nac['PortAuthSessionIPAddress'] . '</td>';
    if ($nac['PortAuthSessionAuthzStatus'] == 'authorizationSuccess') {
        echo '<td><i class="fa fa-check-circle fa-lg icon-theme"  aria-hidden="true" style="color:green;"></i></td>';
    } elseif ($nac['PortAuthSessionAuthzStatus'] == 'authorizationFailed') {
        echo '<td><i class="fa fa-times-circle fa-lg icon-theme" aria-hidden="true" style="color:red;"></i></td>';
    } else {
        echo '<td>' . $nac['PortAuthSessionAuthzStatus'] . '</td>';
    }
    if ($nac['PortAuthSessionDomain'] == 'voice') {
        echo '<td><i class="fa fa-phone fa-lg icon-theme"  aria-hidden="true"></i></td>';
    } elseif ($nac['PortAuthSessionDomain'] == 'data') {
        echo '<td><i class="fa fa-desktop fa-lg icon-theme"  aria-hidden="true"></i></td>';
    } elseif ($nac['PortAuthSessionDomain'] == 'other') {
        echo '<td><i class="fa fa-exclamation-triangle fa-lg icon-theme"  aria-hidden="true" style="color:red;"></i></td>';
    } else {
        echo '<td>' . strtoupper($nac['PortAuthSessionDomain']) . '</td>';
    }
    echo '<td>' . $nac['PortAuthSessionHostMode'] . '</td>';
    echo '<td>' . $nac['PortAuthSessionUserName'] . '</td>';
    echo '<td>' . $nac['PortAuthSessionTimeOut'] . '</td>';
    echo '<td>' . $nac['PortAuthSessionTimeLeft'] . '</td>';
    if ($nac['PortAuthSessionAuthcStatus'] == 'notRun') {
        echo '<td><span class="label label-primary">notRun</span></td>';
    } elseif ($nac['PortAuthSessionAuthcStatus'] == 'running') {
        echo '<td><span class="label label-primary">running</span></td>';
    } elseif ($nac['PortAuthSessionAuthcStatus'] == 'failedOver') {
        echo '<td><i class="fa fa-times-circle fa-lg icon-theme"  aria-hidden="true" style="color:red;"></i></td>';
    } elseif ($nac['PortAuthSessionAuthcStatus'] == 'authcSuccess') {
        echo '<td><i class="fa fa-check-circle fa-lg icon-theme"  aria-hidden="true" style="color:green;"></i></td>';
    } elseif ($nac['PortAuthSessionAuthcStatus'] == 'authcFailed') {
        echo '<td><i class="fa fa-times-circle fa-lg icon-theme"  aria-hidden="true" style="color:red;"></i></td>';
    } elseif ($nac['PortAuthSessionAuthcStatus'] == '6') {
        echo '<td><i class="fa fa-times-circle fa-lg icon-theme"  aria-hidden="true" style="color:red;"></i></td>';
    } else {
        echo '<td>' . strtoupper($nac['PortAuthSessionAuthcStatus']) . '</td>';
    }
    if ($nac['PortSessionMethod'] == 'dot1x') {
        echo '<td><span class="label label-success">802.1x</span></td>';
    } elseif ($nac['PortSessionMethod'] == 'macAuthBypass') {
        echo '<td><span class="label label-primary">MAB</span></td>';
    } elseif ($nac['PortSessionMethod'] == 'other') {
        echo '<td><span class="label label-danger">Other</span></td>';
    } else {
        echo '<td>' . strtoupper($nac['PortSessionMethod']) . '</td>';
    }
    echo '</tr>';
}
echo '</table>';
