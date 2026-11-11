<?php

$graphs = [
    'linux_pressure_cpu' => 'CPU Pressure',
    'linux_pressure_io' => 'IO Pressure',
    'linux_pressure_memory' => 'Memory Pressure',
    'linux_pressure_irq' => 'IRQ Pressure',
    'linux_pressure_stall_time' => 'Stall Time',
];

// irq pressure is only reported by 6.1+ kernels with CONFIG_IRQ_TIME_ACCOUNTING
$irq_rrd_filename = Rrd::name($device['hostname'], ['app', 'linux_pressure', $app['app_id'], 'system___irq___full']);
if (! Rrd::checkRrdExists($irq_rrd_filename)) {
    unset($graphs['linux_pressure_irq']);
}

foreach ($graphs as $key => $text) {
    $graph_type = $key;
    $graph_array['height'] = '100';
    $graph_array['width'] = '215';
    $graph_array['to'] = App\Facades\LibrenmsConfig::get('time.now');
    $graph_array['id'] = $app['app_id'];
    $graph_array['type'] = 'application_' . $key;

    echo '<div class="panel panel-default">
    <div class="panel-heading">
        <h3 class="panel-title">' . $text . '</h3>
    </div>
    <div class="panel-body">
    <div class="row">';
    include 'includes/html/print-graphrow.inc.php';
    echo '</div>';
    echo '</div>';
    echo '</div>';
}
