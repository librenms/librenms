<?php

use LibreNMS\Config;

// FIXME functions!
if (! $graph_type) {
    $graph_type = 'pagp_bits';
}

$daily_traffic = 'graph.php?port=' . $port['port_id'] . "&amp;type=$graph_type&amp;from=" . Config::get('time.day') . '&amp;to=' . Config::get('time.now') . '&amp;width=215&amp;height=100';
$daily_url = 'graph.php?port=' . $port['port_id'] . "&amp;type=$graph_type&amp;from=" . Config::get('time.day') . '&amp;to=' . Config::get('time.now') . '&amp;width=500&amp;height=150';

$weekly_traffic = 'graph.php?port=' . $port['port_id'] . "&amp;type=$graph_type&amp;from=" . Config::get('time.week') . '&amp;to=' . Config::get('time.now') . '&amp;width=215&amp;height=100';
$weekly_url = 'graph.php?port=' . $port['port_id'] . "&amp;type=$graph_type&amp;from=" . Config::get('time.week') . '&amp;to=' . Config::get('time.now') . '&amp;width=500&amp;height=150';

$monthly_traffic = 'graph.php?port=' . $port['port_id'] . "&amp;type=$graph_type&amp;from=" . Config::get('time.month') . '&amp;to=' . Config::get('time.now') . '&amp;width=215&amp;height=100';
$monthly_url = 'graph.php?port=' . $port['port_id'] . "&amp;type=$graph_type&amp;from=" . Config::get('time.month') . '&amp;to=' . Config::get('time.now') . '&amp;width=500&amp;height=150';

$yearly_traffic = 'graph.php?port=' . $port['port_id'] . "&amp;type=$graph_type&amp;from=" . Config::get('time.year') . '&amp;to=' . Config::get('time.now') . '&amp;width=215&amp;height=100';
$yearly_url = 'graph.php?port=' . $port['port_id'] . "&amp;type=$graph_type&amp;from=" . Config::get('time.year') . '&amp;to=' . Config::get('time.now') . '&amp;width=500&amp;height=150';

echo "<a href='#' onmouseover=\"return overlib('<img src=\'$daily_url\'>', LEFT" . Config::get('overlib_defaults') . ");\" onmouseout=\"return nd();\">
      <img src='$daily_traffic' border=0></a> ";
echo "<a href='#' onmouseover=\"return overlib('<img src=\'$weekly_url\'>', LEFT" . Config::get('overlib_defaults') . ");\" onmouseout=\"return nd();\">
      <img src='$weekly_traffic' border=0></a> ";
echo "<a href='#' onmouseover=\"return overlib('<img src=\'$monthly_url\'>', LEFT" . Config::get('overlib_defaults') . ", WIDTH, 350);\" onmouseout=\"return nd();\">
      <img src='$monthly_traffic' border=0></a> ";
echo "<a href='#' onmouseover=\"return overlib('<img src=\'$yearly_url\'>', LEFT" . Config::get('overlib_defaults') . ", WIDTH, 350);\" onmouseout=\"return nd();\">
      <img src='$yearly_traffic' border=0></a>";

foreach (dbFetchRows('SELECT * FROM `ports` WHERE `pagpGroupIfIndex` = ? and `device_id` = ?', [$port['ifIndex'], $device['device_id']]) as $member) {
    $member = cleanPort($member);
    echo "$br<i class='fa fa-anchor fa-lg icon-theme' aria-hidden='true'></i> <strong>" . generate_port_link($member) . ' (PAgP)</strong>';
    $br = '<br />';
}
