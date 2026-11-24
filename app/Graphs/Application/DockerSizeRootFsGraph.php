<?php

namespace App\Graphs\Application;

class DockerSizeRootFsGraph extends DockerContainerGraph
{
    protected function field(): string
    {
        return 'size_root_fs';
    }

    protected function legendTitle(): string
    {
        return __('graph.application.docker.container_size_root_fs');
    }
}
