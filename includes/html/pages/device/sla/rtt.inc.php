    <div class="panel-heading">
        <h3 class="panel-title">Round Trip Time</h3>
    </div>
    <div class="panel-body">
<?php
    $graph_array = [];
    $graph_array['device'] = $device['device_id'];
    $graph_array['height'] = '100';
    $graph_array['width'] = '215';
    $graph_array['to'] = \LibreNMS\Config::get('time.now');
    $graph_array['type'] = 'device_sla';
    $graph_array['id'] = $vars['id'];
    require 'includes/html/print-graphrow.inc.php';
    ?>
    </div>
