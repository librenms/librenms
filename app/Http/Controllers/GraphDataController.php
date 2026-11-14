<?php

namespace App\Http\Controllers;

use App\Http\Requests\GraphRequest;
use App\Http\Resources\GraphDataResource;
use Illuminate\Http\JsonResponse;
use LibreNMS\Exceptions\GraphException;
use LibreNMS\Interfaces\Data\Graphing\SeriesGraphInterface;

/**
 * The data of a graph for the browser to draw, for graphs that are defined by their series
 */
class GraphDataController extends Controller
{
    public function __invoke(GraphRequest $request): GraphDataResource|JsonResponse
    {
        $graph = $request->graph();

        if (! $graph instanceof SeriesGraphInterface) {
            return response()->json(['message' => __('graphs.image_only')], 404);
        }

        try {
            return new GraphDataResource($graph->getData());
        } catch (GraphException $e) {
            return response()->json(['message' => $e->getMessage()], $e->httpStatus());
        }
    }
}
