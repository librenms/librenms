<?php

$link_array = [
    'page' => 'device',
    'device' => $device['device_id'],
    'tab' => 'apps',
    'app' => 'text_blob',
];

$blobs = $app->data['blobs'] ?? [];
$warns = $app->data['warns'] ?? [];

if (!is_array($blobs)) {
    $blobs = [];
}

if (isset($vars['blob_name'])) {
    $vars['blob_name'] = htmlspecialchars($vars['blob_name']);
}

print_optionbar_start();

$label = ! isset($vars['blob_name'])
    ? '<span class="pagemenu-selected">Basics</span>'
    : 'Basics';
echo generate_link($label, $link_array);
echo ' | Text Blobs: ';
$blob_names = array_keys($blobs);
sort($blob_names);
foreach ($blob_names as $index => $blob_name) {
    $blob_name = htmlspecialchars($blob_name);
    $label = $vars['blob_name'] == $blob_name
        ? '<span class="pagemenu-selected">' . $blob_name . '</span>'
        : $blob_name;

    echo generate_link($label, $link_array, ['blob_name' => $blob_name]) . "\n";

    if ($index < (count($blob_names) - 1)) {
        echo ', ';
    }
}

if (isset($warns) && is_array($warns) && !empty($warns)) {
    echo '<br>Config Warnings: <pre>' . str_replace("\n", "<br>\n", htmlspecialchars(implode("\n", $warns))) . '<pre>';
}

if (isset($vars['blob_name']) && isset($blobs[$vars['blob_name']]) && is_scalar($blobs[$vars['blob_name']])) {
    echo '<hr><pre>' . str_replace("\n", "<br>", htmlspecialchars($blobs[$vars['blob_name']])) . '</pre>';
}

print_optionbar_end();

$graphs = [];
if (isset($vars['blob_name'])) {
    $graphs['text_blob_size'] = 'Blob Size';
    $graphs['text_blob_exit_value'] = 'Exit Value';
    $graphs['text_blob_exit_signal'] = 'Exit Signal';
    $graphs['text_blob_coredumped'] = 'Coredumped';
} else {
    $graphs['text_blob_total_size'] = 'Total Size Of Blobs';
}

foreach ($graphs as $key => $text) {
    $graph_type = $key;
    $graph_array['height'] = '100';
    $graph_array['width'] = '215';
    $graph_array['to'] = LibreNMS\Config::get('time.now');
    $graph_array['id'] = $app['app_id'];
    $graph_array['type'] = 'application_' . $key;

    if (isset($vars['blob_name'])) {
        $graph_array['blob_name'] = $vars['blob_name'];
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
