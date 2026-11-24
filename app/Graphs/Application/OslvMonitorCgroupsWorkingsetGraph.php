<?php

namespace App\Graphs\Application;

class OslvMonitorCgroupsWorkingsetGraph extends OslvMonitorGraph
{
    protected function stats(): array
    {
        return [
            'workingset_refault_anon' => 'refault_anon',
            'workingset_refault_file' => 'refault_file',
            'workingset_activate_anon' => 'activate_anon',
            'workingset_activate_file' => 'activate_file',
            'workingset_restore_anon' => 'restore_anon',
            'workingset_restore_file' => 'restore_file',
            'workingset_nodereclaim' => 'nodereclaim',
        ];
    }

    protected function legendTitle(): string
    {
        return __('graph.units.per_second');
    }
}
