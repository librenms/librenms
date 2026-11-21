<?php

namespace LibreNMS\Tests\Feature\Http;

use App\Data\Graphing\GraphViewOptions;
use App\Facades\LibrenmsConfig;
use App\Facades\Rrd;
use App\Http\Requests\GraphRequest;
use App\Models\Device;
use App\Models\Port;
use App\Models\User;
use App\Models\UserPref;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Js;
use LibreNMS\Tests\TestCase;
use PHPUnit\Framework\Attributes\PreserveGlobalState;
use PHPUnit\Framework\Attributes\RunInSeparateProcess;
use Spatie\Permission\Models\Role;

class GraphsPageControllerTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        Role::findOrCreate('admin');
        LibrenmsConfig::set('auth_mechanism', 'mysql');
        LibrenmsConfig::set('webui.dynamic_graphs', false);
    }

    public function testAuthenticatedDeviceGraphPageRenders(): void
    {
        $device = Device::factory()->create(['hostname' => 'graph-device.example.com']);

        $response = $this->actingAs($this->adminUser())
            ->get("/graphs?device={$device->device_id}&type=device_poller_perf");

        $response->assertOk();
        $response->assertSee('Poller Time');
        $response->assertSee('graph?device=' . $device->device_id, false);
        $this->assertNoPhpWarningOutput($response->getContent());
    }

    public function testGraphPageIgnoresWidthAndHeightFromUrlInput(): void
    {
        $device = Device::factory()->create(['hostname' => 'graph-device.example.com']);

        $response = $this->actingAs($this->adminUser())
            ->get("/graphs?device={$device->device_id}&type=device_poller_perf&width=2000&height=1000");

        $response->assertOk();
        $response->assertDontSee('width=2000');
        $response->assertDontSee('height=1000');
        $response->assertSee('width=1075');
        $response->assertSee('height=300');
    }

    public function testDateSelectorUsesSelectedRange(): void
    {
        $device = Device::factory()->create();

        $response = $this->actingAs($this->adminUser())
            ->get("/graphs?device={$device->device_id}&type=device_poller_perf&from=1700000000&to=1700003600");

        $response->assertOk();
        $response->assertSee('x-data="dateRangePicker"', false);
        $response->assertSee('data-start="1700000000"', false);
        $response->assertSee('data-end="1700003600"', false);
        $this->assertNoPhpWarningOutput($response->getContent());
    }

    public function testDateSelectorQueryRangeOverridesLegacyPathRange(): void
    {
        $device = Device::factory()->create();

        $response = $this->actingAs($this->adminUser())
            ->get("/graphs/to=1700003600/id={$device->device_id}/type=device_poller_perf/from=1700000000/?from=1700007200&to=1700010800");

        $response->assertOk();
        $response->assertSee('data-start="1700007200"', false);
        $response->assertSee('data-end="1700010800"', false);
        $this->assertNoPhpWarningOutput($response->getContent());
    }

    public function testShowCommandUsesGraphVars(): void
    {
        Rrd::partialMock()->shouldReceive('checkRrdExists')->andReturn(true);
        $device = Device::factory()->create(['hostname' => 'port-device.example.com']);
        $port = Port::factory()->for($device)->create([
            'ifName' => 'GigabitEthernet0/1',
            'ifDescr' => 'GigabitEthernet0/1',
            'ifAlias' => 'uplink',
            'ifSpeed' => 1000000000,
        ]);

        $response = $this->actingAs($this->adminUser())
            ->get("/graphs?id={$port->port_id}&type=port_bits&showcommand=yes");

        $response->assertOk();
        $response->assertSee('RRDTool Command');
        $response->assertSee('port-id' . $port->port_id . '.rrd', false);
        $this->assertNoPhpWarningOutput($response->getContent());
    }

    public function testDeviceShowCommand(): void
    {
        $device = Device::factory()->create();

        $response = $this->actingAs($this->adminUser())
            ->get("/graphs?id={$device->device_id}&type=device_poller_perf&showcommand=yes");

        $response->assertOk();
        $response->assertSee('RRDTool Command');
        $this->assertNoPhpWarningOutput($response->getContent());
    }

    public function testShowCommandWorksFromAnyWorkingDirectory(): void
    {
        // Regression: legacy graph templates use relative requires, so the controller must run
        // them from the install root. Without that, "Show RRD Command" 500s when the request's
        // working directory isn't the install dir (e.g. served via the html/ front controller).
        $device = Device::factory()->create();
        $port = Port::factory()->for($device)->create([
            'ifName' => 'GigabitEthernet0/2',
            'ifDescr' => 'GigabitEthernet0/2',
            'ifSpeed' => 1000000000,
        ]);

        $originalCwd = getcwd();
        chdir(sys_get_temp_dir());

        try {
            $response = $this->actingAs($this->adminUser())
                ->get("/graphs?id={$port->port_id}&type=port_nupkts&showcommand=yes");

            $response->assertOk();
            $response->assertSee('RRDTool Command');
            $this->assertNoPhpWarningOutput($response->getContent());
        } finally {
            if ($originalCwd !== false) {
                chdir($originalCwd);
            }
        }
    }

    #[RunInSeparateProcess]
    #[PreserveGlobalState(false)]
    public function testUnauthenticatedGraphPageRedirectsToLogin(): void
    {
        $device = Device::factory()->create();
        Auth::shouldUse('web');
        Auth::logout();
        Auth::forgetGuards();
        $this->flushSession();

        $this->get("/graphs?device={$device->device_id}&type=device_poller_perf")
            ->assertRedirect('/login');
    }

    public function testInvalidParameterKeyReturnsValidationError(): void
    {
        $device = Device::factory()->create();

        $response = $this->actingAs($this->adminUser())
            ->get("/graphs?device={$device->device_id}&type=device_poller_perf&invalid<key>=1");

        $response->assertSessionHasErrors();
    }

    public function testInvalidParameterValueReturnsValidationError(): void
    {
        $device = Device::factory()->create();

        $response = $this->actingAs($this->adminUser())
            ->get("/graphs?device={$device->device_id}&type=device_poller_perf&legend=invalid;value");

        $response->assertSessionHasErrors();
    }

    public function testInvalidGraphTypeReturnsForbidden(): void
    {
        $device = Device::factory()->create();

        $response = $this->actingAs($this->adminUser())
            ->get("/graphs?device={$device->device_id}&type=invalid_type");

        $response->assertForbidden();
    }

    public function testCommaSeparatedIdIsAcceptedByValidation(): void
    {
        $device = Device::factory()->create();
        $secondDevice = Device::factory()->create();

        $request = GraphRequest::create("/graphs?id={$device->device_id},{$secondDevice->device_id}&type=device_poller_perf");
        $validator = validator($request->all(), new GraphRequest()->rules());

        $this->assertFalse($validator->errors()->has('id'));
    }

    public function testInvalidCommaSeparatedIdFailsValidation(): void
    {
        $device = Device::factory()->create();

        $request = GraphRequest::create("/graphs?id={$device->device_id},abc&type=device_poller_perf");
        $validator = validator($request->all(), new GraphRequest()->rules());

        $this->assertTrue($validator->errors()->has('id'));
    }

    public function testDynamicGraphsRendersCorrectly(): void
    {
        $device = Device::factory()->create(['hostname' => 'graph-device.example.com']);
        LibrenmsConfig::set('webui.dynamic_graphs', true);

        $response = $this->actingAs($this->adminUser())
            ->get("/graphs?device={$device->device_id}&type=device_poller_perf");

        $response->assertOk();
        $response->assertSee('class="graph graph-image img-responsive tw:w-full tw:h-auto tw:border-0"', false);
        $response->assertSee('from={{start}}', false);
        $response->assertSee('to={{end}}', false);
        $response->assertSee('width={{width}}', false);
    }

    public function testSeriesGraphsCanBeDrawnInTheBrowser(): void
    {
        $device = Device::factory()->create();
        $user = $this->adminUser();

        $response = $this->actingAs($user)->get("/graphs?device={$device->device_id}&type=device_netstat_ip");

        $response->assertOk();
        $response->assertSee(__('graph.interactive'));
        $response->assertDontSee('x-data="timeSeriesGraph', false);
        $response->assertSee('graph?device=' . $device->device_id, false);

        $this->actingAs($user)->postJson('/preferences', ['pref' => 'graph_renderer', 'value' => 'js'])->assertOk();
        $response = $this->actingAs($user)->get("/graphs?device={$device->device_id}&type=device_netstat_ip&from=1700000000&to=1700003600&showcommand=yes");

        $response->assertOk();
        $page = $response->viewData('graphPage');
        $this->assertTrue($page['interactive']);
        $this->assertSame(['from' => 1700000000, 'to' => 1700003600], $page['range']);
        $this->assertEquals(new GraphViewOptions, $page['options']);
        $this->assertSame(['compare' => null, 'trend' => false], $page['view']);
        $this->assertSame(route('graph.data', ['device' => $device->device_id, 'type' => 'device_netstat_ip', 'showcommand' => 'yes']), $page['dataUrl']);
        $response->assertSee('x-data="graphsPage(' . Js::from($page) . ')"', false);
        $response->assertSee('x-data="timeSeriesGraph(', false);
        $response->assertSee('data-reload="false"', false);
        $response->assertSee(__('graph.help.title'));
        $response->assertSee(__('graph.help.zoom_range'));
        $response->assertSee(__('graph.help.columns'));
        $response->assertDontSee(__('graph.help.trend'), false); // a graph drawn in the browser draws trends itself
        $response->assertDontSee('graph.help.'); // every help text is translated
        $response->assertDontSee('graph.options.');
        $response->assertSee("choose('scale', 'log')", false);
        $response->assertSee("setOption('line_style', 'linear')", false);
        $response->assertSee(__('graph.options.mirror'));
        $response->assertSee("choose('stacking', 'stack')", false);
        $response->assertSee(':disabled="! resettable()"', false);
        $response->assertSee(__('graph.copy_link'));
        $this->assertSame(['copied' => __('graph.link_copied'), 'failed' => __('graph.link_not_copied')], $page['lang']);
        $response->assertSee('popovertarget="graph-legend-columns-page-graph"', false); // the legend columns are chosen in the legend
        $response->assertSee('id="graph-legend-columns-page-graph"', false);
        $response->assertDontSee(__('graph.options.command'));
        $this->assertNoPhpWarningOutput($response->getContent());

        $this->actingAs($user)->postJson('/preferences', ['pref' => 'graph_renderer', 'value' => 'default'])->assertOk();
        $response = $this->actingAs($user)->get("/graphs?device={$device->device_id}&type=device_netstat_ip");
        $response->assertDontSee('x-data="timeSeriesGraph', false);
        $this->assertFalse($response->viewData('graphPage')['interactive']);
        $this->assertSame(['last' => 86400], $response->viewData('graphPage')['range']);
        $response->assertSee(__('graph.settings_preferences'));
        $response->assertDontSee(':disabled="! resettable()"', false); // an image has no zoom or size of its own to undo
        // the link of an image graph can be shared too
        $response->assertSee(__('graph.copy_link'));
        $response->assertSee('data-reload="true"', false);
        // the image graph has the same toolbar, its data can still be downloaded
        $response->assertSee(__('graph.zoom_in'));
        $response->assertSee(__('graph.export_csv'));
        $response->assertDontSee("choose('scale'", false);
        $response->assertDontSee("setOption('line_style'", false);
    }

    public function testGraphDrawnInTheBrowserStartsWithThePeriodUpToNow(): void
    {
        $device = Device::factory()->create();
        $user = $this->adminUser();
        $this->actingAs($user)->postJson('/preferences', ['pref' => 'graph_renderer', 'value' => 'js'])->assertOk();

        $response = $this->actingAs($user)->get("/graphs?device={$device->device_id}&type=device_netstat_ip&from=-2d");

        $response->assertOk();
        $response->assertSee('x-data="timeSeriesGraph(', false);
        $this->assertSame(['last' => 172800], $response->viewData('graphPage')['range']);
        $response->assertSee('showPeriod(172800, $event)', false);
    }

    public function testPeriodThumbnailsEndNowWhateverRangeIsShown(): void
    {
        $device = Device::factory()->create();

        $response = $this->actingAs($this->adminUser())
            ->get("/graphs?device={$device->device_id}&type=device_poller_perf&from=1700000000&to=1700003600");

        $response->assertOk();
        $this->assertMatchesRegularExpression('/src="[^"]*to=1700003600[^"]*"\s+fetchpriority="high"/', $response->getContent(), 'the main graph');
        preg_match_all('/src="([^"]*)"[^>]*fetchpriority="low"/', $response->getContent(), $thumbnails);
        $this->assertNotEmpty($thumbnails[1]);
        foreach ($thumbnails[1] as $src) {
            $this->assertStringNotContainsString('to=', $src);
        }
    }

    public function testGraphsDrawnByScriptsAreAlwaysImages(): void
    {
        $device = Device::factory()->create();
        $user = $this->adminUser();
        $this->actingAs($user)->postJson('/preferences', ['pref' => 'graph_renderer', 'value' => 'js'])->assertOk();

        $response = $this->actingAs($user)->get("/graphs?device={$device->device_id}&type=device_poller_perf");

        $response->assertOk();
        $response->assertDontSee(__('graph.interactive'));
        $response->assertDontSee(__('graph.settings_preferences'));
        $response->assertDontSee('x-data="timeSeriesGraph', false);
        $response->assertSee(__('graph.options.command'));
        $response->assertDontSee(__('graph.export_csv'));
        $this->assertMatchesRegularExpression('/src="[^"]*type=device_poller_perf[^"]*"\s+fetchpriority="high"/', $response->getContent(), 'the main graph is an image');
    }

    public function testGraphPreferencesAreSavedAndShapeTheGraph(): void
    {
        $device = Device::factory()->create();
        $user = $this->adminUser();
        $this->actingAs($user)->postJson('/preferences', ['pref' => 'graph_renderer', 'value' => 'js'])->assertOk();
        $saved = ['range' => false, 'line_style' => 'smooth', 'columns' => ['avg', 'p95'], 'width' => 0.5, 'height' => 400];

        $this->actingAs($user)->postJson('/preferences', ['pref' => 'graph_options', 'value' => $saved])->assertOk();
        $response = $this->actingAs($user)->get("/graphs?device={$device->device_id}&type=device_netstat_ip");

        $response->assertOk();
        $response->assertSee('x-data="timeSeriesGraph(', false);
        $page = $response->viewData('graphPage');
        $this->assertEquals(new GraphViewOptions(...$saved), $page['options']);
        $this->assertEquals(GraphViewOptions::preferenceDefaults(), $page['preferenceDefaults'], 'what restore goes back to');
        $this->assertSame([...GraphViewOptions::addressDefaults(), 'compare' => null, 'trend' => false], $page['viewDefaults'], 'what reset goes back to');
        $response->assertSee('style="width: 50%; height: 400px; min-height: 0; max-height: none; aspect-ratio: auto"', false);

        $this->actingAs($user)->postJson('/preferences', ['pref' => 'graph_options', 'value' => [...$saved, 'width' => null, 'height' => null]])->assertOk();
        $this->actingAs($user)->get("/graphs?device={$device->device_id}&type=device_netstat_ip")
            ->assertDontSee('aspect-ratio: auto"', false);

        // restored to the defaults
        $this->actingAs($user)->postJson('/preferences', ['pref' => 'graph_options', 'value' => 'default'])->assertOk();
        $this->assertNull(UserPref::getPref($user, 'graph_options'));
        $this->assertEquals(new GraphViewOptions, $this->actingAs($user)->get("/graphs?device={$device->device_id}&type=device_netstat_ip")->viewData('graphPage')['options']);
    }

    public function testAnAddressShowsTheGraphAsItWasShared(): void
    {
        $device = Device::factory()->create();
        $user = $this->adminUser();
        $this->actingAs($user)->postJson('/preferences', ['pref' => 'graph_renderer', 'value' => 'js'])->assertOk();
        $this->actingAs($user)->postJson('/preferences', ['pref' => 'graph_options', 'value' => ['line_style' => 'smooth', 'columns' => ['avg']]])->assertOk();

        $response = $this->actingAs($user)->get("/graphs?device={$device->device_id}&type=device_netstat_ip&stacking=overlay&scale=log&previous=week&trend=1");

        $response->assertOk();
        // The address shows log scaled overlays, the viewer keeps their lines and legend.
        $shared = new GraphViewOptions(stacking: 'overlay', scale: 'log', columns: ['avg'], line_style: 'smooth');
        $page = $response->viewData('graphPage');
        $this->assertEquals($shared, $page['options']);
        $this->assertSame(['compare' => 'week', 'trend' => true], $page['view']);
        $this->assertSame(route('graph.data', ['device' => $device->device_id, 'type' => 'device_netstat_ip']), $page['dataUrl']);

        // nothing of the shared graph is kept for the next one
        $response = $this->actingAs($user)->get("/graphs?device={$device->device_id}&type=device_netstat_ip");
        $this->assertEquals(new GraphViewOptions(columns: ['avg'], line_style: 'smooth'), $response->viewData('graphPage')['options']);
    }

    public function testViewParametersMustBeKnownValues(): void
    {
        $device = Device::factory()->create();
        $user = $this->adminUser();

        foreach (['previous=month', 'scale=cubic', 'stacking=sideways', 'mirror=2', 'trend=yes'] as $query) {
            [$key] = explode('=', $query);
            $this->actingAs($user)->get("/graphs?device={$device->device_id}&type=device_netstat_ip&$query")
                ->assertSessionHasErrors($key);
        }
        $this->actingAs($user)->get("/graphs?device={$device->device_id}&type=device_netstat_ip&previous=day&scale=fit&mirror=0&trend=1")
            ->assertOk();
    }

    public function testGraphOptionsMustBeKnownValues(): void
    {
        $user = $this->adminUser();

        foreach ([
            'not an array',
            ['unknown' => true],
            'stepped',
            ['line_style' => 'dashed'],
            ['range' => 'below'],
            // how one graph is shown is only in its address
            ['scale' => 'log'],
            ['stacking' => 'stack'],
            ['mirror' => false],
            ['columns' => ['median']],
            ['height' => 50],
            ['width' => 1.5, 'height' => 400],
        ] as $value) {
            $this->actingAs($user)->postJson('/preferences', ['pref' => 'graph_options', 'value' => $value])
                ->assertUnprocessable();
        }
    }

    public function testImageGraphOptionsChangeTheAddress(): void
    {
        $port = Port::factory()->for(Device::factory())->create();

        $response = $this->actingAs($this->adminUser())
            ->get("/graphs?type=port_bits&id={$port->port_id}&previous=yes");
        $response->assertSee(__('graph.help.trend')); // an image of traffic draws a trend into the future

        $response->assertOk();
        $response->assertViewHas('imageOptions', function (array $options): bool {
            $byLabel = array_column($options, null, 'label');
            $query = function (string $label) use ($byLabel): array {
                parse_str((string) parse_url($byLabel[$label]['link'], PHP_URL_QUERY), $query);

                return $query;
            };

            return $byLabel[__('graph.options.legend')]['checked'] && $query(__('graph.options.legend'))['legend'] === 'no'
                && $byLabel[__('graph.options.previous')]['checked'] && ! isset($query(__('graph.options.previous'))['previous'])
                && ! $byLabel[__('graph.options.command')]['checked'] && $query(__('graph.options.command'))['showcommand'] === 'yes'
                && isset($query(__('graph.options.port_speed'))['port_speed_zoom']);
        });
    }

    public function testSensorGraphPageUsesSubtitleFromAuthFile(): void
    {
        $device = Device::factory()->create(['hostname' => 'sensor-device.example.com']);
        $sensor = \App\Models\Sensor::factory()->for($device)->create([
            'sensor_descr' => 'Chassis Temp',
            'sensor_class' => 'temperature',
        ]);

        $response = $this->actingAs($this->adminUser())
            ->get("/graphs?id={$sensor->sensor_id}&type=sensor_temperature");

        $response->assertOk();
        $response->assertSee(' :: Sensor :: Chassis Temp');
        $this->assertNoPhpWarningOutput($response->getContent());
    }

    public function testMultiPortGraphPageUsesSubtitleFromAuthFile(): void
    {
        $device = Device::factory()->create();
        $port1 = Port::factory()->for($device)->create();
        $port2 = Port::factory()->for($device)->create();

        $response = $this->actingAs($this->adminUser())
            ->get("/graphs?id={$port1->port_id},{$port2->port_id}&type=multiport_bits");

        $response->assertOk();
        $response->assertSee('Multi Port');
        $this->assertNoPhpWarningOutput($response->getContent());
    }

    public function testCustomerGraphPageAcceptsCustomerName(): void
    {
        Port::factory()->for(Device::factory())->create([
            'port_descr_type' => 'cust',
            'port_descr_descr' => 'Café & Co (EU)',
        ]);

        $response = $this->actingAs($this->adminUser())
            ->get('/graphs?type=customer_bits&customer=' . urlencode('Café & Co (EU)'));

        $response->assertOk();
        $response->assertSee('Customer :: Café & Co (EU)');
        $this->assertNoPhpWarningOutput($response->getContent());
    }

    public function testDeviceGraphPageUsesDefaultSubtitleWhenNoTitleInAuthFile(): void
    {
        $device = Device::factory()->create(['hostname' => 'perf-device.example.com']);

        $response = $this->actingAs($this->adminUser())
            ->get("/graphs?device={$device->device_id}&type=device_poller_perf");

        $response->assertOk();
        $response->assertSee(' :: Poller Time');
        $this->assertNoPhpWarningOutput($response->getContent());
    }

    public function testCustomoidGraphPageRenders(): void
    {
        $device = Device::factory()->create(['hostname' => 'customoid-device.example.com']);
        \App\Models\Customoid::create([
            'device_id' => $device->device_id,
            'customoid_descr' => 'Test OID',
            'customoid_oid' => '.1.3.6.1.2.1.1.1.0',
            'customoid_unit' => '%',
        ]);

        // The device Graphs tab links to the full-size page as
        // type=customoid_<descr>&device=<id>&unit=<unit>. A description with
        // a space and a unit like % must pass page validation.
        $response = $this->actingAs($this->adminUser())
            ->get('/graphs?type=customoid_Test%20OID&device=' . $device->device_id . '&unit=%25');

        $response->assertOk();
        $response->assertSee(' :: Custom OID ');
        $this->assertNoPhpWarningOutput($response->getContent());
    }

    public function testCustomoidGraphPageRendersWithSpecialUnit(): void
    {
        $device = Device::factory()->create(['hostname' => 'customoid-device.example.com']);
        \App\Models\Customoid::create([
            'device_id' => $device->device_id,
            'customoid_descr' => 'Temperature',
            'customoid_oid' => '.1.3.6.1.2.1.1.1.0',
            'customoid_unit' => '°C',
        ]);

        $response = $this->actingAs($this->adminUser())
            ->get('/graphs?type=customoid_Temperature&device=' . $device->device_id . '&unit=%C2%B0C');

        $response->assertOk();
        $response->assertSee(' :: Custom OID ');
        $this->assertNoPhpWarningOutput($response->getContent());
    }

    public function testBareGraphTypeStillForbidden(): void
    {
        $response = $this->actingAs($this->adminUser())
            ->get('/graphs?type=port&id=1');

        $response->assertForbidden();
    }

    private function adminUser(): User
    {
        return User::factory()->create(['enabled' => 1])
            ->assignRole('admin');
    }

    private function assertNoPhpWarningOutput(string $content): void
    {
        $this->assertStringNotContainsString('Undefined variable', $content);
        $this->assertStringNotContainsString('Warning', $content);
        $this->assertStringNotContainsString('Notice', $content);
    }
}
