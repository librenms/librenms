<?php

namespace App\Http\Controllers;

use App\Facades\DeviceCache;
use App\Facades\LibrenmsConfig;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;
use LibreNMS\Util\StringHelpers;
use LibreNMS\Util\Time;
use LibreNMS\Util\Url;

class GraphsPageController extends Controller
{
    public function __invoke(Request $request): View
    {
        // Pull in only the legacy graph helpers this page needs (mirrors LibreNMS\Util\Graph).
        // Authentication is handled by the route's auth middleware, so init.php is not required.
        include_once base_path('includes/dbFacile.php');
        include_once base_path('includes/common.php');
        include_once base_path('includes/html/functions.inc.php');
        include_once base_path('includes/rewrites.php');

        $vars = $request->except(['page', 'username', 'password']);

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

        // Authorize and resolve the graphed entity. The per-type auth include sets $auth and,
        // depending on the type, the $device and/or $port models used for the page heading.
        $device = isset($vars['device']) ? DeviceCache::get($vars['device']) : null;
        $port = null;
        /** @var bool $auth set by the required auth.inc.php below */
        $auth = false;
        if ($type && is_file(base_path("includes/html/graphs/$type/auth.inc.php"))) {
            require base_path("includes/html/graphs/$type/auth.inc.php");
        }

        if (! $auth) {
            abort(403);
        }

        $subtitle = $this->buildSubtitle($type, $subtype, $vars);
        $pageTitle = trim($this->entityTitle($device, $port) . $subtitle);

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

        // Build a /graphs query-string URL, merging $changes into the current vars (null removes a key).
        $graphUrl = fn (array $changes = []): string => url()->query(
            'graphs',
            array_filter(array_merge($vars, $changes), fn ($v) => $v !== null)
        );

        // Subtype navigation options for <x-select>; each value is the destination URL.
        $subtypeOptions = [];
        $subtypeSelected = null;
        if (count($graphSubtypes) > 1) {
            foreach ($graphSubtypes as $availType) {
                $subtypeOptions[] = [
                    'value' => $graphUrl(['type' => $type . '_' . $availType]),
                    'text' => StringHelpers::niceCase($availType),
                ];
            }
            $subtypeSelected = $graphUrl(['type' => $type . '_' . $subtype]);
        }

        // Thumbnail period row.
        $thumbTo = LibrenmsConfig::get('time.now');
        $periodThumbs = [];
        foreach (LibrenmsConfig::get('graphs.row.normal') as $period => $text) {
            $from = LibrenmsConfig::get("time.$period");
            $periodThumbs[] = [
                'text' => $text,
                'link' => $graphUrl(['from' => $from, 'to' => $thumbTo]),
                'vars' => array_merge($vars, [
                    'height' => '60',
                    'width' => $thumbWidth,
                    'legend' => 'no',
                    'from' => $from,
                    'to' => $thumbTo,
                ]),
            ];
        }

        // Legend / previous / RRD-command / port-speed-zoom controls.
        $toggles = [];
        $toggles[] = (isset($vars['legend']) && $vars['legend'] == 'no')
            ? ['text' => 'Show Legend', 'link' => $graphUrl(['legend' => null])]
            : ['text' => 'Hide Legend', 'link' => $graphUrl(['legend' => 'no'])];
        $toggles[] = (isset($vars['previous']) && $vars['previous'] == 'yes')
            ? ['text' => 'Hide Previous', 'link' => $graphUrl(['previous' => null])]
            : ['text' => 'Show Previous', 'link' => $graphUrl(['previous' => 'yes'])];
        $toggles[] = $showCommand
            ? ['text' => 'Hide RRD Command', 'link' => $graphUrl(['showcommand' => null])]
            : ['text' => 'Show RRD Command', 'link' => $graphUrl(['showcommand' => 'yes'])];
        if (($vars['type'] ?? '') === 'port_bits') {
            $toggles[] = ($vars['port_speed_zoom'] ?? LibrenmsConfig::get('graphs.port_speed_zoom'))
                ? ['text' => 'Zoom to Traffic', 'link' => $graphUrl(['port_speed_zoom' => 0])]
                : ['text' => 'Zoom to Port Speed', 'link' => $graphUrl(['port_speed_zoom' => 1])];
        }
        $trendHint = ($vars['type'] ?? '') === 'port_bits' || str_contains((string) ($vars['type'] ?? ''), 'sensor_');

        // Main graph rendering (data prepared here; the view only echoes the server-built markup).
        $mainGraphVars = array_merge($vars, ['height' => $graphHeight, 'width' => $graphWidth]);
        $graphJsState = generate_graph_js_state($mainGraphVars);
        $dynamicGraphHtml = LibrenmsConfig::get('webui.dynamic_graphs', false) === true
            ? generate_dynamic_graph_js($mainGraphVars) . generate_dynamic_graph_tag($mainGraphVars)
            : null;
        $mainGraphTag = Url::lazyGraphTag($mainGraphVars);

        $fullType = (string) ($vars['type'] ?? '');
        $graphDescr = LibrenmsConfig::has("graph_descr.$fullType")
            ? LibrenmsConfig::get("graph_descr.$fullType")
            : null;

        $viewData = [
            'device' => $device,
            'port' => $port,
            'subtitle' => $subtitle,
            'pageTitle' => $pageTitle,
            'subtypeOptions' => $subtypeOptions,
            'subtypeSelected' => $subtypeSelected,
            'periodThumbs' => $periodThumbs,
            'toggles' => $toggles,
            'trendHint' => $trendHint,
            'dateSelectorHtml' => $this->renderDateSelector($mainGraphVars),
            'graphWidth' => $graphWidth,
            'graphJsState' => $graphJsState,
            'dynamicGraphHtml' => $dynamicGraphHtml,
            'mainGraphTag' => $mainGraphTag,
            'graphDescr' => $graphDescr,
            'showCommand' => $showCommand,
            'rrdCommandHtml' => $showCommand ? $this->renderRrdCommand($mainGraphVars) : null,
            'refresh' => LibrenmsConfig::get('page_refresh'),
        ];

        return view('graphs.show', $viewData);
    }

    /**
     * Build the plain-text subtitle (" :: ...") describing the graph subtype.
     *
     * @param  array<string, mixed>  $vars
     */
    private function buildSubtitle(string $type, string $subtype, array $vars): string
    {
        if (LibrenmsConfig::has("graph_types.$type.$subtype.descr")) {
            return ' :: ' . LibrenmsConfig::get("graph_types.$type.$subtype.descr");
        }

        if ($type === 'device' && $subtype === 'collectd') {
            $subtitle = ' :: ' . StringHelpers::niceCase($subtype) . ' :: ' . ($vars['c_plugin'] ?? '');
            if (isset($vars['c_plugin_instance'])) {
                $subtitle .= ' - ' . $vars['c_plugin_instance'];
            }
            $subtitle .= ' - ' . ($vars['c_type'] ?? '');
            if (isset($vars['c_type_instance'])) {
                $subtitle .= ' - ' . $vars['c_type_instance'];
            }

            return $subtitle;
        }

        return ' :: ' . StringHelpers::niceCase($subtype);
    }

    /**
     * Plain-text heading for the graphed entity (used for the browser page title).
     */
    private function entityTitle(?object $device, ?object $port): string
    {
        if ($port !== null) {
            $title = $device?->displayName() . ' :: Port ' . $port->getLabel();
            if ($port->ifAlias != '' && $port->ifAlias != $port->ifDescr) {
                $title .= ', ' . $port->ifAlias;
            }

            return $title;
        }

        return $device?->displayName() ?? '';
    }

    /**
     * @param  array<string, mixed>  $graphVars
     */
    private function renderDateSelector(array $graphVars): string
    {
        $graph_array = $graphVars;
        ob_start();
        include base_path('includes/html/print-date-selector.inc.php');

        return (string) ob_get_clean();
    }

    /**
     * @param  array<string, mixed>  $graphVars
     */
    private function renderRrdCommand(array $graphVars): string
    {
        $vars = $graphVars;
        $auth = false;
        $command_only = 1;
        ob_start();
        require base_path('includes/html/graphs/graph.inc.php');

        return (string) ob_get_clean();
    }
}
