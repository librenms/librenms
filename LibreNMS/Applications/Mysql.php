<?php

namespace LibreNMS\Applications;

use App\Models\Application;

class Mysql extends ApplicationDefinition
{
    public const string TYPE = 'mysql';
    protected const ?string DOCS = 'MySQL';
    protected const ?string NAME = 'MySQL';

    protected const ?array OVERVIEW = [
        'network_traffic',
        'connections',
        'command_counters',
        'select_types',
    ];

    protected const array SECTIONS = [
        'system' => 'System',
        'queries' => 'Queries',
        'innodb' => 'InnoDB',
    ];

    protected const array GRAPH_SETS = [
        'system' => [
            'connections' => 'Connections',
            'files_tables' => 'Files and Tables',
            'myisam_indexes' => 'MyISAM Indexes',
            'network_traffic' => 'Network Traffic',
            'table_locks' => 'Table Locks',
            'temporary_objects' => 'Temporary Objects',
        ],
        'queries' => [
            'command_counters' => 'Command Counters',
            'query_cache' => 'Query Cache',
            'query_cache_memory' => 'Query Cache Memory',
            'select_types' => 'Select Types',
            'slow_queries' => 'Slow Queries',
            'sorts' => 'Sorts',
        ],
        'innodb' => [
            'innodb_buffer_pool' => 'InnoDB Buffer Pool',
            'innodb_buffer_pool_activity' => 'InnoDB Buffer Pool Activity',
            'innodb_insert_buffer' => 'InnoDB Insert Buffer',
            'innodb_io' => 'InnoDB IO',
            'innodb_io_pending' => 'InnoDB IO Pending',
            'innodb_log' => 'InnoDB Log',
            'innodb_row_operations' => 'InnoDB Row Operations',
            'innodb_semaphores' => 'InnoDB semaphores',
            'innodb_transactions' => 'InnoDB Transactions',
        ],
    ];

    protected function buildDevicePage(ApplicationPage $page, Application $application): void
    {
        $section = $page->option('app_section');
        if ($section === null || ! isset(self::SECTIONS[$section])) {
            $section = array_key_first(self::SECTIONS);
        }
        $page->addFilter('MySQL', 'app_section', self::SECTIONS, $section);
        $page->addGraphs(self::GRAPH_SETS[$section]);
    }
}
