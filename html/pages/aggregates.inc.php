<table class="table table-condensed table-hover table-striped">

<?php

if ($bg == '#ffffff') {
    $bg = '#e5e5e5';
} else {
    $bg = '#ffffff';
}

$ports = [];
# In order to work around librenms foreslash handling, convert &slsh; back to /
$str = preg_replace('/&slsh;/', '/', $vars['iface']);
$device_groups = explode(',', html_entity_decode($str));
foreach ($device_groups as $string) {
    $interfaces_for_device = explode(':', $string);
    $device = array_shift($interfaces_for_device);
    #array_push($device_data[$device], $interfaces_for_device)
    foreach ($interfaces_for_device as $interface_name) {
        $interface_name = preg_replace('/\*/', '%', $interface_name);
        $ports = array_merge($ports, dbFetchRows(
            "SELECT * FROM `ports` as I, `devices` as D
                WHERE (I.ifDescr LIKE \"$interface_name\"
                OR I.ifName LIKE \"$interface_name\")
                AND I.device_id = D.device_id
                AND D.hostname LIKE \"$device%\""
        ));
    }
}

foreach ($ports as $port) {
    $if_list  .= $seperator.$port['port_id'];
    $seperator = ',';
}

unset($seperator);

$label = 'aggregate ports';
if ($vars['label']) {
    $label = $vars['label'];
}

echo "<tr class='iftype'>
    <td colspan='5'><span class=list-large>Total Graph for $label</span><br />'";

if ($if_list) {
    $graph_type      = 'multiport_bits_separate';
    $port['port_id'] = $if_list;

    include 'includes/print-interface-graphs.inc.php';

    echo "</td></tr>
    <tr bgcolor='$iftype'>
        <th>Device</th>
        <th>Interface</th>
        <th>Speed</th>
        <th>Circuit</th>
        <th>Notes</th>
    </tr>";

    foreach ($ports as $port) {
        $port = cleanPort($port);
        $done = 'yes';
        unset($class);
        $port['ifAlias'] = str_ireplace($type.': ', '', $port['ifAlias']);
        $port['ifAlias'] = str_ireplace('[PNI]', 'Private', $port['ifAlias']);
        $ifclass         = ifclass($port['ifOperStatus'], $port['ifAdminStatus']);
        if ($bg == '#ffffff') {
            $bg = '#e5e5e5';
        } else {
            $bg = '#ffffff';
        }

        echo "<tr class='iftype'>
            <td><span class=list-large>".generate_port_link($port, $port['port_descr_descr'])."</span><br />
            <span class=interface-desc style='float: left;'>".generate_device_link($port).' '.generate_port_link($port).' </span></td>
            <td>'.generate_port_link($port, makeshortif($port['ifDescr'])).'</td>
            <td>'.$port['port_descr_speed'].'</td>
            <td>'.$port['port_descr_circuit'].'</td>
            <td>'.$port['port_descr_notes']."</td>
            </tr>
            <tr class='iftype'>
            <td colspan='5'";

        if (dbFetchCell('SELECT count(*) FROM mac_accounting WHERE port_id = ?', array($port['port_id']))) {
            echo "<span style='float: right;'><a href='" . generate_url(array('page'=>'device', 'device'=>$port['device_id'], 'tab'=>'port', 'port'=>$port['port_id'], 'view'=>'macaccounting')) . "'><i class='fa fa-pie-chart fa-lg icon-theme' aria-hidden='true'></i> MAC Accounting</a></span>";
        }

        echo '<br />';

        if (file_exists(get_port_rrdfile_path($port['hostname'], $port['port_id']))) {
            $graph_type = 'port_bits';

            include 'includes/print-interface-graphs.inc.php';
        }

        echo '</td></tr>';
    }
} else {
    echo 'None found.</td></tr>';
}

?>
</table>
