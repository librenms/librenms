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

echo '
<div class="panel-group" id="accordion">
    <form class="form-horizontal" role="form" action="" method="post">
';

echo generate_dynamic_config_panel('Graph settings', $config_groups, $graph_conf);
echo generate_dynamic_config_panel('Search settings', $config_groups, $search_conf);

echo '
    </form>
</div>
';
