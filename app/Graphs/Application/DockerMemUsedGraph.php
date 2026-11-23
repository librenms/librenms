<?php

namespace App\Graphs\Application;

class DockerMemUsedGraph extends DockerContainerGraph
{
    protected function field(): string
    {
        return 'mem_used';
    }

    protected function legendTitle(): string
    {
        return __('graph.application.docker.memory_used');
    }
}
