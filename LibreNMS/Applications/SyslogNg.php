<?php

namespace LibreNMS\Applications;

use App\Models\Application;

class SyslogNg extends ApplicationDefinition
{
    public const string TYPE = 'syslog-ng';
    protected const ?string DOCS = 'Syslog-NG';
    protected const ?string NAME = 'Syslog-NG';
    protected const ?string GRAPH_PREFIX = 'syslog_ng';

    protected const ?array OVERVIEW = [
        'center',
        'batch_size_avg',
        'batch_size_max',
        'memory_usage',
        'msg_size_avg',
        'connections',
        'discarded',
        'dropped',
        'processed',
        'queued',
        'truncated_bytes',
        'truncated_count',
        'written',
    ];

    protected const array GRAPHS = [
        'batch_size_avg' => 'Batch Size Average',
        'batch_size_max' => 'Batch Size Maximum',
        'memory_usage' => 'Memory Usage',
        'msg_size_avg' => 'Message Size Average',
        'connections' => 'Connections',
        'discarded' => 'Discarded',
        'dropped' => 'Dropped',
        'processed' => 'Processed',
        'queued' => 'Queued',
        'truncated_bytes' => 'Truncated Bytes',
        'truncated_count' => 'Truncated Count',
        'written' => 'Written',
    ];

    protected function buildDevicePage(ApplicationPage $page, Application $application): void
    {
        $data = $application->data ?? [];
        $sources = array_keys($data['sources'] ?? []);
        sort($sources);
        $source = $page->addResourceFilter('Sources', 'syslog_ng_source', $sources, 'Global');

        if ($source === null) {
            $page->addGraphs(['center' => 'Center (Queued / Received)']);
        }
        $stats = $source === null ? ($data['global'] ?? []) : ($data['sources'][$source] ?? []);
        $page->addGraphs(array_intersect_key(self::GRAPHS, array_filter($stats)), ['syslog_ng_source' => $source]);
    }
}
