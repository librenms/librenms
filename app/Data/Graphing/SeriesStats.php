<?php

namespace App\Data\Graphing;

use App\Facades\LibrenmsConfig;
use App\Graphs\Rrd\GraphTheme;

/**
 * The rolling average and percentile lines a series style asks for, like the legacy generic_stats graphs.
 * The image graphs and the browser chart both draw these, so they share labels, colors and which lines are left out.
 */
final readonly class SeriesStats
{
    /** @var array<string, array{period: string, step: int, minRange: int}> period is a translation key, a graph shorter than minRange seconds would only show NaN */
    private const array ROLLING_AVERAGES = [
        'hourly' => ['period' => 'graph.stats.hour', 'step' => 3600, 'minRange' => 0],
        'daily' => ['period' => 'graph.stats.day', 'step' => 86400, 'minRange' => 129600],
        'weekly' => ['period' => 'graph.stats.week', 'step' => 604800, 'minRange' => 691200],
    ];

    /**
     * @param  array<string, array{label: string, period: string, step: int, color: string}>  $averages  keyed by name, such as hourly. Label is the line,
     *                                                                                                   such as 1 hour avg, period names the rolling period, such as 1 hour.
     * @param  list<array{percentile: int|float, label: string, color: string}>  $percentiles
     */
    private function __construct(
        public array $averages,
        public array $percentiles,
    ) {
    }

    /**
     * Colors come in the legacy order, the percentiles first, and are taken even for lines that are left out, so each line keeps its color at any range
     *
     * @param  int  $range  seconds covered by the graph
     */
    public static function of(SeriesStyle $style, GraphTheme $theme, int $range): self
    {
        $percentileColors = array_map(fn () => $theme->next($style->role), $style->percentiles);

        $averages = [];
        if ($style->rollingAverages) {
            foreach (self::ROLLING_AVERAGES as $name => ['period' => $period, 'step' => $step, 'minRange' => $minRange]) {
                $color = $theme->next($style->role);
                if ($range >= $minRange) {
                    $period = __($period);
                    $averages[$name] = ['label' => __('graph.stats.avg', ['period' => $period]), 'period' => $period, 'step' => $step, 'color' => $color];
                }
            }
        }

        $percentiles = [];
        if (! LibrenmsConfig::get('graph_stat_percentile_disable')) {
            foreach ($style->percentiles as $index => $percentile) {
                $percentiles[] = ['percentile' => $percentile, 'label' => __('graph.stats.percentile', ['percentile' => $percentile]), 'color' => $percentileColors[$index]];
            }
        }

        return new self($averages, $percentiles);
    }
}
