<?php

use Illuminate\Support\Arr;
use LibreNMS\Util\Color;
use LibreNMS\Util\Html;
use LibreNMS\Util\Number;
use LibreNMS\Util\Url;

$graph_type = 'mempool_usage';

$mempools = \DeviceCache::getPrimary()->mempools;

if ($mempools->isNotEmpty()) {
    $mempools_url = url('device') . '/device=' . DeviceCache::getPrimary()->device_id . '/tab=health/metric=mempool/';
    echo '
        <div class="overview-panel tw:mb-5">
        <div class="tw:px-4 tw:py-2.5 tw:bg-neutral-100 tw:border-b tw:border-gray-300 tw:text-neutral-700 tw:dark:bg-dark-gray-200 tw:dark:border-zinc-800 tw:dark:text-dark-white-200">
        ';
    echo '<a href="' . $mempools_url . '">';
    echo '<i class="fas fa-memory fa-lg icon-theme" aria-hidden="true"></i> <strong>Memory</strong></a>';
    echo '
        </div>
        <div class="tw:flex tw:min-w-0 tw:flex-col tw:bg-white tw:divide-y tw:divide-gray-300 tw:dark:bg-dark-gray-400 tw:dark:divide-zinc-800">
        ';

    echo '<div class="tw:grid tw:items-center tw:gap-2.5 tw:px-2 tw:py-2 tw:hover:bg-neutral-100 tw:dark:hover:bg-dark-gray-300">
              <div>';
    $graph = \App\Http\Controllers\Device\Tabs\OverviewController::setGraphWidth([
        'device' => DeviceCache::getPrimary()->device_id,
        'type' => 'device_mempool',
        'from' => \App\Facades\LibrenmsConfig::get('time.day'),
        'legend' => 'no',
        'popup_title' => DeviceCache::getPrimary()->hostname . ' - Memory Usage',
    ]);
    echo \LibreNMS\Util\Url::graphPopup($graph, \LibreNMS\Util\Url::lazyGraphTag($graph, 'tw:w-full tw:h-auto'), $mempools_url);
    echo '  </div>
            </div>';

    // percentage line items
    foreach ($mempools as $mempool) {
        $available_used_all = null;
        $percent_text = $mempool->mempool_perc;
        if ($mempool->mempool_class == 'system' && $mempools->count() > 1) {
            // calculate available RAM instead of Free
            $buffers = $mempools->firstWhere('mempool_class', '=', 'buffers')->mempool_used ?? 0;
            $cached = $mempools->firstWhere('mempool_class', '=', 'cached')->mempool_used ?? 0;

            $available_used_all = Number::calculatePercent($mempool->mempool_used + $buffers + $cached, $mempool->mempool_total, 0);
        }

        $total = Number::formatBi($mempool->mempool_total);
        $used = Number::formatBi($mempool->mempool_used);
        $free = Number::formatBi($mempool->mempool_free);
        $percent_colors = Color::percentage($mempool->mempool_perc, $mempool->mempool_perc_warn ?: null);

        $graph_array = [
            'type' => 'mempool_usage',
            'id' => $mempool->mempool_id,
            'height' => 100,
            'width' => 210,
            'from' => \App\Facades\LibrenmsConfig::get('time.day'),
            'to' => \App\Facades\LibrenmsConfig::get('time.now'),
            'legend' => 'no',
        ];

        $link = Url::generate(['page' => 'graphs'], Arr::only($graph_array, ['id', 'type', 'from']));
        $overlib_content = generate_overlib_content($graph_array, DeviceCache::getPrimary()->hostname . ' - ' . $mempool->mempool_descr);

        $graph_array['width'] = 80;
        $graph_array['height'] = 20;
        $graph_array['bg'] = 'ffffff00';
        // the 00 at the end makes the area transparent.
        $minigraph = \LibreNMS\Util\Url::lazyGraphTag($graph_array);

        $percentageBar = match ($mempool->mempool_class) {
            'system' => Html::percentageBar(400, 10, $mempool->mempool_perc, "$used / $total ($mempool->mempool_perc%)", $free, $mempool->mempool_perc_warn, $available_used_all),
            'virtual', 'swap' => Html::percentageBar(400, 10, $mempool->mempool_perc, "$used / $total ($mempool->mempool_perc%)", $free, $mempool->mempool_perc_warn),
            default => Html::percentageBar(400, 10, $mempool->mempool_perc, "$used ($mempool->mempool_perc%)", '', $mempool->mempool_perc_warn),
        };

        echo '<div class="tw:flex tw:items-center tw:gap-2.5 tw:px-2 tw:py-2 tw:hover:bg-neutral-100 tw:dark:hover:bg-dark-gray-300">
            <div class="tw:w-36 tw:min-w-0 tw:shrink-0 tw:truncate">' . \LibreNMS\Util\Url::overlibLink($link, $mempool->mempool_descr, $overlib_content) . '</div>
            <div class="tw:flex tw:min-w-0 tw:flex-1 tw:justify-center">' . \LibreNMS\Util\Url::overlibLink($link, $minigraph, $overlib_content) . '</div>
            <div>' . \LibreNMS\Util\Url::overlibLink($link, $percentageBar, $overlib_content) . '</div>
            </div>';
    }//end foreach

    echo '</div>
        </div>';
}//end if
