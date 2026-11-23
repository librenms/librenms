<?php

namespace App\Graphs\Application;

class SyslogNgTruncatedBytesGraph extends SyslogNgGraph
{
    protected const string STAT = 'truncated_bytes';
    protected const string LEGEND = 'graph.units.bytes_per_second';
}
