<?php

namespace App\Data\Graphing;

/**
 * Rolling averages and percentile lines added to each series, like the legacy generic_stats graphs.
 */
final readonly class StatsOptions
{
    /**
     * @param  bool  $hourly  1 hour average, min and max
     * @param  bool  $daily  1 day average, min and max, when the graph covers at least 36 hours
     * @param  bool  $weekly  1 week average, min and max, when the graph covers at least 8 days
     * @param  list<float>  $percentiles  horizontal lines at these percentiles, unless graph_stat_percentile_disable is set
     */
    public function __construct(
        public bool $hourly = true,
        public bool $daily = true,
        public bool $weekly = true,
        public array $percentiles = [25, 50, 75],
    ) {
    }
}
