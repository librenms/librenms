<?php

namespace App\Graphs\Application;

class SyslogNgDroppedGraph extends SyslogNgGraph
{
    protected const string STAT = 'dropped';
    protected const ?string SOURCE_LABEL = 'graph.application.syslog_ng.dropped';
}
