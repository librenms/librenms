<?php

namespace App\Graphs\Application;

use App\Data\Graphing\DataSeries;
use App\Data\Graphing\GraphOptions;

class SyslogNgCenterGraph extends ApplicationGraph
{
    public function getSeries(): array
    {
        $metric = $this->metric(['stat' => 'center']);

        return [
            'queued' => new DataSeries($metric, 'queued', __('graph.application.syslog_ng.queued')),
            'received' => new DataSeries($metric, 'received', __('graph.application.syslog_ng.received')),
        ];
    }

    protected function options(): GraphOptions
    {
        return new GraphOptions(legendTitle: __('graph.units.messages_per_second'), palette: 'rainbow');
    }
}
