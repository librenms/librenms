<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use LibreNMS\Config;
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
        $vars['graph_type'] = $vars['graph_type'] ?? Config::get('webui.graph_type');

        if (\Auth::check()) {
            // only allow debug for logged in users
            Debug::set(! empty($vars['debug']));
        }

        $headers = [
            'Content-type' => Graph::imageType($vars['graph_type']),
        ];

        try {
            return response(Graph::get($vars), 200, Debug::isEnabled() ? [] : $headers);
        } catch (RrdGraphException $e) {
            if (Debug::isEnabled()) {
                throw $e;
            }

            return response($e->generateErrorImage(), 500, $headers);
        }
    }
}
