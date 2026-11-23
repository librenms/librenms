<?php

namespace App\Graphs\Application;

use App\Data\Graphing\DataSeries;
use App\Data\Graphing\GraphOptions;
use App\Data\Graphing\SeriesStyle;

class DockerTotalsGraph extends ApplicationGraph
{
    public function getSeries(): array
    {
        $metric = $this->metric();
        $style = new SeriesStyle(fill: true);

        return [
            'created' => new DataSeries($metric, 'created', __('graph.application.docker.created'), style: $style),
            'restarting' => new DataSeries($metric, 'restarting', __('graph.application.docker.restarting'), style: $style),
            'running' => new DataSeries($metric, 'running', __('graph.application.docker.running'), style: $style),
            'removing' => new DataSeries($metric, 'removing', __('graph.application.docker.removing'), style: $style),
            'paused' => new DataSeries($metric, 'paused', __('graph.application.docker.paused'), style: $style),
            'exited' => new DataSeries($metric, 'exited', __('graph.application.docker.exited'), style: $style),
            'dead' => new DataSeries($metric, 'dead', __('graph.application.docker.dead'), style: $style),
        ];
    }

    protected function options(): GraphOptions
    {
        return new GraphOptions(scaleMin: 0, legendTitle: __('graph.application.docker.status'), palette: 'mixed');
    }
}
