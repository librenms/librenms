<?php

namespace LibreNMS\Applications;

use App\Models\Application;

class Sneck extends ApplicationDefinition
{
    public const string TYPE = 'sneck';
    protected const ?string DOCS = 'Sneck';

    protected const ?array OVERVIEW = [
        'results',
        'time',
        'runtime',
    ];

    protected const array GRAPHS = [
        'results' => 'Results',
        'time' => 'Time Difference',
    ];

    protected function buildDevicePage(ApplicationPage $page, Application $application): void
    {
        $data = $application->data ?? [];
        $checks = array_keys($data['checks'] ?? []);
        $debugs = array_keys($data['debugs'] ?? []);
        $check = $page->option('sneck_check');
        $debug = $page->option('sneck_debug');
        if ($checks || $debugs) {
            $page->addFilter('Check Info', 'sneck_check', ['' => 'Overview'] + array_combine($checks, $checks), $debug === null ? $check : false);
            $page->addFilter('Debug Info', 'sneck_debug', array_combine($debugs, $debugs), $debug);
            if (isset($data['run_time'])) {
                $page->details['Runtime'] = $data['run_time'] . ' seconds';
            }
        }

        $result = $data['debugs'][$debug ?? ''] ?? $data['checks'][$check ?? ''] ?? null;
        if ($result !== null) {
            foreach (['check' => 'Check', 'ran' => 'Ran', 'run_time' => 'Runtime', 'exit' => 'Exit', 'error' => 'Error'] as $key => $label) {
                if (isset($result[$key])) {
                    $page->details[$label] = $key === 'run_time' ? $result[$key] . ' seconds' : $result[$key];
                }
            }
            if (isset($result['output'])) {
                $page->text['Output'] = $result['output'];
            }
        } else {
            parent::buildDevicePage($page, $application);
            if (isset($data['runtime'])) {
                $page->addGraphs(['runtime' => 'Runtime']);
            }
            if ($data) {
                $page->details['Alerts'] = (string) ($data['alertString'] ?? '');
            }
        }
        if ($data) {
            $page->text['Raw JSON'] = json_encode($result ?? $data, JSON_PRETTY_PRINT | JSON_INVALID_UTF8_SUBSTITUTE);
        }
    }
}
