<?php

namespace App\Graphs\Rrd;

use App\Data\Graphing\GraphOptions;
use App\Data\Graphing\GraphParameters;
use App\Facades\LibrenmsConfig;
use LibreNMS\Enum\ImageFormat;

/**
 * The rrdtool graph options that come before the graph definition: time range, size, fonts, colors, title and scale
 */
class RrdOptionsBuilder
{
    private const int TINY = 99;
    private const int MEDIUM_SMALL = 300;
    private const int MEDIUM = 350;
    // measured, added to the graph width by the axis, by font size
    private const array AXIS_PIXELS = [7 => 75, 8 => 81];

    private readonly int $fontSize;

    public function __construct(private readonly GraphParameters $params)
    {
        $this->fontSize = self::fontSize($params->width);
    }

    public static function fontSize(int $width): int
    {
        return $width <= self::MEDIUM_SMALL ? 7 : 8;
    }

    /**
     * The requested width in full size mode, otherwise the graph and its axis
     */
    public static function imageWidth(GraphParameters $params): int
    {
        return $params->full_size ? $params->width : $params->width + self::AXIS_PIXELS[self::fontSize($params->width)];
    }

    /**
     * @param  string  $title  the graph title, replaced by the user title if set
     * @return array<int, string>
     */
    public function build(string $title, GraphOptions $options): array
    {
        return array_map(strval(...), [
            ...$this->imageOptions($title),
            ...$this->scaleOptions($options),
            ...$this->axisOptions($options),
        ]);
    }

    /**
     * @return array<int, string|int|float>
     */
    private function imageOptions(string $title): array
    {
        $params = $this->params;
        $options = ['--start', $params->from, '--end', $params->to, '--width', $params->width, '--height', $params->height];

        // image must fit final dimensions
        if ($params->full_size) {
            $options[] = '--full-size-mode';
        }

        if ($params->imageFormat === ImageFormat::Svg) {
            $options[] = '--imgformat=SVG';
            if ($params->width < self::MEDIUM) {
                array_push($options, '-m', 0.75, '-R', 'light');
            }
        }

        // set up fonts
        $font = LibrenmsConfig::get('mono_font');
        array_push($options, '--font', 'TITLE:' . $this->fontSize . ':' . $font);
        array_push($options, '--font', 'LEGEND:' . $this->fontSize . ':' . $font);
        array_push($options, '--font', 'AXIS:' . ($this->fontSize - 1) . ':' . $font);
        array_push($options, '--font-render-mode', 'normal');

        // set up colors
        foreach ($this->colors() as $name => $color) {
            array_push($options, '-c', $name . '#' . $color);
        }

        // remove all text, height is too small
        if ($params->height < self::TINY) {
            $options[] = '--only-graph';
        }

        if (! $params->visible('legend')) {
            $options[] = '-g';
        }

        if ($params->visible('title')) {
            $options[] = '--title=' . $this->escape($this->fitTitle($params->user_title ?? $title));
        }

        return $options;
    }

    /**
     * The requested scale is used when the graph does not set one, both widen to show every threshold
     *
     * @return array<int, string|int|float>
     */
    private function scaleOptions(GraphOptions $graph): array
    {
        $options = [];
        $min = $graph->scaleMin ?? $this->params->scale_min;
        $max = $graph->scaleMax ?? $this->params->scale_max;
        $rigid = $graph->scaleRigid ?? $this->params->scale_rigid;
        $bounds = $graph->thresholdBounds(! RrdSeriesDefinition::overlaid($graph));

        // limits leave no threshold out
        if ($bounds !== null) {
            $min = $min === null ? null : min($min, $bounds[0]);
            $max = $max === null ? null : max($max, $bounds[1]);
        }

        if ($min === null && $max === null) {
            $options[] = '--alt-autoscale-max';
            $rigid ??= true;
        }
        if ($min !== null) {
            array_push($options, '-l', $min);
        }
        if ($max !== null) {
            array_push($options, '-u', $max);
        }
        if ($rigid) {
            $options[] = '--rigid';
        }

        return $options;
    }

    /**
     * @return array<int, string|int|float>
     */
    private function axisOptions(GraphOptions $graph): array
    {
        $options = ['--slope-mode'];

        if ($graph->altYGrid) {
            $options[] = '--alt-y-grid';
        }
        if ($graph->base !== null) {
            array_push($options, '--base', $graph->base);
        }
        // rrdtool axis formats have no SI prefix, so axes with a unit show plain values
        $unitsExponent = $graph->unitsExponent ?? ($graph->axisUnit === null ? null : 0);
        if ($unitsExponent !== null) {
            array_push($options, '--units-exponent', $unitsExponent);
        }
        if ($graph->logarithmic) {
            $options[] = '--logarithmic';
        }

        $rightAxis = $graph->rightAxis ?? ($graph->rightAxisLabel === null ? null : '1:0');
        if ($rightAxis !== null) {
            array_push($options, '--right-axis', $this->escape($rightAxis));
        }
        if ($graph->rightAxisLabel !== null) {
            array_push($options, '--right-axis-label', $this->escape($graph->rightAxisLabel));
        }
        $leftAxisFormat = $graph->leftAxisFormat ?? ($graph->axisUnit === null ? null : '%lg ' . str_replace('%', '%%', $graph->axisUnit));
        if ($leftAxisFormat !== null) {
            array_push($options, '--left-axis-format', $this->escape($leftAxisFormat));
        }
        if ($graph->unitsLength !== null) {
            array_push($options, '--units-length', $graph->unitsLength);
        }
        if ($graph->verticalLabel !== null) {
            array_push($options, '--vertical-label', $this->escape($graph->verticalLabel));
        }

        return $options;
    }

    /**
     * @return array<string, string> rrdtool color names to hex colors
     */
    private function colors(): array
    {
        $dark = $this->params->isDark();
        $defaults = LibrenmsConfig::get($dark ? 'rrdgraph_def_text_dark' : 'rrdgraph_def_text');
        $font = ltrim(LibrenmsConfig::get($dark ? 'rrdgraph_def_text_color_dark' : 'rrdgraph_def_text_color'), '#');

        preg_match_all('/-c ([A-Z]+)#([0-9A-Fa-f]{6,8})/', $defaults, $matches);
        $colors = ['FONT' => $font];
        foreach ($matches[1] as $index => $key) {
            $colors[$key] = $matches[2][$index];
        }

        // user overrides
        if ($this->params->font_color) {
            $colors['FONT'] = $this->params->font_color;
        }

        if ($this->params->canvas) {
            $colors['CANVAS'] = $this->params->canvas;
        }

        if ($this->params->background) {
            $colors['BACK'] = $this->params->background;
        }

        return $colors;
    }

    private function fitTitle(string $title): string
    {
        // linear approximation
        $slope = 0.1332;
        $intercept = 15.55;
        $sizeAdjustment = 8 / $this->fontSize; // Adjust the slope if the font size deviates from 8
        $adjustedSlope = $slope * $sizeAdjustment;

        $maxChars = (int) floor($this->params->width * $adjustedSlope + $intercept);

        if ($maxChars <= 0) {
            return '';
        }

        if (strlen($title) <= $maxChars) {
            return $title;
        }

        return substr($title, 0, max(0, $maxChars - 3)) . '...';
    }

    private function escape(string $input): string
    {
        return str_replace(["'", '"', "\n", "\r"], '', $input);
    }
}
