<?php

namespace App\Http\Controllers;

use App\Data\Graphing\DataSeries;
use App\Facades\LibrenmsConfig;
use App\Graphs\AbstractSeriesGraph;
use App\Graphs\SeriesPalette;
use App\Http\Requests\GraphRequest;
use Illuminate\Http\JsonResponse;
use LibreNMS\Exceptions\GraphException;

/**
 * A graph for the browser to draw: the values of each series and how to draw them.
 * Only for graphs that are defined by their series.
 */
class GraphDataController extends Controller
{
    public function __invoke(GraphRequest $request): JsonResponse
    {
        $graph = $request->graph();

        if (! $graph instanceof AbstractSeriesGraph) {
            return response()->json(['message' => __('graphs.image_only')], 404);
        }

        try {
            $data = $graph->getData();
        } catch (GraphException $e) {
            return response()->json(['message' => $e->getMessage()], $e->httpStatus());
        }

        $options = $graph->getOptions();
        $drawn = array_intersect_key($graph->getSeries(), $data->series);
        $palette = new SeriesPalette($options->palette);
        $inverts = ! LibrenmsConfig::get('webui.graph_stacked');
        $averaged = count(array_filter($drawn, fn (DataSeries $series) => $series->style->stackAverage));

        $series = [];
        foreach ($drawn as $key => $item) {
            $style = $item->style;
            $values = $data->series[$key];
            $color = $style->color ?? $palette->next();
            // stacked series are solid areas, lines are only filled when the style asks for it
            $fill = $style->stacked ? $color : ($style->fill ? $style->fillColor ?? $color . '20' : null);

            $series[] = [
                'key' => $key,
                'label' => $item->description,
                'unit' => $item->unit,
                'color' => "#$color",
                'fill_color' => $fill === null ? null : "#$fill",
                'max_fill_color' => $style->maxFillColor === null ? null : "#$style->maxFillColor",
                'stacked' => $style->stacked,
                'invert' => $style->invert && $inverts,
                'draw_scale' => $style->stackAverage ? $style->drawScale / $averaged : $style->drawScale,
                'avg' => $values['avg'],
                // the same as the average while every point is a single stored value, the browser fills those in
                'min' => $values['min'] === $values['avg'] ? null : $values['min'],
                'max' => $values['max'] === $values['avg'] ? null : $values['max'],
            ];
        }

        return response()->json([
            'timestamps' => $data->timestamps,
            'axis' => [
                'label' => $options->verticalLabel ?? $options->legendTitle,
                'min' => $options->scaleMin,
                'max' => $options->scaleMax,
                'base' => $options->base ?? 1000,
            ],
            'series' => $series,
        ]);
    }
}
