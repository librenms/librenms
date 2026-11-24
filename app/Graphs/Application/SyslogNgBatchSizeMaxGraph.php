<?php

namespace App\Graphs\Application;

class SyslogNgBatchSizeMaxGraph extends SyslogNgGraph
{
    protected const string STAT = 'batch_size_max';
    protected const string LEGEND = 'graph.units.bytes';
}
