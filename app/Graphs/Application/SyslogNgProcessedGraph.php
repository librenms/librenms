<?php

namespace App\Graphs\Application;

class SyslogNgProcessedGraph extends SyslogNgGraph
{
    protected const string STAT = 'processed';
    protected const ?string SOURCE_LABEL = 'graph.application.syslog_ng.processed';
}
