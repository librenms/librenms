<?php

namespace App\Http\Controllers;

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
            return response()->json(['message' => __('graph.image_only')], 404);
        }

        try {
            return response()->json($graph->getData());
        } catch (GraphException $e) {
            return response()->json(['message' => $e->getMessage()], $e->httpStatus());
        }
    }
}
