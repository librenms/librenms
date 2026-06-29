<?php

namespace App\Http\Controllers;

use App\Facades\DeviceCache;
use App\Facades\LibrenmsConfig;
use App\Facades\Rrd;
use App\Http\Requests\GraphsPageRequest;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Log;
use LibreNMS\Util\Graph;
use LibreNMS\Util\StringHelpers;
use LibreNMS\Util\Time;
use LibreNMS\Util\Url;
use function base_path;

class GraphsPageController extends Controller
{
    public function __invoke(GraphsPageRequest $request): View
    {
        $fullType = $request->string('type')->toString();
        [$type, $subtype] = explode('_', $fullType, 2);

        // Authorize before doing any other page-setup work.
        [$device, $port] = $this->authorizeGraph($type, $request);

        if ($request->has('widescreen')) {
            if ($request->input('widescreen') === 'yes') {
                $request->session()->put('widescreen', 1);
            } elseif ($request->input('widescreen') === 'no') {
                $request->session()->forget('widescreen');
            }
        }

        ['width' => $graphWidth, 'height' => $graphHeight, 'thumbWidth' => $thumbWidth] = $this->graphDimensions($request);

        $rawFrom = $request->input('from');
        $rawTo = $request->input('to');
        $from = Time::parseAt($rawFrom ?? '') ?: LibrenmsConfig::get('time.day');
        $to = Time::parseAt($rawTo ?? '') ?: LibrenmsConfig::get('time.now');

        $subtitle = $this->buildSubtitle($type, $subtype, $request);
        $pageTitle = trim($this->entityTitle($device, $port) . $subtitle);

        $graphSubtypes = in_array($type, ['sensor', 'wireless'])
            ? []
            : get_graph_subtypes($type);

        $showCommand = $request->input('showcommand') === 'yes';

        // Build a /graphs query-string URL, merging $changes into the current request parameters (null removes a key).
        $graphUrl = fn (array $changes = []): string => url()->query(
            'graphs',
            array_filter(array_merge($request->except(['page', 'username', 'password']), $changes), fn ($v) => $v !== null)
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

        $vars = $request->except(['page', 'username', 'password']);
        $vars['from'] = $from;
        $vars['to'] = $to;
        if ($port) {
            $vars['device'] = $port->device_id;
            $vars['id'] = $port->port_id;
        } elseif ($device) {
            $vars['device'] = $device->device_id;
            $vars['id'] = $device->device_id;
        }

        // Thumbnail period row. A thumbnail is "active" when its duration matches the range
        // currently shown in the main graph (comparing durations stays correct even though the
        // absolute now-anchored timestamps drift between requests).
        $thumbTo = LibrenmsConfig::get('time.now');
        $currentDuration = (int) $to - (int) $from;
        $periodThumbs = [];
        foreach (LibrenmsConfig::get('graphs.row.normal') as $period => $text) {
            $periodFrom = LibrenmsConfig::get("time.$period");
            $periodDuration = (int) $thumbTo - (int) $periodFrom;
            $periodThumbs[] = [
                'text' => $text,
                'active' => $periodDuration > 0 && abs($currentDuration - $periodDuration) <= 0.1 * $periodDuration,
                'link' => $graphUrl(['from' => Time::toRelativeOffset($periodDuration), 'to' => null]),
                'vars' => array_merge($vars, [
                    'height' => '90',
                    'width' => (int) round($thumbWidth * 1.5),
                    'legend' => 'no',
                    'absolute' => 1, // full-size-mode: PNG renders at exactly width x height, so the
                                     // thumbnail can reserve its space and load without layout shift
                    'from' => $periodFrom,
                    'to' => $thumbTo,
                ]),
            ];
        }

        // Legend / previous / RRD-command / port-speed-zoom controls.
        $toggles = [];
        $toggles[] = ($request->input('legend') === 'no')
            ? ['text' => 'Show Legend', 'link' => $graphUrl(['legend' => null])]
            : ['text' => 'Hide Legend', 'link' => $graphUrl(['legend' => 'no'])];
        $toggles[] = ($request->input('previous') === 'yes')
            ? ['text' => 'Hide Previous', 'link' => $graphUrl(['previous' => null])]
            : ['text' => 'Show Previous', 'link' => $graphUrl(['previous' => 'yes'])];
        $toggles[] = $showCommand
            ? ['text' => 'Hide RRD Command', 'link' => $graphUrl(['showcommand' => null])]
            : ['text' => 'Show RRD Command', 'link' => $graphUrl(['showcommand' => 'yes'])];
        if ($fullType === 'port_bits') {
            $toggles[] = $request->boolean('port_speed_zoom', (bool) LibrenmsConfig::get('graphs.port_speed_zoom'))
                ? ['text' => 'Zoom to Traffic', 'link' => $graphUrl(['port_speed_zoom' => 0])]
                : ['text' => 'Zoom to Port Speed', 'link' => $graphUrl(['port_speed_zoom' => 1])];
        }
        $trendHint = $fullType === 'port_bits' || str_contains($fullType, 'sensor_');

        // Main graph rendering.
        $width = $request->integer('width');
        if ($width < 10) {
            $width = $graphWidth;
        }

        $height = $request->integer('height');
        if ($height < 10) {
            $height = $graphHeight;
        }

        $mainGraphVars = array_merge($vars, ['height' => $height, 'width' => $width]);
        $graphDescr = LibrenmsConfig::has("graph_descr.$fullType")
            ? LibrenmsConfig::get("graph_descr.$fullType")
            : null;

        $isDynamicGraph = LibrenmsConfig::get('webui.dynamic_graphs', false) === true;
        $dynamicGraphWidth = 0;
        $dynamicGraphSrcTemplate = null;
        if ($isDynamicGraph) {
            $dynamicGraphWidth = $width;

            $placeholders = [
                'width' => '{{width}}',
                'from' => '{{start}}',
                'to' => '{{end}}',
            ];

            $params = [];
            foreach ($mainGraphVars as $key => $value) {
                $params[$key] = $placeholders[$key] ?? $value;
            }

            $dynamicGraphSrcTemplate = str_replace(
                ['%7B%7Bstart%7D%7D', '%7B%7Bend%7D%7D', '%7B%7Bwidth%7D%7D'],
                ['{{start}}', '{{end}}', '{{width}}'],
                route('graph', $params)
            );
        }

        return view('graphs.show', [
            'device' => $device,
            'port' => $port,
            'subtitle' => $subtitle,
            'pageTitle' => $pageTitle,
            'subtypeOptions' => $subtypeOptions,
            'subtypeSelected' => $subtypeSelected,
            'periodThumbs' => $periodThumbs,
            'toggles' => $toggles,
            'trendHint' => $trendHint,
            'graphFrom' => $rawFrom ?? '-1d',
            'graphTo' => $rawTo,
            'mainGraphVars' => $mainGraphVars,
            'graphDescr' => $graphDescr,
            'showCommand' => $showCommand,
            'rrdCommand' => $showCommand ? $this->renderRrdCommand($mainGraphVars) : null,
            'isDynamicGraph' => $isDynamicGraph,
            'dynamicGraphWidth' => $dynamicGraphWidth,
            'dynamicGraphSrcTemplate' => $dynamicGraphSrcTemplate,
            'refresh' => LibrenmsConfig::get('page_refresh'),
        ]);
    }

    /**
     * Build the plain-text subtitle (" :: ...") describing the graph subtype.
     */
    private function buildSubtitle(string $type, string $subtype, GraphsPageRequest $request): string
    {
        if (LibrenmsConfig::has("graph_types.$type.$subtype.descr")) {
            return ' :: ' . LibrenmsConfig::get("graph_types.$type.$subtype.descr");
        }

        if ($type === 'device' && $subtype === 'collectd') {
            $subtitle = ' :: ' . StringHelpers::niceCase($subtype) . ' :: ' . ($request->input('c_plugin') ?? '');
            if ($cPluginInstance = $request->input('c_plugin_instance')) {
                $subtitle .= ' - ' . $cPluginInstance;
            }
            $subtitle .= ' - ' . ($request->input('c_type') ?? '');
            if ($cTypeInstance = $request->input('c_type_instance')) {
                $subtitle .= ' - ' . $cTypeInstance;
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
     * Authorize the request and resolve the graphed entity as early as possible.
     *
     * The per-type auth include sets $auth and, depending on the type, the $device and/or
     * $port models used for the page heading. Aborts with 403 when access is not permitted.
     *
     * @return array{0: ?object, 1: ?object} the resolved [$device, $port]
     */
    private function authorizeGraph(string $type, GraphsPageRequest $request): array
    {
        include_once base_path('includes/dbFacile.php');
        include_once base_path('includes/common.php');
        include_once base_path('includes/html/functions.inc.php');
        include_once base_path('includes/rewrites.php');

        $device = null;
        if ($deviceId = $request->input('device')) {
            $device = DeviceCache::get($deviceId);
        } elseif (($entityId = $request->input('id')) && $type !== 'port') {
            $device = DeviceCache::get($entityId);
        }
        $port = null;
        $auth = false;

        // Legacy auth.inc.php files expect their inputs in a $vars array in scope
        $vars = $request->except(['page', 'username', 'password']);
        if ($request->has('id') && ! $request->has('device') && $type !== 'port') {
            $vars['device'] = $request->input('id');
        }

        $authPath = base_path("includes/html/graphs/{$type}/auth.inc.php");
        if (is_file($authPath)) {
            $runAuth = static function (string $file, array $vars, mixed &$device, mixed &$port, bool &$auth): void {
                require $file;
            };
            $runAuth($authPath, $vars, $device, $port, $auth);
        }

        if (! $auth) {
            abort(403);
        }

        return [$device, $port];
    }

    /**
     * Resolve the main graph and thumbnail dimensions together. Width starts from the widescreen
     * preference and is overridden by the client-reported screen width; height comes from config and
     * is likewise refined by the reported screen height.
     *
     * @return array{width: int, height: int, thumbWidth: int}
     */
    private function graphDimensions(GraphsPageRequest $request): array
    {
        if ($request->session()->get('widescreen')) {
            $width = 1700;
            $thumbWidth = 180;
        } else {
            $width = 1075;
            $thumbWidth = 113;
        }

        if ($screenWidth = $request->session()->get('screen_width')) {
            $width = $screenWidth > 800
                ? (int) ($screenWidth - ($screenWidth / 10))
                : (int) ($screenWidth - ($screenWidth / 4));
        }

        $height = LibrenmsConfig::get('webui.min_graph_height');
        if ($screenHeight = $request->session()->get('screen_height')) {
            $height = $screenHeight > 960
                ? (int) ($screenHeight - ($screenHeight / 2))
                : (int) max($height, $screenHeight - ($screenHeight / 1.5));
        }

        return ['width' => $width, 'height' => $height, 'thumbWidth' => $thumbWidth];
    }


    /**
     * @param  array<string, mixed>  $graphVars
     */
    private function renderRrdCommand(array $graphVars): ?string
    {
        try {
            $rrd_options = Graph::getRrdOptions($graphVars);

            return implode(' ', array_map(escapeshellarg(...), [
                'rrdtool',
                ...Rrd::buildCommand('graph', '-', $rrd_options)
            ]));
        } catch (\Throwable $e) {
            Log::error('RRDTool Command Error: ' . $e->getMessage(), ['exception' => $e]);
            return null;
        }
    }
}
