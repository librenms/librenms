<?php

namespace App\Graphs\Application;

use App\Data\Graphing\DataSeries;
use App\Data\Graphing\GraphOptions;

class PureftpdConnectionsGraph extends ApplicationGraph
{
    public function getSeries(): array
    {
        $metric = $this->metric(['stat' => 'connections']);

        return [
            'download' => new DataSeries($metric, 'download', __('graph.application.pureftpd.download')),
            'upload' => new DataSeries($metric, 'upload', __('graph.application.pureftpd.upload')),
            'idle' => new DataSeries($metric, 'idle', __('graph.application.pureftpd.idle')),
        ];
    }

    protected function options(): GraphOptions
    {
        return new GraphOptions(legendTitle: __('graph.units.connections'), palette: 'mixed');
    }
}
