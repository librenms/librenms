<?php

if (Rrd::checkRrdExists(Rrd::name(DeviceCache::getPrimary()->hostname, 'icmp-perf'))) {
    $perf_url = Url('device') . '/device=' . DeviceCache::getPrimary()->device_id . '/tab=graphs/group=poller/';
    echo '
        <div class="row">
        <div class="col-md-12">
        <div class="panel panel-default panel-condensed">
        <div class="panel-heading">
        <a href="' . $perf_url . '">
        <i class="fas fa-area-chart fa-lg icon-theme" aria-hidden="true"></i><strong>Ping Response</strong></a>
        </div>
        <table class="table table-hover table-condensed table-striped">
            <tr>
            <td colspan="4">';

    $graph = \App\Http\Controllers\Device\Tabs\OverviewController::setGraphWidth([
        'device' => DeviceCache::getPrimary()->device_id,
        'type' => 'device_icmp_perf',
        'from' => \LibreNMS\Config::get('time.day'),
        'legend' => 'yes',
        'popup_title' => DeviceCache::getPrimary()->hostname . ' - Ping Response',
    ]);

    echo \LibreNMS\Util\Url::graphPopup($graph, \LibreNMS\Util\Url::lazyGraphTag($graph), $perf_url);
    echo '  </td>
            </tr>
        </table>
        </div>
        </div>
        </div>';
}//end if
