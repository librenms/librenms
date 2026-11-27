<?php

namespace App\Graphs\Application;

class NvidiaMclkGraph extends NvidiaGraph
{
    protected const string FIELD = 'mclk';

    protected const string LEGEND = 'graph.application.nvidia.ram_mhz';
}
