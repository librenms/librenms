<?php

$app = \App\Models\Application::query()->where('device_id', $device['device_id'])->where('app_type', 'puppet-agent')->first();

// show only if Puppet Agent Application discovered
if ($app) {
    $params = [];
    $sql = 'SELECT `metric`, `value` FROM `application_metrics` WHERE `app_id` =' . $app->app_id;
    $metrics = dbFetchKeyValue($sql, $params); ?><div class='row'>
          <div class='col-md-12'>
              <div class='panel panel-default panel-condensed device-overview'>
                  <div class='panel-heading'>
                      <a href="device/device=<?php echo $device['device_id']?>/tab=apps/app=puppet-agent/">
                          <i class="fa fa-cogs fa-lg icon-theme" aria-hidden="true"></i>
                          <strong>Puppet Agent</strong>
                      </a>
                  </div>
              <div class="panel-body">
    <?php
        $graph_array = [];
    $graph_array['height'] = '100';
    $graph_array['width'] = '210';
    $graph_array['to'] = \LibreNMS\Config::get('time.now');
    $graph_array['id'] = $app->app_id;
    $graph_array['from'] = \LibreNMS\Config::get('time.day');
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

    echo '<div class="row">
      <div class="col-sm-4">Summary</div>
        <div class="col-sm-8">
          <table width=100%><tr>
            <td><span>' . $overlib_link_last_run . ': ' . $metrics['last_run_last_run'] . 'min</span></td>
            <td><span>' . $overlib_link_runtime . ': ' . $metrics['time_total'] . 's</span></td>
            <td><span>' . $overlib_link_resources . ': ' . $metrics['resources_total'] . '</span></td>
          </tr></table>
        </div>
        <div class="col-sm-4">' . $overlib_link_events . '</div>
        <div class="col-sm-8">
          <table width=100%><tr>
            <td><span ' . ($metrics['events_success'] ? 'class="blue"' : '') . '>Success: ' . $metrics['events_success'] . '</span></td>
            <td><span ' . ($metrics['events_failure'] ? 'class="red"' : '') . '>Failure: ' . $metrics['events_failure'] . '</span></td>
            <td><span>Total: ' . $metrics['events_total'] . '</span></td>
          </tr></table>
        </div>
      </div>
      </div>
    </div>
  </div>
</div>';
}
