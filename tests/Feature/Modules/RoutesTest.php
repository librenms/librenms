<?php

namespace LibreNMS\Tests\Feature\Modules;

use App\Models\Device;
use App\Models\Route;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Event;
use LibreNMS\Data\Source\Snmp\SnmpQueryBuilder;
use LibreNMS\Data\Source\Snmp\SnmpResponse;
use LibreNMS\Interfaces\Discovery\RouteDiscovery;
use LibreNMS\Modules\Routes;
use LibreNMS\OS;
use LibreNMS\Tests\DBTestCase;
use Mockery;

final class RoutesTest extends DBTestCase
{
    use DatabaseTransactions;

    private Device $device;

    protected function setUp(): void
    {
        parent::setUp();
        $this->device = Device::factory()->create(['os' => 'generic']);
        $this->app->bind(SnmpQueryBuilder::class, function () {
            $query = Mockery::mock(SnmpQueryBuilder::class);
            $query->shouldReceive('hideMib')->andReturnSelf();
            $query->shouldReceive('walk', 'get')->andReturnUsing(fn () => new SnmpResponse([]));

            return $query;
        });
    }

    public function testObservedRoutesUpdateInPlaceWhileHistoryKeepsItsLastSeenTime(): void
    {
        $this->travelTo(Carbon::parse('2026-01-01 00:00:00'));
        $this->discover([$this->route(), $this->route(['inetCidrRouteDest' => '10.1.0.0'])]);
        $entries = $this->device->routes()->get()->keyBy('inetCidrRouteDest');
        $original = $entries['10.0.0.0'];
        $historical = $entries['10.1.0.0'];

        $this->travelTo(Carbon::parse('2026-01-02 00:00:00'));
        $this->discover([$this->route(['inetCidrRouteMetric1' => 123, 'port_id' => 42])]);

        $entries = $this->device->routes()->get()->keyBy('inetCidrRouteDest');
        $this->assertCount(2, $entries);
        $this->assertSame($original->route_id, $entries['10.0.0.0']->route_id);
        $this->assertSame(123, $entries['10.0.0.0']->inetCidrRouteMetric1);
        $this->assertSame(42, $entries['10.0.0.0']->port_id);
        $this->assertTrue($original->created_at->equalTo($entries['10.0.0.0']->created_at));
        $this->assertSame('2026-01-02 00:00:00', $entries['10.0.0.0']->updated_at->toDateTimeString());
        $this->assertSame($historical->getAttributes(), $entries['10.1.0.0']->getAttributes());

        $this->travelTo(Carbon::parse('2026-01-03 00:00:00'));
        $this->discover([$this->route(['inetCidrRouteMetric1' => 123, 'port_id' => 42])]);
        $this->assertSame('2026-01-03 00:00:00', $original->fresh()->updated_at->toDateTimeString());
    }

    public function testCompositeKeysDistinguishContextsInterfacesHopsAndPrefixes(): void
    {
        $this->discover([
            $this->route(),
            $this->route(['context_name' => 'vrf-blue']),
            $this->route(['inetCidrRouteIfIndex' => 11]),
            $this->route(['inetCidrRouteNextHop' => '192.0.2.2']),
            $this->route(['inetCidrRoutePfxLen' => 25]),
        ]);
        $ids = $this->device->routes()->orderBy('route_id')->pluck('route_id')->all();

        $this->discover([$this->route(['context_name' => 'vrf-blue', 'inetCidrRouteMetric1' => 222])]);

        $this->assertCount(5, $ids);
        $this->assertSame($ids, $this->device->routes()->orderBy('route_id')->pluck('route_id')->all());
        $this->assertSame(222, $this->device->routes()->where('context_name', 'vrf-blue')->sole()->inetCidrRouteMetric1);
        $this->assertSame(4, $this->device->routes()->where('inetCidrRouteMetric1', 1)->count());
    }

    public function testEmptyOrInvalidDiscoveryDoesNotTouchHistory(): void
    {
        $this->travelTo(Carbon::parse('2026-01-01 00:00:00'));
        $this->discover([$this->route()]);
        $original = $this->device->routes()->sole()->getAttributes();
        $this->travelTo(Carbon::parse('2026-01-02 00:00:00'));
        $this->discover([]);
        $this->discover([$this->route(['inetCidrRouteDest' => '']), $this->route(['inetCidrRouteDest' => 'invalid'])]);

        $this->assertSame($original, $this->device->routes()->sole()->getAttributes());
    }

    public function testDuplicatesAreCollapsedAndTheLastDiscoveredRouteWins(): void
    {
        $this->discover([$this->route()]);
        $original = $this->device->routes()->sole();
        $original->replicate()->save();

        $this->discover([$this->route(['inetCidrRouteMetric1' => 20]), $this->route(['inetCidrRouteMetric1' => 30])]);

        $entry = $this->device->routes()->sole();
        $this->assertSame($original->route_id, $entry->route_id);
        $this->assertSame(30, $entry->inetCidrRouteMetric1);
    }

    public function testLegacyNullContextMatchesTheDefaultContext(): void
    {
        $route = $this->route(['device_id' => $this->device->device_id, 'port_id' => 0, 'context_name' => null]);
        $route->save();

        $this->discover([$this->route()]);

        $entry = $this->device->routes()->sole();
        $this->assertSame($route->route_id, $entry->route_id);
        $this->assertSame('', $entry->context_name);
    }

    public function testNormalizationScopeAndExplicitWritableColumns(): void
    {
        $other = Device::factory()->create();
        $foreign = $this->route(['device_id' => $other->device_id, 'port_id' => 0]);
        $foreign->save();
        $this->travelTo(Carbon::parse('2026-01-02 00:00:00'));
        $this->discover([$this->route([
            'device_id' => $other->device_id,
            'route_id' => $foreign->route_id,
            'created_at' => '2020-01-01 00:00:00',
            'context_name' => ' "blue" ',
            'inetCidrRouteMetric1' => -1,
            'inetCidrRouteNextHopAS' => -1,
        ])]);

        $entry = $this->device->routes()->sole();
        $this->assertSame('blue', $entry->context_name);
        $this->assertSame(0, $entry->port_id);
        $this->assertSame(0, $entry->inetCidrRouteMetric1);
        $this->assertSame(4294967295, $entry->inetCidrRouteNextHopAS);
        $this->assertSame('2026-01-02 00:00:00', $entry->created_at->toDateTimeString());
        $this->assertNotSame($foreign->route_id, $entry->route_id);
        $this->assertEquals($foreign->getAttributes(), $foreign->fresh()->getAttributes());
    }

    public function testPersistenceDoesNotDispatchEloquentEvents(): void
    {
        $events = array_map(fn ($event) => "eloquent.$event: " . Route::class, ['saving', 'saved', 'creating', 'created', 'updating', 'updated', 'deleting', 'deleted']);
        Event::fake($events);

        $this->discover([$this->route()]);
        $this->discover([$this->route(['inetCidrRouteMetric1' => 2])]);

        foreach ($events as $event) {
            Event::assertNotDispatched($event);
        }
        $this->assertSame(2, $this->device->routes()->sole()->inetCidrRouteMetric1);
    }

    public function testVariedUpdatesUseBatchesAndPreserveRowIds(): void
    {
        $routes = [];
        for ($i = 1; $i <= 1005; $i++) {
            $routes[] = $this->route(['inetCidrRouteDest' => long2ip(ip2long('10.0.0.0') + $i), 'inetCidrRouteMetric1' => $i]);
        }
        $this->discover($routes);
        $ids = $this->device->routes()->orderBy('route_id')->pluck('route_id')->all();
        foreach ($routes as $route) {
            $route->inetCidrRouteMetric1++;
            $route->inetCidrRoutePolicy = "quoted' ? policy";
        }

        $db = DB::connection();
        $db->enableQueryLog();
        $db->flushQueryLog();
        try {
            $this->discover($routes);
            $this->assertCount(4, $db->getQueryLog()); // two reads and two updates
        } finally {
            $db->disableQueryLog();
        }

        $this->assertSame($ids, $this->device->routes()->orderBy('route_id')->pluck('route_id')->all());
        $this->assertSame(2, $this->device->routes()->where('inetCidrRouteDest', '10.0.0.1')->sole()->inetCidrRouteMetric1);
        $this->assertSame(1006, $this->device->routes()->where('inetCidrRouteDest', long2ip(ip2long('10.0.0.0') + 1005))->sole()->inetCidrRouteMetric1);
        $this->assertSame(1005, $this->device->routes()->where('inetCidrRoutePolicy', "quoted' ? policy")->count());
    }

    /** @param array<string, int|string|null> $attributes */
    private function route(array $attributes = []): Route
    {
        return Route::factory()->make(array_replace([
            'context_name' => '',
            'inetCidrRouteDest' => '10.0.0.0',
            'inetCidrRouteIfIndex' => 10,
            'inetCidrRouteNextHop' => '192.0.2.1',
            'inetCidrRoutePfxLen' => 24,
            'inetCidrRouteMetric1' => 1,
            'inetCidrRouteType' => 4,
            'inetCidrRouteProto' => 3,
            'inetCidrRouteNextHopAS' => 0,
        ], $attributes));
    }

    /** @param list<Route> $routes */
    private function discover(array $routes): void
    {
        $os = Mockery::mock(OS::class, RouteDiscovery::class);
        $os->shouldReceive('getDevice')->andReturn($this->device);
        $os->shouldReceive('discoverRoutes')->andReturn(new Collection($routes));

        (new Routes)->discover($os);
    }
}
