<?php

namespace App\Graphs\Application;

class OslvMonitorCgroupsMemMiscGraph extends OslvMonitorGraph
{
    protected function stats(): array
    {
        return [
            'anon' => 'anon',
            'file' => 'file',
            'kernel' => 'kernel',
            'kernel_stack' => 'kernel_stack',
            'pagetables' => 'pagetables',
            'sec_pagetables' => 'sec_pagetables',
            'percpu' => 'percpu',
            'vmalloc' => 'vmalloc',
            'shmem' => 'shmem',
            'file_mapped' => 'file_mapped',
            'file_dirty' => 'file_dirty',
            'file_writeback' => 'file_writeback',
            'swapcached' => 'swapcached',
            'anon_thp' => 'anon_thp',
            'file_thp' => 'file_thp',
            'shmem_thp' => 'shmem_thp',
            'inactive_anon' => 'inactive_anon',
            'active_anon' => 'active_anon',
            'slab_reclaimable' => 'slab_reclaimable',
            'slab_unreclaimable' => 'slab_unreclaimable',
            'slab' => 'slab',
        ];
    }

    protected function legendTitle(): string
    {
        return __('graph.units.bytes');
    }
}
