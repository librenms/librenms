<?php

namespace App\Graphs\Application;

use App\Data\Graphing\DataSeries;
use App\Data\Graphing\GraphOptions;

/**
 * One field of every GPU the poller last saw, each GPU is stored in app-nvidia-<app_id>-<index>.rrd
 */
abstract class NvidiaGraph extends ApplicationGraph
{
    /** the field drawn for each GPU */
    protected const string FIELD = '';

    /** the translation key of the legend title */
    protected const string LEGEND = '';

    public function getSeries(): array
    {
        $series = [];
        foreach ($this->application()->data['gpus'] ?? [] as $index) {
            $series["gpu$index"] = new DataSeries($this->metric(['gpu' => $index]), static::FIELD, __('graph.application.nvidia.gpu', ['index' => $index]));
        }

        return $series;
    }

    protected function options(): GraphOptions
    {
        return new GraphOptions(legendTitle: __(static::LEGEND), palette: 'greens');
    }
}
