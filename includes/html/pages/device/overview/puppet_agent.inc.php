<?php

$app = \App\Models\Application::query()->where('device_id', $device['device_id'])->where('app_type', 'puppet-agent')->first();

// show only if Puppet Agent Application discovered
if ($app) {
    $metrics = $app->metrics->pluck('value', 'metric');

    $graph_array = [];
    $graph_array['height'] = '100';
    $graph_array['width'] = '210';
    $graph_array['to'] = \App\Facades\LibrenmsConfig::get('time.now');
    $graph_array['id'] = $app->app_id;
    $graph_array['from'] = \App\Facades\LibrenmsConfig::get('time.day');
    $graph_array['legend'] = 'no';

    // graph last run
    $title_last_run = 'Last run';
    $graph_array_last_run = $graph_array;
    $graph_array_last_run['type'] = 'application_puppet-agent_last_run';

    $link_array_last_run = $graph_array_last_run;
    $link_array_last_run['page'] = 'graphs';
    unset($link_array_last_run['height'], $link_array_last_run['width'], $link_array_last_run['legend']);
    $link_last_run = \LibreNMS\Util\Url::generate($link_array_last_run);

    $overlib_content_last_run = generate_overlib_content($graph_array_last_run, $device['hostname'] . ' - ' . $title_last_run);
    $overlib_link_last_run = \LibreNMS\Util\Url::overlibLink($link_last_run, 'Last run', $overlib_content_last_run);

    // graph runtime
    $title_runtime = 'Runtime';
    $graph_array_runtime = $graph_array;
    $graph_array_runtime['type'] = 'application_puppet-agent_time';

    $link_array_runtime = $graph_array_runtime;
    $link_array_runtime['page'] = 'graphs';
    unset($link_array_runtime['height'], $link_array_runtime['width'], $link_array_runtime['legend']);
    $link_runtime = \LibreNMS\Util\Url::generate($link_array_runtime);

    $overlib_content_runtime = generate_overlib_content($graph_array_runtime, $device['hostname'] . ' - ' . $title_runtime);
    $overlib_link_runtime = \LibreNMS\Util\Url::overlibLink($link_runtime, $title_runtime, $overlib_content_runtime);

    // graph resources
    $title_resources = 'Resources';
    $graph_array_resources = $graph_array;
    $graph_array_resources['type'] = 'application_puppet-agent_resources';

    $link_array_resources = $graph_array_resources;
    $link_array_resources['page'] = 'graphs';
    unset($link_array_resources['height'], $link_array_resources['width'], $link_array_resources['legend']);
    $link_resources = \LibreNMS\Util\Url::generate($link_array_resources);

    $overlib_content_resources = generate_overlib_content($graph_array_resources, $device['hostname'] . ' - ' . $title_resources);
    $overlib_link_resources = \LibreNMS\Util\Url::overlibLink($link_resources, $title_resources, $overlib_content_resources);

    // graph events
    $title_events = 'Change Events';
    $graph_array_events = $graph_array;
    $graph_array_events['type'] = 'application_puppet-agent_events';

    $link_array_events = $graph_array_events;
    $link_array_events['page'] = 'graphs';
    unset($link_array_events['height'], $link_array_events['width'], $link_array_events['legend']);
    $link_events = \LibreNMS\Util\Url::generate($link_array_events);

    $overlib_content_events = generate_overlib_content($graph_array_events, $device['hostname'] . ' - ' . $title_events);
    $overlib_link_events = \LibreNMS\Util\Url::overlibLink($link_events, $title_events, $overlib_content_events);

    $row_class = 'tw:grid tw:items-center tw:gap-2.5 tw:px-2 tw:py-2 tw:hover:bg-neutral-100 tw:dark:hover:bg-dark-gray-300 tw:grid-cols-[1fr_2fr]';
    $success_class = $metrics['events_success'] ? 'tw:text-blue-600' : '';
    $failure_class = $metrics['events_failure'] ? 'tw:text-red-500' : '';

    echo '<div class="overview-panel tw:mb-5">
        <div class="tw:px-4 tw:py-2.5 tw:bg-neutral-100 tw:border-b tw:border-gray-300 tw:text-neutral-700 tw:dark:bg-dark-gray-200 tw:dark:border-zinc-800 tw:dark:text-dark-white-200">
            <a href="device/device=' . $device['device_id'] . '/tab=apps/app=puppet-agent/">
                <i class="fa fa-cogs fa-lg icon-theme" aria-hidden="true"></i>
                <strong>Puppet Agent</strong>
            </a>
        </div>
        <div class="tw:flex tw:flex-col tw:bg-white tw:divide-y tw:divide-gray-300 tw:dark:bg-dark-gray-400 tw:dark:divide-zinc-800">
            <div class="' . $row_class . '">
                <div class="tw:font-medium">Summary</div>
                <div class="tw:min-w-0">
                    <table class="tw:w-full tw:table-fixed">
                        <tr>
                            <td class="tw:px-2"><span>' . $overlib_link_last_run . ': ' . $metrics['last_run_last_run'] . 'min</span></td>
                            <td class="tw:px-2"><span>' . $overlib_link_runtime . ': ' . $metrics['time_total'] . 's</span></td>
                            <td class="tw:px-2"><span>' . $overlib_link_resources . ': ' . $metrics['resources_total'] . '</span></td>
                        </tr>
                    </table>
                </div>
            </div>
            <div class="' . $row_class . '">
                <div>' . $overlib_link_events . '</div>
                <div class="tw:min-w-0">
                    <table class="tw:w-full tw:table-fixed">
                        <tr>
                            <td class="tw:px-2"><span class="' . $success_class . '">Success: ' . $metrics['events_success'] . '</span></td>
                            <td class="tw:px-2"><span class="' . $failure_class . '">Failure: ' . $metrics['events_failure'] . '</span></td>
                            <td class="tw:px-2"><span>Total: ' . $metrics['events_total'] . '</span></td>
                        </tr>
                    </table>
                </div>
            </div>
        </div>
    </div>';
}
