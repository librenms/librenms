
<table border=0 cellpadding=10 cellspacing=10 width=100%>
  <tr>
    <td bgcolor=#e5e5e5 valign=top>
<?php

// <table width=100% border=0><tr><td><div style="margin-bottom: 5px; font-size: 18px; font-weight: bold;">Devices with Alerts</div></td><td width=35 align=center><div class=tablehead>Host</div></td><td align=center width=35><div class=tablehead>Int</div></td><td align=center width=35><div class=tablehead>Srv</div></tr>
?>
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
    echo "<div style='border: solid 2px #d0D0D0; float: left; padding: 5px; width: 120px; height: 90px; background: #ffbbbb; margin: 4px;'>
        <center><strong>".generate_device_link($device, shorthost($device['hostname']))."</strong><br />
        <span style='font-size: 14px; font-weight: bold; margin: 5px; color: #c00;'>Device Down</span>
        <span class=body-date-1>".truncate($device['location'], 20).'</span>
        </center></div>';
}

$sql = "SELECT * FROM `ports` AS I, `devices` AS D WHERE I.device_id = D.device_id AND ifOperStatus = 'down' AND ifAdminStatus = 'up' AND D.ignore = '0' AND I.ignore = '0'";
foreach (dbFetchRows($sql) as $interface) {
    echo "<div style='border: solid 2px #D0D0D0; float: left; padding: 5px; width: 120px; height: 90px; background: #ffddaa; margin: 4px;'>
        <center><strong>".generate_device_link($interface, shorthost($interface['hostname']))."</strong><br />
        <span style='font-size: 14px; font-weight: bold; margin: 5px; color: #c00;'>Port Down</span>
        <strong>".generate_port_link($interface, makeshortif($interface['ifDescr'])).'</strong> <br />
        <span class=body-date-1>'.truncate($interface['ifAlias'], 20).'</span>
        </center></div>';
}

$sql = "SELECT * FROM `services` AS S, `devices` AS D WHERE S.device_id = D.device_id AND service_status = 'down' AND D.ignore = '0' AND S.service_ignore = '0'";
foreach (dbFetchRows($sql) as $service) {
    echo "<div style='border: solid 2px #D0D0D0; float: left; padding: 5px; width: 120px; height: 90px; background: #ffddaa; margin: 4px;'>
        <center><strong>".generate_device_link($service, shorthost($service['hostname']))."</strong><br />
        <span style='font-size: 14px; font-weight: bold; margin: 5px; color: #c00;'>Service Down</span>
        <strong>".$service['service_type'].'</strong><br />
        <span class=body-date-1>'.truncate($interface['ifAlias'], 20).'</span>
        </center></div>';
}

$sql = "SELECT * FROM `devices` AS D, bgpPeers AS B WHERE bgpPeerState != 'established' AND B.device_id = D.device_id";
foreach (dbFetchRows($sql) as $peer) {
    echo "<div style='border: solid 2px #d0D0D0; float: left; padding: 5px; width: 120px; height: 90px; background: #ffddaa; margin: 4px;'>
        <center><strong>".generate_device_link($peer, shorthost($peer['hostname']))."</strong><br />
        <span style='font-size: 14px; font-weight: bold; margin: 5px; color: #c00;'>BGP Down</span>
        <strong>".$peer['bgpPeerIdentifier'].'</strong> <br />
        <span class=body-date-1>AS'.$peer['bgpPeerRemoteAs'].' '.truncate($peer['astext'], 10).'</span>
        </center></div>';
}

if (filter_var(\LibreNMS\Config::get('uptime_warning'), FILTER_VALIDATE_FLOAT) !== false && \LibreNMS\Config::get('uptime_warning') > 0) {
    $sql = "SELECT * FROM `devices` AS D, devices_attribs AS A WHERE A.device_id = D.device_id AND A.attrib_type = 'uptime' AND A.attrib_value < '" . \LibreNMS\Config::get('uptime_warning') . "'";
    foreach (dbFetchRows($sql) as $device) {
        echo "<div style='border: solid 2px #d0D0D0; float: left; padding: 5px; width: 120px; height: 90px; background: #ddffdd; margin: 4px;'>
            <center><strong>".generate_device_link($device, shorthost($device['hostname']))."</strong><br />
            <span style='font-size: 14px; font-weight: bold; margin: 5px; color: #090;'>Device<br />Rebooted</span><br />
            <span class=body-date-1>".formatUptime($device['attrib_value']).'</span>
            </center></div>';
    }
}

echo "

    <div style='clear: both;'>$errorboxes</div> <div style='margin: 4px; clear: both;'>

    <h3>Recent Syslog Messages</h3>

    ";

$sql = "SELECT *, DATE_FORMAT(timestamp, '" . \LibreNMS\Config::get('dateformat.mysql.compact') . "') AS date from syslog ORDER BY timestamp DESC LIMIT 20";
echo '<table cellspacing=0 cellpadding=2 width=100%>';
foreach (dbFetchRows($sql) as $entry) {
    unset($syslog_output);
    include 'includes/html/print-syslog.inc.php';
    echo $syslog_output;
}

echo '</table>';

echo '</div>

    </td>
    <td bgcolor=#e5e5e5 width=275 valign=top>';

// this stuff can be customised to show whatever you want....
if (Auth::user()->hasGlobalRead()) {
    $sql  = "SELECT * FROM ports AS I, devices AS D WHERE `ifAlias` like 'L2TP: %' AND I.device_id = D.device_id AND D.hostname LIKE '%";
    $sql .= \LibreNMS\Config::get('mydomain') . "' ORDER BY I.ifAlias";
    unset($seperator);
    foreach (dbFetchRows($sql) as $interface) {
        $ports['l2tp'] .= $seperator.$interface['port_id'];
        $seperator      = ',';
    }

    $sql  = "SELECT * FROM ports AS I, devices AS D WHERE `ifAlias` like 'Transit: %' AND I.device_id = D.device_id AND D.hostname LIKE '%";
    $sql .= \LibreNMS\Config::get('mydomain') . "' ORDER BY I.ifAlias";
    unset($seperator);
    foreach (dbFetchRows($sql) as $interface) {
        $ports['transit'] .= $seperator.$interface['port_id'];
        $seperator     = ',';
    }

    $sql  = "SELECT * FROM ports AS I, devices AS D WHERE `ifAlias` like 'Server: thlon-pbx%' AND I.device_id = D.device_id AND D.hostname LIKE '%";
    $sql .= \LibreNMS\Config::get('mydomain') . "' ORDER BY I.ifAlias";
    unset($seperator);
    foreach (dbFetchRows($sql) as $interface) {
        $ports['voip'] .= $seperator.$interface['port_id'];
        $seperator  = ',';
    }

    if ($ports['transit']) {
        echo "<a onmouseover=\"return overlib('<img src=\'graph.php?type=multi_bits&amp;ports=" . $ports['transit'] . '&amp;from=' . \LibreNMS\Config::get('time.day') . '&amp;to=' . \LibreNMS\Config::get('time.now') . "&amp;width=400&amp;height=150\'>', CENTER, LEFT, FGCOLOR, '#e5e5e5', BGCOLOR, '#e5e5e5', WIDTH, 400, HEIGHT, 250);\" onmouseout=\"return nd();\"  >" . "<div style='font-size: 18px; font-weight: bold;'>Internet Transit</div>" . "<img src='graph.php?type=multi_bits&amp;ports=" . $ports['transit'] . '&amp;from=' . \LibreNMS\Config::get('time.day') . '&amp;to=' . \LibreNMS\Config::get('time.now') . "&amp;width=200&amp;height=100'></a>";
    }

    if ($ports['l2tp']) {
        echo "<a onmouseover=\"return overlib('<img src=\'graph.php?type=multi_bits&amp;ports=" . $ports['l2tp'] . '&amp;from=' . \LibreNMS\Config::get('time.day') . '&amp;to=' . \LibreNMS\Config::get('time.now') . "&amp;width=400&amp;height=150\'>', LEFT, FGCOLOR, '#e5e5e5', BGCOLOR, '#e5e5e5', WIDTH, 400, HEIGHT, 250);\" onmouseout=\"return nd();\"  >" . "<div style='font-size: 18px; font-weight: bold;'>L2TP ADSL</div>" . "<img src='graph.php?type=multi_bits&amp;ports=" . $ports['l2tp'] . '&amp;from=' . \LibreNMS\Config::get('time.day') . '&amp;to=' . \LibreNMS\Config::get('time.now') . "&amp;width=200&amp;height=100'></a>";
    }

    if ($ports['voip']) {
        echo "<a onmouseover=\"return overlib('<img src=\'graph.php?type=multi_bits&amp;ports=" . $ports['voip'] . '&amp;from=' . \LibreNMS\Config::get('time.day') . '&amp;to=' . \LibreNMS\Config::get('time.now') . "&amp;width=400&amp;height=150\'>', LEFT, FGCOLOR, '#e5e5e5', BGCOLOR, '#e5e5e5', WIDTH, 400, HEIGHT, 250);\" onmouseout=\"return nd();\"  >" . "<div style='font-size: 18px; font-weight: bold;'>VoIP to PSTN</div>" . "<img src='graph.php?type=multi_bits&amp;ports=" . $ports['voip'] . '&amp;from=' . \LibreNMS\Config::get('time.day') . '&amp;to=' . \LibreNMS\Config::get('time.now') . "&amp;width=200&amp;height=100'></a>";
    }
}//end if

// END VOSTRON
?>
</td>

  </tr>
  <tr>
</tr></table>
