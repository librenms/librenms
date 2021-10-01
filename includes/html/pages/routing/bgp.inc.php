<?php

use LibreNMS\Exceptions\InvalidIpException;
use LibreNMS\Util\IPv6;

if (! Auth::user()->hasGlobalRead()) {
    include 'includes/html/error-no-perm.inc.php';
} else {
    $link_array = [
        'page'     => 'routing',
        'protocol' => 'bgp',
    ];

    print_optionbar_start('', '');

    echo '<span style="font-weight: bold;">BGP</span> &#187; ';

    if (! $vars['type']) {
        $vars['type'] = 'all';
    }

    if ($vars['type'] == 'all') {
        echo "<span class='pagemenu-selected'>";
    }

    echo generate_link('All', $vars, ['type' => 'all']);
    if ($vars['type'] == 'all') {
        echo '</span>';
    }

    echo ' | ';

    if ($vars['type'] == 'internal') {
        echo "<span class='pagemenu-selected'>";
    }

    echo generate_link('iBGP', $vars, ['type' => 'internal']);
    if ($vars['type'] == 'internal') {
        echo '</span>';
    }

    echo ' | ';

    if ($vars['type'] == 'external') {
        echo "<span class='pagemenu-selected'>";
    }

    echo generate_link('eBGP', $vars, ['type' => 'external']);
    if ($vars['type'] == 'external') {
        echo '</span>';
    }

    echo ' | ';

    if ($vars['adminstatus'] == 'stop') {
        echo "<span class='pagemenu-selected'>";
        echo generate_link('Shutdown', $vars, ['adminstatus' => null]);
        echo '</span>';
    } else {
        echo generate_link('Shutdown', $vars, ['adminstatus' => 'stop']);
    }

    echo ' | ';

    if ($vars['adminstatus'] == 'start') {
        echo "<span class='pagemenu-selected'>";
        echo generate_link('Enabled', $vars, ['adminstatus' => null]);
        echo '</span>';
    } else {
        echo generate_link('Enabled', $vars, ['adminstatus' => 'start']);
    }

    echo ' | ';

    if ($vars['state'] == 'down') {
        echo "<span class='pagemenu-selected'>";
        echo generate_link('Down', $vars, ['state' => null]);
        echo '</span>';
    } else {
        echo generate_link('Down', $vars, ['state' => 'down']);
    }

    // End BGP Menu
    if (! isset($vars['view'])) {
        $vars['view'] = 'details';
    }

    echo '<div style="float: right;">';

    if ($vars['view'] == 'details') {
        echo "<span class='pagemenu-selected'>";
    }

    echo generate_link('No Graphs', $vars, ['view' => 'details', 'graph' => 'NULL']);
    if ($vars['view'] == 'details') {
        echo '</span>';
    }

    echo ' | ';

    if ($vars['graph'] == 'updates') {
        echo "<span class='pagemenu-selected'>";
    }

    echo generate_link('Updates', $vars, ['view' => 'graphs', 'graph' => 'updates']);
    if ($vars['graph'] == 'updates') {
        echo '</span>';
    }

    echo ' | Prefixes: Unicast (';
    if ($vars['graph'] == 'prefixes_ipv4unicast') {
        echo "<span class='pagemenu-selected'>";
        $extra_sql = " AND `bgpPeerIdentifier` NOT LIKE '%:%'";
    }

    echo generate_link('IPv4', $vars, ['view' => 'graphs', 'graph' => 'prefixes_ipv4unicast']);
    if ($vars['graph'] == 'prefixes_ipv4unicast') {
        echo '</span>';
    }

    echo '|';

    if ($vars['graph'] == 'prefixes_ipv6unicast') {
        echo "<span class='pagemenu-selected'>";
        $extra_sql = " AND `bgpPeerIdentifier` LIKE '%:%'";
    }

    echo generate_link('IPv6', $vars, ['view' => 'graphs', 'graph' => 'prefixes_ipv6unicast']);
    if ($vars['graph'] == 'prefixes_ipv6unicast') {
        echo '</span>';
    }

    echo '|';

    if ($vars['graph'] == 'prefixes_ipv4vpn') {
        echo "<span class='pagemenu-selected'>";
    }

    echo generate_link('VPNv4', $vars, ['view' => 'graphs', 'graph' => 'prefixes_ipv4vpn']);
    if ($vars['graph'] == 'prefixes_ipv4vpn') {
        echo '</span>';
    }

    echo '|';

    if ($vars['graph'] == 'prefixes_ipv6vpn') {
        echo "<span class='pagemenu-selected'>";
    }

    echo generate_link('VPNv6', $vars, ['view' => 'graphs', 'graph' => 'prefixes_ipv6vpn']);
    if ($vars['graph'] == 'prefixes_ipv6vpn') {
        echo '</span>';
    }

    echo ')';

    echo ' | Multicast (';
    if ($vars['graph'] == 'prefixes_ipv4multicast') {
        echo "<span class='pagemenu-selected'>";
    }

    echo generate_link('IPv4', $vars, ['view' => 'graphs', 'graph' => 'prefixes_ipv4multicast']);
    if ($vars['graph'] == 'prefixes_ipv4multicast') {
        echo '</span>';
    }

    echo '|';

    if ($vars['graph'] == 'prefixes_ipv6multicast') {
        echo "<span class='pagemenu-selected'>";
    }

    echo generate_link('IPv6', $vars, ['view' => 'graphs', 'graph' => 'prefixes_ipv6multicast']);
    if ($vars['graph'] == 'prefixes_ipv6multicast') {
        echo '</span>';
    }

    echo ')';

    echo ' | MAC (';
    if ($vars['graph'] == 'macaccounting_bits') {
        echo "<span class='pagemenu-selected'>";
    }

    echo generate_link('Bits', $vars, ['view' => 'graphs', 'graph' => 'macaccounting_bits']);
    if ($vars['graph'] == 'macaccounting_bits') {
        echo '</span>';
    }

    echo '|';

    if ($vars['graph'] == 'macaccounting_pkts') {
        echo "<span class='pagemenu-selected'>";
    }

    echo generate_link('Packets', $vars, ['view' => 'graphs', 'graph' => 'macaccounting_pkts']);
    if ($vars['graph'] == 'macaccounting_pkts') {
        echo '</span>';
    }

    echo ')';

    echo '</div>';

    print_optionbar_end();

    echo "<table border=0 cellspacing=0 cellpadding=5 width=100% class='table sortable'>";
    echo '<tr style="height: 30px"><td width=1></td><th>Local address</th><th></th><th>Peer address</th><th>Type</th><th>Family</th><th>Remote AS</th><th>Peer description</th><th>State</th><th>Last error</th><th width=200>Uptime / Updates</th></tr>';

    if ($vars['type'] == 'external') {
        $where = 'AND D.bgpLocalAs != B.bgpPeerRemoteAs';
    } elseif ($vars['type'] == 'internal') {
        $where = 'AND D.bgpLocalAs = B.bgpPeerRemoteAs';
    }

    if ($vars['adminstatus'] == 'stop') {
        $where .= " AND (B.bgpPeerAdminStatus = 'stop')";
    } elseif ($vars['adminstatus'] == 'start') {
        $where .= " AND (B.bgpPeerAdminStatus = 'start' || B.bgpPeerAdminStatus = 'running')";
    }

    if ($vars['state'] == 'down') {
        $where .= " AND (B.bgpPeerState != 'established')";
    }

    $peer_query = "SELECT * FROM `bgpPeers` AS `B`, `devices` AS `D` WHERE `B`.`device_id` = `D`.`device_id` $where $extra_sql ORDER BY `D`.`hostname`, `B`.`bgpPeerRemoteAs`, `B`.`bgpPeerIdentifier`";
    foreach (dbFetchRows($peer_query) as $peer) {
        unset($alert, $bg_image);

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

        if ($peer['bgpPeerRemoteAs'] == $peer['bgpLocalAs']) {
            $peer_type = "<span style='color: #00f;'>iBGP</span>";
        } else {
            $peer_type = "<span style='color: #0a0;'>eBGP</span>";
            if ($peer['bgpPeerRemoteAS'] >= '64512' && $peer['bgpPeerRemoteAS'] <= '65535') {
                $peer_type = "<span style='color: #f00;'>Priv eBGP</span>";
            }
        }

        try {
            $peer_ip = new IPv6($peer['bgpLocalAddr']);
        } catch (InvalidIpException $e) {
            $peer_ip = $peer['bgpLocalAddr'];
        }

        try {
            $peer_ident = new IPv6($peer['bgpPeerIdentifier']);
        } catch (InvalidIpException $e) {
            $peer_ident = $peer['bgpPeerIdentifier'];
        }

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
        $peeraddresslink = '<span class=list-large>' . \LibreNMS\Util\Url::overlibLink($overlib_link, $peer_ident, \LibreNMS\Util\Url::graphTag($graph_array_zoom)) . '</span>';

        // Local Address
        $graph_array['afi'] = 'ipv4';
        $graph_array['safi'] = 'unicast';
        $graph_array_zoom['afi'] = 'ipv4';
        $graph_array_zoom['safi'] = 'unicast';
        $overlib_link = 'device/device=' . $peer['device_id'] . '/tab=routing/proto=bgp/';
        $localaddresslink = '<span class=list-large>' . \LibreNMS\Util\Url::overlibLink($overlib_link, $peer_ip, \LibreNMS\Util\Url::graphTag($graph_array_zoom)) . '</span>';

        if ($peer['bgpPeerLastErrorCode'] == 0 && $peer['bgpPeerLastErrorSubCode'] == 0) {
            $last_error = $peer['bgpPeerLastErrorText'];
        } else {
            $last_error = describe_bgp_error_code($peer['bgpPeerLastErrorCode'], $peer['bgpPeerLastErrorSubCode']) . '<br/>' . $peer['bgpPeerLastErrorText'];
        }

        echo '<tr class="bgp"' . ($peer['alert'] ? ' bordercolor="#cc0000"' : '') . ($peer['disabled'] ? ' bordercolor="#cccccc"' : '') . '>';

        unset($sep);
        foreach (dbFetchRows('SELECT * FROM `bgpPeers_cbgp` WHERE `device_id` = ? AND bgpPeerIdentifier = ?', [$peer['device_id'], $peer['bgpPeerIdentifier']]) as $afisafi) {
            $afi = $afisafi['afi'];
            $safi = $afisafi['safi'];
            $this_afisafi = $afi . $safi;
            $peer['afi'] .= $sep . $afi . '.' . $safi;
            $sep = '<br />';
            $peer['afisafi'][$this_afisafi] = 1;
            // Build a list of valid AFI/SAFI for this peer
        }

        unset($sep);

        echo '  <td></td>
            <td width=150>' . $localaddresslink . '<br />' . generate_device_link($peer, null, ['tab' => 'routing', 'proto' => 'bgp']) . '</td>
            <td width=30><b>&#187;</b></td>
            <td width=150>' . $peeraddresslink . "</td>
            <td width=50><b>$peer_type</b></td>
            <td width=50>" . $peer['afi'] . '</td>
            <td><strong>AS' . $peer['bgpPeerRemoteAs'] . '</strong><br />' . $peer['astext'] . '</td>
            <td>' . $peer['bgpPeerDescr'] . "</td>
            <td><strong><span style='color: $admin_col;'>" . $peer['bgpPeerAdminStatus'] . "</span><br /><span style='color: $col;'>" . $peer['bgpPeerState'] . '</span></strong></td>
            <td>' . $last_error . '</td>
            <td>' . \LibreNMS\Util\Time::formatInterval($peer['bgpPeerFsmEstablishedTime']) . "<br />
            Updates <i class='fa fa-arrow-down icon-theme' aria-hidden='true'></i> " . \LibreNMS\Util\Number::formatSi($peer['bgpPeerInUpdates'], 2, 3, '') . "
            <i class='fa fa-arrow-up icon-theme' aria-hidden='true'></i> " . \LibreNMS\Util\Number::formatSi($peer['bgpPeerOutUpdates'], 2, 3, '') . '</td></tr>';

        unset($invalid);
        switch ($vars['graph']) {
            case 'prefixes_ipv4unicast':
            case 'prefixes_ipv4multicast':
            case 'prefixes_ipv4vpn':
            case 'prefixes_ipv6unicast':
            case 'prefixes_ipv6multicast':
                [,$afisafi] = explode('_', $vars['graph']);
                if (isset($peer['afisafi'][$afisafi])) {
                    $peer['graph'] = 1;
                }
                // fall-through
            case 'updates':
                $graph_array['type'] = 'bgp_' . $vars['graph'];
                $graph_array['id'] = $peer['bgpPeer_id'];
        }

        switch ($vars['graph']) {
            case 'macaccounting_bits':
            case 'macaccounting_pkts':
                $acc = dbFetchRow('SELECT * FROM `ipv4_mac` AS I, `mac_accounting` AS M, `ports` AS P, `devices` AS D WHERE I.ipv4_address = ? AND M.mac = I.mac_address AND P.port_id = M.port_id AND D.device_id = P.device_id', [$peer['bgpPeerIdentifier']]);
                $database = Rrd::name($device['hostname'], ['cip', $acc['ifIndex'], $acc['mac']]);
                if (is_array($acc) && is_file($database)) {
                    $peer['graph'] = 1;
                    $graph_array['id'] = $acc['ma_id'];
                    $graph_array['type'] = $vars['graph'];
                }
        }

        if ($vars['graph'] == 'updates') {
            $peer['graph'] = 1;
        }

        if ($peer['graph']) {
            $graph_array['height'] = '100';
            $graph_array['width'] = '218';
            $graph_array['to'] = \LibreNMS\Config::get('time.now');
            echo '<tr></tr><tr class="bgp"' . ($bg_image ? ' background="' . $bg_image . '"' : '') . '"><td colspan="9">';

            include 'includes/html/print-graphrow.inc.php';

            echo '</td></tr>';
        }
    }//end foreach

    echo '</table>';
}//end if
