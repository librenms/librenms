<?php

$no_refresh = true;

$config_groups = get_config_by_group('webui');

$search_conf = array(
    array('name'               => 'webui.global_search_result_limit',
          'descr'              => 'Set the max search result limit',
          'type'               => 'text',
    ),
);

$graph_conf = array(
    array('name'               => 'webui.min_graph_height',
          'descr'              => 'Set the minimum graph height',
          'type'               => 'text',
    ),
);

$availability_map_conf = array(
    array('name'               => 'webui.availability_map_compact',
          'descr'              => 'Availability map compact view',
          'type'               => 'checkbox',
    ),
    array('name'               => 'webui.availability_map_sort_status',
          'descr'              => 'Sort devices by status',
          'type'               => 'checkbox',
    ),
    array('name'               => 'webui.availability_map_use_device_groups',
          'descr'              => 'Use device groups filter',
          'type'               => 'checkbox',
    ),
);

$dashboard_conf = array(
    array('name'               => 'webui.default_dashboard_id',
          'descr'              => 'Set global default dashboard id',
          'type'               => 'select',
          'options'            => dbFetchRows(
              "SELECT 0 as `value`, 'no default dashboard' as `description`
               UNION ALL
               SELECT `dashboards`.`dashboard_id` as `value`,
                 CONCAT( `users`.`username`, ':', `dashboards`.`dashboard_name`,
                   CASE
                     WHEN `dashboards`.`access` = 1 THEN ' (shared, read-only)'
                     WHEN `dashboards`.`access` = 2 THEN ' (shared, read-write)'
                     ELSE ''
                   END
                 ) as `description`
               FROM `dashboards` JOIN `users` ON `users`.`user_id` = `dashboards`.`user_id`
               WHERE `dashboards`.`access` > 0;"
          ),
    ),
);

echo '
<div class="panel-group" id="accordion">
    <form class="form-horizontal" role="form" action="" method="post">
';

echo generate_dynamic_config_panel('Graph settings', $config_groups, $graph_conf);
echo generate_dynamic_config_panel('Search settings', $config_groups, $search_conf);
echo generate_dynamic_config_panel('Availability map settings', $config_groups, $availability_map_conf);
echo generate_dynamic_config_panel('Dashboard settings', $config_groups, $dashboard_conf);

echo '
    </form>
</div>
';
