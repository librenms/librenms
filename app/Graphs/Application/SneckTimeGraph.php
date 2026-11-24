<?php

namespace App\Graphs\Application;

use App\Data\Graphing\DataSeries;
use App\Data\Graphing\GraphOptions;

class SneckTimeGraph extends ApplicationGraph
{
    public function getSeries(): array
    {
        $metric = $this->metric();

        return [
            'time' => new DataSeries($metric, 'time', __('graph.application.sneck.on_dev')),
            'time_to_polling' => new DataSeries($metric, 'time_to_polling', __('graph.application.sneck.to_polling')),
        ];
    }

    protected function options(): GraphOptions
    {
        return new GraphOptions(legendTitle: __('graph.application.sneck.diff_in_secs'), palette: 'psychedelic');
    }
}
