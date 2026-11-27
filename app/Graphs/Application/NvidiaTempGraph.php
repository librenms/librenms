<?php

namespace App\Graphs\Application;

class NvidiaTempGraph extends NvidiaGraph
{
    protected const string FIELD = 'temp';

    protected const string LEGEND = 'graph.units.celsius';
}
