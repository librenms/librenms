<?php

$link_array = [
    'page' => 'device',
    'device' => $device['device_id'],
    'tab' => 'apps',
    'app' => 'borgbackup',
];

$mode = $app->data['mode'] ?? 'single';
$errored = $app->data['errored'] ?? [];

// option bar only needed if there are errors or multiple repos
if (strcmp($data['mode'], 'multi') == 0 || isset($errored['0'])) {
    print_optionbar_start();

    // handle total/repos bar if multi
    if (strcmp($data['mode'], 'multi') == 0) {
        echo generate_link('Totals', $link_array);
        echo ' | Repos: ';
        $repos = $app->data['repos'] ?? [];
        sort($pools);
        foreach ($pools as $index => $repo) {
            $label = $vars['borgbackup'] == $repo
                ? '<span class="pagemenu-selected">' . htmlspecialchars($repo) . '</span>'
                : htmlspecialchars($repo);

            echo generate_link($label, $link_array, ['borgbackup' => $repo]);

            if ($index < (count($repos) - 1)) {
                echo ', ';
            }
        }
    }

    // handle errors if present
    if (strcmp($data['mode'], 'multi') == 0 && isset($errored['0'])) {
        echo "\n<br>\nErrored Repos: " . htmlspecialchars(implode(', ', array_keys($errored)));
    } elseif (strcmp($data['mode'], 'single') == 0 && isset($errored['0'])) {
        echo "\n<br>\nError: " . htmlspecialchars($errored['single']);
    }
    print_optionbar_end();
}

$graphs = [
    'borgbackup_unique_csize' => 'Deduplicated Size',
    'borgbackup_total_csize' => 'Compressed Size',
    'borgbackup_total_size' => 'Original Size',
    'borgbackup_total_chunks' => 'Total Chunks',
    'borgbackup_total_unique_chunks' => 'Unique Chunks',
    'borgbackup_unique_size' => 'Unique Chunk Size',
    'borgbackup_time_since_last_modified' => 'Seconds since last repo update',
    'borgbackup_errored' => 'Errored Repos',
    'borgbackup_locked' => 'Locked',
    'borgbackup_locked_for' => 'Locked For',
];

foreach ($graphs as $key => $text) {
    $graph_type = $key;
    $graph_array['height'] = '100';
    $graph_array['width'] = '215';
    $graph_array['to'] = \LibreNMS\Config::get('time.now');
    $graph_array['id'] = $app['app_id'];
    $graph_array['type'] = 'application_' . $key;

    if (isset($vars['borgrepo'])) {
        $graph_array['borgrepo'] = $vars['borgrepo'];
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
