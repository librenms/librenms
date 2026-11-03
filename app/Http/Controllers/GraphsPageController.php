<?php

namespace App\Http\Controllers;

use App\Data\Graphing\GraphFactory;
use App\Data\Graphing\GraphParameters;
use App\Facades\LibrenmsConfig;
use App\Http\Requests\GraphRequest;
use App\Models\Device;
use App\Models\Port;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Log;
use LibreNMS\Interfaces\Data\Graphing\RrdGraphInterface;
use LibreNMS\Util\Graph;
use LibreNMS\Util\StringHelpers;
use LibreNMS\Util\Time;

class GraphsPageController extends Controller
{
    public function __invoke(GraphRequest $request): View
    {
        $params = $request->graphParams();
        $graph = $request->graph();
        $device = $graph->getDevice();
        $port = $graph->getPort();
        $fullType = "{$params->type}_$params->subtype";
        $showCommand = $request->input('showcommand') === 'yes';
        $isDynamicGraph = LibrenmsConfig::get('webui.dynamic_graphs', false) === true;

        $this->handleWidescreenPreference($request);

        $subtitle = $this->buildSubtitle($params->type, $params->subtype, $graph->getSubtitle());

        [$subtypeOptions, $subtypeSelected] = $this->subtypeNavigationOptions($request);

        ['width' => $graphWidth, 'height' => $graphHeight, 'thumbWidth' => $thumbWidth] = $this->graphDimensions($request);
        $width = $graphWidth;
        $height = $graphHeight;
        $mainGraphVars = $request->toVars(['height' => $height, 'width' => $width]);

        return view('graphs.show', [
            'device' => $device,
            'port' => $port,
            'subtitle' => $subtitle,
            'pageTitle' => $this->entityTitle($device, $port) . $subtitle,
            'subtypeOptions' => $subtypeOptions,
            'subtypeSelected' => $subtypeSelected,
            'periodThumbs' => $this->periodThumbnails($request, $params->from, $params->ends_now ? null : $params->to, $thumbWidth),
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
    private function handleWidescreenPreference(GraphRequest $request): void
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
    private function subtypeNavigationOptions(GraphRequest $request): array
    {
        $type = $request->graphParams()->type;
        $graphSubtypes = in_array($type, ['sensor', 'wireless'], true) ? [] : Graph::getSubtypes($type, $request->graph()->getDevice());

        if (count($graphSubtypes) <= 1) {
            return [[], null];
        }

        return [
            array_map(fn ($availType) => [
                'value' => $this->graphUrl($request, ['type' => "{$type}_{$availType}"]),
                'text' => StringHelpers::niceCase($availType),
            ], $graphSubtypes),
            $this->graphUrl($request, ['type' => "{$type}_{$request->graphParams()->subtype}"]),
        ];
    }

    /**
     * Build the thumbnail navigation entries for each period.
     *
     * @return array<int, array<string, mixed>>
     */
    private function periodThumbnails(GraphRequest $request, int $from, ?int $to, int $thumbWidth): array
    {
        $now = (int) LibrenmsConfig::get('time.now');
        $currentDuration = ($to ?? $now) - $from;
        $periodThumbs = [];

        foreach (LibrenmsConfig::get('graphs.row.normal') as $period => $text) {
            $periodFrom = (int) LibrenmsConfig::get("time.$period");
            $periodDuration = $now - $periodFrom;
            $periodThumbs[] = [
                'text' => __("settings.settings.graphs.row.normal.options.$period"),
                'active' => $to === null && $periodDuration > 0 && abs($currentDuration - $periodDuration) <= 0.05 * $periodDuration,
                'link' => $this->graphUrl($request, ['from' => Time::toRelativeOffset($periodDuration), 'to' => null]),
                'vars' => $request->toVars([
                    'height' => '90',
                    'width' => (int) round($thumbWidth * 1.5),
                    'legend' => 'no',
                    'absolute' => 1,
                    'from' => $periodFrom,
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
    private function controlToggles(GraphRequest $request, string $fullType, bool $showCommand): array
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
    private function buildSubtitle(string $type, string $subtype, ?string $graphSubtitle): string
    {
        if ($graphSubtitle) {
            return $graphSubtitle;
        }

        if (LibrenmsConfig::has("graph_types.$type.$subtype.descr")) {
            return ' :: ' . LibrenmsConfig::get("graph_types.$type.$subtype.descr");
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
    private function graphDimensions(GraphRequest $request): array
    {
        if ($request->session()->get('widescreen')) {
            $width = 1700;
            $thumbWidth = 180;
        } else {
            $width = 1075;
            $thumbWidth = 113;
        }

        if ($screenWidth = $request->session()->get('screen_width')) {
            // Render at the full screen width so the image is always at least as wide as
            // its (narrower) container; CSS then only ever scales it down, never up (blur).
            $width = (int) $screenWidth;
        }

        $height = LibrenmsConfig::get('webui.min_graph_height');
        if ($screenHeight = $request->session()->get('screen_height')) {
            $height = $screenHeight > 960
                ? (int) ($screenHeight - ($screenHeight / 2))
                : (int) max($height, $screenHeight - ($screenHeight / 1.5));
        }

        // Keep the graph landscape
        $height = min($height, $width);

        return ['width' => $width, 'height' => $height, 'thumbWidth' => $thumbWidth];
    }

    /**
     * @param  array<string, mixed>  $graphVars
     */
    private function renderRrdCommand(array $graphVars): ?string
    {
        try {
            $graph = app(GraphFactory::class)->graphFor(new GraphParameters($graphVars));
            if (! $graph instanceof RrdGraphInterface) {
                return null;
            }

            $rrd_options = $graph->getRrdCommandOptions();

            // rrd file names are relative to the rrd directory
            return 'cd ' . escapeshellarg((string) LibrenmsConfig::get('rrd_dir')) . ' && ' . implode(' ', array_map(escapeshellarg(...), [
                'rrdtool',
                'graph',
                '-',
                ...$rrd_options,
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
    private function graphUrl(GraphRequest $request, array $changes = []): string
    {
        $params = array_merge(
            $request->except(['page', 'username', 'password', 'width', 'height']),
            $changes
        );

        return route('graphs', array_filter($params, fn ($v) => $v !== null));
    }
}
