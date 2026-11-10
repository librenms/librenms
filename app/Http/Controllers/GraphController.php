<?php

namespace App\Http\Controllers;

use App\Http\Requests\GraphImageRequest;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use LibreNMS\Exceptions\GraphException;
use LibreNMS\Util\Debug;
use LibreNMS\Util\Graph;
use LibreNMS\Util\Url;

class GraphController extends Controller
{
    /**
     * @throws GraphException
     */
    public function __invoke(Request $request): Response
    {
        if ($request->user() !== null) {
            // only allow debug for logged in users, set before the graph request authorizes and validates so those show debug output too
            Debug::set(! empty(Url::parseLegacyPathVars()['debug']));
        }

        $graphRequest = app(GraphImageRequest::class);

        try {
            $image = $graphRequest->graph()->render();
            $status = 200;
        } catch (GraphException $e) {
            if (Debug::isEnabled()) {
                throw $e;
            }

            $image = Graph::errorImage($graphRequest->graphParams(), $e);
            $status = $e->httpStatus();
        }

        if (Debug::isEnabled()) {
            return response('<img src="' . $image->inline() . '" alt="graph" />');
        }

        $data = $graphRequest->input('output') == 'base64' ? $image->base64() : $image->data;

        return response($data, $status, ['Content-type' => $image->contentType()]);
    }
}
