<?php

namespace App\Http\Controllers;

use App\Facades\DeviceCache;
use App\Facades\LibrenmsConfig;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;
use LibreNMS\Util\Time;
use LibreNMS\Util\Url;

class GraphsPageController extends Controller
{
    public function __invoke(Request $request, string $path = ''): View
    {
        $init_modules = ['web', 'auth'];
        require base_path('/includes/init.php');

        $vars = array_merge(
            Url::parseLegacyPathVars($request->path()),
            $request->except(['username', 'password'])
        );
        unset($vars['page']);

        if (isset($vars['widescreen'])) {
            if ($vars['widescreen'] === 'yes') {
                session()->put('widescreen', 1);
            } elseif ($vars['widescreen'] === 'no') {
                session()->forget('widescreen');
            }
        }

        if (session('widescreen')) {
            $graphWidth = 1700;
            $thumbWidth = 180;
        } else {
            $graphWidth = 1075;
            $thumbWidth = 113;
        }

        $vars['from'] = Time::parseAt($vars['from'] ?? '') ?: LibrenmsConfig::get('time.day');
        $vars['to'] = Time::parseAt($vars['to'] ?? '') ?: LibrenmsConfig::get('time.now');

        preg_match('/^(?P<type>[A-Za-z0-9]+)_(?P<subtype>.+)/', (string) ($vars['type'] ?? ''), $graphtype);
        $type = basename($graphtype['type'] ?? '');
        $subtype = basename($graphtype['subtype'] ?? '');

        if (isset($vars['device'])) {
            $device = DeviceCache::get($vars['device']);
        }

        $title = '';
        /** @var bool $auth set by the required auth.inc.php below */
        $auth = false;
        if ($type && is_file(base_path("includes/html/graphs/$type/auth.inc.php"))) {
            require base_path("includes/html/graphs/$type/auth.inc.php");
        }

        if (! $auth) {
            abort(403);
        }

        if (LibrenmsConfig::has("graph_types.$type.$subtype.descr")) {
            $title .= ' :: ' . LibrenmsConfig::get("graph_types.$type.$subtype.descr");
        } elseif ($type === 'device' && $subtype === 'collectd') {
            $title .= ' :: ' . \LibreNMS\Util\StringHelpers::niceCase($subtype) . ' :: ' . ($vars['c_plugin'] ?? '');
            if (isset($vars['c_plugin_instance'])) {
                $title .= ' - ' . $vars['c_plugin_instance'];
            }
            $title .= ' - ' . ($vars['c_type'] ?? '');
            if (isset($vars['c_type_instance'])) {
                $title .= ' - ' . $vars['c_type_instance'];
            }
        } else {
            $title .= ' :: ' . \LibreNMS\Util\StringHelpers::niceCase($subtype);
        }

        $graphSubtypes = in_array($type, ['sensor', 'wireless'])
            ? []
            : get_graph_subtypes($type);

        if ($screenWidth = \Session::get('screen_width')) {
            $graphWidth = $screenWidth > 800
                ? (int) ($screenWidth - ($screenWidth / 10))
                : (int) ($screenWidth - ($screenWidth / 4));
        }

        $graphHeight = LibrenmsConfig::get('webui.min_graph_height');
        if ($screenHeight = \Session::get('screen_height')) {
            $graphHeight = $screenHeight > 960
                ? (int) ($screenHeight - ($screenHeight / 2))
                : (int) max($graphHeight, $screenHeight - ($screenHeight / 1.5));
        }

        $showCommand = isset($vars['showcommand']) && $vars['showcommand'] === 'yes';
        $thumbArray = LibrenmsConfig::get('graphs.row.normal');

        return view('graphs.show', [
            'vars'          => $vars,
            'type'          => $type,
            'subtype'       => $subtype,
            'title'         => $title,
            'graphWidth'    => $graphWidth,
            'thumbWidth'    => $thumbWidth,
            'graphHeight'   => $graphHeight,
            'graphSubtypes' => $graphSubtypes,
            'thumbArray'    => $thumbArray,
            'showCommand'   => $showCommand,
            'refresh'       => LibrenmsConfig::get('page_refresh'),
        ]);
    }
}
