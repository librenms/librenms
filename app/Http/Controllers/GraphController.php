<?php

namespace App\Http\Controllers;

use App\Http\Requests\GraphRequest;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;
use LibreNMS\Enum\ImageFormat;
use LibreNMS\Exceptions\RrdGraphException;
use LibreNMS\Util\Debug;

class GraphController extends Controller
{
    /**
     * @throws RrdGraphException
     */
    public function __invoke(GraphRequest $request): Response
    {
        if (Auth::check()) {
            // only allow debug for logged in users
            Debug::set($request->boolean('debug'));
        }

        try {
            $graph = $request->getGraph();

            $image = $graph->render();

            if (Debug::isEnabled()) {
                return response('<img src="' . $image->inline() . '" alt="graph" />');
            }

            $headers = [
                'Content-type' => $image->contentType(),
            ];

            if ($request->input('output') == 'base64') {
                return response($image->base64(), 200, $headers);
            }

            return response($image->data, 200, $headers);
        } catch (RrdGraphException $e) {
            if (Debug::isEnabled()) {
                throw $e;
            }

            try {
                $format = $request->getGraph()->getParams()->imageFormat;
            } catch (\Throwable) {
                $format = ImageFormat::Png;
            }

            return response($e->generateErrorImage(), 500, ['Content-type' => $format->contentType()]);
        }
    }
}
