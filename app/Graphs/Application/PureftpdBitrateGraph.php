<?php

namespace App\Graphs\Application;

use App\Data\Graphing\DataSeries;
use App\Data\Graphing\GraphOptions;

class PureftpdBitrateGraph extends ApplicationGraph
{
    public function getSeries(): array
    {
        $metric = $this->metric(['stat' => 'bitrate']);

        return [
            'download' => new DataSeries($metric, 'download', __('graph.application.pureftpd.download')),
            'upload' => new DataSeries($metric, 'upload', __('graph.application.pureftpd.upload')),
        ];
    }

    protected function options(): GraphOptions
    {
        return new GraphOptions(legendTitle: __('graph.units.bits_per_second'), palette: 'mixed');
    }
}
