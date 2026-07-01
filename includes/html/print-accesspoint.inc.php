<?php

use LibreNMS\Util\Number;
use LibreNMS\Util\Url;

$text = $ap['name'] . ' (' . $ap['type'] . ')';
$ap['text'] = $text;

echo "<tr valign=top onclick=\"location.href='" . generate_ap_url($ap) . "/'\" style='cursor: pointer;'>
         <td valign=top width=350>";
echo '        <span class=list-large> ' . generate_ap_link($ap, " $text </span><br />");
echo '<span class=interface-desc>';
echo "$break" . $ap['mac_addr'] . '<br>' . $ap['type'] . ' - channel ' . $ap['channel'];
echo "<br />txpow $ap[txpow]";
echo '</span>';
echo '</td><td width=100>';

echo '</td><td width=150>';
echo generate_ap_link($ap, Url::graphTag(['type' => 'accesspoints_numasoclients', 'id' => $ap['accesspoint_id'], 'from' => '-1d', 'width' => 100, 'height' => 20, 'legend' => 'no']));
echo "<br>\n";
echo generate_ap_link($ap, Url::graphTag(['type' => 'accesspoints_radioutil', 'id' => $ap['accesspoint_id'], 'from' => '-1d', 'width' => 100, 'height' => 20, 'legend' => 'no']));
echo "<br>\n";
echo generate_ap_link($ap, Url::graphTag(['type' => 'accesspoints_interference', 'id' => $ap['accesspoint_id'], 'from' => '-1d', 'width' => 100, 'height' => 20, 'legend' => 'no']));
echo "<br>\n";

echo '</td><td width=120>';

echo "<i class='fa fa-wifi fa-lg icon-theme' aria-hidden='true'></i> " . Number::formatBi($ap['numasoclients'], 2, 0, '') . ' Clients<br />';
echo "<i class='fa fa-wifi fa-lg icon-theme' aria-hidden='true'></i> " . Number::formatBi($ap['radioutil'], 2, 0, '') . ' % busy<br />';
echo "<i class='fa fa-wifi fa-lg icon-theme' aria-hidden='true'></i> " . Number::formatBi($ap['interference'], 2, 0, '') . ' interference index<br />';

echo '</td></tr>';

if ($vars['ap'] > 0) { // We have a selected AP, let's show details
    $graph_type = 'accesspoints_numasoclients';
    echo "<tr style='padding: 0px;'><td colspan=7>";
    echo "<div class='graphhead'>Associated Clients</div>";
    include 'includes/html/print-accesspoint-graphs.inc.php';
    echo '</td></tr>';

    $graph_type = 'accesspoints_interference';
    echo "<tr style='padding: 0px;'><td colspan=7>";
    echo "<div class='graphhead'>Interference</div>";
    include 'includes/html/print-accesspoint-graphs.inc.php';
    echo '</td></tr>';

    $graph_type = 'accesspoints_channel';
    echo "<tr style='padding: 0px;'><td colspan=7>";
    echo "<div class='graphhead'>Channel</div>";
    include 'includes/html/print-accesspoint-graphs.inc.php';
    echo '</td></tr>';

    $graph_type = 'accesspoints_txpow';
    echo "<tr style='padding: 0px;'><td colspan=7>";
    echo "<div class='graphhead'>Transmit Power</div>";
    include 'includes/html/print-accesspoint-graphs.inc.php';
    echo '</td></tr>';

    $graph_type = 'accesspoints_radioutil';
    echo "<tr style='padding: 0px;'><td colspan=7>";
    echo "<div class='graphhead'>Radio Utilization</div>";
    include 'includes/html/print-accesspoint-graphs.inc.php';
    echo '</td></tr>';

    $graph_type = 'accesspoints_nummonclients';
    echo "<tr style='padding: 0px;'><td colspan=7>";
    echo "<div class='graphhead'>Monitored Clients</div>";
    include 'includes/html/print-accesspoint-graphs.inc.php';
    echo '</td></tr>';

    $graph_type = 'accesspoints_nummonbssid';
    echo "<tr style='padding: 0px;'><td colspan=7>";
    echo "<div class='graphhead'>Number of monitored BSSIDs</div>";
    include 'includes/html/print-accesspoint-graphs.inc.php';
    echo '</td></tr>';
}//end if
