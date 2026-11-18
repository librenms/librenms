<?php

namespace App\Http\Controllers;

use App\Data\Graphing\GraphData;
use App\Data\Graphing\GraphDataSeries;
use App\Data\Graphing\ResolvedStyle;
use App\Graphs\AbstractSeriesGraph;
use App\Http\Requests\GraphRequest;
use Illuminate\Http\JsonResponse;
use LibreNMS\Exceptions\GraphException;

/**
 * The values of each series and how to draw them, for the browser.
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
        $styles = ResolvedStyle::resolve($drawn, $options->palette);
        $hex = fn (?string $color) => $color === null ? null : "#$color";

        $series = [];
        foreach ($drawn as $key => $item) {
            $style = $styles[$key];
            $values = $data->series[$key];

            $series[] = new GraphDataSeries(
                key: $key,
                label: $item->description,
                unit: $item->unit,
                color: "#$style->color",
                fillColor: $hex($style->fillColor),
                maxFillColor: $hex($style->maxFillColor),
                stacked: $style->stacked,
                invert: $style->invert,
                drawScale: $style->drawScale,
                avg: $values['avg'],
                min: $values['min'],
                max: $values['max'],
            );
        }

        return response()->json(new GraphData(
            timestamps: $data->timestamps,
            axis: [
                'label' => $options->verticalLabel ?? $options->legendTitle,
                'min' => $options->scaleMin,
                'max' => $options->scaleMax,
                'base' => $options->base ?? 1000,
            ],
            series: $series,
        ));
    }
}
