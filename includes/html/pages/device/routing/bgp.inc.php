<?php

use LibreNMS\Util\IP;

$link_array = [
    'page'   => 'device',
    'device' => $device['device_id'],
    'tab'    => 'routing',
    'proto'  => 'bgp',
];

if (! isset($vars['view'])) {
    $vars['view'] = 'basic';
}

print_optionbar_start();

echo '<strong>Local AS : ' . $device['bgpLocalAs'] . '</strong> ';

echo "<span style='font-weight: bold;'>BGP</span> &#187; ";

if ($vars['view'] == 'basic') {
    echo "<span class='pagemenu-selected'>";
}

echo generate_link('Basic', $link_array, ['view' => 'basic']);
if ($vars['view'] == 'basic') {
    echo '</span>';
}

echo ' | ';

if ($vars['view'] == 'updates') {
    echo "<span class='pagemenu-selected'>";
}

echo generate_link('Updates', $link_array, ['view' => 'updates']);
if ($vars['view'] == 'updates') {
    echo '</span>';
}

echo ' | Prefixes: ';

if ($vars['view'] == 'prefixes_ipv4unicast') {
    echo "<span class='pagemenu-selected'>";
    $extra_sql = " AND `bgpPeerIdentifier` NOT LIKE '%:%'";
}

echo generate_link('IPv4', $link_array, ['view' => 'prefixes_ipv4unicast']);
if ($vars['view'] == 'prefixes_ipv4unicast') {
    echo '</span>';
}

echo ' | ';

if ($vars['view'] == 'prefixes_ipv4vpn') {
    echo "<span class='pagemenu-selected'>";
}

echo generate_link('VPNv4 Ucast', $link_array, ['view' => 'prefixes_ipv4vpn']);
if ($vars['view'] == 'prefixes_ipv4vpn') {
    echo '</span>';
}

echo ' | ';

if ($vars['view'] == 'prefixes_ipv6unicast') {
    echo "<span class='pagemenu-selected'>";
    $extra_sql = " AND `bgpPeerIdentifier` LIKE '%:%'";
}

echo generate_link('IPv6', $link_array, ['view' => 'prefixes_ipv6unicast']);
if ($vars['view'] == 'prefixes_ipv6unicast') {
    echo '</span>';
}

echo ' | ';

if ($vars['view'] == 'prefixes_ipv6vpn') {
    echo "<span class='pagemenu-selected'>";
    $extra_sql = " AND `bgpPeerIdentifier` LIKE '%:%'";
}

echo generate_link('VPNv6 Ucast', $link_array, ['view' => 'prefixes_ipv6vpn']);
if ($vars['view'] == 'prefixes_ipv6vpn') {
    echo '</span>';
}

echo ' | Traffic: ';

if ($vars['view'] == 'macaccounting_bits') {
    echo "<span class='pagemenu-selected'>";
}

echo generate_link('Bits', $link_array, ['view' => 'macaccounting_bits']);
if ($vars['view'] == 'macaccounting_bits') {
    echo '</span>';
}

echo ' | ';
if ($vars['view'] == 'macaccounting_pkts') {
    echo "<span class='pagemenu-selected'>";
}

echo generate_link('Packets', $link_array, ['view' => 'macaccounting_pkts']);
if ($vars['view'] == 'macaccounting_pkts') {
    echo '</span>';
}

print_optionbar_end();

echo '<table border="0" cellspacing="0" cellpadding="5" width="100%">';
echo '<tr style="height: 30px"><th>Peer address</th><th>Type</th><th>Family</th><th>Remote AS</th><th>Peer description</th><th>State</th><th>Last error</th><th>Uptime</th></tr>';

$i = '1';

foreach (dbFetchRows("SELECT * FROM `bgpPeers` WHERE `device_id` = ? $extra_sql ORDER BY `bgpPeerRemoteAs`, `bgpPeerIdentifier`", [$device['device_id']]) as $peer) {
    $has_macaccounting = dbFetchCell('SELECT COUNT(*) FROM `ipv4_mac` AS I, mac_accounting AS M WHERE I.ipv4_address = ? AND M.mac = I.mac_address', [$peer['bgpPeerIdentifier']]);
    unset($bg_image);
    if (! is_integer($i / 2)) {
        $bg_colour = \LibreNMS\Config::get('list_colour.even');
    } else {
        $bg_colour = \LibreNMS\Config::get('list_colour.odd');
    }

    unset($alert, $bg_image);
    unset($peerhost, $peername);

    if (! is_integer($i / 2)) {
        $bg_colour = \LibreNMS\Config::get('list_colour.odd');
    } else {
        $bg_colour = \LibreNMS\Config::get('list_colour.even');
    }

    if ($peer['bgpPeerState'] == 'established') {
        $col = 'green';
    } else {
        $col = 'red';
        $peer['alert'] = 1;
    }

    if ($peer['bgpPeerAdminStatus'] == 'start' || $peer['bgpPeerAdminStatus'] == 'running') {
        $admin_col = 'green';
    } else {
        $admin_col = 'gray';
    }

    if ($peer['bgpPeerAdminStatus'] == 'stop') {
        $peer['alert'] = 0;
        $peer['disabled'] = 1;
    }

    if ($peer['bgpPeerRemoteAs'] == $device['bgpLocalAs']) {
        $peer_type = "<span style='color: #00f;'>iBGP</span>";
    } else {
        $peer_type = "<span style='color: #0a0;'>eBGP</span>";
    }

    $query = 'SELECT * FROM ipv4_addresses AS A, ports AS I, devices AS D WHERE ';
    $query .= '(A.ipv4_address = ? AND I.port_id = A.port_id)';
    $query .= ' AND D.device_id = I.device_id';
    $ipv4_host = dbFetchRow($query, [$peer['bgpPeerIdentifier']]);

    $query = 'SELECT * FROM ipv6_addresses AS A, ports AS I, devices AS D WHERE ';
    $query .= '(A.ipv6_address = ? AND I.port_id = A.port_id)';
    $query .= ' AND D.device_id = I.device_id';
    $ipv6_host = dbFetchRow($query, [$peer['bgpPeerIdentifier']]);

    if ($ipv4_host) {
        $peerhost = $ipv4_host;
    } elseif ($ipv6_host) {
        $peerhost = $ipv6_host;
    } else {
        unset($peerhost);
    }

    if (is_array($peerhost)) {
        $peerhost = cleanPort($peerhost);
        // $peername = generate_device_link($peerhost);
        $peername = generate_device_link($peerhost) . ' ' . generate_port_link($peerhost);
        $peer_url = 'device/device=' . $peer['device_id'] . '/tab=routing/proto=bgp/view=updates/';
    } else {
        // FIXME
        // $peername = gethostbyaddr($peer['bgpPeerIdentifier']); // FFffuuu DNS // Cache this in discovery?
        // if ($peername == $peer['bgpPeerIdentifier'])
        // {
        // unset($peername);
        // } else {
        // $peername = "<i>".$peername."<i>";
        // }
    }

    unset($peer_af);
    unset($sep);

    foreach (dbFetchRows('SELECT * FROM `bgpPeers_cbgp` WHERE `device_id` = ? AND bgpPeerIdentifier = ?', [$device['device_id'], $peer['bgpPeerIdentifier']]) as $afisafi) {
        $afi = $afisafi['afi'];
        $safi = $afisafi['safi'];
        $this_afisafi = $afi . $safi;
        $peer['afi'] .= $sep . $afi . '.' . $safi;
        $sep = '<br />';
        $peer['afisafi'][$this_afisafi] = 1;
        // Build a list of valid AFI/SAFI for this peer
    }

    unset($sep);

    // make ipv6 look pretty
    $peer['bgpPeerIdentifier'] = (string) IP::parse($peer['bgpPeerIdentifier'], true);

    // display overlib graphs
    $graph_array = [];
    $graph_array['type'] = 'bgp_updates';
    $graph_array['id'] = $peer['bgpPeer_id'];
    $graph_array['to'] = \LibreNMS\Config::get('time.now');
    $graph_array['from'] = \LibreNMS\Config::get('time.day');
    $graph_array['height'] = '110';
    $graph_array['width'] = $width;

    // Peer Address
    $graph_array_zoom = $graph_array;
    $graph_array_zoom['height'] = '150';
    $graph_array_zoom['width'] = '500';
    $overlib_link = 'device/device=' . $peer['device_id'] . '/tab=routing/proto=bgp/';

    $link_array = $graph_array;
    $link_array['page'] = 'graphs';
    unset($link_array['height'], $link_array['width'], $link_array['legend']);
    $link = \LibreNMS\Util\Url::generate($link_array);
    $peeraddresslink = '<span class=list-large>' . \LibreNMS\Util\Url::overlibLink($link, $peer['bgpPeerIdentifier'], \LibreNMS\Util\Url::graphTag($graph_array_zoom)) . '</span>';

    if ($peer['bgpPeerLastErrorCode'] == 0 && $peer['bgpPeerLastErrorSubCode'] == 0) {
        $last_error = $peer['bgpPeerLastErrorText'];
    } else {
        $last_error = describe_bgp_error_code($peer['bgpPeerLastErrorCode'], $peer['bgpPeerLastErrorSubCode']) . '<br/>' . $peer['bgpPeerLastErrorText'];
    }

    echo '<tr bgcolor="' . $bg_colour . '"' . ($peer['alert'] ? ' bordercolor="#cc0000"' : '') . ($peer['disabled'] ? ' bordercolor="#cccccc"' : '') . '>
        ';

    echo '
        <td>' . $peeraddresslink . '<br />' . $peername . "</td>
        <td>$peer_type</td>
        <td style='font-size: 10px; font-weight: bold; line-height: 10px;'>" . (isset($peer['afi']) ? $peer['afi'] : '') . '</td>
        <td><strong>AS' . $peer['bgpPeerRemoteAs'] . '</strong><br />' . $peer['astext'] . '</td>
        <td>' . $peer['bgpPeerDescr'] . "</td>
        <td><strong><span style='color: $admin_col;'>" . $peer['bgpPeerAdminStatus'] . "<span><br /><span style='color: $col;'>" . $peer['bgpPeerState'] . '</span></strong></td>
        <td>' . $last_error . '</td>
        <td>' . \LibreNMS\Util\Time::formatInterval($peer['bgpPeerFsmEstablishedTime']) . "<br />
        Updates <i class='fa fa-arrow-down icon-theme' aria-hidden='true'></i> " . $peer['bgpPeerInUpdates'] . "
        <i class='fa fa-arrow-up icon-theme' aria-hidden='true'></i> " . $peer['bgpPeerOutUpdates'] . '</td>
        </tr>
        <tr height=5></tr>';

    unset($invalid);

    switch ($vars['view']) {
        case 'prefixes_ipv4unicast':
        case 'prefixes_ipv4multicast':
        case 'prefixes_ipv4vpn':
        case 'prefixes_ipv6unicast':
        case 'prefixes_ipv6multicast':
            [,$afisafi] = explode('_', $vars['view']);
            if (isset($peer['afisafi'][$afisafi])) {
                $peer['graph'] = 1;
            }

            // FIXME no break??
        case 'updates':
            $graph_array['type'] = 'bgp_' . $vars['view'];
            $graph_array['id'] = $peer['bgpPeer_id'];
    }

    switch ($vars['view']) {
        case 'macaccounting_bits':
        case 'macaccounting_pkts':
            $acc = dbFetchRow('SELECT * FROM `ipv4_mac` AS I, `mac_accounting` AS M, `ports` AS P, `devices` AS D WHERE I.ipv4_address = ? AND M.mac = I.mac_address AND P.port_id = M.port_id AND D.device_id = P.device_id', [$peer['bgpPeerIdentifier']]);
            $database = Rrd::name($device['hostname'], ['cip', $acc['ifIndex'], $acc['mac']]);
            if (is_array($acc) && is_file($database)) {
                $peer['graph'] = 1;
                $graph_array['id'] = $acc['ma_id'];
                $graph_array['type'] = $vars['view'];
            }
    }

    if ($vars['view'] == 'updates') {
        $peer['graph'] = 1;
    }

    if ($peer['graph']) {
        $graph_array['height'] = '100';
        $graph_array['width'] = '216';
        $graph_array['to'] = \LibreNMS\Config::get('time.now');
        echo '<tr bgcolor="' . $bg_colour . '"' . ($bg_image ? ' background="' . $bg_image . '"' : '') . '"><td colspan="7">';

        include 'includes/html/print-graphrow.inc.php';

        echo '</td></tr>';
    }

    $i++;

    unset($valid_afi_safi);
}//end foreach
?>

</table>
