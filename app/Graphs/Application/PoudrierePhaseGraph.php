<?php

namespace App\Graphs\Application;

class PoudrierePhaseGraph extends PoudriereGraph
{
    protected const string PALETTE = 'rainbow';

    protected function stats(): array
    {
        return [
            'check-sanity' => 'check-sanity',
            'pkg-depends' => 'pkg-depends',
            'fetch-depends' => 'fetch-depends',
            'fetch checksum' => 'fetch checksum',
            'extract-depends' => 'extract-depends',
            'extract patch-depends' => 'extract patch-depends',
            'patch' => 'patch',
            'build-depends' => 'build-depends',
            'lib-depends' => 'lib-depends',
            'configure' => 'configure',
            'build' => 'build',
            'run-depends' => 'run-depends',
            'stage' => 'stage',
            'package' => 'package',
        ];
    }

    protected function legendTitle(): string
    {
        return __('graph.units.count');
    }
}
