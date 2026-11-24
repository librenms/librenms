<?php

namespace App\Graphs\Application;

use App\Data\Graphing\DataSeries;
use App\Data\Graphing\GraphOptions;

/**
 * A graph of NFS stats, each stored in its own app-nfs-<app_id>-<stat>.rrd file. The stats and their labels are the
 * $nfs_graphs entry of includes/nfs-shared.inc.php for the nfs_os request var, or its default when the OS has none.
 */
abstract class NfsGraph extends ApplicationGraph
{
    /** the $nfs_graphs entry holding the stats of this graph */
    protected const string STATS = '';

    /** the part of a long stat list: 1 is the first 12 stats in sorted order, 2 the next 12. Null shows them all. */
    protected const ?int PART = null;

    /** whether the stats are rates, shown under a "per second" legend title */
    protected const bool PER_SECOND = true;

    protected const string PALETTE = 'psychedelic';

    private const int PART_SIZE = 12;

    protected function validation(): array
    {
        return [...parent::validation(), 'nfs_os' => 'nullable|string'];
    }

    public function getSeries(): array
    {
        require base_path('includes/nfs-shared.inc.php');

        $os = $this->params->input->string('nfs_os') ?? '';
        $stats = $nfs_graphs[static::STATS][$os] ?? $nfs_graphs[static::STATS][''] ?? [];

        if (static::PART !== null) {
            $names = array_keys($stats);
            sort($names);
            $partStats = [];
            foreach (array_slice($names, (static::PART - 1) * self::PART_SIZE, self::PART_SIZE) as $name) {
                $partStats[$name] = $stats[$name];
            }
            $stats = $partStats;
        }

        // the labels are NFS operation and counter names, so they are not translated
        $series = [];
        foreach ($stats as $stat => $label) {
            $series[$stat] = new DataSeries($this->metric(['stat' => $stat]), 'data', $label);
        }

        return $series;
    }

    protected function options(): GraphOptions
    {
        return new GraphOptions(legendTitle: static::PER_SECOND ? __('graph.units.per_second') : '', palette: static::PALETTE);
    }
}
