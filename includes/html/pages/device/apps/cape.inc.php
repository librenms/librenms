<?php

$packages = Rrd::getRrdApplicationArrays($device, $app['app_id'], 'cape', 'pkg___-___');

foreach ($packages as $index => $value) {
    $packages[$index] = preg_replace('/^pkg___-___-/', '', $value);
}

if (isset($vars['stddev'])) {
    if ($vars['stddev'] != 'on' and $vars['stddev'] != 'off') {
        $vars['stddev']='off';
    }
}else{
    $vars['stddev']='off';
}

print_optionbar_start();

if (sizeof($packages) > 0) {

    if (isset($vars['package'])) {
        echo generate_link('General', $link_array, []);
    }else{
        $label = '<span class="pagemenu-selected">General</span>';
        echo generate_link($label, $link_array, []);
    }

    echo " | <b>Packages:</b> ";
    $packages_int = 0;
    while(isset($packages[$packages_int])) {
        $package = $packages[$packages_int];
##        $package=preg_filter('/^pkg\-/', '', $package);
        $label = $package;

        if ($vars['package'] == $package) {
            $label = '<span class="pagemenu-selected">' . $package . '</span>';
        }

        $packages_int++;

        $append = '';
        if (isset($packages[$packages_int])) {
            $append = ', ';
        }

        echo generate_link($label, $link_array, ['package'=>$package]) . $append;

    }

    echo "<br>\n";
}

echo "<b>Show Standard Deviation:</b> ";

if ($vars['stddev'] == 'on') {
    $label = '<span class="pagemenu-selected">On</span>';
    echo generate_link($label, $link_array, ['stddev'=>'on']) . ', ' .
    generate_link('Off', $link_array, ['stddev'=>'off']);
}else{
    $label = '<span class="pagemenu-selected">Off</span>';
    echo generate_link('On', $link_array, ['stddev'=>'on']) . ', ' .
    generate_link($label, $link_array, ['stddev'=>'off']);
}

print_optionbar_end();

if (isset($vars['package'])) {
    $graphs = [
        'cape_pkg_tasks' => 'Package Tasks',
        'cape_run_stats' => 'Run Stats',
        'cape_anti_issues' => 'Anti Issues Per Run Stats During Time Slot',
        'cape_api_calls' => 'API Calls Per Run Stats During Time Slot',
        'cape_crash_issues' => 'Crash Issues Per Run Stats During Time Slot',
        'cape_domains' => 'Domains Per Run Stats During Time Slot',
        'cape_dropped_files' => 'Dropped Files Per Run Stats During Time Slot',
        'cape_files_written' => 'Files Written Per Run Stats During Time Slot',
        'cape_registry_keys_modified' => 'Registry Keys Modified Per Run Stats During Time Slot',
        'cape_running_processes' => 'Running Processes Per Run Stats During Time Slot',
        'cape_signatures_alert' => 'Signatures Alert Per Run Stats During Time Slot',
        'cape_signatures_total' => 'Signatures Total Per Run Stats During Time Slot',
        'cape_malscore' => 'Malscore Stats During Time Slot',
    ];
}else{
    if (sizeof($packages) > 0) {
        $graphs = [
            'cape_status' => 'Run Statuses',
            'cape_lines' => 'Log Lines',
            'cape_run_stats' => 'Run Stats',
            'cape_anti_issues' => 'Anti Issues Per Run Stats During Time Slot',
            'cape_anti_issues_pkg' => 'Anti Issues Per Run Stats During Time Slot By Package',
            'cape_api_calls' => 'API Calls Per Run Stats During Time Slot',
            'cape_api_calls_pkg' => 'API Calls Per Run Stats During Time Slot By Package',
            'cape_crash_issues' => 'Crash Issues Per Run Stats During Time Slot',
            'cape_crash_issues_pkg' => 'Crash Issues Per Run Stats During Time Slot By Package',
            'cape_domains' => 'Domains Per Run Stats During Time Slot',
            'cape_domains_pkg' => 'Domains Per Run Stats During Time Slot By Package',
            'cape_dropped_files' => 'Dropped Files Per Run Stats During Time Slot',
            'cape_dropped_files_pkg' => 'Dropped Files Per Run Stats During Time Slot By Package',
            'cape_files_written' => 'Files Written Per Run Stats During Time Slot',
            'cape_files_written_pkg' => 'Files Written Per Run Stats During Time Slot By Package',
            'cape_registry_keys_modified' => 'Registry Keys Modified Per Run Stats During Time Slot',
            'cape_registry_keys_modified_pkg' => 'Registry Keys Modified Per Run Stats During Time Slot By Package',
            'cape_running_processes' => 'Running Processes Per Run Stats During Time Slot',
            'cape_running_processes_pkg' => 'Running Processes Per Run Stats During Time Slot By Package',
            'cape_signatures_alert' => 'Signatures Alert Per Run Stats During Time Slot',
            'cape_signatures_alert_pkg' => 'Signatures Alert Per Run Stats During Time Slot By Package',
            'cape_signatures_total' => 'Signatures Total Per Run Stats During Time Slot',
            'cape_signatures_total_pkg' => 'Signatures Total Per Run Stats During Time Slot By Package',
            'cape_malscore' => 'Malscore Stats During Time Slot',
        ];
    } else {
        $graphs = [
            'cape_status' => 'Run Statuses',
            'cape_lines' => 'Log Lines',
            'cape_run_stats' => 'Run Stats',
            'cape_anti_issues' => 'Anti Issues Per Run Stats During Time Slot',
            'cape_api_calls' => 'API Calls Per Run Stats During Time Slot',
            'cape_crash_issues' => 'Crash Issues Per Run Stats During Time Slot',
            'cape_domains' => 'Domains Per Run Stats During Time Slot',
            'cape_dropped_files' => 'Dropped Files Per Run Stats During Time Slot',
            'cape_files_written' => 'Files Written Per Run Stats During Time Slot',
            'cape_registry_keys_modified' => 'Registry Keys Modified Per Run Stats During Time Slot',
            'cape_running_processes' => 'Running Processes Per Run Stats During Time Slot',
            'cape_signatures_alert' => 'Signatures Alert Per Run Stats During Time Slot',
            'cape_signatures_total' => 'Signatures Total Per Run Stats During Time Slot',
            'cape_malscore' => 'Malscore Stats During Time Slot',
        ];
   }
}

foreach ($graphs as $key => $text) {
    $graph_type = $key;
    $graph_array['height'] = '100';
    $graph_array['width'] = '215';
    $graph_array['to'] = time();
    $graph_array['id'] = $app['app_id'];
    $graph_array['type'] = 'application_' . $key;

    if (isset($vars['package'])) {
        $graph_array['package'] = $vars['package'];
    }

    $graph_array['stddev'] = $vars['stddev'];

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
