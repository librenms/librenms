<?php

use App\Facades\LibrenmsConfig;

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
$ap['graph_type'] = 'accesspoints_numasoclients';
echo generate_ap_link($ap, "<img src='graph.php?type=$ap[graph_type]&amp;id=" . $ap['accesspoint_id'] . '&amp;from=' . LibrenmsConfig::get('time.day') . '&amp;to=' . LibrenmsConfig::get('time.now') . '&amp;width=100&amp;height=20&amp;legend=no&amp;bg=ffffff00' . "'>"); // the 00 at the end makes the background transparent so it adapts to the theme
echo "<br>\n";
$ap['graph_type'] = 'accesspoints_radioutil';
echo generate_ap_link($ap, "<img src='graph.php?type=$ap[graph_type]&amp;id=" . $ap['accesspoint_id'] . '&amp;from=' . LibrenmsConfig::get('time.day') . '&amp;to=' . LibrenmsConfig::get('time.now') . '&amp;width=100&amp;height=20&amp;legend=no&amp;bg=ffffff00' . "'>"); // the 00 at the end makes the background transparent so it adapts to the theme
echo "<br>\n";
$ap['graph_type'] = 'accesspoints_interference';
echo generate_ap_link($ap, "<img src='graph.php?type=$ap[graph_type]&amp;id=" . $ap['accesspoint_id'] . '&amp;from=' . LibrenmsConfig::get('time.day') . '&amp;to=' . LibrenmsConfig::get('time.now') . '&amp;width=100&amp;height=20&amp;legend=no&amp;bg=ffffff00' . "'>"); // the 00 at the end makes the background transparent so it adapts to the theme
echo "<br>\n";

echo '</td><td width=120>';

echo "<i class='fa fa-wifi fa-lg icon-theme' aria-hidden='true'></i> " . \LibreNMS\Util\Number::formatBi($ap['numasoclients'], 2, 0, '') . ' Clients<br />';
echo "<i class='fa fa-wifi fa-lg icon-theme' aria-hidden='true'></i> " . \LibreNMS\Util\Number::formatBi($ap['radioutil'], 2, 0, '') . ' % busy<br />';
echo "<i class='fa fa-wifi fa-lg icon-theme' aria-hidden='true'></i> " . \LibreNMS\Util\Number::formatBi($ap['interference'], 2, 0, '') . ' interference index<br />';

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
