<?php

namespace App\Graphs\Application;

use App\Data\Graphing\DataSeries;
use App\Data\Graphing\GraphOptions;

class ZfsL2RwBytesGraph extends ApplicationGraph
{
    public function getSeries(): array
    {
        $metric = $this->metric(['stat' => '_____group2']);

        return [
            'l2_write_bytes' => new DataSeries($metric, 'l2_write_bytes', __('graph.application.zfs.l2_write')),
            'l2_read_bytes' => new DataSeries($metric, 'l2_read_bytes', __('graph.application.zfs.l2_read')),
        ];
    }

    protected function options(): GraphOptions
    {
        return new GraphOptions(legendTitle: __('graph.units.bytes'), palette: 'psychedelic');
    }
}
