<?php

$name = 'syslog-ng';

$link_array = [
    'page' => 'device',
    'device' => $device['device_id'],
    'tab' => 'apps',
    'app' => 'syslog-ng',
];

if (isset($vars['syslog_ng_source'])) {
    $vars['syslog_ng_source'] = htmlspecialchars($vars['syslog_ng_source']);
}

$app_data = $app->data;
$sources = array_keys($app_data['sources'] ?? []);
sort($sources);

print_optionbar_start();

$global_label = ! isset($vars['syslog_ng_source'])
    ? '<span class="pagemenu-selected">Global</span>'
    : 'Global';
echo generate_link($global_label, $link_array);

if (! empty($sources)) {
    echo ' | Sources: ';
    foreach ($sources as $index => $source_name) {
        $source_safe = htmlspecialchars((string) $source_name);
        $label = (isset($vars['syslog_ng_source']) && $vars['syslog_ng_source'] === $source_safe)
            ? '<span class="pagemenu-selected">' . $source_safe . '</span>'
            : $source_safe;

        echo generate_link($label, $link_array, ['syslog_ng_source' => $source_safe]);

        if ($index < (count($sources) - 1)) {
            echo ', ';
        }
    }
}

print_optionbar_end();

$stat_labels = [
    'batch_size_avg' => 'Batch Size Average',
    'batch_size_max' => 'Batch Size Maximum',
    'memory_usage' => 'Memory Usage',
    'msg_size_avg' => 'Message Size Average',
    'connections' => 'Connections',
    'discarded' => 'Discarded',
    'dropped' => 'Dropped',
    'processed' => 'Processed',
    'queued' => 'Queued',
    'truncated_bytes' => 'Truncated Bytes',
    'truncated_count' => 'Truncated Count',
    'written' => 'Written',
];

$graphs = [];
if (! isset($vars['syslog_ng_source'])) {
    $graphs['syslog_ng_center'] = 'Center (Queued / Received)';
}

foreach ($stat_labels as $stat => $label) {
    if (isset($vars['syslog_ng_source'])) {
        if (isset($app_data['sources'][$vars['syslog_ng_source']][$stat]) && $app_data['sources'][$vars['syslog_ng_source']][$stat]) {
            $graphs['syslog_ng_' . $stat] = $label;
        }
    } else {
        if (isset($app_data['global'][$stat]) && $app_data['global'][$stat]) {
            $graphs['syslog_ng_' . $stat] = $label;
        }
    }
}

foreach ($graphs as $key => $text) {
    $graph_array['height'] = '100';
    $graph_array['width'] = '215';
    $graph_array['to'] = App\Facades\LibrenmsConfig::get('time.now');
    $graph_array['id'] = $app['app_id'];
    $graph_array['type'] = 'application_' . $key;

    if (isset($vars['syslog_ng_source'])) {
        $graph_array['syslog_ng_source'] = $vars['syslog_ng_source'];
    } else {
        unset($graph_array['syslog_ng_source']);
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
