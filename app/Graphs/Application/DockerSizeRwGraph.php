<?php

namespace App\Graphs\Application;

class DockerSizeRwGraph extends DockerContainerGraph
{
    protected function field(): string
    {
        return 'size_rw';
    }

    protected function legendTitle(): string
    {
        return __('graph.application.docker.container_size_rw');
    }
}
