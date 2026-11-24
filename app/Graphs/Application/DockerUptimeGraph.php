<?php

namespace App\Graphs\Application;

class DockerUptimeGraph extends DockerContainerGraph
{
    protected function field(): string
    {
        return 'uptime';
    }

    protected function legendTitle(): string
    {
        return __('graph.application.docker.uptime');
    }
}
