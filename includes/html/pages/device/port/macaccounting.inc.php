<?php

use LibreNMS\Config;

// FIXME - REWRITE!
$hostname = $device['hostname'];
$hostid = $device['port_id'];
$ifname = $port['ifDescr'];
$ifIndex = $port['ifIndex'];
$speed = \LibreNMS\Util\Number::formatSi($port['ifSpeed'], 2, 3, 'bps');

$ifalias = $port['name'];

if ($port['ifPhysAddress']) {
    $mac = $port['ifPhysAddress'];
}

$color = 'black';
if ($port['ifAdminStatus'] == 'down') {
    $status = "<span class='grey'>Disabled</span>";
}

if ($port['ifAdminStatus'] == 'up' && $port['ifOperStatus'] != 'up') {
    $status = "<span class='red'>Enabled / Disconnected</span>";
}

if ($port['ifAdminStatus'] == 'up' && $port['ifOperStatus'] == 'up') {
    $status = "<span class='green'>Enabled / Connected</span>";
}

$i = 1;
$inf = \LibreNMS\Util\Rewrite::normalizeIfName($ifname);

echo "<div style='clear: both;'>";

if ($vars['subview'] == 'top10') {
    if (! isset($vars['sort'])) {
        $vars['sort'] = 'in';
    }

    if (! isset($vars['period'])) {
        $vars['period'] = '1day';
    }

    $from = '-' . $vars['period'];

    echo "<div style='margin: 0px 0px 0px 0px'>
         <div style=' margin:0px; float: left;';>
           <div style='margin: 0px 10px 5px 0px; padding:5px; background: #e5e5e5;'>
           <span class=device-head>Day</span><br />

           <a href='" . \LibreNMS\Util\Url::generate($link_array, ['view' => 'macaccounting', 'subview' => 'top10', 'graph' => $vars['graph'], 'sort' => $vars['sort'], 'period' => '1d']) . "'>

             <img style='border: #5e5e5e 2px;' valign=middle src='graph.php?id=" . $port['port_id'] . '&amp;stat=' . $vars['graph'] . '&amp;type=port_mac_acc_total&amp;sort=' . $vars['sort'] . '&amp;from=' . Config::get('time.day') . '&amp;to=' . Config::get('time.now') . "&amp;width=150&amp;height=50' />
           </a>
           </div>
           <div style='margin: 0px 10px 5px 0px; padding:5px; background: #e5e5e5;'>
           <span class=device-head>Two Day</span><br />
           <a href='" . \LibreNMS\Util\Url::generate($link_array, ['view' => 'macaccounting', 'subview' => 'top10', 'graph' => $vars['graph'], 'sort' => $vars['sort'], 'period' => '2d']) . "/'>
             <img style='border: #5e5e5e 2px;' valign=middle src='graph.php?id=" . $port['port_id'] . '&amp;stat=' . $vars['graph'] . '&amp;type=port_mac_acc_total&amp;sort=' . $vars['sort'] . '&amp;from=' . Config::get('time.twoday') . '&amp;to=' . Config::get('time.now') . "&amp;width=150&amp;height=50' />
           </a>
           </div>
           <div style='margin: 0px 10px 5px 0px; padding:5px; background: #e5e5e5;'>
           <span class=device-head>Week</span><br />
            <a href='" . \LibreNMS\Util\Url::generate($link_array, ['view' => 'macaccounting', 'subview' => 'top10', 'graph' => $vars['graph'], 'sort' => $vars['sort'], 'period' => '1w']) . "/'>
            <img style='border: #5e5e5e 2px;' valign=middle src='graph.php?id=" . $port['port_id'] . '&amp;type=port_mac_acc_total&amp;sort=' . $vars['sort'] . '&amp;stat=' . $vars['graph'] . '&amp;from=' . Config::get('time.week') . '&amp;to=' . Config::get('time.now') . "&amp;width=150&amp;height=50' />
            </a>
            </div>
            <div style='margin: 0px 10px 5px 0px; padding:5px; background: #e5e5e5;'>
            <span class=device-head>Month</span><br />
            <a href='" . \LibreNMS\Util\Url::generate($link_array, ['view' => 'macaccounting', 'subview' => 'top10', 'graph' => $vars['graph'], 'sort' => $vars['sort'], 'period' => '1m']) . "/'>
            <img style='border: #5e5e5e 2px;' valign=middle src='graph.php?id=" . $port['port_id'] . '&amp;type=port_mac_acc_total&amp;sort=' . $vars['sort'] . '&amp;stat=' . $vars['graph'] . '&amp;from=' . Config::get('time.month') . '&amp;to=' . Config::get('time.now') . "&amp;width=150&amp;height=50' />
            </a>
            </div>
            <div style='margin: 0px 10px 5px 0px; padding:5px; background: #e5e5e5;'>
            <span class=device-head>Year</span><br />
            <a href='" . \LibreNMS\Util\Url::generate($link_array, ['view' => 'macaccounting', 'subview' => 'top10', 'graph' => $vars['graph'], 'sort' => $vars['sort'], 'period' => '1y']) . "/'>
            <img style='border: #5e5e5e 2px;' valign=middle src='graph.php?id=" . $port['port_id'] . '&amp;type=port_mac_acc_total&amp;sort=' . $vars['sort'] . '&amp;stat=' . $vars['graph'] . '&amp;from=' . Config::get('time.year') . '&amp;to=' . Config::get('time.now') . "&amp;width=150&amp;height=50' />
            </a>
            </div>
       </div>
       <div style='float: left;'>
         <img src='graph.php?id=" . $port['port_id'] . '&amp;type=port_mac_acc_total&amp;sort=' . $vars['sort'] . '&amp;stat=' . $vars['graph'] . "&amp;from=$from&amp;to=" . Config::get('time.now') . "&amp;width=745&amp;height=300' />
       </div>
       <div style=' margin:0px; float: left;';>
            <div style='margin: 0px 0px 5px 10px; padding:5px; background: #e5e5e5;'>
           <span class=device-head>Traffic</span><br />
           <a href='" . \LibreNMS\Util\Url::generate($link_array, ['view' => 'macaccounting', 'subview' => 'top10', 'graph' => 'bits', 'sort' => $vars['sort'], 'period' => $vars['period']]) . "'>
             <img style='border: #5e5e5e 2px;' valign=middle src='graph.php?id=" . $port['port_id'] . '&amp;stat=bits&amp;type=port_mac_acc_total&amp;sort=' . $vars['sort'] . "&amp;from=$from&amp;to=" . Config::get('time.now') . "&amp;width=150&amp;height=50' />
           </a>
           </div>
           <div style='margin: 0px 0px 5px 10px; padding:5px; background: #e5e5e5;'>
           <span class=device-head>Packets</span><br />
           <a href='" . \LibreNMS\Util\Url::generate($link_array, ['view' => 'macaccounting', 'subview' => 'top10', 'graph' => 'pkts', 'sort' => $vars['sort'], 'period' => $vars['period']]) . "/'>
             <img style='border: #5e5e5e 2px;' valign=middle src='graph.php?id=" . $port['port_id'] . '&amp;stat=pkts&amp;type=port_mac_acc_total&amp;sort=' . $vars['sort'] . "&amp;from=$from&amp;to=" . Config::get('time.now') . "&amp;width=150&amp;height=50' />
           </a>
           </div>
           <div style='margin: 0px 0px 5px 10px; padding:5px; background: #e5e5e5;'>
           <span class=device-head>Top Input</span><br />
           <a href='" . \LibreNMS\Util\Url::generate($link_array, ['view' => 'macaccounting', 'subview' => 'top10', 'graph' => $vars['graph'], 'sort' => 'in', 'period' => $vars['period']]) . "'>
             <img style='border: #5e5e5e 2px;' valign=middle src='graph.php?id=" . $port['port_id'] . '&amp;stat=' . $vars['graph'] . "&amp;type=port_mac_acc_total&amp;sort=in&amp;from=$from&amp;to=" . Config::get('time.now') . "&amp;width=150&amp;height=50' />
           </a>
           </div>
           <div style='margin: 0px 0px 5px 10px; padding:5px; background: #e5e5e5;'>
           <span class=device-head>Top Output</span><br />
           <a href='" . \LibreNMS\Util\Url::generate($link_array, ['view' => 'macaccounting', 'subview' => 'top10', 'graph' => $vars['graph'], 'sort' => 'out', 'period' => $vars['period']]) . "'>
             <img style='border: #5e5e5e 2px;' valign=middle src='graph.php?id=" . $port['port_id'] . '&amp;stat=' . $vars['graph'] . "&amp;type=port_mac_acc_total&amp;sort=out&amp;from=$from&amp;to=" . Config::get('time.now') . "&amp;width=150&amp;height=50' />
           </a>
           </div>
           <div style='margin: 0px 0px 5px 10px; padding:5px; background: #e5e5e5;'>
           <span class=device-head>Top Aggregate</span><br />
           <a href='" . \LibreNMS\Util\Url::generate($link_array, ['view' => 'macaccounting', 'subview' => 'top10', 'graph' => $vars['graph'], 'sort' => 'both', 'period' => $vars['period']]) . "'>
             <img style='border: #5e5e5e 2px;' valign=middle src='graph.php?id=" . $port['port_id'] . '&amp;stat=' . $vars['graph'] . "&amp;type=port_mac_acc_total&amp;sort=both&amp;from=$from&amp;to=" . Config::get('time.now') . "&amp;width=150&amp;height=50' />
           </a>
           </div>
       </div>
     </div>
";
    unset($query);
} else {
    $query = 'SELECT *, (M.cipMacHCSwitchedBytes_input_rate + M.cipMacHCSwitchedBytes_output_rate) as bps FROM `mac_accounting` AS M,
                       `ports` AS I, `devices` AS D WHERE M.port_id = ? AND I.port_id = M.port_id AND I.device_id = D.device_id ORDER BY bps DESC';
    $param = [$port['port_id']];

    foreach (dbFetchRows($query, $param) as $acc) {
        if (! is_integer($i / 2)) {
            $row_colour = Config::get('list_colour.even');
        } else {
            $row_colour = Config::get('list_colour.odd');
        }

        $addy = dbFetchRow('SELECT * FROM ipv4_mac where mac_address = ?', [$acc['mac']]);
        // $name = gethostbyaddr($addy['ipv4_address']); FIXME - Maybe some caching for this?
        $arp_host = dbFetchRow('SELECT * FROM ipv4_addresses AS A, ports AS I, devices AS D WHERE A.ipv4_address = ? AND I.port_id = A.port_id AND D.device_id = I.device_id', [$addy['ipv4_address']]);
        $arp_host = cleanPort($arp_host);
        if ($arp_host) {
            $arp_name = generate_device_link($arp_host);
            $arp_name .= ' ' . generate_port_link($arp_host);
        } else {
            unset($arp_if);
        }

        if ($name == $addy['ipv4_address']) {
            unset($name);
        }

        if (dbFetchCell('SELECT count(*) FROM bgpPeers WHERE device_id = ? AND bgpPeerIdentifier = ?', [$acc['device_id'], $addy['ipv4_address']])) {
            $peer_info = dbFetchRow('SELECT * FROM bgpPeers WHERE device_id = ? AND bgpPeerIdentifier = ?', [$acc['device_id'], $addy['ipv4_address']]);
        } else {
            unset($peer_info);
        }

        if ($peer_info) {
            $asn = 'AS' . $peer_info['bgpPeerRemoteAs'];
            $astext = $peer_info['astext'];
        } else {
            unset($as);
            unset($astext);
            unset($asn);
        }

        if ($vars['graph']) {
            $graph_type = 'macaccounting_' . $vars['graph'];
        } else {
            $graph_type = 'macaccounting_bits';
        }

        if ($vars['subview'] == 'minigraphs') {
            if (! $asn) {
                $asn = 'No Session';
            }

            echo "<div style='display: block; padding: 3px; margin: 3px; min-width: 221px; max-width:221px; min-height:90px; max-height:90px; text-align: center; float: left; background-color: #e5e5e5;'>
      " . $addy['ipv4_address'] . ' - ' . $asn . "
          <a href='#' onmouseover=\"return overlib('\
     <div style=\'font-size: 16px; padding:5px; font-weight: bold; color: #555555;\'>" . $name . ' - ' . $addy['ipv4_address'] . ' - ' . $asn . "</div>\
     <img src=\'graph.php?id=" . $acc['ma_id'] . "&amp;type=$graph_type&amp;from=" . Config::get('time.twoday') . '&amp;to=' . Config::get('time.now') . "&amp;width=450&amp;height=150\'>\
     ', CENTER, LEFT, FGCOLOR, '#e5e5e5', BGCOLOR, '#e5e5e5', WIDTH, 400, HEIGHT, 150);\" onmouseout=\"return nd();\" >
          <img src='graph.php?id=" . $acc['ma_id'] . "&amp;type=$graph_type&amp;from=" . Config::get('time.twoday') . '&amp;to=' . Config::get('time.now') . "&amp;width=213&amp;height=45'></a>

          <span style='font-size: 10px;'>" . $name . '</span>
         </div>';
        } else {
            echo "<div style='background-color: $row_colour; padding: 0px;'>";

            echo '
      <table>
        <tr>
          <td class=list-large width=200>' . \LibreNMS\Util\Rewrite::readableMac($acc['mac']) . '</td>
          <td class=list-large width=200>' . $addy['ipv4_address'] . '</td>
          <td class=list-large width=500>' . $name . ' ' . $arp_name . '</td>
          <td class=list-large width=100>' . \LibreNMS\Util\Number::formatSi(($acc['cipMacHCSwitchedBytes_input_rate'] / 8), 2, 3, 'bps') . '</td>
          <td class=list-large width=100>' . \LibreNMS\Util\Number::formatSi(($acc['cipMacHCSwitchedBytes_output_rate'] / 8), 2, 3, 'bps') . '</td>
        </tr>
      </table>
    ';

            $peer_info['astext'];

            $graph_array['type'] = $graph_type;
            $graph_array['id'] = $acc['ma_id'];
            $graph_array['height'] = '100';
            $graph_array['width'] = '216';
            $graph_array['to'] = Config::get('time.now');
            echo '<tr bgcolor="' . $bg_colour . '"' . ($bg_image ? ' background="' . $bg_image . '"' : '') . '"><td colspan="7">';

            include 'includes/html/print-graphrow.inc.php';

            echo '</td></tr>';

            $i++;
        }//end if
    }//end foreach
}//end if
