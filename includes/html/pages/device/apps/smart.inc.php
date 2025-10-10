<?php

print_optionbar_start();

$baseLink = [
    'page' => 'device',
    'device' => $device['device_id'],
    'tab' => 'apps',
    'app' => 'smart',
];

$disks = $app->data['disks'] ?? [];

if (! empty($disks)) {
    ksort($disks);
}

$driveLinks = [];
foreach ($disks as $diskName => $diskData) {
    $label = $diskName;

    if (isset($vars['disk']) && $vars['disk'] === $diskName) {
        $label = "<span class=\"pagemenu-selected\">{$label}</span>";
    }

    $healthStatus = match ($diskData['health_pass'] ?? null) {
        1 => ' (OK)',
        0 => ' (FAIL)',
        default => '',
    };

    $driveLinks[] = generate_link($label, $baseLink, ['disk' => $diskName]) . $healthStatus;
}

echo generate_link('All Drives', $baseLink) . ' | drives: ' . implode(', ', $driveLinks);

print_optionbar_end();

if (isset($vars['disk'])) {
    $currentDisk = $disks[$vars['disk']] ?? [];

    if (! isset($app->data['legacy']) && ! empty($currentDisk)) {
        print_optionbar_start();

        $diskFields = [
            'disk' => 'Disk',
            'serial' => 'Serial',
            'vendor' => 'Vendor',
            'product' => 'Product',
            'model_family' => 'Model Family',
            'model_number' => 'Model Number',
            'device_model' => 'Device Model',
            'revision' => 'Revision',
            'fw_version' => 'FW Version',
        ];

        foreach ($diskFields as $field => $label) {
            if (isset($currentDisk[$field])) {
                echo "{$label}: {$currentDisk[$field]}<br>\n";
            }
        }

        if (isset($currentDisk['selftest_log'])) {
            echo '<pre>' . str_replace('n#', "\n#", $currentDisk['selftest_log']) . "</pre><br>\n";
        }

        print_optionbar_end();
    }

    $graphs = [
        'smart_big5' => 'Reliability / Age',
        'smart_temp' => 'Temperature',
        'smart_ssd' => 'SSD-specific',
        'smart_other' => 'Other',
        'smart_tests_status' => 'S.M.A.R.T self-tests results',
        'smart_tests_ran' => 'S.M.A.R.T self-tests run count',
        'smart_runtime' => 'Power On Hours',
    ];

    if (($currentDisk['is_ssd'] ?? 0) !== 1) {
        unset($graphs['smart_ssd']);
    }
} else {
    $smartAttributes = [
        'id5' => ['smart_id5', 'ID# 5, Reallocated Sectors Count'],
        'id9' => ['smart_id9', 'ID# 9, Power On Hours'],
        'id10' => ['smart_id10', 'ID# 10, Spin Retry Count'],
        'id173' => ['smart_id173', 'ID# 173, SSD Wear Leveller Worst Case Erase Count'],
        'id177' => ['smart_id177', 'ID# 177, SSD Wear Leveling Count'],
        'id183' => ['smart_id183', 'ID# 183, Detected Uncorrectable Bad Blocks'],
        'id184' => ['smart_id184', 'ID# 184, End-to-End error / IOEDC'],
        'id187' => ['smart_id187', 'ID# 187, Reported Uncorrectable Errors'],
        'id188' => ['smart_id188', 'ID# 188, Command Timeout'],
        'id190' => ['smart_id190', 'ID# 190, Airflow Temperature (C)'],
        'id194' => ['smart_id194', 'ID# 194, Temperature (C)'],
        'id196' => ['smart_id196', 'ID# 196, Reallocation Event Count'],
        'id197' => ['smart_id197', 'ID# 197, Current Pending Sector Count'],
        'id198' => ['smart_id198', 'ID# 198, Uncorrectable Sector Count / Offline Uncorrectable / Off-Line Scan Uncorrectable Sector Count'],
        'id199' => ['smart_id199', 'ID# 199, UltraDMA CRC Error Count'],
        'id231' => ['smart_id231', 'ID# 231, SSD Life Left'],
        'id232' => ['smart_id232', 'ID# 232, Available Reserved Space'],
        'id233' => ['smart_id233', 'ID# 233, Media Wearout Indicator'],
    ];

    $graphs = [];
    $hasData = $app->data['has'] ?? [];

    foreach ($smartAttributes as $attribute => [$graphKey, $graphLabel]) {
        if (($hasData[$attribute] ?? 0) === 1) {
            $graphs[$graphKey] = $graphLabel;
        }
    }

    if (($hasData['id190'] ?? 0) === 1 || ($hasData['id194'] ?? 0) === 1) {
        $graphs = ['smart_maxtemp' => 'Max Temp(C), Airflow Temperature or Device'] + $graphs;
    }
}

foreach ($graphs as $graphKey => $graphTitle) {
    $graph_array = [
        'height' => '100',
        'width' => '215',
        'to' => \App\Facades\LibrenmsConfig::get('time.now'),
        'id' => $app['app_id'],
        'type' => "application_{$graphKey}",
        'scale_min' => '0',
    ];

    if (isset($vars['disk'])) {
        $graph_array['disk'] = $vars['disk'];
    }

    echo <<<HTML
    <div class="panel panel-default">
        <div class="panel-heading">
            <h3 class="panel-title">{$graphTitle}</h3>
        </div>
        <div class="panel-body">
            <div class="row">
    HTML;

    include 'includes/html/print-graphrow.inc.php';

    echo <<<'HTML'
            </div>
        </div>
    </div>
    HTML;
}
