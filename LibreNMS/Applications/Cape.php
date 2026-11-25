<?php

namespace LibreNMS\Applications;

use App\Models\Application;

class Cape extends ApplicationDefinition
{
    public const string TYPE = 'cape';
    protected const ?string DOCS = 'CAPEv2';
    protected const ?string NAME = 'CAPEv2';

    protected const ?array OVERVIEW = [
        'status',
        'pending',
        'run_stats',
        'lines',
        'malscore_stats',
        'pkg_tasks_all',
    ];

    protected const array PACKAGE_GRAPHS = [
        'pkg_tasks' => 'Package Tasks',
        'run_stats' => 'Run Stats',
        'malscore_stats' => 'Malscore Averages',
    ];

    protected const array GRAPHS = [
        'status' => 'Run Statuses',
        'pending' => 'Pending',
        'lines' => 'Log Lines',
        'run_stats' => 'Run Stats',
        'malscore_stats' => 'Malscore Averages',
        'pkg_tasks_all' => 'Package Tasks',
    ];

    protected const array LEGACY_GRAPHS = [
        'status' => 'Run Statuses',
        'lines' => 'Log Lines',
        'pending' => 'Pending',
    ];

    protected const array BYTIMESLOT_GRAPHS = [
        'malscore' => 'Malscore Stats During Time Slot',
        'anti_issues' => 'Anti Issues Per Run Stats During Time Slot',
        'api_calls' => 'API Calls Per Run Stats During Time Slot',
        'crash_issues' => 'Crash Issues Per Run Stats During Time Slot',
        'domains' => 'Domains Per Run Stats During Time Slot',
        'dropped_files' => 'Dropped Files Per Run Stats During Time Slot',
        'files_written' => 'Files Written Per Run Stats During Time Slot',
        'registry_keys_modified' => 'Registry Keys Modified Per Run Stats During Time Slot',
        'running_processes' => 'Running Processes Per Run Stats During Time Slot',
        'signatures_alert' => 'Signatures Alert Per Run Stats During Time Slot',
        'signatures_total' => 'Signatures Total Per Run Stats During Time Slot',
    ];

    protected const array STATSAVG_GRAPHS = [
        'anti_issues_avg' => 'Anti Issues Per Run Average',
        'api_calls_avg' => 'API Calls Per Run Stats Average',
        'crash_issues_avg' => 'Crash Issues Per Run Average',
        'domains_avg' => 'Domains Per Run Stats Average',
        'dropped_files_avg' => 'Dropped Files Per Run Average',
        'files_written_avg' => 'Files Written Per Run Average',
        'registry_keys_modified_avg' => 'Registry Keys Modified Per Run Average',
        'running_processes_avg' => 'Running Processes Per Run Average',
        'signatures_alert_avg' => 'Signatures Alert Per Run Average',
        'signatures_total_avg' => 'Signatures Total Per Run Average',
    ];

    protected const array RUNSTATS_GRAPHS = [
        'banned' => 'Banned Run Statuses Averages',
        'running' => 'Running Run Statuses Averages',
        'completed' => 'Completed Run Statuses Averages',
        'distributed' => 'Distributed Run Statuses Averages',
        'reported' => 'Reported Run Statuses Averages',
        'recovered' => 'Recovered Run Statuses Averages',
        'failed_analysis' => 'Failed Analysis Run Statuses Averages',
        'failed_processing' => 'Failed Processing Run Statuses Averages',
        'failed_reporting' => 'Failed Reporting  Run Statuses Averages',
    ];

    protected const array BYPKG_GRAPHS = [
        'anti_issues_pkg' => 'Anti Issues Per Run Stats During Time Slot By Package',
        'api_calls_pkg' => 'API Calls Per Run Stats During Time Slot By Package',
        'crash_issues_pkg' => 'Crash Issues Per Run Stats During Time Slot By Package',
        'domains_pkg' => 'Domains Per Run Stats During Time Slot By Package',
        'signatures_total_pkg' => 'Signatures Total Per Run Stats During Time Slot By Package',
        'signatures_alert_pkg' => 'Signatures Alert Per Run Stats During Time Slot By Package',
        'running_processes_pkg' => 'Running Processes Per Run Stats During Time Slot By Package',
        'registry_keys_modified_pkg' => 'Registry Keys Modified Per Run Stats During Time Slot By Package',
        'files_written_pkg' => 'Files Written Per Run Stats During Time Slot By Package',
    ];

    protected function buildDevicePage(ApplicationPage $page, Application $application): void
    {
        $files = $this->rrdResources($application, 'pkg___-___');
        $packages = array_map(fn (string $file) => preg_replace('/^pkg___-___-/', '', $file), $files);
        $package = $page->option('package');

        $labels = [
            'statsavg' => 'Run Stats Averages',
            'bytimeslot' => 'By Time Slot',
            'bypkg' => 'By Package',
            'stddev' => 'Standard Deviation',
            'runstats' => 'More Run Stats',
        ];
        $options = [];
        foreach ($labels as $key => $label) {
            $options[$key] = $page->option($key) === 'on' ? 'on' : 'off';
        }
        if ($packages) {
            $page->addFilter('Packages', 'package', ['' => 'General'] + array_combine($packages, $packages), $package, $options);
            if ($package !== null) {
                unset($labels['bypkg'], $labels['runstats']);
            }
            foreach ($labels as $key => $label) {
                $page->addFilter($label, $key, ['on' => 'On', 'off' => 'Off'], $options[$key], ['package' => $package] + $options);
            }
        }

        if ($package !== null) {
            $graphs = self::PACKAGE_GRAPHS;
            if ($options['bytimeslot'] === 'on') {
                $graphs += self::BYTIMESLOT_GRAPHS;
            }
            if ($options['statsavg'] === 'on') {
                $graphs += self::STATSAVG_GRAPHS;
            }
        } else {
            $graphs = $packages ? self::GRAPHS : self::LEGACY_GRAPHS;
            foreach (['runstats' => self::RUNSTATS_GRAPHS, 'bypkg' => self::BYPKG_GRAPHS, 'statsavg' => self::STATSAVG_GRAPHS, 'bytimeslot' => self::BYTIMESLOT_GRAPHS] as $option => $extra) {
                if ($packages && $options[$option] === 'on') {
                    $graphs += $extra;
                }
            }
        }
        $page->addGraphs($graphs, ['package' => $package, 'stddev' => $options['stddev']]);
    }
}
