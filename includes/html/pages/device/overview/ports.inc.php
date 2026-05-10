<?php

use App\Facades\DeviceCache;
use App\Facades\LibrenmsConfig;
use App\Http\Controllers\Device\Tabs\OverviewController;
use App\Models\Port;
use LibreNMS\Util\ObjectCache;
use LibreNMS\Util\Rewrite;
use LibreNMS\Util\Url;

$device = DeviceCache::getPrimary();
if (ObjectCache::portCounts(['total'], $device->device_id)['total'] > 0) {
    echo '<div class="row">
          <div class="col-md-12">
            <div class="panel panel-default panel-condensed">
              <div class="panel-heading">
              <i class="fa fa-road fa-lg icon-theme" aria-hidden="true"></i><strong> Overall Traffic</strong>
            </div>';

    $graph_array = OverviewController::setGraphWidth();
    $graph_array['to'] = LibrenmsConfig::get('time.now');
    $graph_array['device'] = $device->device_id;
    $graph_array['type'] = 'device_bits';
    $graph_array['from'] = LibrenmsConfig::get('time.day');
    $graph_array['legend'] = 'no';
    $graph = Url::lazyGraphTag($graph_array);

    //Generate tooltip
    $graph_array['width'] = 210;
    $graph_array['height'] = 100;
    $link_array = $graph_array;
    $link_array['page'] = 'graphs';
    unset($link_array['height'], $link_array['width']);
    $link = Url::generate($link_array);

    $graph_array['width'] = '210';
    $overlib_content = generate_overlib_content($graph_array, $device->hostname . ' - Device Traffic');

    echo Url::overlibLink($link, $graph, $overlib_content);

    $ports = ObjectCache::portCounts(['total', 'up', 'down', 'disabled'], $device->device_id);
    echo '<div class="panel-body">
    <a class="btn btn-default" role="button" href="' . route('device', ['device' => $device->device_id, 'tab' => 'ports']) . '">Total: <span class="badge">' . $ports['total'] . '</span></a>
    <a class="btn btn-success" role="button" href="' . route('device', ['device' => $device->device_id, 'tab' => 'ports', 'status' => 'up', 'admin' => 'up']) . '">Up: <span class="badge">' . $ports['up'] . '</span></a>
    <a class="btn btn-danger" role="button" href="' . route('device', ['device' => $device->device_id, 'tab' => 'ports', 'status' => 'down', 'admin' => 'up']) . '">Down: <span class="badge">' . $ports['down'] . '</span></a>
    <a class="btn btn-primary" role="button" href="' . route('device', ['device' => $device->device_id, 'tab' => 'ports', 'disabled' => 1]) . '">Disabled: <span class="badge">' . $ports['disabled'] . '</span></a>
    </div>';

    echo '<div class="panel-footer">';

    echo Port::query()
        ->whereBelongsTo($device)
        ->isDeleted()
        ->where('disabled', 0)
        ->orderBy('ifName')
        ->get()
        ->map(fn ($p) => Url::portLink($p, Rewrite::shortenIfName(strtolower((string) $p->getLabel()))))
        ->implode(', ');
    echo '</div>';
    echo '</div>';
    echo '</div>';
    echo '</div>';
}//end if
