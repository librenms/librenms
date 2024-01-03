<?php

print_optionbar_start();

$link_array = [
    'page' => 'device',
    'device' => $device['device_id'],
    'tab' => 'apps',
    'app' => 'smart',
];

$drives = [];

$app_data = $app->data;

array_multisort(array_keys($app_data['disks']), SORT_ASC, $app_data['disks']);

foreach ($app_data['disks'] as $label => $disk_data) {
    $disk = $label;

    if ($vars['disk'] == $disk) {
        $label = '<span class="pagemenu-selected">' . $label . '</span>';
    }

    if (isset($app_data['disks'][$disk]['health_pass'])) {
        if ($app_data['disks'][$disk]['health_pass'] == 1) {
            $health_status = '(OK)';
        } else {
            $health_status = '(FAIL)';
        }
    }

    array_push($drives, generate_link($label, $link_array, ['disk' => $disk]) . $health_status);
}

printf('%s | drives: %s', generate_link('All Drives', $link_array), implode(', ', $drives));

print_optionbar_end();

if (isset($vars['disk'])) {
    if (! isset($app_data['legacy'])) {
        print_optionbar_start();
        if (isset($app_data['disks'][$vars['disk']]['disk'])) {
            echo 'Disk: ' . $app_data['disks'][$vars['disk']]['disk'] . "<br>\n";
        }
        if (isset($app_data['disks'][$vars['disk']]['serial'])) {
            echo 'Serial: ' . $app_data['disks'][$vars['disk']]['serial'] . "<br>\n";
        }
        if (isset($app_data['disks'][$vars['disk']]['vendor'])) {
            echo 'Vendor: ' . $app_data['disks'][$vars['disk']]['vendor'] . "<br>\n";
        }
        if (isset($app_data['disks'][$vars['disk']]['product'])) {
            echo 'Product: ' . $app_data['disks'][$vars['disk']]['product'] . "<br>\n";
        }
        if (isset($app_data['disks'][$vars['disk']]['model_family'])) {
            echo 'Model Family: ' . $app_data['disks'][$vars['disk']]['model_family'] . "<br>\n";
        }
        if (isset($app_data['disks'][$vars['disk']]['model_number'])) {
            echo 'Model Number: ' . $app_data['disks'][$vars['disk']]['model_number'] . "<br>\n";
        }
        if (isset($app_data['disks'][$vars['disk']]['device_model'])) {
            echo 'Device Model: ' . $app_data['disks'][$vars['disk']]['device_model'] . "<br>\n";
        }
        if (isset($app_data['disks'][$vars['disk']]['revision'])) {
            echo 'Revision: ' . $app_data['disks'][$vars['disk']]['revision'] . "<br>\n";
        }
        if (isset($app_data['disks'][$vars['disk']]['fw_version'])) {
            echo 'FW Version: ' . $app_data['disks'][$vars['disk']]['fw_version'] . "<br>\n";
        }
        if (isset($app_data['disks'][$vars['disk']]['selftest_log'])) {
            echo '<pre>' . str_replace('n#', "\n#", $app_data['disks'][$vars['disk']]['selftest_log']) . "</pre><br>\n";
        }
    }
    print_optionbar_end();
    $graphs = [
        'smart_big5' => 'Reliability / Age',
        'smart_temp' => 'Temperature',
        'smart_ssd' => 'SSD-specific',
        'smart_other' => 'Other',
        'smart_tests_status' => 'S.M.A.R.T self-tests results',
        'smart_tests_ran' => 'S.M.A.R.T self-tests run count',
        'smart_runtime' => 'Power On Hours',
    ];
    if ($app_data['disks'][$vars['disk']]['is_ssd'] != 1) {
        unset($graphs['smart_ssd']);
    }
} else {
    $graphs = [];

    if ($app_data['has']['id5'] == 1) {
        $graphs['smart_id5'] = 'ID# 5, Reallocated Sectors Count';
    }

    if ($app_data['has']['id9'] == 1) {
        $graphs['smart_id9'] = 'ID# 9, Power On Hours';
    }

    if ($app_data['has']['id10'] == 1) {
        $graphs['smart_id10'] = 'ID# 10, Spin Retry Count';
    }

    if ($app_data['has']['id173'] == 1) {
        $graphs['smart_id173'] = 'ID# 173, SSD Wear Leveller Worst Case Erase Count';
    }

    if ($app_data['has']['id177'] == 1) {
        $graphs['smart_id177'] = 'ID# 177, SSD Wear Leveling Count';
    }

    if ($app_data['has']['id183'] == 1) {
        $graphs['smart_id183'] = 'ID# 183, Detected Uncorrectable Bad Blocks';
    }

    if ($app_data['has']['id184'] == 1) {
        $graphs['smart_id184'] = 'ID# 184, End-to-End error / IOEDC';
    }

    if ($app_data['has']['id187'] == 1) {
        $graphs['smart_id187'] = 'ID# 187, Reported Uncorrectable Errors';
    }

    if ($app_data['has']['id188'] == 1) {
        $graphs['smart_id188'] = 'ID# 188, Command Timeout';
    }

    if ($app_data['has']['id190'] == 1 || $app_data['has']['id194'] == 1) {
        $graphs['smart_maxtemp'] = 'Max Temp(C), Airflow Temperature or Device';
    }

    if ($app_data['has']['id190'] == 1) {
        $graphs['smart_id190'] = 'ID# 190, Airflow Temperature (C)';
    }

    if ($app_data['has']['id194'] == 1) {
        $graphs['smart_id194'] = 'ID# 194, Temperature (C)';
    }

    if ($app_data['has']['id196'] == 1) {
        $graphs['smart_id196'] = 'ID# 196, Reallocation Event Count';
    }

    if ($app_data['has']['id197'] == 1) {
        $graphs['smart_id197'] = 'ID# 197, Current Pending Sector Count';
    }

    if ($app_data['has']['id198'] == 1) {
        $graphs['smart_id198'] = 'ID# 198, Uncorrectable Sector Count / Offline Uncorrectable / Off-Line Scan Uncorrectable Sector Count';
    }

    if ($app_data['has']['id199'] == 1) {
        $graphs['smart_id199'] = 'ID# 199, UltraDMA CRC Error Count';
    }

    if ($app_data['has']['id231'] == 1) {
        $graphs['smart_id231'] = 'ID# 231, SSD Life Left';
    }

    if ($app_data['has']['id232'] == 1) {
        $graphs['smart_id232'] = 'ID# 232, Available Reservd Space';
    }

    if ($app_data['has']['id233'] == 1) {
        $graphs['smart_id233'] = 'ID# 233, Media Wearout Indicator';
    }
}

foreach ($graphs as $key => $text) {
    $graph_type = $key;
    $graph_array['height'] = '100';
    $graph_array['width'] = '215';
    $graph_array['to'] = \LibreNMS\Config::get('time.now');
    $graph_array['id'] = $app['app_id'];
    $graph_array['type'] = 'application_' . $key;
    $graph_array['scale_min'] = '0';

    if (isset($vars['disk'])) {
        $graph_array['disk'] = $vars['disk'];
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
