<?php

namespace App\Graphs\Application;

use App\Data\Graphing\DataSeries;
use App\Data\Graphing\GraphOptions;

class ChronydStratumGraph extends ApplicationGraph
{
    public function getSeries(): array
    {
        return [
            'stratum' => new DataSeries($this->metric(), 'stratum', __('graph.application.chronyd.stratum')),
        ];
    }

    protected function options(): GraphOptions
    {
        return new GraphOptions(legendTitle: __('graph.application.chronyd.level'), palette: 'mixed');
    }
}
