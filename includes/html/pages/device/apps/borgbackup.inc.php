<?php

// Repository navigation and filtering
print_optionbar_start();

$baseLink = [
    'page'   => 'device',
    'device' => $device['device_id'],
    'tab'    => 'apps',
    'app'    => 'borgbackup',
];

$logscale = isset($vars['logscale']) && $vars['logscale'] == 1;

$repos = $app->data['repos'] ?? [];
if (! empty($repos)) {
    ksort($repos);
}

// Format helper functions for display
$format_bytes = function ($bytes) {
    if (! is_numeric($bytes)) {
        return htmlspecialchars((string) $bytes);
    }

    $units = ['B', 'KB', 'MB', 'GB', 'TB', 'PB'];
    $i = 0;
    while ($bytes >= 1024 && $i < count($units) - 1) {
        $bytes /= 1024;
        $i++;
    }

    return round($bytes, 2) . ' ' . $units[$i];
};

$format_time = function ($ts) {
    if (! is_numeric($ts)) {
        return htmlspecialchars((string) $ts);
    }

    // if timestamp appears to be milliseconds, reduce to seconds
    if ($ts > 1000000000000) {
        $ts = (int) ($ts / 1000);
    }

    return date('Y-m-d H:i:s', (int) $ts);
};

// Generate repository links for navigation
$repoLinks = [];
foreach ($repos as $repoName => $repoData) {
    $label = $repoName;

    if (isset($vars['borgrepo']) && $vars['borgrepo'] === $repoName) {
        $label = "<span class=\"pagemenu-selected\">{$label}</span>";
    }

    $status = '';
    if (($repoData['errored'] ?? 0) === 1) {
        $status = ' (ERRORED)';
    }

    $repoLinks[] = generate_link($label, $baseLink, ['borgrepo' => $repoName]) . $status;
}

echo generate_link('All Repos', $baseLink) . ' | repos: ' . implode(', ', $repoLinks);

print_optionbar_end();

// Display selected repository details
if (isset($vars['borgrepo'])) {
    $currentRepo = $repos[$vars['borgrepo']] ?? [];

    if (! empty($currentRepo)) {
        // Repository details header
        print_optionbar_start();

        $repoFields = [
            'path' => 'Repo Path',
            'last_modified' => 'Last Modified',
            'locked' => 'Locked',
            'locked_for' => 'Locked For',
            'unique_csize' => 'Deduplicated Size',
            'total_csize' => 'Compressed Size',
            'total_size' => 'Original Size',
        ];

        foreach ($repoFields as $field => $label) {
            if (! isset($currentRepo[$field])) {
                continue;
            }

            $value = $currentRepo[$field];

            // format sizes
            if (str_contains($field, 'size') || str_contains($field, 'csize') || str_contains($field, 'chunks')) {
                $out = $format_bytes($value);
            // format timestamps
            } elseif (str_contains($field, 'time') || str_contains($field, 'modified') || str_contains($field, 'last')) {
                $out = $format_time($value);
            // booleans/locked
            } elseif (in_array($field, ['locked'])) {
                $out = ($value) ? 'Yes' : 'No';
            } else {
                $out = htmlspecialchars((string) $value);
            }

            echo "{$label}: {$out}<br>\n";
        }

        print_optionbar_end();
    }

    // Graphs for selected repository
    $graphs = [
        'borgbackup_size' => 'All Sizes',
        'borgbackup_chunks' => 'All Chunks',
        'borgbackup_time_since_last_modified' => 'Seconds since last repo update',
        'borgbackup_errored' => 'Errored Repos',
        'borgbackup_locked' => 'Locked',
        'borgbackup_locked_for' => 'Locked For',
    ];
} else {
    // Graphs for all repositories (aggregate view)
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
}

// Render all graphs
foreach ($graphs as $graphKey => $graphTitle) {
    $graph_array = [
        'height' => '100',
        'width'  => '215',
        'to'     => \App\Facades\LibrenmsConfig::get('time.now'),
        'id'     => $app['app_id'],
        'type'   => "application_{$graphKey}",
    ];

    if (isset($vars['borgrepo'])) {
        $graph_array['borgrepo'] = $vars['borgrepo'];
    }

    if ($graphKey == 'borgbackup_size') {
        $graph_array['logscale'] = $logscale ? '1' : '0';
    } elseif ($graphKey == 'borgbackup_chunks') {
        $graph_array['chunks_logscale'] = $chunks_logscale ? '1' : '0';
    }

    $chunks_logscale = isset($vars['chunks_logscale']) && $vars['chunks_logscale'] == 1;

    echo <<<HTML
    <div class="panel panel-default">
        <div class="panel-heading">
            <h3 class="panel-title">{$graphTitle}</h3>
        </div>
        <div class="panel-body">
            <div class="row">
    HTML;

    if ($graphKey == 'borgbackup_size') {
        $sizeToggleLabel = $logscale ? 'Linear' : 'Log';
        $newLogscale = $logscale ? '0' : '1';
        $toggleUrl = \LibreNMS\Util\Url::generate($baseLink, array_merge($vars, ['logscale' => $newLogscale]));
        echo '<div style="margin-bottom: 10px;"><a href="' . $toggleUrl . '" class="btn btn-default btn-xs">' . $sizeToggleLabel . ' Scale</a></div>';
    } elseif ($graphKey == 'borgbackup_chunks') {
        $chunksToggleLabel = $chunks_logscale ? 'Linear' : 'Log';
        $newChunksLogscale = $chunks_logscale ? '0' : '1';
        $toggleUrl = \LibreNMS\Util\Url::generate($baseLink, array_merge($vars, ['chunks_logscale' => $newChunksLogscale]));
        echo '<div style="margin-bottom: 10px;"><a href="' . $toggleUrl . '" class="btn btn-default btn-xs">' . $chunksToggleLabel . ' Scale</a></div>';
    }

    include 'includes/html/print-graphrow.inc.php';

    echo <<<'HTML'
            </div>
        </div>
    </div>
    HTML;
}
