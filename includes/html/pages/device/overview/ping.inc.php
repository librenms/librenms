<?php

if (Rrd::checkRrdExists(Rrd::name(DeviceCache::getPrimary()->hostname, 'icmp-perf'))) {
    $perf_url = url('device') . '/device=' . DeviceCache::getPrimary()->device_id . '/tab=graphs/group=poller/';
    echo '
        <div class="overview-panel tw:mb-5">
        <div class="overview-panel-heading">
        <a href="' . $perf_url . '">
        <i class="fas fa-area-chart fa-lg icon-theme" aria-hidden="true"></i><strong>Ping Response</strong></a>
        </div>
        <div class="overview-panel-body">
            <div class="overview-row">
            <div>';

    $graph = \App\Http\Controllers\Device\Tabs\OverviewController::setGraphWidth([
        'device' => DeviceCache::getPrimary()->device_id,
        'type' => 'device_icmp_perf',
        'from' => \App\Facades\LibrenmsConfig::get('time.day'),
        'legend' => 'yes',
        'popup_title' => DeviceCache::getPrimary()->hostname . ' - Ping Response',
    ]);

    echo \LibreNMS\Util\Url::graphPopup($graph, \LibreNMS\Util\Url::lazyGraphTag($graph, 'tw:w-full tw:h-auto'), $perf_url);
    echo '  </div>
            </div>
        </div>
        </div>';
}//end if
