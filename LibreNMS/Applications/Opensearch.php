<?php

namespace LibreNMS\Applications;

use App\Models\Application;

class Opensearch extends ApplicationDefinition
{
    public const string TYPE = 'opensearch';
    protected const ?string DOCS = 'Opensearch';
    protected const ?string NAME = 'Elasticsearch\\Opensearch';

    protected const ?array OVERVIEW = [
        'status',
        'c_nodes',
        'c_data_nodes',
        'c_pending_tasks',
        'c_in_fl_fetch',
        'c_task_max_in_time',
        'c_act_shards_perc',
        'mem_all',
        'time_all',
    ];

    protected const array SECTIONS = [
        'cluster' => 'Cluster',
        'translog' => 'Translog',
        'indexing' => 'Indexing',
        'search' => 'Search',
        'refresh' => 'Refresh',
        'flush' => 'Flush',
        'qc' => 'Query Cache',
        'get' => 'Get',
        'merges' => 'Merges',
        'warmer' => 'Warmer',
        'fielddata' => 'Fielddata',
        'segments' => 'Segments',
        'rc' => 'Request Cache',
        'store' => 'Store',
        'time' => 'Time',
        'memory' => 'Memory',
    ];

    protected const array GRAPH_SETS = [
        'cluster' => [
            'c_nodes' => 'Nodes',
            'c_data_nodes' => 'Data Nodes',
            'c_all_shards' => 'Combined Shard Stats',
            'c_pending_tasks' => 'Pending Tasks',
            'c_in_fl_fetch' => 'In Flight Fetches',
            'status' => 'Status: 0=Green, 1=Yellow, 2=Red, 3=Unknown',
            'c_task_max_in_time' => 'Tasks Max Time In Milliseconds',
            'c_act_shards_perc' => 'Active Shards Percentage',
            'c_act_pri_shards' => 'Active Primary Shards',
            'c_act_shards' => 'Active Shards',
            'c_rel_shards' => 'Relocating Shards',
            'c_init_shards' => 'Initializing Shards',
            'c_delayed_shards' => 'Delayed Shards',
            'c_unass_shards' => 'Unassigned Shards',
        ],
        'translog' => [
            'ttl_ops' => 'Translog Operations',
            'ttl_size' => 'Translog Size In Bytes',
            'ttl_uncom_ops' => 'Translog Uncommitted Operations',
            'ttl_uncom_size' => 'Translog Uncommitted Size In Bytes',
            'ttl_last_mod_age' => 'Translog Earliest Last Modified Age',
        ],
        'indexing' => [
            'ti_total' => 'Index',
            'ti_time' => 'Index Time',
            'ti_failed' => 'Index Failed',
            'ti_del_total' => 'Delete',
            'ti_del_time' => 'Delete Time',
            'ti_noop_up_total' => 'NoOP Update',
            'ti_throttled_time' => 'Throttle Time',
            'ti_throttled' => 'Throttled',
        ],
        'search' => [
            'ts_q_total' => 'Queries',
            'ts_q_time' => 'Query Time',
            'ts_f_total' => 'Fetch',
            'ts_f_time' => 'Fetch Time',
            'ts_sc_total' => 'Scrolls',
            'ts_sc_time' => 'Scroll Time',
            'ts_su_total' => 'Suggests',
            'ts_su_time' => 'Suggest Time',
        ],
        'refresh' => [
            'tr_total' => 'Refreshes',
            'tr_time' => 'Refresh Time',
            'tr_ext_total' => 'External',
            'tr_ext_time' => 'External Time',
        ],
        'flush' => [
            'tf_total' => 'Flushes',
            'tf_periodic' => 'Periodic',
            'tf_time' => 'Flush Time',
        ],
        'qc' => [
            'tqc_size' => 'Query Cache Memory Size',
            'tqc_total' => 'Query Cache',
            'tqc_hit' => 'Query Cache Hits',
            'tqc_miss' => 'Query Cache Misses',
            'tqc_cache_size' => 'Query Cache Size',
            'tqc_cache_count' => 'Query Cache Count',
            'tqc_evictions' => 'Query Cache Evictions',
        ],
        'get' => [
            'tg_total' => 'Gets',
            'tg_time' => 'Get Time',
            'tg_exists_total' => 'Exists',
            'tg_exists_time' => 'Get Exists Time',
            'tg_missing_total' => 'Get Missing Total',
            'tg_missing_time' => 'Get Missing Time',
        ],
        'merges' => [
            'tm_total' => 'Merges',
            'tm_time' => 'Merges Time',
            'tm_docs' => 'Merges Docs',
            'tm_size' => 'Merges Size',
            'tm_throttled_time' => 'Merges Throttled Time',
            'tm_throttled_size' => 'Merges Throttled Size',
        ],
        'warmer' => [
            'tw_total' => 'Warmer',
            'tw_time' => 'Warmer Time',
        ],
        'fielddata' => [
            'tfd_size' => 'Fielddata Memory Size',
            'tfd_evictions' => 'Fielddata Evictions',
        ],
        'segments' => [
            'tseg_count' => 'Segments Count',
            'tseg_size' => 'Segments Memory Size',
            'tseg_terms_size' => 'Segments Terms Memory Size',
            'tseg_fields_size' => 'Segments Stored Fields Memory Size',
            'tseg_tvector_size' => 'Segments Term Vertors Memory Size',
            'tseg_norms_size' => 'Segments Norms Memory Size',
            'tseg_points_size' => 'Segments Points Memory Size',
            'tseg_docval_size' => 'Segments Doc Values Memory Size',
            'tseg_indwrt_size' => 'Segments Index Writer Memory Size',
            'tseg_vermap_size' => 'Segments Version Map Memory Size',
            'tseg_fbs_size' => 'Segment Fixed Bit Set Memory Size',
        ],
        'rc' => [
            'trc_size' => 'Request Cache Memory Size',
            'trc_evictions' => 'Request Cache Evictions',
            'trc_hits' => 'Request Cache Hits',
            'trc_misses' => 'Request Cache Misses',
        ],
        'store' => [
            'tst_size' => 'Store Size',
            'tst_res_size' => 'Store Reserved Size',
        ],
        'time' => [
            'c_task_max_in_time' => 'Tasks Max Time',
            'time_all' => 'Combined',
            'ti_time' => 'Index Time',
            'ti_del_time' => 'Delete Time',
            'ti_throttled_time' => 'Throttle Time',
            'ts_q_time' => 'Query Time',
            'ts_f_time' => 'Fetch Time',
            'ts_sc_time' => 'Scroll Time',
            'ts_su_time' => 'Suggest Time',
            'tr_time' => 'Refresh Time',
            'tr_ext_time' => 'External Time',
            'tf_time' => 'Flush Time',
            'tg_time' => 'Get Time',
            'tg_exists_time' => 'Get Exists Time',
            'tg_missing_time' => 'Get Missing Time',
            'tm_time' => 'Merges Time',
            'tm_throttled_time' => 'Merges Throttled Time',
            'tw_time' => 'Warmer Time',
        ],
        'memory' => [
            'mem_all' => 'Combined',
            'tqc_size' => 'Query Cache Size',
            'trc_size' => 'Request Cache Memory Size',
            'tfd_size' => 'Field Data Memory Size',
            'tseg_size' => 'Segments Memory Size',
            'tseg_terms_size' => 'Segments Terms Memory Size',
            'tseg_fields_size' => 'Segments Stored Fields Memory Size',
            'tseg_tvector_size' => 'Segments Term Vertors Memory Size',
            'tseg_norms_size' => 'Segments Norms Memory Size',
            'tseg_points_size' => 'Segments Points Memory Size',
            'tseg_docval_size' => 'Segments Doc Values Memory Size',
            'tseg_indwrt_size' => 'Segments Index Writer Memory Size',
            'tseg_vermap_size' => 'Segments Version Map Memory Size',
            'tseg_fbs_size' => 'Segment Fixed Bit Set Memory Size',
        ],
    ];

    protected function buildDevicePage(ApplicationPage $page, Application $application): void
    {
        $section = $page->option('set') ?? $page->option('cluster');
        if ($section === null || ! isset(self::SECTIONS[$section])) {
            $section = 'cluster';
        }

        $page->details['Cluster Name'] = $application->data['cluster'] ?? '';
        $page->addFilter('Graph Sets', 'set', self::SECTIONS, $section);
        $page->addGraphs(self::GRAPH_SETS[$section]);
    }
}
