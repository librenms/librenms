<?php

namespace App\Graphs\Application;

class SyslogNgMsgSizeAvgGraph extends SyslogNgGraph
{
    protected const string STAT = 'msg_size_avg';
    protected const string LEGEND = 'graph.units.bytes';
}
