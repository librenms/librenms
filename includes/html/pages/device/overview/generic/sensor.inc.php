<?php

use LibreNMS\Util\Html;

$sensors = DeviceCache::getPrimary()->sensors->where('sensor_class', $sensor_class->value)->where('group', '!=', 'transceiver')->sortBy([
    ['group', 'asc'],
    ['sensor_descr', 'asc'],
]); // cache all sensors on device and exclude transceivers

if ($sensors->isNotEmpty()) {
    $sensor_fa_icon = 'fa-' . $sensor_class->icon();

    echo '
        <div class="overview-panel tw:mb-5">
        <div class="tw:px-4 tw:py-2.5 tw:bg-neutral-100 tw:border-b tw:border-gray-300 tw:text-neutral-700 tw:dark:bg-dark-gray-200 tw:dark:border-zinc-800 tw:dark:text-dark-white-200">';
    echo '<a href="device/device=' . $device['device_id'] . '/tab=health/metric=' . $sensor_class->value . '/"><i class="fa ' . $sensor_fa_icon . ' fa-lg icon-theme" aria-hidden="true"></i><strong> ' . $sensor_class->label() . '</strong></a>';
    echo '      </div>
        <div class="tw:flex tw:min-w-0 tw:flex-col tw:bg-white tw:divide-y tw:divide-gray-300 tw:dark:bg-dark-gray-400 tw:dark:divide-zinc-800">';
    $group = '';
    foreach ($sensors as $sensor) {
        if ($group != $sensor->group) {
            $group = $sensor->group;
            echo '<div class="tw:px-2 tw:py-2 tw:font-bold"><strong>' . $group . '</strong></div>';
        }

        // FIXME - make this "four graphs in popup" a function/include and "small graph" a function.
        // FIXME - So now we need to clean this up and move it into a function. Isn't it just "print-graphrow"?
        // FIXME - DUPLICATED IN health/sensors
        $graph_array = [];
        $graph_array['height'] = '100';
        $graph_array['width'] = '210';
        $graph_array['to'] = \App\Facades\LibrenmsConfig::get('time.now');
        $graph_array['id'] = $sensor->sensor_id;
        $graph_array['type'] = 'sensor_' . $sensor_class->value;
        $graph_array['from'] = \App\Facades\LibrenmsConfig::get('time.day');
        $graph_array['legend'] = 'no';

        $link_array = $graph_array;
        $link_array['page'] = 'graphs';
        unset($link_array['height'], $link_array['width'], $link_array['legend']);
        $link = \LibreNMS\Util\Url::generate($link_array);

        if ($sensor->poller_type == 'ipmi') {
            $sensor->sensor_descr = substr((string) ipmiSensorName($device['hardware'], $sensor->sensor_descr), 0, 48);
        } else {
            $sensor->sensor_descr = substr((string) $sensor->sensor_descr, 0, 48);
        }

        $overlib_content = '<div class=overlib><span class=overlib-text>' . $device['hostname'] . ' - ' . $sensor->sensor_descr . '</span><br />';
        foreach (['day', 'week', 'month', 'year'] as $period) {
            $graph_array['from'] = \App\Facades\LibrenmsConfig::get("time.$period");
            $overlib_content .= str_replace('"', "\'", \LibreNMS\Util\Url::graphTag($graph_array));
        }

        $overlib_content .= '</div>';

        $graph_array['width'] = 80;
        $graph_array['height'] = 20;
        $graph_array['bg'] = 'ffffff00';
        // the 00 at the end makes the area transparent.
        $graph_array['from'] = \App\Facades\LibrenmsConfig::get('time.day');
        $sensor_minigraph = \LibreNMS\Util\Url::lazyGraphTag($graph_array);

        $sensor_current = Html::severityToLabel($sensor->currentStatus(), $sensor->formatValue());

        echo '<div class="tw:flex tw:items-center tw:gap-2.5 tw:px-2 tw:py-2 tw:hover:bg-neutral-100 tw:dark:hover:bg-dark-gray-300">
            <div class="tw:w-48 tw:min-w-0 tw:shrink-0 tw:whitespace-nowrap">' . \LibreNMS\Util\Url::overlibLink($link, \LibreNMS\Util\Rewrite::shortenIfName($sensor->sensor_descr), $overlib_content, $sensor_class->value) . '</div>
            <div class="tw:flex tw:min-w-0 tw:flex-1 tw:justify-center">' . \LibreNMS\Util\Url::overlibLink($link, $sensor_minigraph, $overlib_content, $sensor_class->value) . '</div>
            <div class="tw:text-right">' . \LibreNMS\Util\Url::overlibLink($link, $sensor_current, $overlib_content, $sensor_class->value) . '</div>
            </div>';
    }//end foreach

    echo '</div>';
    echo '</div>';
}//end if
