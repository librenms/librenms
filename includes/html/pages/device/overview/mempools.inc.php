<?php

use Illuminate\Support\Arr;
use LibreNMS\Util\Colors;
use LibreNMS\Util\Html;
use LibreNMS\Util\Number;
use LibreNMS\Util\Url;

$graph_type = 'mempool_usage';

$mempools = \DeviceCache::getPrimary()->mempools;

function print_mempool_percent_bar($mempool)
{
}

if ($mempools->isNotEmpty()) {
    $mempools_url = url('device') . '/device=' . DeviceCache::getPrimary()->device_id . '/tab=health/metric=mempool/';
    echo '
        <div class="row">
        <div class="col-md-12">
        <div class="panel panel-default panel-condensed">
        <div class="panel-heading">
        ';
    echo '<a href="' . $mempools_url . '">';
    echo '<i class="fa fa-braille fa-lg icon-theme" aria-hidden="true"></i> <strong>Memory</strong></a>';
    echo '
        </div>
        <table class="table table-hover table-condensed table-striped">
        ';

    echo '<tr>
              <td colspan="4">';
    $graph = \App\Http\Controllers\Device\Tabs\OverviewController::setGraphWidth([
        'device' => DeviceCache::getPrimary()->device_id,
        'type' => 'device_mempool',
        'from' => \LibreNMS\Config::get('time.day'),
        'legend' => 'no',
        'popup_title' => DeviceCache::getPrimary()->hostname . ' - Memory Usage',
    ]);
    echo \LibreNMS\Util\Url::graphPopup($graph, \LibreNMS\Util\Url::lazyGraphTag($graph), $mempools_url);
    echo '  </td>
            </tr>';

    // percentage line items
    foreach ($mempools as $mempool) {
        $available_used_all = null;
        $percent_text = $mempool->mempool_perc;
        if ($mempool->mempool_class == 'system' && $mempools->count() > 1) {
            // calculate available RAM instead of Free
            $buffers = $mempools->firstWhere('mempool_class', '=', 'buffers');
            $cached = $mempools->firstWhere('mempool_class', '=', 'cached');

            $available_used_all = $mempool->mempool_total ? round(($mempool->mempool_used + $buffers->mempool_used + $cached->mempool_used) / $mempool->mempool_total * 100) : 0;
        }

        $total = Number::formatBi($mempool->mempool_total);
        $used = Number::formatBi($mempool->mempool_used);
        $free = Number::formatBi($mempool->mempool_free);
        $percent_colors = Colors::percentage($mempool->mempool_perc, $mempool->mempool_perc_warn ?: null);

        $graph_array = [
            'type' => 'mempool_usage',
            'id' => $mempool->mempool_id,
            'height' => 100,
            'width' => 210,
            'from' => \LibreNMS\Config::get('time.day'),
            'to' => \LibreNMS\Config::get('time.now'),
            'legend' => 'no',
        ];

        $link = Url::generate(['page' => 'graphs'], Arr::only($graph_array, ['id', 'type', 'from']));
        $overlib_content = generate_overlib_content($graph_array, DeviceCache::getPrimary()->hostname . ' - ' . $mempool->mempool_descr);

        $graph_array['width'] = 80;
        $graph_array['height'] = 20;
        $graph_array['bg'] = 'ffffff00';
        // the 00 at the end makes the area transparent.
        $minigraph = \LibreNMS\Util\Url::lazyGraphTag($graph_array);

        $percentageBar = $available_used_all
            ? Html::percentageBar(200, 20, $mempool->mempool_perc, "$mempool->mempool_perc%", "$available_used_all%", $mempool->mempool_perc_warn, $available_used_all)
            : Html::percentageBar(200, 20, $mempool->mempool_perc, "$mempool->mempool_perc%", '', $mempool->mempool_perc_warn);
        echo '<tr>
            <td class="col-md-4">' . \LibreNMS\Util\Url::overlibLink($link, $mempool->mempool_descr, $overlib_content) . '</td>
            <td class="col-md-4">' . \LibreNMS\Util\Url::overlibLink($link, $minigraph, $overlib_content) . '</td>
            <td class="col-md-4">' . \LibreNMS\Util\Url::overlibLink($link, $percentageBar, $overlib_content) . '
            </a></td>
            </tr>';
    }//end foreach

    echo '</table>
        </div>
        </div>
        </div>';
}//end if
