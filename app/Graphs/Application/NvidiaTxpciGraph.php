<?php

namespace App\Graphs\Application;

class NvidiaTxpciGraph extends NvidiaGraph
{
    protected const string FIELD = 'txpci';

    protected const string LEGEND = 'graph.application.nvidia.tx_mb_per_second';
}
