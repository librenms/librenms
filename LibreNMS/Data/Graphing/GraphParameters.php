<?php
/**
 * GraphParameters.php
 *
 * -Description-
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

namespace LibreNMS\Data\Graphing;

use App\Facades\DeviceCache;
use Illuminate\Support\Str;
use LibreNMS\Config;
use LibreNMS\Enum\ImageFormat;
use LibreNMS\Util\Clean;
use LibreNMS\Util\Time;

class GraphParameters
{
    public readonly array $visibleElements;

    public string $title = '';
    public readonly ?string $user_title;
    public readonly string $type;
    public readonly string $subtype;
    public readonly ImageFormat $imageFormat;

    public readonly string $font;
    public readonly string $font_color;
    public readonly int $font_size;
    public readonly string $background;
    public readonly string $canvas;

    public readonly int $width;
    public readonly int $height;
    public readonly bool $full_size;
    public readonly bool $is_small;

    public readonly int $from;
    public readonly int $to;
    public readonly int $period;
    public readonly int $prev_from;

    public readonly bool $inverse;
    public readonly string $in;
    public readonly string $out;
    public ?int $scale_max = null;
    public ?int $scale_min = null;
    public ?bool $scale_rigid = null;
    public bool $sloped = true;

    public int $float_precision = 2;

    private const TINY = 99;
    private const SMALL = 224;
    private const MEDIUM_SMALL = 300;
    private const MEDIUM = 350;

    public function __construct(array $vars)
    {
        $this->imageFormat = ImageFormat::forGraph($vars['graph_type'] ?? null);
        [$this->type, $this->subtype] = $this->extractType($vars['type'] ?? '');

        $this->width = (int) ($vars['width'] ?? 400);
        $this->height = (int) ($vars['height'] ?? $this->width / 3);
        $this->full_size = ! empty($vars['absolute']);
        $this->is_small = $this->width < self::SMALL;

        $this->font = Config::get('mono_font');
        $this->font_color = Clean::alphaDash($vars['font'] ?? '');
        $this->font_size = $this->width <= self::MEDIUM_SMALL ? 7 : 8;

        $this->canvas = Clean::alphaDash($vars['bg'] ?? '');
        $this->background = Clean::alphaDash($vars['bbg'] ?? '');

        $this->user_title = $vars['graph_title'] ?? null; // if the user sets a title, show it
        $this->visibleElements = [
            'title' => isset($this->user_title) || (isset($vars['title']) && $vars['title'] !== 'no'),
            'legend' => empty($vars['legend']) || $vars['legend'] !== 'no',
            'total' => ! ($vars['nototal'] ?? $this->is_small),
            'details' => ! ($vars['nodetails'] ?? $this->is_small),
            'aggregate' => ! empty($vars['noagg']),
            'previous' => isset($vars['previous']) && $vars['previous'] == 'yes',
        ];

        $this->from = Time::parseAt($vars['from'] ?? '-1d');
        $this->to = empty($vars['to']) ? time() : Time::parseAt($vars['to']);
        $this->period = $this->to - $this->from;
        $this->prev_from = $this->from - $this->period;
        $this->scale_min = $vars['scale_min'] ?? null;
        $this->scale_max = $vars['scale_max'] ?? null;
        $this->scale_rigid = isset($vars['scale_rigid']) ? $vars['scale_rigid'] && $vars['scale_rigid'] !== 'no' : null;

        $this->inverse = ! empty($vars['inverse']);
        $this->in = $this->inverse ? 'out' : 'in';
        $this->out = $this->inverse ? 'in' : 'out';
    }

    public function visible(string $element): bool
    {
        return $this->visibleElements[$element] ?? true;
    }

    public function all(): array
    {
        $variables = get_object_vars($this);

        // legacy compat
        $variables['nototal'] = ! $this->visibleElements['total'];
        $variables['nodetails'] = ! $this->visibleElements['details'];
        $variables['noagg'] = ! $this->visibleElements['aggregate'];
        $variables['title'] = $this->visibleElements['title'];

        return $variables;
    }

    public function toRrdOptions(): array
    {
        $options = ['--start', $this->from, '--end', $this->to, '--width', $this->width, '--height', $this->height];

        // image must fit final dimensions
        if ($this->full_size) {
            $options[] = '--full-size-mode';
        }

        if ($this->imageFormat === ImageFormat::svg) {
            $options[] = '--imgformat=SVG';
            if ($this->width < self::MEDIUM) {
                array_push($options, '-m', 0.75, '-R', 'light');
            }
        }

        // set up fonts
        array_push($options, '--font', 'LEGEND:' . $this->font_size . ':' . $this->font);
        array_push($options, '--font', 'AXIS:' . ($this->font_size - 1) . ':' . $this->font);
        array_push($options, '--font-render-mode', 'normal');

        // set up colors
        foreach ($this->graphColors() as $name => $color) {
            array_push($options, '-c', $name . '#' . $color);
        }

        // set up scaling scaling
        if ($this->scale_min === null && $this->scale_max === null) {
            $options[] = '--alt-autoscale-max';
            if ($this->scale_rigid === null) {
                $this->scale_rigid = true;
            }
        }
        if ($this->scale_min !== null) {
            array_push($options, '-l', $this->scale_min);
        }
        if ($this->scale_max !== null) {
            array_push($options, '-u', $this->scale_max);
        }
        if ($this->scale_rigid) {
            $options[] = '--rigid';
        }

        if ($this->sloped) {
            $options[] = '--slope-mode';
        }

        // remove all text, height is too small
        if ($this->height < self::TINY) {
            $options[] = '--only-graph';
        }

        if (! $this->visible('legend')) {
            $options[] = '-g';
        }

        if ($this->visible('title')) {
            // remove single quotes, because we can't drop out of the string if this is sent to rrdtool stdin
            $options[] = '--title=' . escapeshellarg(str_replace("'", '', $this->getTitle()));
        }

        return $options;
    }

    public function __toString(): string
    {
        return implode(' ', $this->toRrdOptions());
    }

    /**
     * Get the graph title. In order:
     * - User set title
     * - Graph set title
     * - Fallback default title
     */
    public function getTitle(): string
    {
        return $this->user_title ?? $this->title ?: $this->defaultTitle();
    }

    private function graphColors(): array
    {
        $config_suffix = Config::get('applied_site_style') == 'dark' ? '_dark' : '';
        $def_colors = Config::get('rrdgraph_def_text' . $config_suffix);
        $def_font = ltrim(Config::get('rrdgraph_def_text_color' . $config_suffix), '#');

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

    private function extractType(string $type): array
    {
        preg_match('/^(?P<type>[A-Za-z0-9]+)_(?P<subtype>.+)/', $type, $graphtype);
        $type = basename($graphtype['type']);
        $subtype = basename($graphtype['subtype']);

        return [$type, $subtype];
    }

    private function defaultTitle(): string
    {
        $title = DeviceCache::getPrimary()->displayName() ?: ucfirst($this->type);
        $title .= '::';
        $title .= Str::title(str_replace('_', ' ', $this->subtype));

        return $title;
    }
}
