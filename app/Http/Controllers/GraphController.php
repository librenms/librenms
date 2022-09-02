<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use LibreNMS\Config;
use LibreNMS\Util\Debug;
use LibreNMS\Util\Url;

class GraphController extends Controller
{
    public function __invoke(Request $request, string $path = ''): Response
    {
        define('IGNORE_ERRORS', true);

        include_once base_path('includes/dbFacile.php');
        include_once base_path('includes/common.php');
        include_once base_path('includes/html/functions.inc.php');
        include_once base_path('includes/rewrites.php');

        $auth = \Auth::guest(); // if user not logged in, assume we authenticated via signed url, allow_unauth_graphs or allow_unauth_graphs_cidr
        $vars = array_merge(Url::parseLegacyPathVars($request->path()), $request->except(['username', 'password']));
        if (\Auth::check()) {
            // only allow debug for logged in users
            Debug::set(! empty($vars['debug']));
        }

        // TODO, import graph.inc.php code and call Rrd::graph() directly
        chdir(base_path());
        ob_start();
        include base_path('includes/html/graphs/graph.inc.php');
        $output = ob_get_clean();
        ob_end_clean();

        $headers = [];
        if (! Debug::isEnabled()) {
            $headers['Content-type'] = (Config::get('webui.graph_type') == 'svg' ? 'image/svg+xml' : 'image/png');
        }

        return response($output, 200, $headers);
    }
}
