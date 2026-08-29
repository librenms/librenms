<?php

use LibreNMS\Util\Rewrite;
use LibreNMS\Util\Url;

// fixme new url format

echo '<tbody><tr><td></td>';

echo "<td width=200 class=list-large><a href='routing/vrf/" . e($vrf['mplsVpnVrfRouteDistinguisher']) . "/'>" . e($vrf['vrf_name']) . '</a></td>';
echo '<td width=150 class=box-desc>' . e($vrf['mplsVpnVrfDescription']) . '</td>';
echo '<td width=100 class=box-desc>' . e($vrf['mplsVpnVrfRouteDistinguisher']) . '</td>';

echo '<td class="list-bold">';
foreach (dbFetchRows('SELECT * FROM ports WHERE `device_id` = ? AND `ifVrf` = ?', [$device['device_id'], $vrf['vrf_id']]) as $port) {
    $port = cleanPort($port, $device);
    if ($vars['view'] == 'graphs') {
        $graph_type = 'port_' . $vars['graph'];
        $popup_content = '<div style="font-size: 16px; padding:5px; font-weight: bold; color: #e5e5e5;">' . e($device['hostname'] . ' - ' . $port['ifDescr']) . '</div>' .
            e($port['ifAlias']) .
            Url::graphTag(['type' => $graph_type, 'id' => $port['port_id'], 'from' => '-2d', 'width' => 450, 'height' => 150]);
        $link_text = Url::graphTag(['type' => $graph_type, 'id' => $port['port_id'], 'from' => '-2d', 'width' => 132, 'height' => 40, 'legend' => 'no']);
        $overlib_link = Url::overlibLink('device/' . $device['device_id'] . '/port/' . $port['port_id'] . '/', $link_text, $popup_content);

        echo "<div style='display: block; padding: 2px; margin: 2px; min-width: 139px; max-width:139px; min-height:85px; max-height:85px; text-align: center; float: left; background-color: #e9e9e9;'>
    <div style='font-weight: bold;'>" . Rewrite::shortenIfName($port['ifDescr']) . "</div>
    $overlib_link
    <div style='font-size: 9px;'>" . substr((string) short_port_descr($port['ifAlias']), 0, 22) . '</div>
   </div>';
    } else {
        echo $vrf['port_sep'] . generate_port_link($port, Rewrite::shortenIfName($port['ifDescr']));
        $vrf['port_sep'] = ', ';
    }
}

echo '</td>';
echo '</tr></tbody>';
