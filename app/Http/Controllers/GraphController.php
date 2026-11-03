<?php

namespace App\Http\Controllers;

use App\Http\Requests\GraphImageRequest;
use Illuminate\Http\Response;
use LibreNMS\Exceptions\GraphException;
use LibreNMS\Util\Debug;
use LibreNMS\Util\Graph;

class GraphController extends Controller
{
    /**
     * @throws GraphException
     */
    public function __invoke(GraphImageRequest $request): Response
    {
        if ($request->user() !== null) {
            // only allow debug for logged in users
            Debug::set($request->boolean('debug'));
        }

        $graph = $request->graph();

        try {
            $image = $graph->render();
            $status = 200;
        } catch (GraphException $e) {
            if (Debug::isEnabled()) {
                throw $e;
            }

            $image = Graph::errorImage($graph->getParams(), $e);
            $status = $e->httpStatus();
        }

        if (Debug::isEnabled()) {
            return response('<img src="' . $image->inline() . '" alt="graph" />');
        }

        $data = $request->input('output') == 'base64' ? $image->base64() : $image->data;

        return response($data, $status, ['Content-type' => $image->contentType()]);
    }
}
