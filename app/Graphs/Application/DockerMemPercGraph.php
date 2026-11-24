<?php

namespace App\Graphs\Application;

class DockerMemPercGraph extends DockerContainerGraph
{
    protected function field(): string
    {
        return 'mem_perc';
    }

    protected function legendTitle(): string
    {
        return __('graph.application.docker.memory_percent');
    }
}
