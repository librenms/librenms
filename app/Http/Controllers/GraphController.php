<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use LibreNMS\Config;
use LibreNMS\Enum\ImageFormat;
use LibreNMS\Exceptions\RrdGraphException;
use LibreNMS\Util\Debug;
use LibreNMS\Util\Graph;
use LibreNMS\Util\Url;

class GraphController extends Controller
{
    /**
     * @throws \LibreNMS\Exceptions\RrdGraphException
     */
    public function __invoke(Request $request, string $path = ''): Response
    {
        $vars = array_merge(Url::parseLegacyPathVars($request->path()), $request->except(['username', 'password']));
        $output = $vars['graph_type'] ?? Config::get('webui.graph_type', 'default');

        if (\Auth::check()) {
            // only allow debug for logged in users
            Debug::set(! empty($vars['debug']));
        }

        try {
            $graph = Graph::get($vars);

            if (Debug::isEnabled()) {
                return response('<img src="' . $graph->inline() . '" alt="graph" />');
            }

            $headers = [
                'Content-type' => $graph->contentType(),
            ];

            if ($output == 'base64') {
                return response($graph, 200, $headers);
            }

            return response($graph->data, 200, $headers);
        } catch (RrdGraphException $e) {
            if (Debug::isEnabled()) {
                throw $e;
            }

            return response($e->generateErrorImage(), 500, ['Content-type' => ImageFormat::forGraph()->contentType()]);
        }
    }
}
