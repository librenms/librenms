<?php

$packages = Rrd::getRrdApplicationArrays($device, $app['app_id'], 'cape', 'pkg___-___');

foreach ($packages as $index => $value) {
    $packages[$index] = preg_replace('/^pkg___-___-/', '', $value);
}

$vars_to_check = [
    'stddev',
    'bytimeslot',
    'bypkg',
    'statsavg',
    'runstats',
];
foreach ($vars_to_check as $index => $value) {
    if (isset($vars[$value])) {
        if ($vars[$value] != 'on' and $vars[$value] != 'off') {
            $vars[$value] = 'off';
        }
    } else {
        $vars[$value] = 'off';
    }
}

if (sizeof($packages) > 0) {
    print_optionbar_start();

    if (isset($vars['package'])) {
        echo generate_link('General', $link_array, ['app' => 'cape', 'stddev' => $vars['stddev'], 'bypkg' => $vars['bypkg'], 'bytimeslot' => $vars['bytimeslot'], 'statsavg' => $vars['statsavg'], 'runstats' => $vars['runstats']]);
    } else {
        $label = '<span class="pagemenu-selected">General</span>';
        echo generate_link($label, $link_array, ['app' => 'cape', 'stddev' => $vars['stddev'], 'bypkg' => $vars['bypkg'], 'bytimeslot' => $vars['bytimeslot'], 'statsavg' => $vars['statsavg'], 'runstats' => $vars['runstats']]);
    }

    echo ' | <b>Packages:</b> ';
    $packages_int = 0;
    while (isset($packages[$packages_int])) {
        $package = $packages[$packages_int];
        $label = $package;

        if ($vars['package'] == $package) {
            $label = '<span class="pagemenu-selected">' . $package . '</span>';
        }

        $packages_int++;

        $append = '';
        if (isset($packages[$packages_int])) {
            $append = ', ';
        }

        echo generate_link($label, $link_array, ['app' => 'cape', 'stddev' => $vars['stddev'], 'bypkg' => $vars['bypkg'], 'bytimeslot' => $vars['bytimeslot'], 'package' => $package, 'statsavg' => $vars['statsavg'], 'runstats' => $vars['runstats']]) . $append;
    }

    echo "<br>\n";

    echo '<b>Run Stats Averages:</b> ';
    if ($vars['statsavg'] == 'on') {
        $label = '<span class="pagemenu-selected">On</span>';
        echo generate_link($label, $link_array, ['app' => 'cape', 'bytimeslot' => $vars['bytimeslot'], 'bypkg' => $vars['bypkg'], 'stddev' => $vars['stddev'], 'package' => $vars['package'], 'statsavg' => 'on', 'runstats' => $vars['runstats']]) . ', ' .
        generate_link('Off', $link_array, ['app' => 'cape', 'bytimeslot' => $vars['bytimeslot'], 'bypkg' => $vars['bypkg'], 'stddev' => $vars['stddev'], 'package' => $vars['package'], 'statsavg' => 'off', 'runstats' => $vars['runstats']]);
    } else {
        $label = '<span class="pagemenu-selected">Off</span>';
        echo generate_link('On', $link_array, ['app' => 'cape', 'bytimeslot' => $vars['bytimeslot'], 'bypkg' => $vars['bypkg'], 'stddev' => $vars['stddev'], 'package' => $vars['package'], 'statsavg' => 'on', 'runstats' => $vars['runstats']]) . ', ' .
        generate_link($label, $link_array, ['app' => 'cape', 'bytimeslot' => $vars['bytimeslot'], 'bypkg' => $vars['bypkg'], 'stddev' => $vars['stddev'], 'package' => $vars['package'], 'statsavg' => 'off', 'runstats' => $vars['runstats']]);
    }

    echo '  |  ';

    echo '<b>By Time Slot:</b> ';
    if ($vars['bytimeslot'] == 'on') {
        $label = '<span class="pagemenu-selected">On</span>';
        echo generate_link($label, $link_array, ['app' => 'cape', 'bytimeslot' => 'on', 'bypkg' => $vars['bypkg'], 'stddev' => $vars['stddev'], 'package' => $vars['package'], 'statsavg' => $vars['statsavg'], 'runstats' => $vars['runstats']]) . ', ' .
        generate_link('Off', $link_array, ['app' => 'cape', 'bytimeslot' => 'off', 'bypkg' => $vars['bypkg'], 'stddev' => $vars['stddev'], 'package' => $vars['package'], 'statsavg' => $vars['statsavg'], 'runstats' => $vars['runstats']]);
    } else {
        $label = '<span class="pagemenu-selected">Off</span>';
        echo generate_link('On', $link_array, ['app' => 'cape', 'bytimeslot' => 'on', 'bypkg' => $vars['bypkg'], 'stddev' => $vars['stddev'], 'package' => $vars['package'], 'statsavg' => $vars['statsavg'], 'runstats' => $vars['runstats']]) . ', ' .
        generate_link($label, $link_array, ['app' => 'cape', 'bytimeslot' => 'off', 'bypkg' => $vars['bypkg'], 'stddev' => $vars['stddev'], 'package' => $vars['package'], 'statsavg' => $vars['statsavg'], 'runstats' => $vars['runstats']]);
    }

    if (! isset($vars['package'])) {
        echo '  |  ';
        echo '<b>By Package:</b> ';
        if ($vars['bypkg'] == 'on') {
            $label = '<span class="pagemenu-selected">On</span>';
            echo generate_link($label, $link_array, ['app' => 'cape', 'bypkg' => 'on', 'bytimeslot' => $vars['bytimeslot'], 'stddev' => $vars['stddev'], 'package' => $vars['package'], 'statsavg' => $vars['statsavg'], 'runstats' => $vars['runstats']]) . ', ' .
            generate_link('Off', $link_array, ['app' => 'cape', 'bypkg' => 'off', 'bytimeslot' => $vars['bytimeslot'], 'stddev' => $vars['stddev'], 'package' => $vars['package'], 'statsavg' => $vars['statsavg'], 'runstats' => $vars['runstats']]);
        } else {
            $label = '<span class="pagemenu-selected">Off</span>';
            echo generate_link('On', $link_array, ['app' => 'cape', 'bypkg' => 'on', 'bytimeslot' => $vars['bytimeslot'], 'stddev' => $vars['stddev'], 'package' => $vars['package'], 'statsavg' => $vars['statsavg'], 'runstats' => $vars['runstats']]) . ', ' .
            generate_link($label, $link_array, ['app' => 'cape', 'bypkg' => 'off', 'bytimeslot' => $vars['bytimeslot'], 'stddev' => $vars['stddev'], 'package' => $vars['package'], 'statsavg' => $vars['statsavg'], 'runstats' => $vars['runstats']]);
        }
    }

    echo '  |  ';

    echo '<b>Standard Deviation:</b> ';
    if ($vars['stddev'] == 'on') {
        $label = '<span class="pagemenu-selected">On</span>';
        echo generate_link($label, $link_array, ['app' => 'cape', 'stddev' => 'on', 'bypkg' => $vars['bypkg'], 'bytimeslot' => $vars['bytimeslot'], 'package' => $vars['package'], 'statsavg' => $vars['statsavg'], 'runstats' => $vars['runstats']]) . ', ' .
        generate_link('Off', $link_array, ['app' => 'cape', 'stddev' => 'off', 'bypkg' => $vars['bypkg'], 'bytimeslot' => $vars['bytimeslot'], 'package' => $vars['package'], 'statsavg' => $vars['statsavg'], 'runstats' => $vars['runstats']]);
    } else {
        $label = '<span class="pagemenu-selected">Off</span>';
        echo generate_link('On', $link_array, ['app' => 'cape', 'stddev' => 'on', 'bypkg' => $vars['bypkg'], 'bytimeslot' => $vars['bytimeslot'], 'package' => $vars['package'], 'statsavg' => $vars['statsavg'], 'runstats' => $vars['runstats']]) . ', ' .
        generate_link($label, $link_array, ['app' => 'cape', 'stddev' => 'off', 'bypkg' => $vars['bypkg'], 'bytimeslot' => $vars['bytimeslot'], 'package' => $vars['package'], 'statsavg' => $vars['statsavg'], 'runstats' => $vars['runstats']]);
    }

    if (! isset($vars['package'])) {
        echo '  |  ';
        echo '<b>Run Statuses Averages:</b> ';
        if ($vars['runstats'] == 'on') {
            $label = '<span class="pagemenu-selected">On</span>';
            echo generate_link($label, $link_array, ['app' => 'cape', 'bypkg' => $vars['bypkg'], 'bytimeslot' => $vars['bytimeslot'], 'stddev' => $vars['stddev'], 'package' => $vars['package'], 'statsavg' => $vars['statsavg'], 'runstats' => 'on']) . ', ' .
            generate_link('Off', $link_array, ['app' => 'cape', 'bypkg' => $vars['bypkg'], 'bytimeslot' => $vars['bytimeslot'], 'stddev' => $vars['stddev'], 'package' => $vars['package'], 'statsavg' => $vars['statsavg'], 'runstats' => 'off']);
        } else {
            $label = '<span class="pagemenu-selected">Off</span>';
            echo generate_link('On', $link_array, ['app' => 'cape', 'bypkg' => $vars['bypkg'], 'bytimeslot' => $vars['bytimeslot'], 'stddev' => $vars['stddev'], 'package' => $vars['package'], 'statsavg' => $vars['statsavg'], 'runstats' => 'on']) . ', ' .
            generate_link($label, $link_array, ['app' => 'cape', 'bypkg' => $vars['bypkg'], 'bytimeslot' => $vars['bytimeslot'], 'stddev' => $vars['stddev'], 'package' => $vars['package'], 'statsavg' => $vars['statsavg'], 'runstats' => 'off']);
        }
    }

    print_optionbar_end();
}

if (isset($vars['package'])) {
    $graphs = [
        'cape_pkg_tasks' => 'Package Tasks',
        'cape_run_stats' => 'Run Stats',
        'cape_malscore_stats' => 'Malscore Averages',
    ];
    if ($vars['bytimeslot'] == 'on') {
        $graphs['cape_malscore'] = 'Malscore Stats During Time Slot';
        $graphs['cape_anti_issues'] = 'Anti Issues Per Run Stats During Time Slot';
        $graphs['cape_api_calls'] = 'API Calls Per Run Stats During Time Slot';
        $graphs['cape_crash_issues'] = 'Crash Issues Per Run Stats During Time Slot';
        $graphs['cape_domains'] = 'Domains Per Run Stats During Time Slot';
        $graphs['cape_dropped_files'] = 'Dropped Files Per Run Stats During Time Slot';
        $graphs['cape_files_written'] = 'Files Written Per Run Stats During Time Slot';
        $graphs['cape_registry_keys_modified'] = 'Registry Keys Modified Per Run Stats During Time Slot';
        $graphs['cape_running_processes'] = 'Running Processes Per Run Stats During Time Slot';
        $graphs['cape_signatures_alert'] = 'Signatures Alert Per Run Stats During Time Slot';
        $graphs['cape_signatures_total'] = 'Signatures Total Per Run Stats During Time Slot';
    }
    if ($vars['statsavg'] == 'on') {
        $graphs['cape_anti_issues_avg'] = 'Anti Issues Per Run Average';
        $graphs['cape_api_calls_avg'] = 'API Calls Per Run Stats Average';
        $graphs['cape_crash_issues_avg'] = 'Crash Issues Per Run Average';
        $graphs['cape_domains_avg'] = 'Domains Per Run Stats Average';
        $graphs['cape_dropped_files_avg'] = 'Dropped Files Per Run Average';
        $graphs['cape_files_written_avg'] = 'Files Written Per Run Average';
        $graphs['cape_registry_keys_modified_avg'] = 'Registry Keys Modified Per Run Average';
        $graphs['cape_running_processes_avg'] = 'Running Processes Per Run Average';
        $graphs['cape_signatures_alert_avg'] = 'Signatures Alert Per Run Average';
        $graphs['cape_signatures_total_avg'] = 'Signatures Total Per Run Average';
    }
} else {
    if (sizeof($packages) > 0) {
        $graphs = [
            'cape_status' => 'Run Statuses',
            'cape_pending' => 'Pending',
            'cape_lines' => 'Log Lines',
            'cape_run_stats' => 'Run Stats',
            'cape_malscore_stats' => 'Malscore Averages',
            'cape_pkg_tasks_all' => 'Package Tasks',
        ];

        if ($vars['runstats'] == 'on') {
            $graphs['cape_banned'] = 'Banned Run Statuses Averages';
            $graphs['cape_running'] = 'Running Run Statuses Averages';
            $graphs['cape_completed'] = 'Completed Run Statuses Averages';
            $graphs['cape_distributed'] = 'Distributed Run Statuses Averages';
            $graphs['cape_reported'] = 'Reported Run Statuses Averages';
            $graphs['cape_recovered'] = 'Recovered Run Statuses Averages';
            $graphs['cape_failed_analysis'] = 'Failed Analysis Run Statuses Averages';
            $graphs['cape_failed_processing'] = 'Failed Processing Run Statuses Averages';
            $graphs['cape_failed_reporting'] = 'Failed Reporting  Run Statuses Averages';
        }

        if ($vars['bypkg'] == 'on') {
            $graphs['cape_anti_issues_pkg'] = 'Anti Issues Per Run Stats During Time Slot By Package';
            $graphs['cape_api_calls_pkg'] = 'API Calls Per Run Stats During Time Slot By Package';
            $graphs['cape_crash_issues_pkg'] = 'Crash Issues Per Run Stats During Time Slot By Package';
            $graphs['cape_domains_pkg'] = 'Domains Per Run Stats During Time Slot By Package';
            $graphs['cape_signatures_total_pkg'] = 'Signatures Total Per Run Stats During Time Slot By Package';
            $graphs['cape_signatures_alert_pkg'] = 'Signatures Alert Per Run Stats During Time Slot By Package';
            $graphs['cape_running_processes_pkg'] = 'Running Processes Per Run Stats During Time Slot By Package';
            $graphs['cape_registry_keys_modified_pkg'] = 'Registry Keys Modified Per Run Stats During Time Slot By Package';
            $graphs['cape_files_written_pkg'] = 'Files Written Per Run Stats During Time Slot By Package';
        }

        if ($vars['statsavg'] == 'on') {
            $graphs['cape_anti_issues_avg'] = 'Anti Issues Per Run Average';
            $graphs['cape_api_calls_avg'] = 'API Calls Per Run Stats Average';
            $graphs['cape_crash_issues_avg'] = 'Crash Issues Per Run Average';
            $graphs['cape_domains_avg'] = 'Domains Per Run Stats Average';
            $graphs['cape_dropped_files_avg'] = 'Dropped Files Per Run Average';
            $graphs['cape_files_written_avg'] = 'Files Written Per Run Average';
            $graphs['cape_registry_keys_modified_avg'] = 'Registry Keys Modified Per Run Average';
            $graphs['cape_running_processes_avg'] = 'Running Processes Per Run Average';
            $graphs['cape_signatures_alert_avg'] = 'Signatures Alert Per Run Average';
            $graphs['cape_signatures_total_avg'] = 'Signatures Total Per Run Average';
        }

        if ($vars['bytimeslot'] == 'on') {
            $graphs['cape_malscore'] = 'Malscore Stats During Time Slot';
            $graphs['cape_anti_issues'] = 'Anti Issues Per Run Stats During Time Slot';
            $graphs['cape_api_calls'] = 'API Calls Per Run Stats During Time Slot';
            $graphs['cape_crash_issues'] = 'Crash Issues Per Run Stats During Time Slot';
            $graphs['cape_domains'] = 'Domains Per Run Stats During Time Slot';
            $graphs['cape_dropped_files'] = 'Dropped Files Per Run Stats During Time Slot';
            $graphs['cape_files_written'] = 'Files Written Per Run Stats During Time Slot';
            $graphs['cape_registry_keys_modified'] = 'Registry Keys Modified Per Run Stats During Time Slot';
            $graphs['cape_running_processes'] = 'Running Processes Per Run Stats During Time Slot';
            $graphs['cape_signatures_alert'] = 'Signatures Alert Per Run Stats During Time Slot';
            $graphs['cape_signatures_total'] = 'Signatures Total Per Run Stats During Time Slot';
        }
    } else {
        $graphs = [
            'cape_status' => 'Run Statuses',
            'cape_lines' => 'Log Lines',
            'cape_pending' => 'Pending',
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
