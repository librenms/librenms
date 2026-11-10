<?php

namespace LibreNMS\Tests\Feature\Http;

use App\Facades\LibrenmsConfig;
use App\Graphs\Rrd\RrdFileResolver;
use App\Models\Bill;
use App\Models\Device;
use App\Models\User;
use ArrayObject;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\URL;
use LibreNMS\RRD\Backend\RrdBackendInterface;
use LibreNMS\Tests\TestCase;
use Mockery;
use Spatie\Permission\Models\Role;

class GraphControllerTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        Role::findOrCreate('admin');
        LibrenmsConfig::set('auth_mechanism', 'mysql');
        LibrenmsConfig::set('webui.graph_type', 'png');
    }

    public function testRendersImage(): void
    {
        $bill = Bill::factory()->create();

        $response = $this->actingAs($this->adminUser())
            ->get("/graph?type=bill_historicmonthly&id=$bill->bill_id&width=600&height=250");

        $response->assertOk();
        $response->assertHeader('Content-Type', 'image/png');
        $this->assertStringStartsWith("\x89PNG", $response->getContent());
    }

    public function testUserTitleMayContainPunctuation(): void
    {
        $bill = Bill::factory()->create();

        $response = $this->actingAs($this->adminUser())
            ->get("/graph?type=bill_historicmonthly&id=$bill->bill_id&graph_title=" . urlencode("Bob's (bill)"));

        $response->assertOk();
        $response->assertHeader('Content-Type', 'image/png');
    }

    public function testUnknownGraphRespondsLikeUnauthorized(): void
    {
        $device = Device::factory()->create();

        $unknown = $this->actingAs($this->adminUser())->get("/graph?type=device_doesnotexist&device=$device->device_id");
        $unauthorized = $this->actingAs(User::factory()->create(['enabled' => 1]))->get("/graph?type=device_processor&device=$device->device_id");

        foreach ([$unknown, $unauthorized] as $response) {
            $response->assertForbidden();
            $response->assertHeader('Content-Type', 'image/png');
        }
        $this->assertSame($unknown->getContent(), $unauthorized->getContent());
    }

    public function testInvalidInputRespondsWithErrorImage(): void
    {
        $device = Device::factory()->create();

        $response = $this->actingAs($this->adminUser())
            ->get("/graph?type=device_processor&device=$device->device_id&width=abc");

        $response->assertUnprocessable();
        $response->assertHeader('Content-Type', 'image/png');
    }

    public function testGraphErrorRespondsWithErrorImage(): void
    {
        $device = Device::factory()->create();

        $response = $this->actingAs($this->adminUser())
            ->get("/graph?type=device_processor&device=$device->device_id");

        $response->assertServerError();
        $response->assertHeader('Content-Type', 'image/png');
    }

    public function testGraphSpecificValidationRespondsWithErrorImage(): void
    {
        $bill = Bill::factory()->create();

        $response = $this->actingAs($this->adminUser())
            ->get("/graph?type=bill_historictransfer&id=$bill->bill_id&imgtype=week");

        $response->assertUnprocessable();
        $response->assertHeader('Content-Type', 'image/png');
    }

    public function testArrayInputDoesNotBreakAuthorization(): void
    {
        $response = $this->actingAs($this->adminUser())->get('/graph?type=bill_historicmonthly&id[]=1');

        $response->assertForbidden();
        $response->assertHeader('Content-Type', 'image/png');
    }

    public function testLegacyAuthErrorsRespondLikeUnauthorized(): void
    {
        $response = $this->actingAs($this->adminUser())->get('/graph?type=bill_bits');

        $response->assertForbidden();
        $response->assertHeader('Content-Type', 'image/png');
    }

    public function testGraphSizeIsLimited(): void
    {
        $bill = Bill::factory()->create();

        $response = $this->actingAs($this->adminUser())
            ->get("/graph?type=bill_historicmonthly&id=$bill->bill_id&width=200000&height=200000");

        $response->assertUnprocessable();
        $response->assertHeader('Content-Type', 'image/png');
    }

    public function testCustomoidWithSpacesAndUnitSymbols(): void
    {
        $device = Device::factory()->create();
        $commands = $this->fakeRrdBackend();

        foreach (['%' => '%25', '°C' => '%C2%B0C'] as $unit => $encoded) {
            $response = $this->actingAs($this->adminUser())
                ->get("/graph?type=customoid_Test%20OID&device=$device->device_id&unit=$encoded");

            $response->assertOk();
            $this->assertSame('image-data', $response->getContent());
            $options = $commands[$commands->count() - 1];
            $this->assertNotEmpty(preg_grep('/^DEF:.*customoid-Test_OID\.rrd:oid_value:AVERAGE$/', $options));
            $this->assertNotEmpty(preg_grep('/^COMMENT:' . preg_quote($unit, '/') . ' +Now/', $options));
        }
    }

    public function testCustomoidUnitCannotAddRrdtoolEscapes(): void
    {
        $device = Device::factory()->create();
        $commands = $this->fakeRrdBackend();

        $this->actingAs($this->adminUser())
            ->get("/graph?type=customoid_Test&device=$device->device_id&unit=" . urlencode('a:b\\g'))
            ->assertOk();

        $this->assertNotEmpty(preg_grep('/^COMMENT:abg +Now/', $commands[0]));
    }

    public function testGuestsAreRefusedWithoutAnonymousGraphs(): void
    {
        $device = Device::factory()->create();
        $this->fakeRrdBackend();

        $this->get("/graph?type=device_ib_dns_performance&device=$device->device_id")->assertRedirect();
        $this->get("/graph?type=device_netstat_ip&device=$device->device_id")->assertRedirect();
    }

    public function testGuestsMayViewWhenAnonymousGraphsAreAllowed(): void
    {
        LibrenmsConfig::set('allow_unauth_graphs', true);
        $device = Device::factory()->create();
        $this->fakeRrdBackend();

        foreach (['device_ib_dns_performance', 'device_netstat_ip'] as $type) {
            $response = $this->get("/graph?type=$type&device=$device->device_id");

            $response->assertOk();
            $this->assertSame('image-data', $response->getContent());
        }
    }

    public function testGuestsMayViewFromAllowedNetworks(): void
    {
        LibrenmsConfig::set('allow_unauth_graphs_cidr', ['192.0.2.0/24']);
        $device = Device::factory()->create();
        $this->fakeRrdBackend();

        $this->withServerVariables(['REMOTE_ADDR' => '192.0.2.10'])
            ->get("/graph?type=device_netstat_ip&device=$device->device_id")
            ->assertOk();
        $this->withServerVariables(['REMOTE_ADDR' => '198.51.100.10'])
            ->get("/graph?type=device_netstat_ip&device=$device->device_id")
            ->assertRedirect();
    }

    public function testSignedUrlsAreTrusted(): void
    {
        $device = Device::factory()->create();
        $this->fakeRrdBackend();

        foreach (['device_ib_dns_performance', 'device_netstat_ip'] as $type) {
            $response = $this->get(URL::signedRoute('graph', ['type' => $type, 'device' => $device->device_id]));

            $response->assertOk();
            $this->assertSame('image-data', $response->getContent());
        }
    }

    public function testUsersWithoutPermissionAreRefused(): void
    {
        $device = Device::factory()->create();
        LibrenmsConfig::set('allow_unauth_graphs', true);
        $this->fakeRrdBackend();

        foreach (['device_ib_dns_performance', 'device_netstat_ip'] as $type) {
            $this->actingAs(User::factory()->create(['enabled' => 1]))
                ->get("/graph?type=$type&device=$device->device_id")
                ->assertForbidden();
        }
    }

    /**
     * Draw every graph as 'image-data', as if all rrd files exist
     *
     * @return ArrayObject<int, array<int, string>> the options of each draw
     */
    private function fakeRrdBackend(): ArrayObject
    {
        $commands = new ArrayObject;

        $rrdFiles = Mockery::mock(RrdFileResolver::class);
        $rrdFiles->shouldReceive('pathFor')->andReturn('localhost/test.rrd');
        $this->app->instance(RrdFileResolver::class, $rrdFiles);

        $backend = Mockery::mock(RrdBackendInterface::class);
        $backend->shouldReceive('graph')->andReturnUsing(function (array $options) use ($commands): string {
            $commands->append($options);

            return 'image-data';
        });
        $this->app->instance(RrdBackendInterface::class, $backend);

        return $commands;
    }

    private function adminUser(): User
    {
        return User::factory()->create(['enabled' => 1])->assignRole('admin');
    }
}
