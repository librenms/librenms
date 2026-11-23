<?php

namespace App\Graphs\Application;

class SyslogNgMemoryUsageGraph extends SyslogNgGraph
{
    protected const string STAT = 'memory_usage';
    protected const string LEGEND = 'graph.units.bytes';
}
