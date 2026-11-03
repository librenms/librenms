<?php

/**
 * RrdOptionsBuilder.php
 *
 * The rrdtool graph options that come before the graph definition: time range, size, fonts, colors, title and scale
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program.  If not, see <https://www.gnu.org/licenses/>.
 *
 * @link       https://www.librenms.org
 *
 * @copyright  2026 Tony Murray
 * @author     Tony Murray <murraytony@gmail.com>
 */

namespace App\Data\Graphing\Rrd;

use App\Data\Graphing\GraphOptions;
use App\Data\Graphing\GraphParameters;
use App\Facades\LibrenmsConfig;
use LibreNMS\Enum\ImageFormat;

class RrdOptionsBuilder
{
    private const TINY = 99;
    private const MEDIUM_SMALL = 300;
    private const MEDIUM = 350;

    private readonly int $fontSize;

    public function __construct(private readonly GraphParameters $params)
    {
        $this->fontSize = $params->width <= self::MEDIUM_SMALL ? 7 : 8;
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
     * The requested scale is used when the graph does not set one
     *
     * @return array<int, string|int|float>
     */
    private function scaleOptions(GraphOptions $graph): array
    {
        $options = [];
        $min = $graph->scaleMin ?? $this->params->scale_min;
        $max = $graph->scaleMax ?? $this->params->scale_max;
        $rigid = $graph->scaleRigid ?? $this->params->scale_rigid;

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
        $options = [];

        if ($graph->sloped) {
            $options[] = '--slope-mode';
        }
        if ($graph->altYGrid) {
            $options[] = '--alt-y-grid';
        }
        if ($graph->base !== null) {
            array_push($options, '--base', $graph->base);
        }
        if ($graph->unitsExponent !== null) {
            array_push($options, '--units-exponent', $graph->unitsExponent);
        }
        if ($graph->logarithmic) {
            $options[] = '--logarithmic';
        }

        if ($graph->rightAxis !== null) {
            array_push($options, '--right-axis', $this->escape($graph->rightAxis));
        }
        if ($graph->rightAxisLabel !== null) {
            array_push($options, '--right-axis-label', $this->escape($graph->rightAxisLabel));
        }
        if ($graph->leftAxisFormat !== null) {
            array_push($options, '--left-axis-format', $this->escape($graph->leftAxisFormat));
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
        $style = $this->params->style ?: session('applied_site_style');
        $defaults = LibrenmsConfig::get($style == 'dark' ? 'rrdgraph_def_text_dark' : 'rrdgraph_def_text');
        $font = ltrim(LibrenmsConfig::get($style == 'dark' ? 'rrdgraph_def_text_color_dark' : 'rrdgraph_def_text_color'), '#');

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
