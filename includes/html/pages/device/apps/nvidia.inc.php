<?php

$graphs = [
    'nvidia_sm' => 'GPU Utilization',
    'nvidia_mem' => 'Memory Utilization',
    'nvidia_enc' => 'Encoder Utilization',
    'nvidia_dec' => 'Decoder Utilization',
    'nvidia_fb' => 'Frame Buffer Memory Usage',
    'nvidia_bar1' => 'Bar1 Memory Usage',
    'nvidia_rxpci' => 'PCIe RX',
    'nvidia_txpci' => 'PCIe TX',
    'nvidia_pwr' => 'Power Usage',
    'nvidia_temp' => 'Temperature',
    'nvidia_mclk' => 'Memory Clock',
    'nvidia_pclk' => 'GPU Clock',
    'nvidia_pviol' => 'Power Violation Percentage',
    'nvidia_tviol' => 'Thermal Violation Boolean',
    'nvidia_sbecc' => 'Single Bit ECC Errors',
    'nvidia_dbecc' => 'Double Bit ECC Errors',
];

foreach ($graphs as $key => $text) {
    $graph_type = $key;
    $graph_array['height'] = '100';
    $graph_array['width'] = '215';
    $graph_array['to'] = \LibreNMS\Config::get('time.now');
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
