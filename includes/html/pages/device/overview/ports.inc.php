<?php

use LibreNMS\Util\ObjectCache;
use LibreNMS\Util\Rewrite;

if (ObjectCache::portCounts(['total'], $device['device_id'])['total'] > 0) {
    echo '<div class="row">
          <div class="col-md-12">
            <div class="panel panel-default panel-condensed">
              <div class="panel-heading">
              <i class="fa fa-road fa-lg icon-theme" aria-hidden="true"></i><strong> Overall Traffic</strong>
            </div>';

    $graph_array = \App\Http\Controllers\Device\Tabs\OverviewController::setGraphWidth();
    $graph_array['to'] = \App\Facades\LibrenmsConfig::get('time.now');
    $graph_array['device'] = $device['device_id'];
    $graph_array['type'] = 'device_bits';
    $graph_array['from'] = \App\Facades\LibrenmsConfig::get('time.day');
    $graph_array['legend'] = 'no';
    $graph = \LibreNMS\Util\Url::lazyGraphTag($graph_array);

    //Generate tooltip
    $graph_array['width'] = 210;
    $graph_array['height'] = 100;
    $link_array = $graph_array;
    $link_array['page'] = 'graphs';
    unset($link_array['height'], $link_array['width']);
    $link = \LibreNMS\Util\Url::generate($link_array);

    $graph_array['width'] = '210';
    $overlib_content = generate_overlib_content($graph_array, $device['hostname'] . ' - Device Traffic');

    echo \LibreNMS\Util\Url::overlibLink($link, $graph, $overlib_content);

    $ports = ObjectCache::portCounts(['total', 'up', 'down', 'disabled'], $device['device_id']);
    echo '<div class="panel-body">
    <a class="btn btn-default" role="button" href="' . \LibreNMS\Util\Url::deviceUrl($device['device_id'], ['tab' => 'ports']) . '">Total: <span class="badge">' . $ports['total'] . '</span></a>
    <a class="btn btn-success" role="button" href="' . \LibreNMS\Util\Url::deviceUrl($device['device_id'], ['tab' => 'ports', 'status' => 'up']) . '">Up: <span class="badge">' . $ports['up'] . '</span></a>
    <a class="btn btn-danger" role="button" href="' . \LibreNMS\Util\Url::deviceUrl($device['device_id'], ['tab' => 'ports', 'status' => 'down']) . '">Down: <span class="badge">' . $ports['down'] . '</span></a>
    <a class="btn btn-primary" role="button" href="' . \LibreNMS\Util\Url::deviceUrl($device['device_id'], ['tab' => 'ports', 'disabled' => 1]) . '">Disabled: <span class="badge">' . $ports['disabled'] . '</span></a>
    </div>';

    echo '<div class="panel-footer">';

    $ifsep = '';

    foreach (dbFetchRows("SELECT * FROM `ports` WHERE device_id = ? AND `deleted` != '1' AND `disabled` = 0 ORDER BY ifName", [$device['device_id']]) as $data) {
        $data = cleanPort($data);
        $data = array_merge($data, $device);
        echo "$ifsep" . generate_port_link($data, Rewrite::shortenIfName(strtolower((string) $data['label'])));
        $ifsep = ', ';
    }

    unset($ifsep);
    echo '</div>';
    echo '</div>';
    echo '</div>';
    echo '</div>';
}//end if
