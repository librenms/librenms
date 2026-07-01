<?php

use App\Facades\LibrenmsConfig;
use LibreNMS\Enum\IfOperStatus;
use LibreNMS\Util\Html;
use LibreNMS\Util\Mac;
use LibreNMS\Util\Url;

// FIXME - REWRITE!
$hostname = $device['hostname'];
$ifname = $port['ifDescr'];
$ifIndex = $port['ifIndex'];
$speed = \LibreNMS\Util\Number::formatSi($port['ifSpeed'], 2, 0, 'bps');

$ifalias = $port['name'];

if ($port['ifPhysAddress']) {
    $mac = $port['ifPhysAddress'];
}

$color = 'black';
if ($port['ifAdminStatus'] == IfOperStatus::Down) {
    $status = "<span class='grey'>Disabled</span>";
}

if ($port['ifAdminStatus'] == IfOperStatus::Up && $port['ifOperStatus'] != IfOperStatus::Up) {
    $status = "<span class='red'>Enabled / Disconnected</span>";
}

if ($port['ifAdminStatus'] == IfOperStatus::Up && $port['ifOperStatus'] == IfOperStatus::Up) {
    $status = "<span class='green'>Enabled / Connected</span>";
}

$i = 1;
$inf = LibreNMS\Util\Rewrite::normalizeIfName($ifname);

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

           <a href='" . Url::generate($link_array, ['view' => 'macaccounting', 'subview' => 'top10', 'graph' => $vars['graph'], 'sort' => $vars['sort'], 'period' => '1d']) . "'>

             <img style='border: #5e5e5e 2px;' valign=middle src='" . e(route('graph', ['type' => 'port_mac_acc_total', 'id' => $port['port_id'], 'stat' => $vars['graph'], 'sort' => $vars['sort'], 'from' => '-1d', 'width' => 150, 'height' => 50])) . "' />
           </a>
           </div>
           <div style='margin: 0px 10px 5px 0px; padding:5px; background: #e5e5e5;'>
           <span class=device-head>Two Day</span><br />
           <a href='" . Url::generate($link_array, ['view' => 'macaccounting', 'subview' => 'top10', 'graph' => $vars['graph'], 'sort' => $vars['sort'], 'period' => '2d']) . "/'>
             <img style='border: #5e5e5e 2px;' valign=middle src='" . e(route('graph', ['type' => 'port_mac_acc_total', 'id' => $port['port_id'], 'stat' => $vars['graph'], 'sort' => $vars['sort'], 'from' => '-2d', 'width' => 150, 'height' => 50])) . "' />
           </a>
           </div>
           <div style='margin: 0px 10px 5px 0px; padding:5px; background: #e5e5e5;'>
           <span class=device-head>Week</span><br />
            <a href='" . Url::generate($link_array, ['view' => 'macaccounting', 'subview' => 'top10', 'graph' => $vars['graph'], 'sort' => $vars['sort'], 'period' => '1w']) . "/'>
             <img style='border: #5e5e5e 2px;' valign=middle src='" . e(route('graph', ['type' => 'port_mac_acc_total', 'id' => $port['port_id'], 'stat' => $vars['graph'], 'sort' => $vars['sort'], 'from' => '-1w', 'width' => 150, 'height' => 50])) . "' />
            </a>
            </div>
            <div style='margin: 0px 10px 5px 0px; padding:5px; background: #e5e5e5;'>
            <span class=device-head>Month</span><br />
            <a href='" . Url::generate($link_array, ['view' => 'macaccounting', 'subview' => 'top10', 'graph' => $vars['graph'], 'sort' => $vars['sort'], 'period' => '1m']) . "/'>
             <img style='border: #5e5e5e 2px;' valign=middle src='" . e(route('graph', ['type' => 'port_mac_acc_total', 'id' => $port['port_id'], 'stat' => $vars['graph'], 'sort' => $vars['sort'], 'from' => '-1mo', 'width' => 150, 'height' => 50])) . "' />
            </a>
            </div>
            <div style='margin: 0px 10px 5px 0px; padding:5px; background: #e5e5e5;'>
            <span class=device-head>Year</span><br />
            <a href='" . Url::generate($link_array, ['view' => 'macaccounting', 'subview' => 'top10', 'graph' => $vars['graph'], 'sort' => $vars['sort'], 'period' => '1y']) . "/'>
             <img style='border: #5e5e5e 2px;' valign=middle src='" . e(route('graph', ['type' => 'port_mac_acc_total', 'id' => $port['port_id'], 'stat' => $vars['graph'], 'sort' => $vars['sort'], 'from' => '-1y', 'width' => 150, 'height' => 50])) . "' />
            </a>
            </div>
       </div>
       <div style='float: left;'>
         <img src='" . e(route('graph', ['id' => $port['port_id'], 'type' => 'port_mac_acc_total', 'sort' => $vars['sort'], 'stat' => $vars['graph'], 'from' => $from, 'width' => 745, 'height' => 300])) . "' />
       </div>
       <div style=' margin:0px; float: left;';>
            <div style='margin: 0px 0px 5px 10px; padding:5px; background: #e5e5e5;'>
           <span class=device-head>Traffic</span><br />
           <a href='" . Url::generate($link_array, ['view' => 'macaccounting', 'subview' => 'top10', 'graph' => 'bits', 'sort' => $vars['sort'], 'period' => $vars['period']]) . "'>
             <img style='border: #5e5e5e 2px;' valign=middle src='" . e(route('graph', ['type' => 'port_mac_acc_total', 'id' => $port['port_id'], 'stat' => 'bits', 'sort' => $vars['sort'], 'from' => $from, 'width' => 150, 'height' => 50])) . "' />
           </a>
           </div>
           <div style='margin: 0px 0px 5px 10px; padding:5px; background: #e5e5e5;'>
           <span class=device-head>Packets</span><br />
           <a href='" . Url::generate($link_array, ['view' => 'macaccounting', 'subview' => 'top10', 'graph' => 'pkts', 'sort' => $vars['sort'], 'period' => $vars['period']]) . "/'>
             <img style='border: #5e5e5e 2px;' valign=middle src='" . e(route('graph', ['id' => $port['port_id'], 'stat' => 'pkts', 'type' => 'port_mac_acc_total', 'sort' => $vars['sort'], 'from' => $from, 'width' => 150, 'height' => 50])) . "' />
           </a>
           </div>
           <div style='margin: 0px 0px 5px 10px; padding:5px; background: #e5e5e5;'>
           <span class=device-head>Top Input</span><br />
           <a href='" . Url::generate($link_array, ['view' => 'macaccounting', 'subview' => 'top10', 'graph' => $vars['graph'], 'sort' => 'in', 'period' => $vars['period']]) . "'>
             <img style='border: #5e5e5e 2px;' valign=middle src='" . e(route('graph', ['id' => $port['port_id'], 'stat' => $vars['graph'], 'type' => 'port_mac_acc_total', 'sort' => 'in', 'from' => $from, 'width' => 150, 'height' => 50])) . "' />
           </a>
           </div>
           <div style='margin: 0px 0px 5px 10px; padding:5px; background: #e5e5e5;'>
           <span class=device-head>Top Output</span><br />
           <a href='" . Url::generate($link_array, ['view' => 'macaccounting', 'subview' => 'top10', 'graph' => $vars['graph'], 'sort' => 'out', 'period' => $vars['period']]) . "'>
             <img style='border: #5e5e5e 2px;' valign=middle src='" . e(route('graph', ['id' => $port['port_id'], 'stat' => $vars['graph'], 'type' => 'port_mac_acc_total', 'sort' => 'out', 'from' => $from, 'width' => 150, 'height' => 50])) . "' />
           </a>
           </div>
           <div style='margin: 0px 0px 5px 10px; padding:5px; background: #e5e5e5;'>
           <span class=device-head>Top Aggregate</span><br />
           <a href='" . Url::generate($link_array, ['view' => 'macaccounting', 'subview' => 'top10', 'graph' => $vars['graph'], 'sort' => 'both', 'period' => $vars['period']]) . "'>
             <img style='border: #5e5e5e 2px;' valign=middle src='" . e(route('graph', ['id' => $port['port_id'], 'stat' => $vars['graph'], 'type' => 'port_mac_acc_total', 'sort' => 'both', 'from' => $from, 'width' => 150, 'height' => 50])) . "' />
           </a>
           </div>
       </div>
     </div>
";
    unset($query);
} else {
    $query = 'SELECT *, (M.bps_in + M.bps_out) as bps FROM `mac_accounting` AS M,
                       `ports` AS I WHERE M.port_id = ? AND I.port_id = M.port_id ORDER BY bps DESC';
    $param = [$port['port_id']];

    foreach (dbFetchRows($query, $param) as $acc) {
        if (! is_int($i / 2)) {
            $row_colour = LibrenmsConfig::get('list_colour.even');
        } else {
            $row_colour = LibrenmsConfig::get('list_colour.odd');
        }
        $ipv4 = App\Models\Ipv4Mac::where('mac_address', $acc['mac'])->value('ipv4_address');
        $arp_host = dbFetchRow('SELECT * FROM ipv4_addresses AS A, ports AS I, devices AS D WHERE A.ipv4_address = ? AND I.port_id = A.port_id AND D.device_id = I.device_id', [$ipv4]);
        $arp_host = cleanPort($arp_host);
        if ($arp_host) {
            $arp_name = generate_device_link($arp_host);
            $arp_name .= ' ' . generate_port_link($arp_host);
        } else {
            $arp_name = '';
        }

        $name ??= $ipv4; // i don't know wtf $name is
        if ($name == $ipv4) {
            $name = '';
        }

        $astext = '';
        $asn = '';
        if (dbFetchCell('SELECT count(*) FROM bgpPeers WHERE device_id = ? AND bgpPeerIdentifier = ?', [$acc['device_id'], $ipv4])) {
            $peer_info = dbFetchRow('SELECT * FROM bgpPeers WHERE device_id = ? AND bgpPeerIdentifier = ?', [$acc['device_id'], $ipv4]);
            $asn = 'AS' . $peer_info['bgpPeerRemoteAs'];
            $astext = $peer_info['astext'];
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

            $popup_content = '<div style="font-size: 16px; padding:5px; font-weight: bold; color: #555555;">' . e($name . ' - ' . $ipv4 . ' - ' . $asn) . '</div>' .
                '<img src="' . route('graph', ['id' => $acc['ma_id'], 'type' => $graph_type, 'from' => LibrenmsConfig::get('time.twoday'), 'width' => 450, 'height' => 150]) . '">';
            $link_text = "<img src='" . e(route('graph', ['id' => $acc['ma_id'], 'type' => $graph_type, 'from' => LibrenmsConfig::get('time.twoday'), 'width' => 213, 'height' => 45])) . "'>";
            $overlib_link = Url::overlibLink('#', $link_text, $popup_content);

            echo "<div style='display: block; padding: 3px; margin: 3px; min-width: 221px; max-width:221px; min-height:90px; max-height:90px; text-align: center; float: left;'>
      " . $ipv4 . ' - ' . $asn . "
          $overlib_link

          <span style='font-size: 10px;'>" . $name . '</span>
         </div>';
        } else {
            echo "<div style='background-color: $row_colour; padding: 0px;'>";

            echo '
      <table>
        <tr>
          <td class=list-large width=200>' . Mac::parse($acc['mac'])->readable() . '</td>
          <td class=list-large width=200>' . $ipv4 . '</td>
          <td class=list-large width=500>' . $name . ' ' . $arp_name . '</td>
          <td class=list-large width=100>' . LibreNMS\Util\Number::formatSi($acc['bps_in'], 2, 3, 'bps') . '</td>
          <td class=list-large width=100>' . LibreNMS\Util\Number::formatSi($acc['bps_out'], 2, 3, 'bps') . '</td>
        </tr>
      </table>
      </div>
      <div class="row">
    ';

            $graph_array = [
                'type' => $graph_type,
                'id' => $acc['ma_id'],
                'height' => 100,
                'width' => 216,
                'to' => Config::get('time.now'),
            ];

            foreach (Html::graphRow($graph_array) as $graph) {
                echo "<div class='col-md-2'>$graph</div>";
            }
            echo '</div>';
            $i++;
        }//end if
    }//end foreach
}//end if
