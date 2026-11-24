<?php

namespace App\Graphs\Application;

use App\Data\Graphing\DataSeries;
use App\Data\Graphing\GraphOptions;

/**
 * A graph of access log stats, of the log named by the log request var or the totals of all logs without one.
 * Each stat is stored in its own app-http_access_log_combined-<app_id>-logs___<log>___<stat>.rrd file,
 * or app-http_access_log_combined-<app_id>-totals_<stat>.rrd for the totals.
 */
abstract class HttpAccessLogCombinedGraph extends ApplicationGraph
{
    /** translation key of the legend title */
    protected const string LEGEND = 'graph.units.count';

    protected const string PALETTE = 'psychedelic';

    /**
     * The stats to draw
     *
     * @return array<int|string, string> the label of each stat, keyed by the stat name
     */
    abstract protected function stats(): array;

    protected function validation(): array
    {
        return [...parent::validation(), 'log' => 'nullable|string'];
    }

    public function getSeries(): array
    {
        $log = $this->params->input->string('log');

        $series = [];
        foreach ($this->stats() as $stat => $label) {
            $name = $log === null ? "totals_$stat" : "logs___{$log}___$stat";
            $series[$name] = new DataSeries($this->metric(['stat' => $name]), 'data', $label);
        }

        return $series;
    }

    protected function options(): GraphOptions
    {
        return new GraphOptions(scaleMin: 0, legendTitle: __(static::LEGEND), palette: static::PALETTE);
    }
}
