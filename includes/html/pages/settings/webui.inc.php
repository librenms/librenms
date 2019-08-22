<?php

$no_refresh = true;

$config_groups = get_config_by_group('webui');

$search_conf = array(
    array('name'               => 'webui.global_search_result_limit',
          'descr'              => 'Set the max search result limit',
          'type'               => 'numeric',
          'required'           => true,
    ),
);

$graph_conf = [
    [
        'name'               => 'webui.min_graph_height',
        'descr'              => 'Set the minimum graph height',
        'type'               => 'numeric',
        'required'           => true,
    ],
    [
        'name'               => 'webui.graph_type',
        'descr'              => 'Set the graph type',
        'type'               => 'select',
        'options'            => [
            'png' => 'png',
            'svg' => 'svg',
        ],
    ],
    [
        'name'  => 'webui.graph_stacked',
        'descr' => 'Use stacked graphs',
        'type'  => 'checkbox',
    ],
    [
        'name'  => 'webui.dynamic_graphs',
        'descr' => 'Enable dynamic graphs',
        'type'  => 'checkbox',
    ]
];

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
    array('name'               => 'webui.availability_map_box_size',
          'descr'              => 'Availability box width',
          'type'               => 'numeric',
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
echo csrf_field();

echo generate_dynamic_config_panel('Graph settings', $config_groups, $graph_conf);
echo generate_dynamic_config_panel('Search settings', $config_groups, $search_conf);
echo generate_dynamic_config_panel('Availability map settings', $config_groups, $availability_map_conf);
echo generate_dynamic_config_panel('Dashboard settings', $config_groups, $dashboard_conf);

echo '
    </form>
</div>
';
