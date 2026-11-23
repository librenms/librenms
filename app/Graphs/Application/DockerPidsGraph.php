<?php

namespace App\Graphs\Application;

class DockerPidsGraph extends DockerContainerGraph
{
    protected function field(): string
    {
        return 'pids';
    }

    protected function legendTitle(): string
    {
        return __('graph.application.docker.pids_count');
    }
}
