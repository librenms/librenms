<?php

$no_refresh = true;

$config_groups = get_config_by_group('external');

$location_conf = [
    [
        'name'    => 'geoloc.engine',
        'descr'   => 'Geocoding Engine',
        'type'    => 'select',
        'options' => [
            ['value' => 'google', 'description' => 'Google Maps'],
            ['value' => 'openstreetmap', 'description' => 'OpenStreetMap'],
            ['value' => 'mapquest', 'description' => 'MapQuest'],
            ['value' => 'bing', 'description' => 'Bing Maps'],
        ]
    ],
    [
        'name'     => 'geoloc.api_key',
        'descr'    => 'Geocoding API Key',
        'type'     => 'text',
        'class'    => 'geoloc_api_key'
    ],
];

$oxidized_conf = array(
    array('name'               => 'oxidized.enabled',
          'descr'              => 'Enable Oxidized support',
          'type'               => 'checkbox',
    ),
    array('name'               => 'oxidized.url',
          'descr'              => 'URL to your Oxidized API',
          'type'               => 'text',
          'pattern'            => '[a-zA-Z0-9]{1,5}://.*',
          'required'           => true,
    ),
    array('name'               => 'oxidized.features.versioning',
          'descr'              => 'Enable config versioning access',
          'type'               => 'checkbox',
    ),
    array('name'               => 'oxidized.group_support',
          'descr'              => 'Enable the return of groups to Oxidized',
          'type'               => 'checkbox',
    ),
    array('name'               => 'oxidized.default_group',
          'descr'              => 'Set the default group returned',
          'type'               => 'text',
    ),
    array('name'               => 'oxidized.reload_nodes',
          'descr'              => 'Reload Oxidized nodes list, each time a device is added',
          'type'               => 'checkbox',
    ),
);

$unixagent_conf = array(
    array('name'               => 'unix-agent.port',
          'descr'              => 'Default unix-agent port',
          'type'               => 'numeric',
          'required'           => true,
    ),
    array('name'               => 'unix-agent.connection-timeout',
          'descr'              => 'Connection timeout',
          'type'               => 'numeric',
          'required'           => true,
    ),
    array('name'               => 'unix-agent.read-timeout',
          'descr'              => 'Read timeout',
          'type'               => 'numeric',
          'required'           => true,
    ),
);

$rrdtool_conf = array(
    array('name'               => 'rrdtool',
          'descr'              => 'Path to rrdtool binary',
          'type'               => 'text',
    ),
    array('name'               => 'rrdtool_tune',
          'descr'              => 'Tune all rrd port files to use max values',
          'type'               => 'checkbox',
    ),
    array('name'               => 'rrd.step',
          'descr'              => 'Change the rrd step value (default 300)',
          'type'               => 'numeric',
          'required'           => true,
    ),
    array('name'               => 'rrd.heartbeat',
          'descr'              => 'Change the rrd heartbeat value (default 600)',
          'type'               => 'numeric',
          'required'           => true,
    ),
);

$peeringdb_conf = array(
    array('name'               => 'peeringdb.enabled',
          'descr'              => 'Enable PeeringDB lookup (data is downloaded with daily.sh)',
          'type'               => 'checkbox',
    ),
);

echo '
<div class="panel-group" id="accordion">
    <form class="form-horizontal" role="form" action="" method="post">
';
echo csrf_field();

echo generate_dynamic_config_panel('Location Geocoding', $config_groups, $location_conf);
echo generate_dynamic_config_panel('Oxidized integration', $config_groups, $oxidized_conf);
echo generate_dynamic_config_panel('Unix-agent integration', $config_groups, $unixagent_conf);
echo generate_dynamic_config_panel('RRDTool Setup', $config_groups, $rrdtool_conf);
echo generate_dynamic_config_panel('PeeringDB Integration', $config_groups, $peeringdb_conf);

?>

    </form>
</div>
<script>
    $('#geoloc\\.engine').change(function () {
        var engine = this.value;
        if (engine === 'openstreetmap') {
            $('.geoloc_api_key').hide();
        } else {
            $('.geoloc_api_key').show();
        }
    }).change(); // trigger initially
</script>
