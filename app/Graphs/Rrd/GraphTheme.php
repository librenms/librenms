<?php

namespace App\Graphs\Rrd;

use App\Data\Graphing\SeriesRole;
use App\Facades\LibrenmsConfig;

/**
 * The colors of one graph in the site style it is drawn in. Graphs say what a series is, the theme picks its color.
 * Colors are hex without a leading #, with an alpha when translucent.
 * Roles that follow a graph_colours palette use it in both styles, so site overrides keep working.
 */
class GraphTheme
{
    private const string TRANSLUCENT = '88';

    /** @var array<string, int> how many series of each role have been drawn, keyed by role name, '' for the palette */
    private array $drawn = [];

    /**
     * @param  string  $palette  the graph_colours palette of series without a role
     * @param  bool  $translucent  series overlap, so areas and fills let the ones behind them show
     */
    public function __construct(
        private readonly bool $dark,
        private readonly string $palette = 'mixed',
        private readonly bool $translucent = false,
    ) {
    }

    /**
     * The color of the next drawn series of a role, drawn series of the same role vary within its shades
     */
    public function next(?SeriesRole $role, bool $stacked = false): string
    {
        $colors = $role === null ? $this->configPalette($this->palette) : $this->shades($role)[$stacked ? 'areas' : 'lines'];
        $key = $role->name ?? '';
        $index = ($this->drawn[$key] ?? 0) % count($colors);
        $this->drawn[$key] = $index + 1;

        return $stacked ? $this->translucent($colors[$index]) : $colors[$index];
    }

    /**
     * The fill below a line of the role, a transparent version of the line when the role has none
     */
    public function fill(?SeriesRole $role, string $line): string
    {
        $fill = $role === null ? null : $this->shades($role)['fill'];

        return $fill === null ? $line . '20' : $this->translucent($fill);
    }

    /**
     * The fill below the maximum of a line of the role, behind its other fills
     */
    public function maxFill(?SeriesRole $role, string $line): string
    {
        $fill = $role === null ? null : $this->shades($role)['maxFill'];

        return $fill === null ? $line . '10' : $this->translucent($fill);
    }

    /**
     * The color of a role that does not vary, such as a reference line or a range
     */
    public function color(SeriesRole $role): string
    {
        return $this->shades($role)['lines'][0];
    }

    /**
     * The previous period of a traffic series, by the side of the axis the series is drawn on when not overlapping
     */
    public function previous(bool $below): string
    {
        return $below ? $this->pick('000099', '7A7AFF') : $this->pick('333300', 'C8C870');
    }

    /**
     * The percentile lines of a series drawn without a color of its own, such as a legend only total
     */
    public function percentile(): string
    {
        return $this->pick('AA0000', 'FF5050');
    }

    public function highestPercentile(): string
    {
        return 'FF0000';
    }

    public function trend(): string
    {
        return $this->pick('666666', 'AAAAAA');
    }

    public function previousStackArea(): string
    {
        return $this->pick('99999999', '77777799');
    }

    public function previousStackLine(): string
    {
        return $this->pick('666666', 'AAAAAA');
    }

    public function axis(): string
    {
        return $this->pick('555555', '999999');
    }

    /**
     * @return array{lines: non-empty-list<string>, areas: non-empty-list<string>, fill: string|null, maxFill: string|null}
     */
    private function shades(SeriesRole $role): array
    {
        $shades = match ($role) {
            SeriesRole::In => ['lines' => [$this->pick('608720', 'A0D050')], 'areas' => $this->configPalette('greens'), 'fill' => '90B040', 'maxFill' => $this->pick('D7FFC7', '3A4D24')],
            SeriesRole::Out => ['lines' => [$this->pick('606090', 'A0A0E0')], 'areas' => $this->configPalette('purples'), 'fill' => '8080C0', 'maxFill' => $this->pick('E0E0FF', '35355A')],
            SeriesRole::PacketsIn => ['lines' => [$this->pick('330033', 'CC88CC')], 'fill' => 'AA66AA', 'maxFill' => $this->pick('CC88CC', '4D2E4D')],
            SeriesRole::PacketsOut => ['lines' => ['FF6600'], 'fill' => 'FFDD88', 'maxFill' => $this->pick('FFEFAA', '5C4A22')],
            SeriesRole::Error => ['lines' => ['FF3300', 'FF6633']],
            SeriesRole::Discard => ['lines' => ['805080', 'C0A060']],
            SeriesRole::Value => ['lines' => [$this->pick('272B30', 'F2F2F2')]],
            SeriesRole::Muted => ['lines' => [$this->pick('D0D0D0', '3E444C')]],
            SeriesRole::Used => ['lines' => ['CC0000']],
            SeriesRole::Total => ['lines' => [$this->pick('708090', 'A0B0C0')]],
            SeriesRole::Loss => ['lines' => ['D42E08'], 'fill' => 'D42E08'],
            SeriesRole::UsageLow => ['lines' => ['AFCC7C']],
            SeriesRole::UsageModerate => ['lines' => ['7DA8C9']],
            SeriesRole::UsageHigh => ['lines' => ['CCA07E']],
            SeriesRole::UsageVeryHigh => ['lines' => ['C97E7D']],
            SeriesRole::UsageCritical => ['lines' => ['C75862']],
            SeriesRole::LowLimit => ['lines' => [$this->pick('00008B', '7A8CFF')]],
            SeriesRole::LowWarning => ['lines' => [$this->pick('005BDF', '4DA3FF')]],
            SeriesRole::HighWarning => ['lines' => ['FFA420']],
            SeriesRole::HighLimit => ['lines' => ['FF0000']],
            SeriesRole::PortSpeed => ['lines' => [$this->pick('000000', 'F2F2F2')]],
        };

        return [
            'lines' => $shades['lines'],
            'areas' => $shades['areas'] ?? $shades['lines'],
            'fill' => $shades['fill'] ?? null,
            'maxFill' => $shades['maxFill'] ?? null,
        ];
    }

    /**
     * @return non-empty-list<string>
     */
    private function configPalette(string $name): array
    {
        $colors = array_values(array_filter((array) LibrenmsConfig::get("graph_colours.$name"), is_string(...)));

        return $colors === [] ? [$this->pick('000000', 'F2F2F2')] : $colors;
    }

    private function translucent(string $color): string
    {
        return $this->translucent && strlen($color) === 6 ? $color . self::TRANSLUCENT : $color;
    }

    private function pick(string $light, string $dark): string
    {
        return $this->dark ? $dark : $light;
    }
}
