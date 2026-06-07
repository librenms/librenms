<?php

if (Rrd::checkRrdExists(Rrd::name(DeviceCache::getPrimary()->hostname, 'icmp-perf'))) {
    $perf_url = url('device') . '/device=' . DeviceCache::getPrimary()->device_id . '/tab=graphs/group=poller/';
    echo '
        <div class="overview-panel tw:mb-5">
        <div class="tw:px-4 tw:py-2.5 tw:bg-[#f5f5f5] tw:border-b tw:border-gray-300 tw:text-[#333] tw:dark:bg-dark-gray-200 tw:dark:border-[#1c1e22] tw:dark:text-dark-white-200">
        <a href="' . $perf_url . '">
        <i class="fas fa-area-chart fa-lg icon-theme" aria-hidden="true"></i><strong>Ping Response</strong></a>
        </div>
        <div class="tw:flex tw:flex-col tw:bg-white tw:divide-y tw:divide-gray-300 tw:dark:bg-dark-gray-400 tw:dark:divide-[#1c1e22]">
            <div class="tw:grid tw:items-center tw:gap-2.5 tw:px-2 tw:py-2 tw:hover:bg-[#f5f5f5] tw:dark:hover:bg-dark-gray-300">
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
