<?php

namespace App\Graphs\Application;

use App\Data\Graphing\DataSeries;
use App\Data\Graphing\GraphOptions;
use App\Data\Graphing\SeriesStyle;

class SquidMemoryGraph extends ApplicationGraph
{
    public function getSeries(): array
    {
        $metric = $this->metric();
        $style = new SeriesStyle(fill: true);

        return [
            'memmaxsize' => new DataSeries($metric, 'memmaxsize', __('graph.application.squid.max_mem'), style: $style),
            'swapmaxsize' => new DataSeries($metric, 'swapmaxsize', __('graph.application.squid.max_swap'), style: $style),
            'swaphighwm' => new DataSeries($metric, 'swaphighwm', __('graph.application.squid.high_swap_wm'), style: $style),
            'swaplowwm' => new DataSeries($metric, 'swaplowwm', __('graph.application.squid.low_swap_wm'), style: $style),
            'currentswapsize' => new DataSeries($metric, 'currentswapsize', __('graph.application.squid.swap_usage'), style: $style),
            'memusage' => new DataSeries($metric, 'memusage', __('graph.application.squid.memory_usage'), style: $style),
        ];
    }

    protected function options(): GraphOptions
    {
        return new GraphOptions(legendTitle: __('graph.units.kilobytes'), palette: 'mixed');
    }
}
