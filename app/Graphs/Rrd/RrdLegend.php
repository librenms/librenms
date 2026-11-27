<?php

namespace App\Graphs\Rrd;

use App\Data\Graphing\DataSeries;
use App\Data\Graphing\GraphOptions;
use App\Data\Graphing\GraphParameters;
use App\Data\Graphing\SeriesSum;
use Closure;
use LibreNMS\Data\Store\Rrd;

/**
 * rrdtool wraps or cuts off legend lines that are too long, so columns and labels are fitted to the image width
 */
class RrdLegend
{
    private const int LABEL_WIDTH = 16;
    private const int MAX_LABEL_WIDTH = 40;
    // measured: a legend character is a pixel narrower than the font size, the right margin keeps clear of the rrdtool watermark
    private const int LEFT_PIXELS = 15;
    private const int RIGHT_PIXELS = 16;
    // added to the graph width by the axis, by font size
    private const array AXIS_PIXELS = [7 => 75, 8 => 81];
    // the color box of a row and the space after it
    private const int ROW_INDENT = 4;
    private const int COLOR_BOX_WIDTH = 2;

    private const int PRECISION = 2;
    /** @var array<string, array{suffix: string, cf: string}> */
    private const array COLUMNS = [
        'now' => ['suffix' => '', 'cf' => 'LAST'],
        'min' => ['suffix' => 'min', 'cf' => 'MIN'],
        'max' => ['suffix' => 'max', 'cf' => 'MAX'],
        'avg' => ['suffix' => '', 'cf' => 'AVERAGE'],
    ];
    // kept on narrow graphs before the percentiles and Min, as legacy graphs did
    private const array COLUMN_PRIORITY = ['now', 'avg', 'max'];
    // sign, three digits, point and decimals: SI prefixes keep values between -999.99 and 999.99
    private const int NUMBER_WIDTH = 5 + self::PRECISION;
    // the number and its SI prefix
    private const int VALUE_WIDTH = self::NUMBER_WIDTH + 1;
    private const int COLUMN_GAP = 1;
    // a space, the value and its SI prefix
    private const int TOTAL_VALUE_WIDTH = 8;
    // space rrdtool leaves between items that are not glued
    private const int ITEM_GAP = 2;

    /** characters that fit a line */
    private readonly int $lineWidth;
    private readonly int $labelWidth;
    private readonly int $unitWidth;
    /** @var list<string|int> keys of COLUMNS and indexes of the percentiles */
    private readonly array $columns;

    /**
     * @param  array<string, DataSeries|SeriesSum>  $series
     * @param  Closure(string $id, int $index, int|float $percentile): string  $percentileDef  defines a percentile of a value, returns its id
     */
    public function __construct(
        GraphParameters $params,
        private readonly GraphOptions $options,
        array $series,
        private readonly Closure $percentileDef,
    ) {
        $this->unitWidth = max([0, ...array_map(fn (DataSeries|SeriesSum $series) => mb_strlen($series->unit), $series)]);

        $fontSize = RrdOptionsBuilder::fontSize($params->width);
        $imageWidth = $params->full_size ? $params->width : $params->width + self::AXIS_PIXELS[$fontSize];
        $this->lineWidth = intdiv(max(0, $imageWidth - self::LEFT_PIXELS - self::RIGHT_PIXELS), $fontSize - 1);
        $rowWidth = $this->lineWidth - self::ROW_INDENT;

        // labels are followed by a space
        $percentiles = array_keys($options->percentiles);
        $fitting = max(1, intdiv($rowWidth - self::LABEL_WIDTH - 1, $this->columnWidth()));
        $kept = array_slice(array_values(array_unique([...self::COLUMN_PRIORITY, ...$percentiles, ...array_keys(self::COLUMNS)])), 0, $fitting);
        $this->columns = array_values(array_filter([...array_keys(self::COLUMNS), ...$percentiles], fn (string|int $column) => in_array($column, $kept, true)));

        $longest = max([0, ...array_map(mb_strlen(...), $this->labels($series, $params))]);
        $this->labelWidth = max(self::LABEL_WIDTH, min(self::MAX_LABEL_WIDTH, $longest, $rowWidth - 1 - count($this->columns) * $this->columnWidth()));
    }

    /**
     * Includes the previous period rows that the traffic summary adds
     *
     * @param  array<string, DataSeries|SeriesSum>  $series
     * @return list<string>
     */
    private function labels(array $series, GraphParameters $params): array
    {
        $labels = [];
        foreach ($series as $key => $row) {
            $labels[] = $row->description;
            if ($params->visible('previous') && $this->options->traffic?->summarizes($key)) {
                $labels[] = __('graph.legend.previous', ['series' => $row->description]);
            }
        }

        return $labels;
    }

    /**
     * @return list<string>
     */
    public function header(): array
    {
        $headings = array_map(fn (string|int $column) => is_string($column)
            ? __("graph.legend.$column")
            : __('graph.legend.percentile', ['percentile' => $this->options->percentiles[$column]]), $this->columns);

        return [
            'COMMENT:' . Rrd::fixedSafeDescr($this->options->legendTitle, self::COLOR_BOX_WIDTH + $this->labelWidth),
            'COMMENT:' . implode('', array_map(fn (string $heading) => mb_str_pad($heading, $this->columnWidth(), ' ', STR_PAD_LEFT), $headings)) . '\\l',
        ];
    }

    public function label(string $text): string
    {
        return Rrd::fixedSafeDescr($text, $this->labelWidth);
    }

    /**
     * Values are glued and spaced by characters, rrdtool's own spacing does not line up with the headings
     *
     * @param  bool  $sameValue  every column summarizes $id instead of reading {$id}min and {$id}max
     * @return list<string>
     */
    public function values(string $id, string $unit, bool $sameValue = false): array
    {
        $values = array_map(fn (string|int $column) => is_string($column)
            ? $id . ($sameValue ? '' : self::COLUMNS[$column]['suffix']) . ':' . self::COLUMNS[$column]['cf']
            : ($this->percentileDef)($id, $column, $this->options->percentiles[$column]), $this->columns);

        $format = str_repeat(' ', self::COLUMN_GAP) . $this->valueFormat($unit);
        $last = array_key_last($values);

        return array_map(fn (string $value, int $index) => "GPRINT:$value:$format" . ($index === $last ? '\\n' : '\\g'), $values, array_keys($values));
    }

    /**
     * @param  list<array{string, string}>  $totals  value id and label
     * @return list<string>
     */
    public function totals(array $totals, string $unit): array
    {
        $drawing = [];
        $used = 0;
        foreach ($totals as [$id, $label]) {
            $width = mb_strlen($label) + self::TOTAL_VALUE_WIDTH + mb_strlen($unit);
            if ($used > 0 && $used + self::ITEM_GAP + $width > $this->lineWidth) {
                $drawing[array_key_last($drawing)] .= '\\l';
                $used = 0;
            }

            $drawing[] = "GPRINT:$id:" . Rrd::safeDescr($label) . " %6.2lf%s$unit";
            $used += ($used > 0 ? self::ITEM_GAP : 0) + $width;
        }

        if ($drawing !== []) {
            $drawing[array_key_last($drawing)] .= '\\l';
        }

        return $drawing;
    }

    private function columnWidth(): int
    {
        return self::COLUMN_GAP + self::VALUE_WIDTH + $this->unitWidth;
    }

    /**
     * Shorter units are padded in front, rrdtool removes trailing spaces of glued text
     */
    private function valueFormat(string $unit): string
    {
        $padding = str_repeat(' ', $this->unitWidth - mb_strlen($unit));

        // the space takes the place of the SI prefix
        return $padding . '%' . self::NUMBER_WIDTH . '.' . self::PRECISION . 'lf' . ($this->options->unitsExponent === 0 ? ' ' : '%s') . str_replace(['%', ':'], ['%%', '\\:'], $unit);
    }
}
