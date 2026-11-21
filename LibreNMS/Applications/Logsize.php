<?php

namespace LibreNMS\Applications;

use App\Models\Application;

class Logsize extends ApplicationDefinition
{
    public const string TYPE = 'logsize';
    protected const ?string DOCS = 'Logsize';

    protected const ?array OVERVIEW = [
        'size',
        'set_sizes',
        'max_size',
        'mean_size',
        'median_size',
        'mode_size',
        'min_size',
    ];

    protected const array FILE_GRAPHS = [
        'size' => 'Log Size',
    ];

    protected const array SET_GRAPHS = [
        'size' => 'Set Size',
        'log_sizes' => 'Log Sizes, Top 12',
        'combined_stats' => 'Combined Log Stats',
        'max_size' => 'Max Log Size',
        'mean_size' => 'Mean Log Size',
        'median_size' => 'Median Log Size',
        'mode_size' => 'Mode Log Size',
        'min_size' => 'Min Log Size',
    ];

    protected const array GRAPHS = [
        'size' => 'Total Size',
        'set_sizes' => 'Set Sizes',
        'combined_stats' => 'Combined Set Stats',
        'max_size' => 'Max Set Size',
        'mean_size' => 'Mean Set Size',
        'median_size' => 'Median Set Size',
        'mode_size' => 'Mode Set Size',
        'min_size' => 'Min Set Size',
    ];

    protected function buildDevicePage(ApplicationPage $page, Application $application): void
    {
        $sets = $application->data['sets'] ?? [];
        ksort($sets, SORT_NATURAL | SORT_FLAG_CASE);
        $set = $page->addResourceFilter('Sets', 'log_set', array_keys($sets), 'Basics');
        $file = $page->option('log_file');

        if ($set !== null && isset($sets[$set])) {
            $files = $sets[$set]['files'] ?? [];
            sort($files);
            $page->details['Files Count'] = count($files);
            $page->addResourceFilter('Files', 'log_file', $files, 'All Files', ['log_set' => $set]);
        }
        $graphs = match (true) {
            $set !== null && $file !== null => self::FILE_GRAPHS,
            $set !== null => self::SET_GRAPHS,
            default => self::GRAPHS,
        };
        $page->addGraphs($graphs, ['log_set' => $set, 'log_file' => $file]);
    }
}
