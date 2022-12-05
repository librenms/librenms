<?php

$perf = \DeviceCache::getPrimary()->perf;

if ($perf->isNotEmpty()) {
    $perf_url = Url('device') . '/device=' . DeviceCache::getPrimary()->device_id . '/tab=graphs/group=availability/';
    echo '
        <div class="row">
        <div class="col-md-12">
        <div class="panel panel-default panel-condensed">
        <div class="panel-heading">
        <a href="' . $perf_url . '">
        <i class="fas fa-area-chart fa-lg icon-theme" aria-hidden="true"></i><strong>Device Availability</strong></a>
        </div>
        <table class="table table-hover table-condensed table-striped">
            <tr>
            <td colspan="4">';

    $graph = \App\Http\Controllers\Device\Tabs\OverviewController::setGraphWidth([
        'device' => DeviceCache::getPrimary()->device_id,
        'type' => 'device_availability',
        'from' => \LibreNMS\Config::get('time.day'),
        'duration' => 86400,
        'legend' => 'yes',
        'popup_title' => DeviceCache::getPrimary()->hostname . ' - Availability',
    ]);

    echo \LibreNMS\Util\Url::graphPopup($graph, \LibreNMS\Util\Url::lazyGraphTag($graph), $perf_url);
    echo '  </td>
            </tr>
        </table>
        </div>
        </div>
        </div>';
}