<?php

namespace App\Graphs\Application;

class DockerCpuUsageGraph extends DockerContainerGraph
{
    protected function field(): string
    {
        return 'cpu_usage';
    }

    protected function legendTitle(): string
    {
        return __('graph.application.docker.cpu_percent');
    }
}
