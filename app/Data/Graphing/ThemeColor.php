<?php

namespace App\Data\Graphing;

use App\Facades\LibrenmsConfig;

/**
 * A color with a variation for each site style, the renderer uses the variation of the style it draws in.
 * A color the request may override, such as the text color, takes the requested color in every style.
 * Colors are hex without a leading #.
 */
final readonly class ThemeColor
{
    /**
     * @param  string|null  $light  null when there is no such color in the light style
     * @param  string|null  $dark  null when there is no such color in the dark style
     * @param  'font_color'|'canvas'|'background'|null  $override  the GraphParameters color that replaces this one when requested
     */
    public function __construct(
        public ?string $light,
        public ?string $dark,
        public ?string $override = null,
    ) {
    }

    /**
     * The configured text color, also for lines that should stand out like text
     */
    public static function text(): self
    {
        return new self(
            ltrim((string) LibrenmsConfig::get('rrdgraph_def_text_color'), '#'),
            ltrim((string) LibrenmsConfig::get('rrdgraph_def_text_color_dark'), '#'),
            'font_color',
        );
    }

    /**
     * A color that barely stands out from the background, for areas behind the data such as ranges or free space
     */
    public static function subtle(): self
    {
        return new self('D0D0D0', '3E444C');
    }

    /**
     * The configured color of the drawing area
     */
    public static function canvas(): self
    {
        return self::imageColor('CANVAS', 'canvas');
    }

    /**
     * The configured color around the drawing area
     */
    public static function background(): self
    {
        return self::imageColor('BACK', 'background');
    }

    /**
     * The hex of a color that may have a variation for each style
     */
    public static function resolve(string|self|null $color, GraphParameters $params): ?string
    {
        return $color instanceof self ? $color->for($params) : $color;
    }

    /**
     * The variation for the style of the request, or the color the request chose instead
     */
    public function for(GraphParameters $params): ?string
    {
        $requested = match ($this->override) {
            'font_color' => $params->font_color,
            'canvas' => $params->canvas,
            'background' => $params->background,
            null => '',
        };

        if ($requested !== '') {
            return $requested;
        }

        return $params->isDark() ? $this->dark : $this->light;
    }

    /**
     * A color of the rrdgraph_def_text settings, which use rrdtool syntax
     *
     * @param  'canvas'|'background'  $override
     */
    private static function imageColor(string $name, string $override): self
    {
        $find = fn (string $setting): ?string => preg_match("/-c $name#([0-9A-Fa-f]{6,8})/", (string) LibrenmsConfig::get($setting), $match) ? $match[1] : null;

        return new self($find('rrdgraph_def_text'), $find('rrdgraph_def_text_dark'), $override);
    }
}
