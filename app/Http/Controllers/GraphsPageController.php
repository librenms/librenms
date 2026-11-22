<?php

namespace App\Http\Controllers;

use App\Data\Graphing\GraphParameters;
use App\Data\Graphing\GraphViewOptions;
use App\Facades\LibrenmsConfig;
use App\Graphs\AbstractSeriesGraph;
use App\Graphs\GraphFactory;
use App\Http\Requests\GraphRequest;
use App\Models\Device;
use App\Models\Port;
use App\Models\UserPref;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use LibreNMS\Applications\ApplicationDefinition;
use LibreNMS\Util\Graph;
use LibreNMS\Util\StringHelpers;
use LibreNMS\Util\Time;

class GraphsPageController extends Controller
{
    /** js draws in the browser, anything else is an image */
    private const string RENDERER_PREFERENCE = 'graph_renderer';
    private const string OPTIONS_PREFERENCE = 'graph_options';
    /** the previous parameter: what the graph drawn in the browser compares with */
    private const array COMPARE = ['yes' => 'period', 'day' => 'day', 'week' => 'week'];
    /** how a graph drawn in the browser is viewed, not what it shows */
    private const array VIEW_PARAMETERS = [...GraphViewOptions::ADDRESS, 'previous', 'trend', 'percentile_lines'];

    public function __invoke(GraphRequest $request): View
    {
        $params = $request->graphParams();
        $context = $request->graph()->getPageContext();
        $device = $context->device;
        $port = $context->port;
        $fullType = "{$params->type}_$params->subtype";
        $canDrawInBrowser = $request->graph() instanceof AbstractSeriesGraph;
        $drawInBrowser = $canDrawInBrowser && UserPref::getPref($request->user(), self::RENDERER_PREFERENCE) === 'js';
        $showCommand = ! $drawInBrowser && $request->input('showcommand') === 'yes';
        $isDynamicGraph = ! $drawInBrowser && LibrenmsConfig::get('webui.dynamic_graphs', false) === true;

        $this->handleWidescreenPreference($request);

        $subtitle = $this->buildSubtitle($params->type, $params->subtype, $context->subtitle);

        [$subtypeOptions, $subtypeSelected] = $this->subtypeNavigationOptions($request);

        ['width' => $width, 'height' => $height, 'thumbWidth' => $thumbWidth] = $this->graphDimensions($request);
        $mainGraphVars = $request->toVars(['height' => $height, 'width' => $width]);
        $entityTitle = $this->entityTitle($device, $port);
        $dataUrl = $canDrawInBrowser
            ? route('graph.data', $request->toVars(array_fill_keys(['width', 'height', 'from', 'to', ...self::VIEW_PARAMETERS], null)))
            : null;

        return view('graphs.show', [
            'device' => $device,
            'port' => $port,
            'subtitle' => $subtitle,
            'pageTitle' => $entityTitle . $subtitle,
            'subtypeOptions' => $subtypeOptions,
            'subtypeSelected' => $subtypeSelected,
            'periodThumbs' => $this->periodThumbnails($request, $thumbWidth),
            'imageOptions' => $drawInBrowser ? [] : $this->imageOptions($request, $fullType, $showCommand),
            'trendHint' => ! $drawInBrowser && ($fullType === 'port_bits' || str_contains($fullType, 'sensor_')),
            'canDrawInBrowser' => $canDrawInBrowser,
            'drawInBrowser' => $drawInBrowser,
            'graphPage' => [
                'interactive' => $drawInBrowser,
                'range' => $params->ends_now ? ['last' => $params->period] : ['from' => $params->from, 'to' => $params->to],
                'options' => GraphViewOptions::fromPreference(UserPref::getPref($request->user(), self::OPTIONS_PREFERENCE))->withAddress($request->query()),
                'view' => [
                    'compare' => self::COMPARE[$request->input('previous')] ?? null,
                    'trend' => $request->boolean('trend'),
                    'percentile_lines' => $request->boolean('percentile_lines', true),
                ],
                'fileName' => Str::slug(str_replace('.', ' ', "$entityTitle $fullType")) ?: 'graph',
                'dataUrl' => $dataUrl,
                'preferencesUrl' => route('preferences.store'),
                'rendererPreference' => self::RENDERER_PREFERENCE,
                'optionsPreference' => self::OPTIONS_PREFERENCE,
                'viewDefaults' => [...GraphViewOptions::addressDefaults(), 'compare' => null, 'trend' => false, 'percentile_lines' => true],
                'preferenceDefaults' => GraphViewOptions::preferenceDefaults(),
                'lang' => ['copied' => __('graph.link_copied'), 'failed' => __('graph.link_not_copied')],
            ],
            'graphFrom' => $request->input('from') ?? '-1d',
            'graphTo' => $request->input('to'),
            'mainGraphVars' => $mainGraphVars,
            'graphDescr' => LibrenmsConfig::get("graph_descr.$fullType"),
            'showCommand' => $showCommand,
            'rrdCommand' => $showCommand ? $this->renderRrdCommand($mainGraphVars) : null,
            'isDynamicGraph' => $isDynamicGraph,
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
        $graphSubtypes = in_array($type, ['sensor', 'wireless'], true) ? [] : Graph::getSubtypes($type, $request->graph()->getPageContext()->device);

        if (count($graphSubtypes) <= 1) {
            return [[], null];
        }

        return [
            array_map(fn ($availType) => [
                'value' => $this->graphUrl($request, ['type' => "{$type}_{$availType}"]),
                'text' => $this->subtypeLabel($type, $availType),
            ], $graphSubtypes),
            $this->graphUrl($request, ['type' => "{$type}_{$request->graphParams()->subtype}"]),
        ];
    }

    /**
     * Build the thumbnail navigation entries for each period.
     *
     * @return array<int, array<string, mixed>>
     */
    private function periodThumbnails(GraphRequest $request, int $thumbWidth): array
    {
        $now = (int) LibrenmsConfig::get('time.now');
        $params = $request->graphParams();
        $currentDuration = $now - $params->from;
        $periodThumbs = [];

        foreach (LibrenmsConfig::get('graphs.row.normal') as $period => $text) {
            $periodFrom = (int) LibrenmsConfig::get("time.$period");
            $periodDuration = $now - $periodFrom;
            $periodThumbs[] = [
                'text' => __("settings.settings.graphs.row.normal.options.$period"),
                'period' => $periodDuration,
                'active' => $params->ends_now && $periodDuration > 0 && abs($currentDuration - $periodDuration) <= 0.05 * $periodDuration,
                'link' => $this->graphUrl($request, ['from' => Time::toRelativeOffset($periodDuration), 'to' => null]),
                'vars' => $request->toVars([
                    'height' => '90',
                    'width' => (int) round($thumbWidth * 1.5),
                    'legend' => 'no',
                    'absolute' => 1,
                    'from' => $periodFrom,
                    'to' => null, // a period is up to now, whatever range is shown
                ]),
            ];
        }

        return $periodThumbs;
    }

    /**
     * Options of the image graph, each changes the address and draws it again.
     *
     * @return array<int, array{label: string, checked: bool, link: string}>
     */
    private function imageOptions(GraphRequest $request, string $fullType, bool $showCommand): array
    {
        $legend = $request->input('legend') !== 'no';
        $previous = $request->input('previous') === 'yes';
        $options = [
            ['label' => __('graph.options.legend'), 'checked' => $legend, 'link' => $this->graphUrl($request, ['legend' => $legend ? 'no' : null])],
            ['label' => __('graph.options.previous'), 'checked' => $previous, 'link' => $this->graphUrl($request, ['previous' => $previous ? null : 'yes'])],
            ['label' => __('graph.options.command'), 'checked' => $showCommand, 'link' => $this->graphUrl($request, ['showcommand' => $showCommand ? null : 'yes'])],
        ];

        if ($fullType === 'port_bits') {
            $speedZoom = $request->boolean('port_speed_zoom', (bool) LibrenmsConfig::get('graphs.port_speed_zoom'));
            $options[] = ['label' => __('graph.options.port_speed'), 'checked' => $speedZoom, 'link' => $this->graphUrl($request, ['port_speed_zoom' => $speedZoom ? 0 : 1])];
        }

        return $options;
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

        return ' :: ' . $this->subtypeLabel($type, $subtype);
    }

    private function subtypeLabel(string $type, string $subtype): string
    {
        return $type === 'application'
            ? ApplicationDefinition::forType($subtype)->displayName()
            : StringHelpers::niceCase($subtype);
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
            return app(GraphFactory::class)->graphFor(new GraphParameters($graphVars))->getDebugCommand();
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
