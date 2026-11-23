<?php

namespace App\Graphs\Application;

class OslvMonitorCgroupsPgGraph extends OslvMonitorGraph
{
    protected function stats(): array
    {
        return [
            'pgactivate' => 'pgactivate',
            'pgdeactivate' => 'pgdeactivate',
            'pglazyfree' => 'pglazyfree',
            'pglazyfreed' => 'pglazyfreed',
            'pgrefill' => 'pgrefill',
            'pgscan' => 'pgscan',
            'pgscan_direct' => 'pgscan_direct',
            'pgscan_khugepaged' => 'pgscan_khugepaged',
            'pgscan_kswapd' => 'pgscan_kswapd',
            'pgsteal' => 'pgsteal',
            'pgsteal_direct' => 'pgsteal_direct',
            'pgsteal_khugepaged' => 'pgsteal_khugepaged',
            'pgsteal_kswapd' => 'pgsteal_kswapd',
        ];
    }

    protected function legendTitle(): string
    {
        return __('graph.units.per_second');
    }
}
