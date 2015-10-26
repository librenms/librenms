<?php

$no_refresh = true;

$config_groups = get_config_by_group('external');

$oxidized_conf = array(
    array('name'               => 'oxidized.enabled',
          'descr'              => 'Enable Oxidized support',
          'type'               => 'checkbox',
    ),
    array('name'               => 'oxidized.url',
          'descr'              => 'URL to your Oxidized API',
          'type'               => 'text',
    ),
    array('name'               => 'oxidized.features.versioning',
          'descr'              => 'Enable config versioning access',
          'type'               => 'checkbox',
    ),
);

echo '
<div class="panel-group" id="accordion">
    <form class="form-horizontal" role="form" action="" method="post">
';

echo generate_dynamic_config_panel('Oxidized integration',true,$config_groups,$oxidized_conf);

echo '
    </form>
</div>
';
