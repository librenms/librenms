<?php

use App\Facades\LibrenmsConfig;
use LibreNMS\Util\Smokeping;
use LibreNMS\Util\Url;

if (LibrenmsConfig::get('smokeping.integration') !== true) {
    return;
}

$smokeping = Smokeping::make(DeviceCache::getPrimary());
if (! $smokeping->hasGraphs()) {
    return;
}

$latency_url = route('device', ['device' => DeviceCache::getPrimary()->device_id, 'tab' => 'latency']);
$heading_link = LibrenmsConfig::get('smokeping.url')
    ? LibrenmsConfig::get('smokeping.url') . '?target=' . DeviceCache::getPrimary()->type . '.' . str_replace('.', '_', DeviceCache::getPrimary()->hostname)
    : $latency_url;

$directions = [];
if ($smokeping->hasInGraph()) {
    $directions['in'] = 'Incoming';
}
if ($smokeping->hasOutGraph()) {
    $directions['out'] = 'Outgoing';
}

echo '
<div class="row">
<div class="col-md-12">
<div class="panel panel-default panel-condensed">
<div class="panel-heading">
<a href="' . $heading_link . '"' . (LibrenmsConfig::get('smokeping.url') ? ' target="_blank"' : '') . '>
<i class="fa fa-line-chart fa-lg icon-theme" aria-hidden="true"></i><strong> Smokeping Latency</strong></a>
</div>
<table class="table table-hover table-condensed table-striped">';

foreach ($directions as $direction => $label) {
    $graph = \App\Http\Controllers\Device\Tabs\OverviewController::setGraphWidth([
        'device' => DeviceCache::getPrimary()->device_id,
        'type' => 'device_smokeping_' . $direction . '_all_avg',
        'from' => LibrenmsConfig::get('time.day'),
        'legend' => 'no',
        'popup_title' => DeviceCache::getPrimary()->hostname . ' - Smokeping ' . $label,
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
