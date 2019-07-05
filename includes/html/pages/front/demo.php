<table border=0 cellpadding=10 cellspacing=10 width=100%>
  <tr>
    <td colspan=2>

<?php

echo '<table><tr>';

$dev_list = array(
    '6'  => 'Central Fileserver',
    '7'  => 'NE61 Fileserver',
    '34' => 'DE56 Fileserver',
);

foreach ($dev_list as $device_id => $descr) {
    echo '<td>';
    echo "<div style='font-size: 16px; font-weight: bold; color: #555555;'>".$descr.'</div>';
    $graph_array['height']      = '100';
    $graph_array['width']       = '310';
    $graph_array['to'] = \LibreNMS\Config::get('time.now');
    $graph_array['device']      = $device_id;
    $graph_array['type']        = 'device_bits';
    $graph_array['from'] = \LibreNMS\Config::get('time.day');
    $graph_array['legend']      = 'no';
    $graph_array['popup_title'] = $descr;
    // $graph_array['link']   = generate_device_link($device_id);
    print_graph_popup($graph_array);

    $graph_array['height'] = '50';
    $graph_array['width']  = '180';

    echo "<div style='margin: 1px; float: left; padding: 5px; background-color: #e5e5e5;'>";
    $graph_array['type'] = 'device_ucd_memory';
    print_graph_popup($graph_array);
    echo '</div>';

    echo "<div style='margin: 1px; float: left; padding: 5px; background-color: #e5e5e5;'>";
    $graph_array['type'] = 'device_processor';
    print_graph_popup($graph_array);
    echo '</div>';

    echo "<div style='margin: 1px; float: left; padding: 5px; background-color: #e5e5e5;'>";
    $graph_array['type'] = 'device_storage';
    print_graph_popup($graph_array);
    echo '</div>';

    echo "<div style='margin: 1px; float: left; padding: 5px; background-color: #e5e5e5;'>";
    $graph_array['type'] = 'device_diskio';
    print_graph_popup($graph_array);
    echo '</div>';

    echo '</td>';
}//end foreach

echo '</tr></table>';

?>

    </td>
  </tr>
  <tr>
    <td bgcolor=#e5e5e5 valign=top>
<?php
$nodes = array();

$sql = "SELECT * FROM `devices` AS D, `devices_attribs` AS A WHERE D.status = '1' AND A.device_id = D.device_id AND A.attrib_type = 'uptime' AND A.attrib_value > '0' AND A.attrib_value < '86400'";

foreach (dbFetchRows($sql) as $device) {
    unset($already);
    $i = 0;
    while ($i <= count($nodes)) {
        $thisnode = $device['device_id'];
        if ($nodes[$i] == $thisnode) {
            $already = 'yes';
        }

        $i++;
    }

    if (!$already) {
        $nodes[] = $device['device_id'];
    }
}

$sql = "SELECT * FROM `devices` WHERE `status` = '0' AND `ignore` = '0'";
foreach (dbFetchRows($sql) as $device) {
    if (device_permitted($device['device_id'])) {
        echo "<div style='text-align: center; margin: 2px; border: solid 2px #d0D0D0; float: left; margin-right: 2px; padding: 3px; width: 118px; height: 85px; background: #ffbbbb;'>
            <strong>".generate_device_link($device, shorthost($device['hostname']))."</strong><br />
            <span style='font-size: 14px; font-weight: bold; margin: 5px; color: #c00;'>Device Down</span><br />
            <span class=body-date-1>".truncate($device['location'], 35).'</span>
            </div>';
    }
}

if (\LibreNMS\Config::get('warn.ifdown')) {
    $sql = "SELECT * FROM `ports` AS I, `devices` AS D WHERE I.device_id = D.device_id AND ifOperStatus = 'down' AND ifAdminStatus = 'up' AND D.ignore = '0' AND I.ignore = '0'";
    foreach (dbFetchRows($sql) as $interface) {
        if (port_permitted($interface['port_id'])) {
            echo "<div style='text-align: center; margin: 2px; border: solid 2px #D0D0D0; float: left; margin-right: 2px; padding: 3px; width: 118px; height: 85px; background: #ffddaa;'>
                <strong>".generate_device_link($interface, shorthost($interface['hostname']))."</strong><br />
                <span style='font-size: 14px; font-weight: bold; margin: 5px; color: #c00;'>Port Down</span><br />
                <strong>".generate_port_link($interface, makeshortif($interface['ifDescr'])).'</strong><br />
                <span class=body-date-1>'.truncate($interface['ifAlias'], 15).'</span>
                </div>';
        }
    }
}

$sql = "SELECT * FROM `services` AS S, `devices` AS D WHERE S.device_id = D.device_id AND service_status = 'down' AND D.ignore = '0' AND S.service_ignore = '0'";
foreach (dbFetchRows($sql) as $service) {
    if (device_permitted($service['device_id'])) {
        echo "<div style='text-align: center; margin: 2px; border: solid 2px #D0D0D0; float: left; margin-right: 2px; padding: 3px; width: 118px; height: 85px; background: #ffddaa;'>
            <strong>".generate_device_link($service, shorthost($service['hostname']))."</strong><br />
            <span style='font-size: 14px; font-weight: bold; margin: 5px; color: #c00;'>Service Down</span><br />
            <strong>".$service['service_type'].'</strong><br />
            <span class=body-date-1>'.truncate($interface['ifAlias'], 15).'</span>
            </center></div>';
    }
}

$sql = "SELECT * FROM `devices` AS D, bgpPeers AS B WHERE bgpPeerAdminStatus = 'start' AND bgpPeerState != 'established' AND B.device_id = D.device_id";
foreach (dbFetchRows($sql) as $peer) {
    if (device_permitted($peer['device_id'])) {
        echo "<div style='text-align: center; margin: 2px; border: solid 2px #D0D0D0; float: left; margin-right: 2px; padding: 3px; width: 118px; height: 85px; background: #ffddaa;'>
            <strong>".generate_device_link($peer, shorthost($peer['hostname']))."</strong><br />
            <span style='font-size: 14px; font-weight: bold; margin: 5px; color: #c00;'>BGP Down</span><br />
            <strong>".$peer['bgpPeerIdentifier'].'</strong><br />
            <span class=body-date-1>AS'.$peer['bgpPeerRemoteAs'].' '.truncate($peer['astext'], 10).'</span>
            </div>';
    }
}

if (filter_var(\LibreNMS\Config::get('uptime_warning'), FILTER_VALIDATE_FLOAT) !== false && \LibreNMS\Config::get('uptime_warning') > 0) {
    $sql = "SELECT * FROM devices_attribs AS A, `devices` AS D WHERE
        A.attrib_value < '" . \LibreNMS\Config::get('uptime_warning') . "' AND A.attrib_type = 'uptime' AND A.device_id = D.device_id AND ignore = '0' AND disabled = '0'";
    foreach (dbFetchRows($sql) as $device) {
        if (device_permitted($device['device_id']) && $device['attrib_value'] < \LibreNMS\Config::get('uptime_warning') && $device['attrib_type'] == 'uptime') {
            echo "<div style='text-align: center; margin: 2px; border: solid 2px #D0D0D0; float: left; margin-right: 2px; padding: 3px; width: 118px; height: 85px; background: #ddffdd;'>
                <strong>".generate_device_link($device, shorthost($device['hostname']))."</strong><br />
                <span style='font-size: 14px; font-weight: bold; margin: 5px; color: #090;'>Device<br />Rebooted</span><br />
                <span class=body-date-1>".formatUptime($device['attrib_value']).'</span>
                </div>';
        }
    }
}

echo "
    <div style='clear: both;'>$errorboxes</div> <div style='margin: 0px; clear: both;'>

    <h3>Recent Syslog Messages</h3>
    ";

$sql = "SELECT *, DATE_FORMAT(timestamp, '" . \LibreNMS\Config::get('dateformat.mysql.compact') . "') AS date from `syslog` ORDER BY seq DESC LIMIT 20";
echo '<table cellspacing=0 cellpadding=2 width=100%>';
foreach (dbFetchRows($sql) as $entry) {
    $entry = array_merge($entry, device_by_id_cache($entry['device_id']));

    unset($syslog_output);
    include 'includes/html/print-syslog.inc.php';
    echo $syslog_output;
}

echo '</table>';

?>
</td>

  </tr>
  <tr>
</tr></table>
