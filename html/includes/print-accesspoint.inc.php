<?php

if ($int_colour) {
    $row_colour = $int_colour;
} else {
    if (!is_integer($i / 2)) {
        $row_colour = $list_colour_a;
    } else {
        $row_colour = $list_colour_b;
    }
}


$text       = $ap['name'].' '.$ap['type'];
$ap['text'] = $text;

echo "<tr style=\"background-color: $row_colour;\" valign=top onmouseover=\"this.style.backgroundColor='$list_highlight';\" onmouseout=\"this.style.backgroundColor='$row_colour';\" onclick=\"location.href='".generate_ap_url($ap)."/'\" style='cursor: pointer;'>
         <td valign=top width=350>";
echo '        <span class=list-large> '.generate_ap_link($ap, " $text </span><br />");
echo '<span class=interface-desc>';
echo "$break".$ap['mac_addr'].'<br>'.$ap['type'].' - channel '.$ap['channel'];
echo "<br />txpow $ap[txpow]";
echo '</span>';
echo '</td><td width=100>';


echo '</td><td width=150>';
$ap['graph_type'] = 'accesspoints_numasoclients';
echo generate_ap_link($ap, "<img src='graph.php?type=$ap[graph_type]&amp;id=".$ap['accesspoint_id'].'&amp;from='.$config['time']['day'].'&amp;to='.$config['time']['now'].'&amp;width=100&amp;height=20&amp;legend=no&amp;bg='.str_replace('#', '', $row_colour)."'>");
echo "<br>\n";
$ap['graph_type'] = 'accesspoints_radioutil';
echo generate_ap_link($ap, "<img src='graph.php?type=$ap[graph_type]&amp;id=".$ap['accesspoint_id'].'&amp;from='.$config['time']['day'].'&amp;to='.$config['time']['now'].'&amp;width=100&amp;height=20&amp;legend=no&amp;bg='.str_replace('#', '', $row_colour)."'>");
echo "<br>\n";
$ap['graph_type'] = 'accesspoints_interference';
echo generate_ap_link($ap, "<img src='graph.php?type=$ap[graph_type]&amp;id=".$ap['accesspoint_id'].'&amp;from='.$config['time']['day'].'&amp;to='.$config['time']['now'].'&amp;width=100&amp;height=20&amp;legend=no&amp;bg='.str_replace('#', '', $row_colour)."'>");
echo "<br>\n";

echo '</td><td width=120>';

echo "<i class='fa fa-wifi fa-lg icon-theme' aria-hidden='true'></i> ".format_bi($ap[numasoclients]).' Clients<br />';
echo "<i class='fa fa-wifi fa-lg icon-theme' aria-hidden='true'></i> ".format_bi($ap[radioutil]).' % busy<br />';
echo "<i class='fa fa-wifi fa-lg icon-theme' aria-hidden='true'></i> ".format_bi($ap[interference]).' interference index<br />';

echo '</td></tr>';



if ($vars['tab'] == 'accesspoint') {
    $graph_type = 'accesspoints_numasoclients';
    echo "<tr style='background-color: $row_colour; padding: 0px;'><td colspan=7>";
    echo "<div class='graphhead'>Associated Clients</div>";
    include 'includes/print-accesspoint-graphs.inc.php';
    echo '</td></tr>';

    $graph_type = 'accesspoints_interference';
    echo "<tr style='background-color: $row_colour; padding: 0px;'><td colspan=7>";
    echo "<div class='graphhead'>Interference</div>";
    include 'includes/print-accesspoint-graphs.inc.php';
    echo '</td></tr>';

    $graph_type = 'accesspoints_channel';
    echo "<tr style='background-color: $row_colour; padding: 0px;'><td colspan=7>";
    echo "<div class='graphhead'>Channel</div>";
    include 'includes/print-accesspoint-graphs.inc.php';
    echo '</td></tr>';

    $graph_type = 'accesspoints_txpow';
    echo "<tr style='background-color: $row_colour; padding: 0px;'><td colspan=7>";
    echo "<div class='graphhead'>Transmit Power</div>";
    include 'includes/print-accesspoint-graphs.inc.php';
    echo '</td></tr>';

    $graph_type = 'accesspoints_radioutil';
    echo "<tr style='background-color: $row_colour; padding: 0px;'><td colspan=7>";
    echo "<div class='graphhead'>Radio Utilization</div>";
    include 'includes/print-accesspoint-graphs.inc.php';
    echo '</td></tr>';

    $graph_type = 'accesspoints_nummonclients';
    echo "<tr style='background-color: $row_colour; padding: 0px;'><td colspan=7>";
    echo "<div class='graphhead'>Monitored Clients</div>";
    include 'includes/print-accesspoint-graphs.inc.php';
    echo '</td></tr>';

    $graph_type = 'accesspoints_nummonbssid';
    echo "<tr style='background-color: $row_colour; padding: 0px;'><td colspan=7>";
    echo "<div class='graphhead'>Number of monitored BSSIDs</div>";
    include 'includes/print-accesspoint-graphs.inc.php';
    echo '</td></tr>';
}//end if
