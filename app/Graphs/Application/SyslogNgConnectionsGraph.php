<?php

namespace App\Graphs\Application;

class SyslogNgConnectionsGraph extends SyslogNgGraph
{
    protected const string STAT = 'connections';
    protected const string LEGEND = 'graph.units.connections_per_second';
}
