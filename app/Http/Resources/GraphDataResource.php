<?php

namespace App\Http\Resources;

use App\Data\Graphing\DataSeries;
use App\Data\Graphing\GraphData;
use App\Facades\LibrenmsConfig;
use App\Graphs\SeriesPalette;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * A graph for the browser to draw: the values of each series and how to draw them
 *
 * @property GraphData $resource
 */
class GraphDataResource extends JsonResource
{
    /** @var string|null */
    public static $wrap = null;

    /**
     * @return array{
     *     timestamps: list<int>,
     *     axis: array{label: string, min: float|null, max: float|null, base: int},
     *     series: list<array<string, mixed>>,
     * }
     */
    public function toArray(Request $request): array
    {
        $graph = $this->resource;
        $options = $graph->options;
        $palette = new SeriesPalette($options->palette);
        $inverts = ! LibrenmsConfig::get('webui.graph_stacked');
        $averaged = count(array_filter($graph->series, fn (DataSeries $series) => $series->style->stackAverage));

        $series = [];
        foreach ($graph->series as $key => $item) {
            $style = $item->style;
            $values = $graph->data->series[$key];
            $color = $style->color ?? $palette->next();

            $series[] = [
                'key' => $key,
                'label' => $item->description,
                'unit' => $item->unit,
                'color' => "#$color",
                'fill_color' => $this->fillColor($item, $color),
                'max_fill_color' => $style->maxFillColor === null ? null : "#$style->maxFillColor",
                'stacked' => $style->stacked,
                'invert' => $style->invert && $inverts,
                'draw_scale' => $style->stackAverage ? $style->drawScale / $averaged : $style->drawScale,
                'avg' => $values->average,
                'min' => $values->min,
                'max' => $values->max,
            ];
        }

        return [
            'timestamps' => $graph->data->timestamps,
            'axis' => [
                'label' => $options->verticalLabel ?? $options->legendTitle,
                'min' => $options->scaleMin,
                'max' => $options->scaleMax,
                'base' => $options->base ?? 1000,
            ],
            'series' => $series,
        ];
    }

    /**
     * Stacked series are solid areas, lines are only filled when the style asks for it
     */
    private function fillColor(DataSeries $series, string $color): ?string
    {
        if ($series->style->stacked) {
            return "#$color";
        }

        return $series->style->fill ? '#' . ($series->style->fillColor ?? $color . '20') : null;
    }
}
