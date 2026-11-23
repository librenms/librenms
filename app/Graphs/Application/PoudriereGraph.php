<?php

namespace App\Graphs\Application;

use App\Data\Graphing\DataSeries;
use App\Data\Graphing\GraphOptions;

/**
 * A graph of poudriere stats, the totals of all sets or of the set in the poudriere_set var.
 * Each stat is a file of its own with the field data: app-poudriere-<id>-totals_<stat>.rrd
 * or app-poudriere-<id>-jps___<set>___<stat>.rrd.
 */
abstract class PoudriereGraph extends ApplicationGraph
{
    protected const string PALETTE = 'psychedelic';

    /**
     * The stats drawn, keyed by the stat name in the rrd filename, with their series label
     *
     * @return array<string, string>
     */
    abstract protected function stats(): array;

    abstract protected function legendTitle(): string;

    protected function validation(): array
    {
        return [...parent::validation(), 'poudriere_set' => 'nullable|string'];
    }

    public function getSeries(): array
    {
        $set = $this->params->input->string('poudriere_set') ?? '';
        $series = [];
        foreach ($this->stats() as $stat => $label) {
            $file = $set === '' ? "totals_$stat" : "jps___{$set}___$stat";
            $series[$stat] = new DataSeries($this->metric(['stat' => $file]), 'data', $label);
        }

        return $series;
    }

    protected function options(): GraphOptions
    {
        return new GraphOptions(scaleMin: 0, legendTitle: $this->legendTitle(), palette: static::PALETTE);
    }
}
