<?php

namespace LibreNMS\Data\Graphing;

/** Keep overlapping traffic directions readable without blending filled areas. */
class TrafficGraphStyle
{
    /**
     * @param  list<string>  $options
     * @return list<string>
     */
    public static function sameAxis(array $options): array
    {
        $colors = [];
        foreach ($options as $option) {
            if (preg_match('/^(?:AREA|LINE[\d.]*):((?:d?out|in)[a-z0-9_]*)#([a-f0-9]{6})/i', $option, $match)) {
                $colors[strtoupper($match[2])] = self::color($match[1]);
            }
        }

        return array_map(function (string $option) use ($colors): string {
            if (preg_match('/^(?:AREA|LINE[\d.]*):((?:d?out|in)[a-z0-9_]*)#[a-f0-9]+(.*)$/i', $option, $match)) {
                $series = $match[1];
                $historical = str_contains($series, 'X');
                $maximum = str_contains($series, '_max');
                $color = self::color($series) . ($historical || $maximum ? '88' : '');
                $suffix = preg_replace('/:dashes(?:=[\d.,]+)?/', '', $match[2]);
                $dash = $historical ? ':dashes=2,4' : (self::outgoing($series) ? ':dashes=6,3' : '');

                return 'LINE' . ($historical || $maximum ? '1' : '2') . ':' . $series . '#' . $color . $suffix . $dash;
            }

            // Some aggregate templates use an invisible HRULE for the outgoing legend swatch.
            if (preg_match('/^(HRULE:[^#]+#)([a-f0-9]{6})(:.*)$/i', $option, $match) && isset($colors[strtoupper($match[2])]) && stripos($match[3], 'out') !== false) {
                return $match[1] . $colors[strtoupper($match[2])] . $match[3];
            }

            return $option;
        }, $options);
    }

    private static function outgoing(string $series): bool
    {
        return (bool) preg_match('/^d?out/i', $series);
    }

    private static function color(string $series): string
    {
        return self::outgoing($series) ? 'FF8C00' : '00A6ED';
    }
}
