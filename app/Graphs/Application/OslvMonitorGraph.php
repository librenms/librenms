<?php

namespace App\Graphs\Application;

use App\Data\Graphing\DataSeries;
use App\Data\Graphing\GraphOptions;

/**
 * A graph of oslv_monitor stats, the totals of all containers or of the container in the oslvm var.
 * Each stat is a file of its own with the field data: app-oslv_monitor-<id>-totals_<stat>.rrd
 * or app-oslv_monitor-<id>-oslvm___<container>___<stat>.rrd.
 */
abstract class OslvMonitorGraph extends ApplicationGraph
{
    /**
     * The stats drawn, keyed by the stat name in the rrd filename, with their series label
     *
     * @return array<string, string>
     */
    abstract protected function stats(): array;

    abstract protected function legendTitle(): string;

    protected function validation(): array
    {
        return [...parent::validation(), 'oslvm' => 'nullable|string'];
    }

    public function getSeries(): array
    {
        $oslvm = $this->params->input->string('oslvm') ?? '';
        $series = [];
        foreach ($this->stats() as $stat => $label) {
            $file = $oslvm === '' ? "totals_$stat" : "oslvm___{$oslvm}___$stat";
            $series[$stat] = new DataSeries($this->metric(['stat' => $file]), 'data', $label);
        }

        return $series;
    }

    protected function options(): GraphOptions
    {
        return new GraphOptions(scaleMin: 0, legendTitle: $this->legendTitle(), palette: 'psychedelic');
    }
}
