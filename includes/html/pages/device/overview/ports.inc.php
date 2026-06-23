<?php

use LibreNMS\Util\ObjectCache;
use LibreNMS\Util\Rewrite;

if (ObjectCache::portCounts(['total'], $device['device_id'])['total'] > 0) {
    echo '<div class="overview-panel tw:mb-5">
            <div class="tw:px-4 tw:py-2.5 tw:bg-neutral-100 tw:border-b tw:border-gray-300 tw:text-neutral-700 tw:dark:bg-dark-gray-200 tw:dark:border-zinc-800 tw:dark:text-dark-white-200">
              <i class="fa fa-road fa-lg icon-theme" aria-hidden="true"></i><strong> Overall Traffic</strong>
            </div>';

    $graph_array = \App\Http\Controllers\Device\Tabs\OverviewController::setGraphWidth();
    $graph_array['to'] = \App\Facades\LibrenmsConfig::get('time.now');
    $graph_array['device'] = $device['device_id'];
    $graph_array['type'] = 'device_bits';
    $graph_array['from'] = \App\Facades\LibrenmsConfig::get('time.day');
    $graph_array['legend'] = 'no';
    $graph = \LibreNMS\Util\Url::lazyGraphTag($graph_array, 'tw:w-full tw:h-auto');

    //Generate tooltip
    $graph_array['width'] = 210;
    $graph_array['height'] = 100;
    $link_array = $graph_array;
    $link_array['page'] = 'graphs';
    unset($link_array['height'], $link_array['width']);
    $link = \LibreNMS\Util\Url::generate($link_array);

    $graph_array['width'] = '210';
    $overlib_content = generate_overlib_content($graph_array, $device['hostname'] . ' - Device Traffic');

    echo '<div class="tw:flex tw:min-w-0 tw:flex-col tw:bg-white tw:divide-y tw:divide-gray-300 tw:dark:bg-dark-gray-400 tw:dark:divide-zinc-800">';
    echo '<div class="tw:px-2 tw:py-2">';
    echo \LibreNMS\Util\Url::overlibLink($link, $graph, $overlib_content);
    echo '</div>';

    $ports = ObjectCache::portCounts(['total', 'up', 'down', 'disabled'], $device['device_id']);
    echo '<div class="tw:flex tw:flex-wrap tw:gap-3 tw:p-3">
    <a class="lnms-btn lnms-btn-default" role="button" href="' . route('device', ['device' => $device['device_id'], 'tab' => 'ports']) . '">Total: <span class="lnms-btn-badge">' . $ports['total'] . '</span></a>
    <a class="lnms-btn lnms-btn-success" role="button" href="' . route('device', ['device' => $device['device_id'], 'tab' => 'ports', 'filter' => ['state' => ['eq' => 'up'], 'active' => ['eq' => 1]]]) . '">Up: <span class="lnms-btn-badge">' . $ports['up'] . '</span></a>
    <a class="lnms-btn lnms-btn-danger" role="button" href="' . route('device', ['device' => $device['device_id'], 'tab' => 'ports', 'filter' => ['state' => ['eq' => 'down'], 'active' => ['eq' => 1]]]) . '">Down: <span class="lnms-btn-badge">' . $ports['down'] . '</span></a>
    <a class="lnms-btn lnms-btn-primary" role="button" href="' . route('device', ['device' => $device['device_id'], 'tab' => 'ports', 'filter' => ['disabled' => ['eq' => '1']]]) . '">Disabled: <span class="lnms-btn-badge">' . $ports['disabled'] . '</span></a>
    </div>';

    echo '<div class="tw:px-4 tw:py-2.5 tw:bg-neutral-100 tw:border-t tw:border-gray-300 tw:dark:bg-dark-gray-200 tw:dark:border-zinc-800">';

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
}//end if
