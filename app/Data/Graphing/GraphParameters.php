<?php

/**
 * GraphParameters.php
 *
 * Graph input that comes from the request: time range, size, style and visible elements
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
 * @copyright  2022 Tony Murray
 * @author     Tony Murray <murraytony@gmail.com>
 */

namespace App\Data\Graphing;

use App\Facades\LibrenmsConfig;
use LibreNMS\Enum\ImageFormat;
use LibreNMS\Util\Clean;
use LibreNMS\Util\Time;

readonly class GraphParameters
{
    /** @var array<string, bool> */
    private array $visibleElements;

    public ?string $user_title;
    public string $type;
    public string $subtype;
    public ImageFormat $imageFormat;

    public string $style;
    public string $font;
    public string $font_color;
    public int $font_size;
    public string $background;
    public string $canvas;

    public int $width;
    public int $height;
    public bool $full_size;
    public bool $is_small;

    public int $from;
    public int $to;
    public int $period;
    public int $prev_from;

    public bool $inverse;
    public string $in;
    public string $out;

    // user requested scale, graph options take precedence
    public ?float $scale_min;
    public ?float $scale_max;
    public ?bool $scale_rigid;

    public const MAX_SIZE = 10000;
    private const TINY = 99;
    private const SMALL = 224;
    private const MEDIUM_SMALL = 300;
    private const MEDIUM = 350;

    /**
     * @param  array<string, mixed>  $vars
     */
    public function __construct(array $vars)
    {
        $input = new GraphVars($vars);

        $this->imageFormat = ImageFormat::forGraph($input->string('graph_type'));
        [$this->type, $this->subtype] = $this->extractType($input->string('type') ?? '');

        $this->width = $this->size($input->int('width') ?? 400);
        $this->height = $this->size($input->int('height') ?? intdiv($this->width, 3));
        $this->full_size = $input->bool('absolute') ?? false;
        $this->is_small = $this->width < self::SMALL;

        $this->style = $input->string('style') ?? '';
        $this->font = LibrenmsConfig::get('mono_font');
        $this->font_color = Clean::alphaDash($input->string('font') ?? '');
        $this->font_size = $this->width <= self::MEDIUM_SMALL ? 7 : 8;

        $this->canvas = Clean::alphaDash($input->string('bg') ?? '');
        $this->background = Clean::alphaDash($input->string('bbg') ?? '');

        $this->user_title = $input->string('graph_title'); // if the user sets a title, show it
        $title = $input->string('title');
        $this->visibleElements = [
            'title' => $this->user_title !== null || ($title !== null && $title !== 'no'),
            'legend' => $input->string('legend') !== 'no',
            'total' => ! ($input->bool('nototal') ?? $this->is_small),
            'details' => ! ($input->bool('nodetails') ?? $this->is_small),
            'aggregate' => $input->bool('noagg') ?? false,
            'previous' => $input->string('previous') === 'yes',
        ];

        $this->from = Time::parseAt($input->string('from') ?: '-1d');
        $to = $input->string('to');
        $this->to = $to ? Time::parseAt($to) : time();
        $this->period = $this->to - $this->from;
        $this->prev_from = $this->from - $this->period;
        $this->scale_min = $input->float('scale_min');
        $this->scale_max = $input->float('scale_max');
        $this->scale_rigid = $input->bool('scale_rigid');

        $this->inverse = $input->bool('inverse') ?? false;
        $this->in = $this->inverse ? 'out' : 'in';
        $this->out = $this->inverse ? 'in' : 'out';
    }

    public function visible(string $element): bool
    {
        return $this->visibleElements[$element] ?? true;
    }

    /**
     * @param  string  $title  the graph title, replaced by the user title if set
     * @return array<int, string|int|float>
     */
    public function toRrdOptions(string $title): array
    {
        $options = ['--start', $this->from, '--end', $this->to, '--width', $this->width, '--height', $this->height];

        // image must fit final dimensions
        if ($this->full_size) {
            $options[] = '--full-size-mode';
        }

        if ($this->imageFormat === ImageFormat::Svg) {
            $options[] = '--imgformat=SVG';
            if ($this->width < self::MEDIUM) {
                array_push($options, '-m', 0.75, '-R', 'light');
            }
        }

        // set up fonts
        array_push($options, '--font', 'TITLE:' . $this->font_size . ':' . $this->font);
        array_push($options, '--font', 'LEGEND:' . $this->font_size . ':' . $this->font);
        array_push($options, '--font', 'AXIS:' . ($this->font_size - 1) . ':' . $this->font);
        array_push($options, '--font-render-mode', 'normal');

        // set up colors
        foreach ($this->graphColors() as $name => $color) {
            array_push($options, '-c', $name . '#' . $color);
        }

        // remove all text, height is too small
        if ($this->height < self::TINY) {
            $options[] = '--only-graph';
        }

        if (! $this->visible('legend')) {
            $options[] = '-g';
        }

        if ($this->visible('title')) {
            $options[] = '--title=' . self::escapeRrdParameter($this->formatTitle($this->user_title ?? $title));
        }

        return $options;
    }

    public static function escapeRrdParameter(string $input): string
    {
        return str_replace(["'", '"', "\n", "\r"], '', $input);
    }

    /**
     * @return array<string, string> rrdtool color names to hex colors
     */
    private function graphColors(): array
    {
        $style = $this->style ?: session('applied_site_style');
        $def_colors = LibrenmsConfig::get($style == 'dark' ? 'rrdgraph_def_text_dark' : 'rrdgraph_def_text');
        $def_font = ltrim(LibrenmsConfig::get($style == 'dark' ? 'rrdgraph_def_text_color_dark' : 'rrdgraph_def_text_color'), '#');

        preg_match_all('/-c ([A-Z]+)#([0-9A-Fa-f]{6,8})/', $def_colors, $matches);
        $colors = ['FONT' => $def_font];
        foreach ($matches[1] as $index => $key) {
            $colors[$key] = $matches[2][$index];
        }

        // user overrides
        if ($this->font_color) {
            $colors['FONT'] = $this->font_color;
        }

        if ($this->canvas) {
            $colors['CANVAS'] = $this->canvas;
        }

        if ($this->background) {
            $colors['BACK'] = $this->background;
        }

        return $colors;
    }

    private function size(int $pixels): int
    {
        return max(1, min(self::MAX_SIZE, $pixels));
    }

    /**
     * @return array{string, string} type and subtype
     */
    private function extractType(string $type): array
    {
        preg_match('/^(?P<type>[A-Za-z0-9]+)_(?P<subtype>.+)/', $type, $graphtype);
        $type = basename($graphtype['type'] ?? '');
        $subtype = basename($graphtype['subtype'] ?? '');

        return [$type, $subtype];
    }

    private function formatTitle(string $title): string
    {
        // linear approximation
        $slope = 0.1332;
        $intercept = 15.55;
        $sizeAdjustment = 8 / $this->font_size; // Adjust the slope if the font size deviates from 8
        $adjustedSlope = $slope * $sizeAdjustment;

        $maxChars = (int) floor($this->width * $adjustedSlope + $intercept);

        if ($maxChars <= 0) {
            return '';
        }

        if (strlen($title) <= $maxChars) {
            return $title;
        }

        return substr($title, 0, max(0, $maxChars - 3)) . '...';
    }
}
