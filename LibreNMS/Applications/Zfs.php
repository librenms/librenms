<?php

namespace LibreNMS\Applications;

use App\Models\Application;

class Zfs extends ApplicationDefinition
{
    public const string TYPE = 'zfs';
    protected const ?string DOCS = 'ZFS';
    protected const ?string NAME = 'ZFS';

    protected const array POOL_GRAPHS = [
        'pool_space' => 'Pool Space',
        'pool_cap' => 'Pool Capacity',
        'pool_frag' => 'Pool Fragmentation',
    ];

    protected const array L2_GRAPHS = [
        'l2_size' => 'L2 size in bytes',
        'l2_rw_bytes' => 'L2 Read And Writes Bytes Per Second',
        'l2_d_to_m_ratio' => 'L2 Data To Meta Ratio',
        'l2_access_total' => 'L2 Total Hits And Misses Per Second',
        'l2_errors' => 'L2 Errors Per Second',
        'l2_sizes' => 'L2 Sizes',
        'l2_asize' => 'L2 Asize',
        'l2_bufc_d_asize' => 'L2 BufC Data Asize',
        'l2_bufc_m_asize' => 'L2 BufC Metadata Asize',
        'l2_hdr_size' => 'L2 HDR Size',
        'l2_log_blk_asize' => 'L2 Log Blk Asize',
        'l2_mfu_asize' => 'L2 MFU Asize',
        'l2_mru_asize' => 'L2 MRU Asize',
        'l2_prefetch_asize' => 'L2 Prefetch Asize',
        'l2_rb_asize' => 'L2 Rebuild Asize',
        'l2_abort_lowmem' => 'L2 Abort Lowmem Per Second',
    ];

    protected const array GRAPHS = [
        'arc_misc' => 'ARC misc',
        'arc_size' => 'ARC size in bytes',
        'arc_size_per' => 'ARC size, percent of max size',
        'arc_size_breakdown' => 'ARC size breakdown',
        'arc_efficiency' => 'ARC efficiency',
        'arc_cache_hits_by_list' => 'ARC cache hits by list',
        'arc_cache_hits_by_type' => 'ARC cache hits by type',
        'arc_cache_misses_by_type' => 'ARC cache misses by type',
        'arc_cache_hits' => 'ARC cache hits',
        'arc_cache_miss' => 'ARC cache misses',
    ];

    protected const array EXTENDED_POOL_GRAPHS = [
        'pool_errors' => 'Pool Errors',
        'pool_operations' => 'Pool Operations',
        'pool_bandwidth' => 'Pool Bandwidth',
        'pool_total_wait' => 'Pool Total Wait',
        'pool_disk_wait' => 'Pool Disk Wait',
        'pool_syncq_wait' => 'Pool SyncQ Wait',
        'pool_asyncq_wait' => 'Pool AsyncQ Wait',
        'pool_scrub_wait' => 'Pool Scrub Wait',
        'pool_trim_wait' => 'Pool Trim Wait',
        'pool_syncq_read' => 'Pool SyncQ Read',
        'pool_syncq_write' => 'Pool SyncQ Write',
        'pool_asyncq_read' => 'Pool AsyncQ Read',
        'pool_asyncq_write' => 'Pool AsyncQ Write',
        'pool_scrubq_read' => 'Pool ScrubQ Read',
        'pool_trimq_write' => 'Pool TrimQ Write',
    ];

    protected function buildDevicePage(ApplicationPage $page, Application $application): void
    {
        $data = $application->data ?? [];
        $pools = $data['pools'] ?? [];
        sort($pools);
        $pool = $page->option('pool');
        $l2 = $pool === null && $page->option('zfs_page') === 'l2';
        $page->addFilter('Cache', 'zfs_page', ['' => 'ARC', 'l2' => 'L2'], $l2 ? 'l2' : ($pool === null ? '' : false));
        $page->addFilter('Pools', 'pool', array_combine($pools, $pools), $pool);

        if ($pool === null) {
            $page->addGraphs($l2 ? self::L2_GRAPHS : self::GRAPHS);

            return;
        }

        if (isset($data['status_info'][$pool])) {
            $page->text['Pool Status'] = $data['status_info'][$pool];
        }
        $graphs = self::POOL_GRAPHS;
        if (($data['version'] ?? 2) >= 2) {
            $graphs += self::EXTENDED_POOL_GRAPHS;
        }
        $page->addGraphs($graphs, ['pool' => $pool]);
    }
}
