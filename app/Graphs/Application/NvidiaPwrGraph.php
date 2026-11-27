<?php

namespace App\Graphs\Application;

class NvidiaPwrGraph extends NvidiaGraph
{
    protected const string FIELD = 'pwr';

    protected const string LEGEND = 'graph.units.watts';
}
