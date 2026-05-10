<?php

use App\Facades\DeviceCache;
use App\Facades\LibrenmsConfig;
use App\Facades\PortCache;
use App\Models\Port;
use LibreNMS\Util\Url;

$device = DeviceCache::getPrimary();
$port = PortCache::get($port['port_id']);

// FIXME functions!
if (! $graph_type) {
    $graph_type = 'pagp_bits';
}

$daily_traffic = 'graph.php?port=' . $port->port_id . "&amp;type=$graph_type&amp;from=" . LibrenmsConfig::get('time.day') . '&amp;to=' . LibrenmsConfig::get('time.now') . '&amp;width=215&amp;height=100';
$daily_url = 'graph.php?port=' . $port->port_id . "&amp;type=$graph_type&amp;from=" . LibrenmsConfig::get('time.day') . '&amp;to=' . LibrenmsConfig::get('time.now') . '&amp;width=500&amp;height=150';

$weekly_traffic = 'graph.php?port=' . $port->port_id . "&amp;type=$graph_type&amp;from=" . LibrenmsConfig::get('time.week') . '&amp;to=' . LibrenmsConfig::get('time.now') . '&amp;width=215&amp;height=100';
$weekly_url = 'graph.php?port=' . $port->port_id . "&amp;type=$graph_type&amp;from=" . LibrenmsConfig::get('time.week') . '&amp;to=' . LibrenmsConfig::get('time.now') . '&amp;width=500&amp;height=150';

$monthly_traffic = 'graph.php?port=' . $port->port_id . "&amp;type=$graph_type&amp;from=" . LibrenmsConfig::get('time.month') . '&amp;to=' . LibrenmsConfig::get('time.now') . '&amp;width=215&amp;height=100';
$monthly_url = 'graph.php?port=' . $port->port_id . "&amp;type=$graph_type&amp;from=" . LibrenmsConfig::get('time.month') . '&amp;to=' . LibrenmsConfig::get('time.now') . '&amp;width=500&amp;height=150';

$yearly_traffic = 'graph.php?port=' . $port->port_id . "&amp;type=$graph_type&amp;from=" . LibrenmsConfig::get('time.year') . '&amp;to=' . LibrenmsConfig::get('time.now') . '&amp;width=215&amp;height=100';
$yearly_url = 'graph.php?port=' . $port->port_id . "&amp;type=$graph_type&amp;from=" . LibrenmsConfig::get('time.year') . '&amp;to=' . LibrenmsConfig::get('time.now') . '&amp;width=500&amp;height=150';

echo "<a href='#' onmouseover=\"return overlib('<img src=\'$daily_url\'>', LEFT" . LibrenmsConfig::get('overlib_defaults') . ");\" onmouseout=\"return nd();\">
      <img src='$daily_traffic' border=0></a> ";
echo "<a href='#' onmouseover=\"return overlib('<img src=\'$weekly_url\'>', LEFT" . LibrenmsConfig::get('overlib_defaults') . ");\" onmouseout=\"return nd();\">
      <img src='$weekly_traffic' border=0></a> ";
echo "<a href='#' onmouseover=\"return overlib('<img src=\'$monthly_url\'>', LEFT" . LibrenmsConfig::get('overlib_defaults') . ", WIDTH, 350);\" onmouseout=\"return nd();\">
      <img src='$monthly_traffic' border=0></a> ";
echo "<a href='#' onmouseover=\"return overlib('<img src=\'$yearly_url\'>', LEFT" . LibrenmsConfig::get('overlib_defaults') . ", WIDTH, 350);\" onmouseout=\"return nd();\">
      <img src='$yearly_traffic' border=0></a>";

echo Port::query()
    ->whereBelongsTo($device)
    ->where('pagpGroupIfIndex', $port->ifIndex)
    ->get()
    ->map(fn ($member) => '<i class="fa fa-anchor fa-lg icon-theme" aria-hidden="true"></i> <strong>' . Url::portLink($member) . ' (PAgP)</strong>')
    ->implode('<br />');
