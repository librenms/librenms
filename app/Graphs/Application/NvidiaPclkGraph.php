<?php

namespace App\Graphs\Application;

class NvidiaPclkGraph extends NvidiaGraph
{
    protected const string FIELD = 'pclk';

    protected const string LEGEND = 'graph.application.nvidia.gpu_mhz';
}
