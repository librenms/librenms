<?php

namespace App\Http\Controllers\Api\V1;

use App\Graphs\AbstractSeriesGraph;
use App\Http\Controllers\Controller;
use App\Http\Requests\ApiGraphRequest;
use Illuminate\Http\JsonResponse;
use LibreNMS\Exceptions\GraphException;

/**
 * The values of each series of a graph, the same data the web UI draws its graphs from.
 */
class GraphDataController extends Controller
{
    public function __invoke(ApiGraphRequest $request): JsonResponse
    {
        $graph = $request->graph();

        if (! $graph instanceof AbstractSeriesGraph) {
            return response()->json(['message' => __('graph.image_only')], 404);
        }

        try {
            return response()->json(['data' => [
                'type' => 'graph-data',
                'id' => $request->string('type')->value(),
                'attributes' => $graph->getData(),
            ]]);
        } catch (GraphException $e) {
            return response()->json(['message' => $e->getMessage()], $e->httpStatus());
        }
    }
}
