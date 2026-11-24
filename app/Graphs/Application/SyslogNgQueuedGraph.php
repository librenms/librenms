<?php

namespace App\Graphs\Application;

class SyslogNgQueuedGraph extends SyslogNgGraph
{
    protected const string STAT = 'queued';
    protected const ?string SOURCE_LABEL = 'graph.application.syslog_ng.queued';
}
