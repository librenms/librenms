<script>
$(function () {
    $('[data-toggle="popover"]').popover()
})
</script>
<?php

use App\Models\Port;
use LibreNMS\Config;
use LibreNMS\Util\IP;
use LibreNMS\Util\Number;

// This file prints a table row for each interface
$port['device_id'] = $device['device_id'];
$port['hostname'] = $device['hostname'];

$if_id = $port['port_id'];

$port = cleanPort($port);

if ($int_colour) {
    $row_colour = $int_colour;
} else {
    if (! is_integer($i / 2)) {
        $row_colour = Config::get('list_colour.even');
    } else {
        $row_colour = Config::get('list_colour.odd');
    }
}

$port_adsl = dbFetchRow('SELECT * FROM `ports_adsl` WHERE `port_id` = ?', [$port['port_id']]);

if ($port['ifInErrors_delta'] > 0 || $port['ifOutErrors_delta'] > 0) {
    $error_img = generate_port_link($port, "<i class='fa fa-flag fa-lg' style='color:red' aria-hidden='true'></i>", 'port_errors');
} else {
    $error_img = '';
}

if (dbFetchCell('SELECT COUNT(*) FROM `mac_accounting` WHERE `port_id` = ?', [$port['port_id']])) {
    $mac = "<a href='" . generate_port_url($port, ['view' => 'macaccounting']) . "'><i class='fa fa-pie-chart fa-lg icon-theme' aria-hidden='true'></i></a>";
} else {
    $mac = '';
}

echo "<tr style=\"background-color: $row_colour;\" valign=top onmouseover=\"this.style.backgroundColor='" . Config::get('list_colour.highlight') . "';\" onmouseout=\"this.style.backgroundColor='$row_colour';\" style='cursor: pointer;'>
    <td valign=top width=350>";

if (Auth::user()->hasGlobalRead()) {
    $port_data = array_to_htmljson($port);
    echo '<i class="fa fa-tag" data-toggle="popover" data-content="' . $port_data . '" data-html="true"></i>';
}

    echo '        <span class=list-large>
        ' . generate_port_link($port, $port['label']) . " $error_img $mac
        </span><br /><span class=interface-desc>" . $port['ifAlias'] . '</span>';

if ($port['ifAlias']) {
    echo '<br />';
}

unset($break);

if ($port_details) {
    foreach (dbFetchRows('SELECT * FROM `ipv4_addresses` WHERE `port_id` = ?', [$port['port_id']]) as $ip) {
        echo "$break <a class=interface-desc href=\"javascript:popUp('ajax/netcmd?cmd=whois&amp;query=$ip[ipv4_address]')\">" . $ip['ipv4_address'] . '/' . $ip['ipv4_prefixlen'] . '</a>';
        $break = '<br />';
    }

    foreach (dbFetchRows('SELECT * FROM `ipv6_addresses` WHERE `port_id` = ?', [$port['port_id']]) as $ip6) {
        echo "$break <a class=interface-desc href=\"javascript:popUp('ajax/netcmd?cmd=whois&amp;query=" . $ip6['ipv6_address'] . "')\">" . IP::parse($ip6['ipv6_address'], true) . '/' . $ip6['ipv6_prefixlen'] . '</a>';
        $break = '<br />';
    }
}

echo '</span>';

$port_group_name_list = Port::find($port['port_id'])->groups->pluck('name')->toArray() ?: ['Default'];

echo '</td><td width=100>';
echo implode('<br>', $port_group_name_list);
echo "</td><td width=100 onclick=\"location.href='" . generate_port_url($port) . "'\" >";

if ($port_details) {
    $port['graph_type'] = 'port_bits';
    echo generate_port_link($port, "<img src='graph.php?type=port_bits&amp;id=" . $port['port_id'] . '&amp;from=' . Config::get('time.day') . '&amp;to=' . Config::get('time.now') . '&amp;width=100&amp;height=20&amp;legend=no&amp;bg=' . str_replace('#', '', $row_colour) . "'>");
    $port['graph_type'] = 'port_upkts';
    echo generate_port_link($port, "<img src='graph.php?type=port_upkts&amp;id=" . $port['port_id'] . '&amp;from=' . Config::get('time.day') . '&amp;to=' . Config::get('time.now') . '&amp;width=100&amp;height=20&amp;legend=no&amp;bg=' . str_replace('#', '', $row_colour) . "'>");
    $port['graph_type'] = 'port_errors';
    echo generate_port_link($port, "<img src='graph.php?type=port_errors&amp;id=" . $port['port_id'] . '&amp;from=' . Config::get('time.day') . '&amp;to=' . Config::get('time.now') . '&amp;width=100&amp;height=20&amp;legend=no&amp;bg=' . str_replace('#', '', $row_colour) . "'>");
}

echo "</td><td width=120 onclick=\"location.href='" . generate_port_url($port) . "'\" >";

if ($port['ifOperStatus'] == 'up') {
    $port['in_rate'] = ($port['ifInOctets_rate'] * 8);
    $port['out_rate'] = ($port['ifOutOctets_rate'] * 8);
    $in_perc = empty($port['ifSpeed']) ? 0 : round(($port['in_rate'] / $port['ifSpeed'] * 100));
    $out_perc = empty($port['ifSpeed']) ? 0 : round(($port['in_rate'] / $port['ifSpeed'] * 100));
    echo "<i class='fa fa-long-arrow-left fa-lg' style='color:green' aria-hidden='true'></i> <span style='color: " . percent_colour($in_perc) . "'>" . Number::formatSi($port['in_rate'], 2, 3, 'bps') . "<br />
        <i class='fa fa-long-arrow-right fa-lg' style='color:blue' aria-hidden='true'></i> <span style='color: " . percent_colour($out_perc) . "'>" . Number::formatSi($port['out_rate'], 2, 3, 'bps') . "<br />
        <i class='fa fa-long-arrow-left fa-lg' style='color:purple' aria-hidden='true'></i> " . Number::formatBi($port['ifInUcastPkts_rate'], 2, 3, 'pps') . "</span><br />
        <i class='fa fa-long-arrow-right fa-lg' style='color:darkorange' aria-hidden='true'></i> " . Number::formatBi($port['ifOutUcastPkts_rate'], 2, 3, 'pps') . '</span>';
}

echo "</td><td width=75 onclick=\"location.href='" . generate_port_url($port) . "'\" >";
if ($port['ifSpeed']) {
    echo '<span class=box-desc>' . \LibreNMS\Util\Number::formatSi($port['ifSpeed'], 2, 3, 'bps') . '</span>';
}

echo '<br />';

if ($port['ifDuplex'] != 'unknown') {
    echo '<span class=box-desc>' . $port['ifDuplex'] . '</span>';
} else {
    echo '-';
}

$vlans = dbFetchColumn(
    'SELECT vlan FROM `ports_vlans` AS PV, vlans AS V ' .
    'WHERE PV.`port_id`=? AND PV.`device_id`=? AND V.`vlan_vlan`=PV.vlan AND V.device_id = PV.device_id',
    [$port['port_id'], $device['device_id']]
);
$vlan_count = count($vlans);

if ($vlan_count > 1) {
    echo '<p class=box-desc><span class=purple><a href="';
    echo \LibreNMS\Util\Url::deviceUrl((int) $device['device_id'], ['tab' => 'vlans']);
    echo '" title="';
    echo implode(', ', $vlans);
    echo '">VLANs: ';
    echo $vlan_count;
    echo '</a></span></p>';
} elseif ($vlan_count == 1 || $port['ifVlan']) {
    echo '<p class=box-desc><span class=blue>VLAN: ';
    echo $vlans[0] ?: $port['ifVlan'];
    echo '</span></p>';
} elseif ($port['ifVrf']) {
    $vrf = dbFetchRow('SELECT * FROM vrfs WHERE vrf_id = ?', [$port['ifVrf']]);
    echo "<p style='color: green;'>" . $vrf['vrf_name'] . '</p>';
}//end if

if ($port_adsl['adslLineCoding']) {
    echo "</td><td width=150 onclick=\"location.href='" . generate_port_url($port) . "'\" >";
    echo $port_adsl['adslLineCoding'] . '/' . rewrite_adslLineType($port_adsl['adslLineType']);
    echo '<br />';
    // ATU-C is CO       -> ATU-C TX is the download speed for the CPE
    // ATU-R is the CPE  -> ATU-R TX is the upload speed of the CPE
    echo 'Sync:' . Number::formatSi($port_adsl['adslAtucChanCurrTxRate'], 2, 3, 'bps') . '/' . Number::formatSi($port_adsl['adslAturChanCurrTxRate'], 2, 3, 'bps');
    echo '<br />';
    // This is the Receive Max AttainableRate, so :
    //    adslAturCurrAttainableRate is DownloadMaxRate
    //    adslAtucCurrAttainableRate is UploadMaxRate
    echo 'Max:' . Number::formatSi($port_adsl['adslAturCurrAttainableRate'], 2, 3, 'bps') . '/' . Number::formatSi($port_adsl['adslAtucCurrAttainableRate'], 2, 3, 'bps');
    echo "</td><td width=150 onclick=\"location.href='" . generate_port_url($port) . "'\" >";
    echo 'Atten:' . $port_adsl['adslAturCurrAtn'] . 'dB/' . $port_adsl['adslAtucCurrAtn'] . 'dB';
    echo '<br />';
    echo 'SNR:' . $port_adsl['adslAturCurrSnrMgn'] . 'dB/' . $port_adsl['adslAtucCurrSnrMgn'] . 'dB';
} else {
    echo "</td><td width=150 onclick=\"location.href='" . generate_port_url($port) . "'\" >";
    if ($port['ifType'] && $port['ifType'] != '') {
        echo '<span class=box-desc>' . \LibreNMS\Util\Rewrite::normalizeIfType($port['ifType']) . '</span>';
    } else {
        echo '-';
    }

    echo '<br />';
    if ($ifHardType && $ifHardType != '') {
        echo '<span class=box-desc>' . $ifHardType . '</span>';
    } else {
        echo '-';
    }

    echo "</td><td width=150 onclick=\"location.href='" . generate_port_url($port) . "'\" >";
    if ($port['ifPhysAddress'] && $port['ifPhysAddress'] != '') {
        echo '<span class=box-desc>' . \LibreNMS\Util\Rewrite::readableMac($port['ifPhysAddress']) . '</span>';
    } else {
        echo '-';
    }

    echo '<br />';
    if ($port['ifMtu'] && $port['ifMtu'] != '') {
        echo '<span class=box-desc>MTU ' . $port['ifMtu'] . '</span>';
    } else {
        echo '-';
    }
}//end if

echo '</td>';
echo '<td width=375 valign=top class="interface-desc">';

$neighborsCount = 0;
$nbLinks = 0;
$int_links = [];
if (strpos($port['label'], 'oopback') === false && ! $graph_type) {
    foreach (dbFetchRows('SELECT * FROM `links` AS L, `ports` AS I, `devices` AS D WHERE L.local_port_id = ? AND L.remote_port_id = I.port_id AND I.device_id = D.device_id', [$if_id]) as $link) {
        $int_links[$link['port_id']] = $link['port_id'];
        $int_links_phys[$link['port_id']] = 1;
        $nbLinks++;
    }

    unset($br);

    if ($port_details && Config::get('enable_port_relationship') === true) {
        // Show which other devices are on the same subnet as this interface
        foreach (dbFetchRows("SELECT `ipv4_network_id` FROM `ipv4_addresses` WHERE `port_id` = ? AND `ipv4_address` NOT LIKE '127.%'", [$port['port_id']]) as $net) {
            $ipv4_network_id = $net['ipv4_network_id'];
            $sql = 'SELECT I.port_id FROM ipv4_addresses AS A, ports AS I, devices AS D
                WHERE A.port_id = I.port_id
                AND A.ipv4_network_id = ? AND D.device_id = I.device_id
                AND D.device_id != ?';
            $array = [
                $net['ipv4_network_id'],
                $device['device_id'],
            ];
            foreach (dbFetchRows($sql, $array) as $new) {
                echo $new['ipv4_network_id'];
                $this_ifid = $new['port_id'];
                $this_hostid = $new['device_id'];
                $this_hostname = $new['hostname'];
                $this_ifname = \LibreNMS\Util\Rewrite::normalizeIfName($new['label']);
                $int_links[$this_ifid] = $this_ifid;
                $int_links_v4[$this_ifid] = 1;
            }
        }//end foreach

        foreach (dbFetchRows('SELECT ipv6_network_id FROM ipv6_addresses WHERE port_id = ?', [$port['port_id']]) as $net) {
            $ipv6_network_id = $net['ipv6_network_id'];
            $sql = "SELECT I.port_id FROM ipv6_addresses AS A, ports AS I, devices AS D
                WHERE A.port_id = I.port_id
                AND A.ipv6_network_id = ? AND D.device_id = I.device_id
                AND D.device_id != ? AND A.ipv6_origin != 'linklayer' AND A.ipv6_origin != 'wellknown'";
            $array = [
                $net['ipv6_network_id'],
                $device['device_id'],
            ];

            foreach (dbFetchRows($sql, $array) as $new) {
                echo $new['ipv6_network_id'];
                $this_ifid = $new['port_id'];
                $this_hostid = $new['device_id'];
                $this_hostname = $new['hostname'];
                $this_ifname = \LibreNMS\Util\Rewrite::normalizeIfName($new['label']);
                $int_links[$this_ifid] = $this_ifid;
                $int_links_v6[$this_ifid] = 1;
            }
        }//end foreach
    }//end if

    if (count($int_links) > 3) {
        echo '<div class="collapse-neighbors"><i class="neighbors-button fa fa-plus fa-lg" aria-hidden="true"></i>
               <span class="neighbors-interface-list-firsts" style="display: inline;">';
    }

    if ($port_details && Config::get('enable_port_relationship') === true && port_permitted($int_link, $device['device_id'])) {
        foreach ($int_links as $int_link) {
            $neighborsCount++;
            if ($neighborsCount == 4) {
                echo '<span class="neighbors-list-continued" style="display: inline;"></br>[...]</span>';
                echo '</span>';
                echo '<span class="neighbors-interface-list" style="display: none;">';
            }
            $link_if = dbFetchRow('SELECT * from ports AS I, devices AS D WHERE I.device_id = D.device_id and I.port_id = ?', [$int_link]);
            $link_if = cleanPort($link_if);
            echo "$br";

            if ($int_links_phys[$int_link]) {
                echo "<i class='fa fa-plus fa-lg' style='color:black' aria-hidden='true'></i> ";
            } else {
                echo "<i class='fa fa-arrow-right fa-lg' style='color:green' aria-hidden='true'></i> ";
            }

            echo '<b>' . generate_port_link($link_if, makeshortif($link_if['label'])) . ' on ' . generate_device_link($link_if);

            if ($int_links_v6[$int_link]) {
                echo " <b style='color: #a10000;'>v6</b>";
            }

            if ($int_links_v4[$int_link]) {
                echo " <b style='color: #00a100'>v4</b>";
            }

            $br = '<br />';
        }//end foreach
    }//end if

    // unset($int_links, $int_links_v6, $int_links_v4, $int_links_phys, $br);
}//end if

if ($port_details && Config::get('enable_port_relationship') === true && port_permitted($port['port_id'], $device['device_id'])) {
    foreach (dbFetchRows('SELECT * FROM `pseudowires` WHERE `port_id` = ?', [$port['port_id']]) as $pseudowire) {
        // `port_id`,`peer_device_id`,`peer_ldp_id`,`cpwVcID`,`cpwOid`
        $pw_peer_dev = dbFetchRow('SELECT * FROM `devices` WHERE `device_id` = ?', [$pseudowire['peer_device_id']]);
        $pw_peer_int = dbFetchRow('SELECT * FROM `ports` AS I, pseudowires AS P WHERE I.device_id = ? AND P.cpwVcID = ? AND P.port_id = I.port_id', [$pseudowire['peer_device_id'], $pseudowire['cpwVcID']]);

        $pw_peer_int = cleanPort($pw_peer_int);
        echo "$br<i class='fa fa-cube fa-lg' style='color:green' aria-hidden='true'></i><b> " . generate_port_link($pw_peer_int, makeshortif($pw_peer_int['label'])) . ' on ' . generate_device_link($pw_peer_dev) . '</b>';
        $br = '<br />';
    }

    foreach (dbFetchRows('SELECT * FROM `ports` WHERE `pagpGroupIfIndex` = ? and `device_id` = ?', [$port['ifIndex'], $device['device_id']]) as $member) {
        $member = cleanPort($member);
        echo "$br<i class='fa fa-cube fa-lg icon-theme' aria-hidden='true'></i> <strong>" . generate_port_link($member) . ' (PAgP)</strong>';
        $br = '<br />';
    }

    if ($port['pagpGroupIfIndex'] && $port['pagpGroupIfIndex'] != $port['ifIndex']) {
        $parent = dbFetchRow('SELECT * FROM `ports` WHERE `ifIndex` = ? and `device_id` = ?', [$port['pagpGroupIfIndex'], $device['device_id']]);
        $parent = cleanPort($parent);
        echo "$br<i class='fa fa-cube fa-lg icon-theme' aria-hidden='true'></i> <strong>" . generate_port_link($parent) . ' (PAgP)</strong>';
        $br = '<br />';
    }

    foreach (dbFetchRows('SELECT * FROM `ports_stack` WHERE `port_id_low` = ? and `device_id` = ?', [$port['ifIndex'], $device['device_id']]) as $higher_if) {
        if ($higher_if['port_id_high']) {
            $this_port = get_port_by_index_cache($device['device_id'], $higher_if['port_id_high']);
            $this_port = cleanPort($this_port);
            echo "$br<i class='fa fa-expand fa-lg icon-theme' aria-hidden='true'></i> <strong>" . generate_port_link($this_port) . '</strong>';
            $br = '<br />';
        }
    }

    foreach (dbFetchRows('SELECT * FROM `ports_stack` WHERE `port_id_high` = ? and `device_id` = ?', [$port['ifIndex'], $device['device_id']]) as $lower_if) {
        if ($lower_if['port_id_low']) {
            $this_port = get_port_by_index_cache($device['device_id'], $lower_if['port_id_low']);
            $this_port = cleanPort($this_port);
            echo "$br<i class='fa fa-compress fa-lg icon-theme' aria-hidden='true'></i> <strong>" . generate_port_link($this_port) . '</strong>';
            $br = '<br />';
        }
    }
}//end if

unset($int_links, $int_links_v6, $int_links_v4, $int_links_phys, $br);

if ($nbLinks > 3) {
    echo '</span></div>';
}
echo '</td></tr>';

// If we're showing graphs, generate the graph and print the img tags
if ($graph_type == 'etherlike') {
    $graph_file = get_port_rrdfile_path($device['hostname'], $if_id, 'dot3');
} else {
    $graph_file = get_port_rrdfile_path($device['hostname'], $if_id);
}

if ($graph_type && is_file($graph_file)) {
    $type = $graph_type;

    echo "<tr style='background-color: $row_colour; padding: 0px;'><td colspan=7>";

    include 'includes/html/print-interface-graphs.inc.php';

    echo '</td></tr>';
}
