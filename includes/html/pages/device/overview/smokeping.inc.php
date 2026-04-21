<?php

use App\Facades\LibrenmsConfig;
use LibreNMS\Data\Store\Rrd;
use LibreNMS\Util\Smokeping;
use LibreNMS\Util\Url;

$device_obj = DeviceCache::getPrimary();
$latency_url = route('device', ['device' => $device_obj->device_id, 'tab' => 'latency']);

$smokeping = LibrenmsConfig::get('smokeping.integration') === true
    ? Smokeping::make($device_obj)
    : null;

if ($smokeping && $smokeping->hasGraphs()) {
    $directions = [];
    if ($smokeping->hasInGraph()) {
        $directions['in'] = 'Incoming';
    }
    if ($smokeping->hasOutGraph()) {
        $directions['out'] = 'Outgoing';
    }

    $heading_link = LibrenmsConfig::get('smokeping.url')
        ? LibrenmsConfig::get('smokeping.url') . '?target=' . $device_obj->type . '.' . str_replace('.', '_', $device_obj->hostname)
        : $latency_url;
    $heading_target = LibrenmsConfig::get('smokeping.url') ? ' target="_blank"' : '';

    echo '
<div class="row">
<div class="col-md-12">
<div class="panel panel-default panel-condensed">
<div class="panel-heading">
<a href="' . $heading_link . '"' . $heading_target . '>
<i class="fa fa-line-chart fa-lg icon-theme" aria-hidden="true"></i><strong> Smokeping Latency</strong></a>
</div>
<table class="table table-hover table-condensed table-striped">';

    foreach ($directions as $direction => $label) {
        $graph = \App\Http\Controllers\Device\Tabs\OverviewController::setGraphWidth([
            'device' => $device_obj->device_id,
            'type' => 'device_smokeping_' . $direction . '_all_avg',
            'from' => LibrenmsConfig::get('time.day'),
            'legend' => 'no',
            'popup_title' => $device_obj->hostname . ' - Smokeping ' . $label,
        ]);
        $graph['height'] = 120;

        echo '<tr><td colspan="4">';
        if (count($directions) > 1) {
            echo '<div class="tw:text-sm tw:font-semibold tw:mb-1">' . $label . '</div>';
        }
        echo Url::graphPopup($graph, Url::lazyGraphTag($graph), $latency_url);
        echo '</td></tr>';
    }

    echo '
</table>
</div>
</div>
</div>';

    return;
}

// Fallback: ICMP Performance graph when smokeping is not available.
// Skip for os=ping devices — overview/ping.inc.php already shows this graph as "Ping Response".
if ($device_obj->os === 'ping' || ! Rrd::checkRrdExists(Rrd::name($device_obj->hostname, 'icmp-perf'))) {
    return;
}

$graph = \App\Http\Controllers\Device\Tabs\OverviewController::setGraphWidth([
    'device' => $device_obj->device_id,
    'type' => 'device_icmp_perf',
    'from' => LibrenmsConfig::get('time.day'),
    'legend' => 'no',
    'popup_title' => $device_obj->hostname . ' - ICMP Performance',
]);
$graph['height'] = 120;

echo '
<div class="row">
<div class="col-md-12">
<div class="panel panel-default panel-condensed">
<div class="panel-heading">
<a href="' . $latency_url . '">
<i class="fa fa-line-chart fa-lg icon-theme" aria-hidden="true"></i><strong> ICMP Performance</strong></a>
</div>
<table class="table table-hover table-condensed table-striped">
<tr><td colspan="4">';
echo Url::graphPopup($graph, Url::lazyGraphTag($graph), $latency_url);
echo '</td></tr>
</table>
</div>
</div>
</div>';
