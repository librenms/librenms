<?php

$no_refresh = true;

$config_groups = get_config_by_group('logging');

$core_conf = array(
    array('name'             => 'logging.level',
        'descr'              => 'Set the logging level',
        'type'               => 'select',
        'options'            => array(
            array(
                'description' => 'none',
                'value' => 0,
            ),
            array(
                'description' => 'info',
                'value' => 3,
            ),
            array(
                'description' => 'error',
                'value' => 7,
            ),
            array(
                'description' => 'debug',
                'value' => 9,
            ),
        ),
    ),
);

echo '
<div class="panel-group" id="accordion">
    <form class="form-horizontal" role="form" action="" method="post">
';

echo generate_dynamic_config_panel('Core Logging', $config_groups, $core_conf);

echo '
    </form>
</div>
';
