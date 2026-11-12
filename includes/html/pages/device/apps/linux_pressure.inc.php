<?php

use LibreNMS\Data\Store\Rrd as RrdStore;

$link_array = [
    'page' => 'device',
    'device' => $device['device_id'],
    'tab' => 'apps',
    'app' => 'linux_pressure',
];

// Cgroup names have slashes in them, which does not work in a URL var, so the
// RRD safe form of the name is what gets passed around and the real one is just the label.
$cgroup_labels = [];
foreach ($app->data['cgroups'] ?? [] as $cgroup) {
    $cgroup_labels[RrdStore::safeName($cgroup)] = $cgroup;
}

// null means the system wide pressure
$selected_cgroup = isset($vars['cgroup'], $cgroup_labels[$vars['cgroup']]) ? $vars['cgroup'] : null;

print_optionbar_start();

$system_link = generate_link('System', $link_array);
echo $selected_cgroup === null ? '<span class="pagemenu-selected">' . $system_link . '</span>' : $system_link;

if (count($cgroup_labels) > 0) {
    $cgroup_links = [];
    foreach ($cgroup_labels as $safe_cgroup => $cgroup) {
        $cgroup_link = generate_link(htmlspecialchars($cgroup), $link_array, ['cgroup' => $safe_cgroup]);
        $cgroup_links[] = $safe_cgroup === $selected_cgroup
            ? '<span class="pagemenu-selected">' . $cgroup_link . '</span>'
            : $cgroup_link;
    }
    echo "<br>\nCgroups: " . implode(', ', $cgroup_links);
}

print_optionbar_end();

$graphs = [
    'linux_pressure_cpu' => 'CPU Pressure',
    'linux_pressure_io' => 'IO Pressure',
    'linux_pressure_memory' => 'Memory Pressure',
    'linux_pressure_irq' => 'IRQ Pressure',
    'linux_pressure_stall_time' => 'Stall Time',
];

// irq pressure is only reported by 6.1+ kernels with CONFIG_IRQ_TIME_ACCOUNTING
$rrd_prefix = $selected_cgroup === null ? 'system' : 'cgroup___' . $selected_cgroup;
$irq_rrd_filename = Rrd::name($device['hostname'], ['app', 'linux_pressure', $app['app_id'], $rrd_prefix . '___irq___full']);
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

    if ($selected_cgroup !== null) {
        $graph_array['cgroup'] = $selected_cgroup;
    }

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
