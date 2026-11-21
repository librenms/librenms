<?php

namespace LibreNMS\Applications;

use App\Models\Application;

class Poudriere extends ApplicationDefinition
{
    public const string TYPE = 'poudriere';
    protected const ?string DOCS = 'Poudriere';

    protected const ?array OVERVIEW = [
        'status',
        'phase',
        'time',
        'log_size',
        'package_size',
        'cpu_perc',
        'mem_perc',
        'time_comparison',
        'user_time',
        'system_time',
        'rss',
        'threads',
        'major_faults',
        'minor_faults',
        'swaps',
        'size_comparison',
        'stack_size',
        'data_size',
        'text_size',
        'read_blocks',
        'copy_on_write_faults',
        'context_switches_comparison',
        'voluntary_context_switches',
        'involuntary_context_switches',
    ];

    protected const array GRAPHS = [
        'status' => 'General Status',
        'phase' => 'Build Phase',
        'time' => 'Build Time',
        'log_size' => 'Log Size',
        'package_size' => 'Package Size',
        'cpu_perc' => 'CPU%',
        'mem_perc' => 'Memory%',
        'time_comparison' => 'Time Comparison(CPU, User, System)',
        'cpu_time' => 'Time, CPU',
        'user_time' => 'Time, User',
        'system_time' => 'Time, System',
        'rss' => 'RSS',
        'threads' => 'Threads',
        'major_faults' => 'Faults, Major',
        'minor_faults' => 'Faults, Minor',
        'swaps' => 'Swaps',
        'size_comparison' => 'Size, Comparison(Stack, Data, Text)',
        'stack_size' => 'Size, Stack',
        'data_size' => 'Size, Data',
        'text_size' => 'Size, Text',
        'read_blocks' => 'Read Blocks',
        'copy_on_write_faults' => 'COW Faults',
        'context_switches_comparison' => 'Context Switches Comparison(Voluntary, Involuntary)',
        'voluntary_context_switches' => 'Context Switches, Voluntary',
        'involuntary_context_switches' => 'Context Switches, Involuntary',
    ];

    protected function buildDevicePage(ApplicationPage $page, Application $application): void
    {
        $data = $application->data ?? [];
        $sets = $data['sets'] ?? [];
        $set = $page->option('poudriere_set');
        $details = $page->option('poudriere_page') === 'details';
        $page->addFilter('Poudriere', 'poudriere_page', ['' => 'Totals', 'details' => 'Details'], $details ? 'details' : ($set === null ? '' : false));
        $page->addFilter('Sets', 'poudriere_set', array_combine($sets, $sets), $set);

        if (! $details) {
            $page->addGraphs(self::GRAPHS, ['poudriere_set' => $set]);

            return;
        }

        $headers = ['Set', 'Ports', 'Jail', 'Build', 'Status', 'Queue', 'Built', 'Fail', 'Skip', 'Ignore', 'Fetch', 'Remain', 'Time', 'Logs'];
        if (isset($data['status'])) {
            $page->addTable('Status', $headers, $this->statusRows($data['status'], count($headers)));
        }
        if (isset($data['build_info'])) {
            $page->addTable('Build Info', ['Set', 'Build', 'ID', 'Total', 'Origin', 'Pkg Name', 'Phase', 'Time', 'TMP FS', 'CPU%', 'MEM%'], $this->buildRows($data['build_info']));
        }
        if (isset($data['history'])) {
            $page->addTable('History', $headers, $this->statusRows($data['history'], count($headers)));
        }
    }

    /** @return list<list<string>> */
    private function statusRows(string $output, int $columns): array
    {
        $rows = [];
        foreach (array_slice(explode("\n", $output), 1) as $line) {
            $row = preg_split('/\s+/', ltrim($line), $columns);
            if (count($row) === $columns) {
                $rows[] = $row;
            }
        }

        return $rows;
    }

    /** @return list<list<string>> */
    private function buildRows(string $output): array
    {
        $rows = [];
        $set = '';
        $build = '';
        foreach (explode("\n", $output) as $line) {
            $line = ltrim($line);
            if (preg_match('/^\[([^\]]+)\]\s+\[([^\]]+)\]\s+\[/', $line, $matches)) {
                [, $set, $build] = $matches;
            } elseif (str_starts_with($line, '[')) {
                $parts = preg_split('/[^\w\-,.:\/@%]+/', $line, 14, PREG_SPLIT_NO_EMPTY);
                if (count($parts) >= 8) {
                    $row = array_slice($parts, 0, 9);
                    if (count($row) === 8) {
                        array_splice($row, 6, 0, '-');
                    }
                    $rows[] = [$set, $build, ...$row];
                }
            }
        }

        return $rows;
    }
}
