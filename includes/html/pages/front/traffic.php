<table border=0 cellpadding=10 cellspacing=10 width=100%>
  <tr>
    <td bgcolor=#e5e5e5 valign=top>
<?php

use LibreNMS\Config;

$nodes     = array();
$param     = array();
$uptimesql = '';
if (filter_var(Config::get('uptime_warning'), FILTER_VALIDATE_FLOAT) !== false && Config::get('uptime_warning') > 0) {
    $uptimesql = ' AND A.attrib_value < ?';
    $param = [Config::get('uptime_warning')];
}

foreach (dbFetchRows("SELECT * FROM `devices` AS D, `devices_attribs` AS A WHERE D.status = '1' AND A.device_id = D.device_id AND A.attrib_type = 'uptime' AND A.attrib_value > '0' ".$uptimesql, $param) as $device) {
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

foreach (dbFetchRows("SELECT * FROM `devices` WHERE `status` = '0' AND `ignore` = '0'") as $device) {
    if (device_permitted($device['device_id'])) {
        echo "<div style='text-align: center; margin: 2px; border: solid 2px #d0D0D0; float: left; margin-right: 2px; padding: 3px; width: 118px; height: 85px; background: #ffbbbb;'>
            <strong>".generate_device_link($device, shorthost($device['hostname']))."</strong><br />
            <span style='font-size: 14px; font-weight: bold; margin: 5px; color: #c00;'>Device Down</span><br />
            <span class=body-date-1>".truncate($device['location'], 35).'</span>
            </div>';
    }
}

if (Config::get('warn.ifdown')) {
    foreach (dbFetchRows("SELECT * FROM `ports` AS I, `devices` AS D WHERE I.device_id = D.device_id AND ifOperStatus = 'down' AND ifAdminStatus = 'up' AND D.ignore = '0' AND I.ignore = '0'") as $interface) {
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

foreach (dbFetchRows("SELECT * FROM `services` AS S, `devices` AS D WHERE S.device_id = D.device_id AND service_status = 'down' AND D.ignore = '0' AND S.service_ignore = '0'") as $service) {
    if (device_permitted($service['device_id'])) {
        echo "<div style='text-align: center; margin: 2px; border: solid 2px #D0D0D0; float: left; margin-right: 2px; padding: 3px; width: 118px; height: 85px; background: #ffddaa;'>
            <strong>".generate_device_link($service, shorthost($service['hostname']))."</strong><br />
            <span style='font-size: 14px; font-weight: bold; margin: 5px; color: #c00;'>Service Down</span><br />
            <strong>".$service['service_type'].'</strong><br />
            <span class=body-date-1>'.truncate($interface['ifAlias'], 15).'</span>
            </center></div>';
    }
}

foreach (dbFetchRows("SELECT * FROM `devices` AS D, bgpPeers AS B WHERE bgpPeerAdminStatus = 'start' AND bgpPeerState != 'established' AND B.device_id = D.device_id") as $peer) {
    if (device_permitted($peer['device_id'])) {
        echo "<div style='text-align: center; margin: 2px; border: solid 2px #D0D0D0; float: left; margin-right: 2px; padding: 3px; width: 118px; height: 85px; background: #ffddaa;'>
            <strong>".generate_device_link($peer, shorthost($peer['hostname']))."</strong><br />
            <span style='font-size: 14px; font-weight: bold; margin: 5px; color: #c00;'>BGP Down</span><br />
            <strong>".$peer['bgpPeerIdentifier'].'</strong><br />
            <span class=body-date-1>AS'.$peer['bgpPeerRemoteAs'].' '.truncate($peer['astext'], 10).'</span>
            </div>';
    }
}

if (filter_var(Config::get('uptime_warning'), FILTER_VALIDATE_FLOAT) !== false && Config::get('uptime_warning') > 0) {
    foreach (dbFetchRows("SELECT * FROM devices_attribs AS A, `devices` AS D WHERE A.attrib_value < ? AND A.attrib_type = 'uptime' AND A.device_id = D.device_id AND ignore = '0' AND disabled = '0'", [Config::get('uptime_warning')]) as $device) {
        if (device_permitted($device['device_id']) && $device['attrib_value'] < Config::get('uptime_warning') && $device['attrib_type'] == 'uptime') {
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

$sql = "SELECT *, DATE_FORMAT(timestamp, '" . Config::get('dateformat.mysql.compact') . "') AS date from syslog,devices WHERE syslog.device_id = devices.device_id ORDER BY seq DESC LIMIT 20";
echo '<table cellspacing=0 cellpadding=2 width=100%>';
foreach (dbFetchRows($sql) as $entry) {
    unset($syslog_output);
    include 'includes/html/print-syslog.inc.php';
    echo $syslog_output;
}

echo '</table>';

echo '</div>

    </td>
    <td bgcolor=#e5e5e5 width=470 valign=top>';

// this stuff can be customised to show whatever you want....
if (Auth::user()->hasGlobalRead()) {
    $sql = "SELECT * FROM ports AS I, devices AS D WHERE `ifAlias` like 'Transit: %' AND I.device_id = D.device_id ORDER BY I.ifAlias";
    unset($seperator);
    foreach (dbFetchRows($sql) as $interface) {
        $ports['transit'] .= $seperator.$interface['port_id'];
        $seperator         = ',';
    }

    $sql = "SELECT * FROM ports AS I, devices AS D WHERE `ifAlias` like 'Peering: %' AND I.device_id = D.device_id ORDER BY I.ifAlias";
    unset($seperator);
    foreach (dbFetchRows($sql) as $interface) {
        $ports['peering'] .= $seperator.$interface['port_id'];
        $seperator         = ',';
    }

    $sql = "SELECT * FROM ports AS I, devices AS D WHERE `ifAlias` like 'Core: %' AND I.device_id = D.device_id ORDER BY I.ifAlias";
    unset($seperator);
    foreach (dbFetchRows($sql) as $interface) {
        $ports['core'] .= $seperator.$interface['port_id'];
        $seperator      = ',';
    }

    echo "<div style=' margin-bottom: 5px;'>";

    if ($ports['peering'] && $ports['transit']) {
        echo "<div style='width: 235px; '>
            <a href='internet/' onmouseover=\"return overlib('\
            <img src=\'graph.php?type=multiport_bits_duo&amp;id=" . $ports['peering'] . '&amp;idb=' . $ports['transit'] . '&amp;from=' . Config::get('time.day') . '&amp;to=' . Config::get('time.now') . "&amp;width=400&amp;height=150\'>\
            <img src=\'graph.php?type=multiport_bits_duo&amp;id=" . $ports['peering'] . '&amp;idb=' . $ports['transit'] . '&amp;from=' . Config::get('time.week') . '&amp;to=' . Config::get('time.now') . "&amp;width=400&amp;height=150\'>\
            ', CENTER, LEFT, FGCOLOR, '#e5e5e5', BGCOLOR, '#e5e5e5', WIDTH, 400, HEIGHT, 150);\" onmouseout=\"return nd();\"  >" . "<div style='font-size: 16px; font-weight: bold; color: #555555;'>Aggregate Internet Traffic</div>" . "<img src='graph.php?type=multiport_bits_duo&amp;id=" . $ports['peering'] . '&amp;idb=' . $ports['transit'] . '&amp;from=' . Config::get('time.day') . '&amp;to=' . Config::get('time.now') . "&amp;width=385&amp;height=100&amp;legend=no'></a></div>";
    }

    echo '</div>';

    echo "<div style=' margin-bottom: 5px;'>";

    if ($ports['transit']) {
        echo "<div style='width: 235px; float: left;'>
            <a href='iftype/transit/' onmouseover=\"return overlib('\
            <img src=\'graph.php?type=multiport_bits&amp;id=" . $ports['transit'] . '&amp;from=' . Config::get('time.day') . '&amp;to=' . Config::get('time.now') . "&amp;width=400&amp;height=150\'>\
            <img src=\'graph.php?type=multiport_bits&amp;id=" . $ports['transit'] . '&amp;from=' . Config::get('time.week') . '&amp;to=' . Config::get('time.now') . "&amp;width=400&amp;height=150\'>\
            ', CENTER, LEFT, FGCOLOR, '#e5e5e5', BGCOLOR, '#e5e5e5', WIDTH, 400, HEIGHT, 150);\" onmouseout=\"return nd();\"  >" . "<div style='font-size: 16px; font-weight: bold; color: #555555;'>Internet Transit</div>" . "<img src='graph.php?type=multiport_bits&amp;id=" . $ports['transit'] . '&amp;from=' . Config::get('time.day') . '&amp;to=' . Config::get('time.now') . "&amp;width=155&amp;height=100&amp;legend=no'></a></div>";
    }

    if ($ports['peering']) {
        echo "<div style='width: 235px; float: right;'>
            <a href='iftype/peering/' onmouseover=\"return overlib('\
            <img src=\'graph.php?type=multiport_bits&amp;id=" . $ports['peering'] . '&amp;from=' . Config::get('time.day') . '&amp;to=' . Config::get('time.now') . "&amp;width=400&amp;height=150\'>\
            <img src=\'graph.php?type=multiport_bits&amp;id=" . $ports['peering'] . '&amp;from=' . Config::get('time.week') . '&amp;to=' . Config::get('time.now') . "&amp;width=400&amp;height=150\'>\
            ', CENTER, LEFT, FGCOLOR, '#e5e5e5', BGCOLOR, '#e5e5e5', WIDTH, 400, HEIGHT, 150);\" onmouseout=\"return nd();\"  >" . "<div style='font-size: 16px; font-weight: bold; color: #555555;'>Internet Peering</div>" . "<img src='graph.php?type=multiport_bits&amp;id=" . $ports['peering'] . '&amp;from=' . Config::get('time.day') . '&amp;to=' . Config::get('time.now') . "&amp;width=155&amp;height=100&amp;legend=no'></a></div>";
    }

    if ($ports['core']) {
        echo "<div style='width: 235px;'>
            <a href='iftype/core/' onmouseover=\"return overlib('\
            <img src=\'graph.php?type=multiport_bits&amp;id=" . $ports['core'] . '&amp;from=' . Config::get('time.day') . '&amp;to=' . Config::get('time.now') . "&amp;width=400&amp;height=150\'>\
            <img src=\'graph.php?type=multiport_bits&amp;id=" . $ports['core'] . '&amp;from=' . Config::get('time.week') . '&amp;to=' . Config::get('time.now') . "&amp;width=400&amp;height=150\'>\
            ', CENTER, LEFT, FGCOLOR, '#e5e5e5', BGCOLOR, '#e5e5e5', WIDTH, 400, HEIGHT, 150);\" onmouseout=\"return nd();\"  >" . "<div style='font-size: 16px; font-weight: bold; color: #555555;'>Core Traffic</div>" . "<img src='graph.php?type=multiport_bits&amp;id=" . $ports['core'] . '&amp;from=' . Config::get('time.day') . '&amp;to=' . Config::get('time.now') . "&amp;width=385&amp;height=100&amp;legend=no'></a></div>";
    }

    echo '</div>';
}//end if

?>
</td>

  </tr>
  <tr>
</tr></table>
