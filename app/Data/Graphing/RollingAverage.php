<?php

namespace App\Data\Graphing;

use App\Graphs\Rrd\GraphTheme;

/**
 * A line of the average of a series over each hour, day or week, like the legacy generic_stats graphs.
 * The image graphs and the browser chart both draw these, so they share labels, colors and which are left out.
 */
final readonly class RollingAverage
{
    /** @var array<string, array{period: string, step: int, minRange: int}> period is a translation key, a graph shorter than minRange seconds would only show NaN */
    private const array PERIODS = [
        'hourly' => ['period' => 'graph.stats.hour', 'step' => 3600, 'minRange' => 0],
        'daily' => ['period' => 'graph.stats.day', 'step' => 86400, 'minRange' => 129600],
        'weekly' => ['period' => 'graph.stats.week', 'step' => 604800, 'minRange' => 691200],
    ];

    /**
     * @param  string  $name  hourly, daily or weekly
     * @param  string  $label  such as 1 hour avg
     * @param  int  $step  seconds of each period, periods end at multiples of it
     */
    private function __construct(
        public string $name,
        public string $label,
        public int $step,
        public string $color,
    ) {
    }

    /**
     * The rolling averages a series style asks for. Each takes the next color of the theme, also those left out,
     * so each line keeps its color at any range.
     *
     * @param  int  $range  seconds covered by the graph
     * @return list<self>
     */
    public static function forStyle(SeriesStyle $style, GraphTheme $theme, int $range): array
    {
        if (! $style->rollingAverages) {
            return [];
        }

        $averages = [];
        foreach (self::PERIODS as $name => ['period' => $period, 'step' => $step, 'minRange' => $minRange]) {
            $color = $theme->next($style->role);
            if ($range >= $minRange) {
                $averages[] = new self($name, __('graph.stats.avg', ['period' => __($period)]), $step, $color);
            }
        }

        return $averages;
    }
}
