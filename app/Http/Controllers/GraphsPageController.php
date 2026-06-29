<?php

namespace App\Http\Controllers;

use App\Facades\LibrenmsConfig;
use App\Facades\Rrd;
use App\Http\Requests\GraphsPageRequest;
use App\Models\Device;
use App\Models\Port;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Log;
use LibreNMS\Util\Graph;
use LibreNMS\Util\StringHelpers;
use LibreNMS\Util\Time;

class GraphsPageController extends Controller
{
    public function __invoke(GraphsPageRequest $request): View
    {
        $fullType = "{$request->type}_$request->subtype";
        $showCommand = $request->input('showcommand') === 'yes';
        $isDynamicGraph = LibrenmsConfig::get('webui.dynamic_graphs', false) === true;

        $this->handleWidescreenPreference($request);

        $subtitle = $this->buildSubtitle($request->type, $request->subtype, $request);

        [$subtypeOptions, $subtypeSelected] = $this->subtypeNavigationOptions($request);

        ['width' => $graphWidth, 'height' => $graphHeight, 'thumbWidth' => $thumbWidth] = $this->graphDimensions($request);
        $width = max(10, $request->integer('width') ?: $graphWidth);
        $height = max(10, $request->integer('height') ?: $graphHeight);
        $mainGraphVars = $request->toVars(['height' => $height, 'width' => $width]);

        return view('graphs.show', [
            'device' => $request->device,
            'port' => $request->port,
            'subtitle' => $subtitle,
            'pageTitle' => $this->entityTitle($request->device, $request->port) . $subtitle,
            'subtypeOptions' => $subtypeOptions,
            'subtypeSelected' => $subtypeSelected,
            'periodThumbs' => $this->periodThumbnails($request, $request->from, $request->to, $thumbWidth),
            'toggles' => $this->controlToggles($request, $fullType, $showCommand),
            'trendHint' => $fullType === 'port_bits' || str_contains($fullType, 'sensor_'),
            'graphFrom' => $request->input('from') ?? '-1d',
            'graphTo' => $request->input('to'),
            'mainGraphVars' => $mainGraphVars,
            'graphDescr' => LibrenmsConfig::get("graph_descr.$fullType"),
            'showCommand' => $showCommand,
            'rrdCommand' => $showCommand ? $this->renderRrdCommand($mainGraphVars) : null,
            'isDynamicGraph' => $isDynamicGraph,
            'dynamicGraphWidth' => $isDynamicGraph ? $width : 0,
            'dynamicGraphSrcTemplate' => $isDynamicGraph ? $this->dynamicGraphSrcTemplate($mainGraphVars) : null,
            'refresh' => LibrenmsConfig::get('page_refresh'),
        ]);
    }

    /**
     * Store widescreen display toggle state in session.
     */
    private function handleWidescreenPreference(GraphsPageRequest $request): void
    {
        if ($request->has('widescreen')) {
            $request->input('widescreen') === 'yes'
                ? $request->session()->put('widescreen', 1)
                : $request->session()->forget('widescreen');
        }
    }

    /**
     * Get the navigation dropdown options and the currently selected URL.
     *
     * @return array{0: array<int, array{value: string, text: string}>, 1: ?string}
     */
    private function subtypeNavigationOptions(GraphsPageRequest $request): array
    {
        $graphSubtypes = in_array($request->type, ['sensor', 'wireless'], true) ? [] : Graph::getSubtypes($request->type, $request->device);

        if (count($graphSubtypes) <= 1) {
            return [[], null];
        }

        return [
            array_map(fn ($availType) => [
                'value' => $this->graphUrl($request, ['type' => "{$request->type}_{$availType}"]),
                'text' => StringHelpers::niceCase($availType),
            ], $graphSubtypes),
            $this->graphUrl($request, ['type' => "{$request->type}_{$request->subtype}"]),
        ];
    }

    /**
     * Build the thumbnail navigation entries for each period.
     *
     * @return array<int, array<string, mixed>>
     */
    private function periodThumbnails(GraphsPageRequest $request, int $from, int $to, int $thumbWidth): array
    {
        $thumbTo = LibrenmsConfig::get('time.now');
        $currentDuration = $to - $from;
        $periodThumbs = [];

        foreach (LibrenmsConfig::get('graphs.row.normal') as $period => $text) {
            $periodFrom = LibrenmsConfig::get("time.{$period}");
            $periodDuration = (int) $thumbTo - (int) $periodFrom;
            $periodThumbs[] = [
                'text' => $text,
                'active' => $periodDuration > 0 && abs($currentDuration - $periodDuration) <= 0.1 * $periodDuration,
                'link' => $this->graphUrl($request, ['from' => Time::toRelativeOffset($periodDuration), 'to' => null]),
                'vars' => $request->toVars([
                    'height' => '90',
                    'width' => (int) round($thumbWidth * 1.5),
                    'legend' => 'no',
                    'absolute' => 1,
                    'from' => $periodFrom,
                    'to' => $thumbTo,
                ]),
            ];
        }

        return $periodThumbs;
    }

    /**
     * Build the toggles array for legend / command / zoom actions.
     *
     * @return array<int, array{text: string, link: string}>
     */
    private function controlToggles(GraphsPageRequest $request, string $fullType, bool $showCommand): array
    {
        $toggles = [
            $request->input('legend') === 'no'
                ? ['text' => 'Show Legend', 'link' => $this->graphUrl($request, ['legend' => null])]
                : ['text' => 'Hide Legend', 'link' => $this->graphUrl($request, ['legend' => 'no'])],
            $request->input('previous') === 'yes'
                ? ['text' => 'Hide Previous', 'link' => $this->graphUrl($request, ['previous' => null])]
                : ['text' => 'Show Previous', 'link' => $this->graphUrl($request, ['previous' => 'yes'])],
            $showCommand
                ? ['text' => 'Hide RRD Command', 'link' => $this->graphUrl($request, ['showcommand' => null])]
                : ['text' => 'Show RRD Command', 'link' => $this->graphUrl($request, ['showcommand' => 'yes'])],
        ];

        if ($fullType === 'port_bits') {
            $toggles[] = $request->boolean('port_speed_zoom', (bool) LibrenmsConfig::get('graphs.port_speed_zoom'))
                ? ['text' => 'Zoom to Traffic', 'link' => $this->graphUrl($request, ['port_speed_zoom' => 0])]
                : ['text' => 'Zoom to Port Speed', 'link' => $this->graphUrl($request, ['port_speed_zoom' => 1])];
        }

        return $toggles;
    }

    /**
     * Resolve the dynamic graph source template URL.
     *
     * @param  array<string, mixed>  $mainGraphVars
     */
    private function dynamicGraphSrcTemplate(array $mainGraphVars): string
    {
        $params = array_merge($mainGraphVars, [
            'width' => '{{width}}',
            'from' => '{{start}}',
            'to' => '{{end}}',
        ]);

        return str_replace(
            ['%7B%7Bstart%7D%7D', '%7B%7Bend%7D%7D', '%7B%7Bwidth%7D%7D'],
            ['{{start}}', '{{end}}', '{{width}}'],
            route('graph', $params)
        );
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
    private function entityTitle(?Device $device, ?Port $port): string
    {
        if ($port !== null) {
            return $device?->display . ' :: Port ' . $port->getFullLabel();
        }

        return $device->display ?? '';
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
                ...Rrd::buildCommand('graph', '-', $rrd_options),
            ]));
        } catch (\Throwable $e) {
            Log::error('RRDTool Command Error: ' . $e->getMessage(), ['exception' => $e]);

            return null;
        }
    }

    /**
     * Build a /graphs query-string URL, merging changes into the current request parameters.
     *
     * @param  array<string, mixed>  $changes
     */
    private function graphUrl(GraphsPageRequest $request, array $changes = []): string
    {
        $params = array_merge(
            $request->except(['page', 'username', 'password']),
            $changes
        );

        return url()->query('graphs', array_filter($params, fn ($v) => $v !== null));
    }
}
