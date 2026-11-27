<?php

namespace App\Graphs\Application;

class NvidiaMemGraph extends NvidiaGraph
{
    protected const string FIELD = 'mem';

    protected const string LEGEND = 'graph.application.nvidia.mem_use';
}
