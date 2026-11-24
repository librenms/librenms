<?php

namespace App\Graphs\Application;

class DockerMemLimitGraph extends DockerContainerGraph
{
    protected function field(): string
    {
        return 'mem_limit';
    }

    protected function legendTitle(): string
    {
        return __('graph.application.docker.memory_limit');
    }
}
