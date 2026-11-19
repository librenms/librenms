<?php

namespace LibreNMS\Tests\Feature\Http;

use App\Models\Component;
use App\Models\ComponentPref;
use App\Models\Device;
use App\Models\LoadbalancerRserver;
use App\Models\LoadbalancerVserver;
use App\Models\NetscalerVserver;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\DB;
use LibreNMS\Tests\TestCase;
use Spatie\Permission\Models\Role;

final class LoadBalancerTabTest extends TestCase
{
    use DatabaseTransactions;

    protected function setUp(): void
    {
        parent::setUp();

        Role::findOrCreate('admin');
    }

    public function testTabOnlyShownWithLoadBalancerData(): void
    {
        $device = Device::factory()->create(['os' => 'f5']);
        $tabLink = 'href="' . route('device.loadbalancer', ['device' => $device]) . '"';

        $this->actingAs($this->admin())
            ->get(route('device', ['device' => $device, 'tab' => 'notes']))
            ->assertOk()
            ->assertDontSee($tabLink, false);

        $this->createComponent($device, 'f5-cert', 'default.crt');
        $this->app->forgetScopedInstances(); // a new request, the controller is scoped

        $this->actingAs($this->admin())
            ->get(route('device', ['device' => $device, 'tab' => 'notes']))
            ->assertOk()
            ->assertSee($tabLink, false);
    }

    public function testF5VirtualServersListLinksDefaultPool(): void
    {
        $device = Device::factory()->create(['os' => 'f5']);
        $pool = $this->createComponent($device, 'f5-ltm-pool', 'Pool <1>');
        $this->createComponent($device, 'f5-ltm-vs', 'VS <1>', ['IP' => '192.0.2.1', 'port' => 443, 'pool' => 'Pool <1>'], status: 2, error: 'Offline');
        $this->createComponent($device, 'f5-ltm-vs', 'VS 2', ['pool' => 'Missing Pool'], status: 1, error: 'Degraded');
        $this->createComponent($device, 'f5-ltm-vs', 'Disabled VS', disabled: 1);

        $response = $this->actingAs($this->admin())
            ->get(route('device.loadbalancer', ['device' => $device]))
            ->assertOk()
            ->assertSeeInOrder(['Virtual Servers (2)', 'Pools (1)'])
            ->assertSee('VS &lt;1&gt;', false)
            ->assertSee('192.0.2.1:443')
            ->assertSee('Missing Pool')
            ->assertSee('type=pools&amp;id=' . $pool->id, false)
            ->assertSee('type=device_bigip_ltm_allvs_conns', false)
            ->assertSee('graph=device_bigip_ltm_vs_conns', false)
            ->assertDontSee('Disabled VS');

        $this->assertLabel('label-danger', 'Offline', (string) $response->getContent());
        $this->assertLabel('label-warning', 'Degraded', (string) $response->getContent());
    }

    public function testF5VirtualServerRowGraphs(): void
    {
        $device = Device::factory()->create(['os' => 'f5']);
        $vs = $this->createComponent($device, 'f5-ltm-vs', 'VS 1');

        $this->actingAs($this->admin())
            ->get(route('device.loadbalancer', ['device' => $device, 'graph' => 'device_bigip_ltm_vs_bytes']))
            ->assertOk()
            ->assertSee('type=device_bigip_ltm_vs_bytes', false)
            ->assertSee('id=' . $vs->id, false);

        $this->actingAs($this->admin())
            ->get(route('device.loadbalancer', ['device' => $device, 'graph' => 'not_a_graph']))
            ->assertOk()
            ->assertDontSee('type=not_a_graph', false);
    }

    public function testF5PoolDetailShowsOnlyItsMembers(): void
    {
        $device = Device::factory()->create(['os' => 'f5']);
        $pool = $this->createComponent($device, 'f5-ltm-pool', 'Pool 1', ['minup' => 2, 'currentup' => 1, 'minupaction' => 3, 'monitor' => 'http']);
        $this->createComponent($device, 'f5-ltm-pool', 'Pool 10');
        // a pool member's label is its pool's name
        $this->createComponent($device, 'f5-ltm-poolmember', 'Pool 1', ['nodename' => 'Node <A>', 'IP' => '192.0.2.5', 'port' => 80]);
        $this->createComponent($device, 'f5-ltm-poolmember', 'Pool 10', ['nodename' => 'Other Node']);

        $this->actingAs($this->admin())
            ->get(route('device.loadbalancer', ['device' => $device, 'type' => 'pools', 'id' => $pool->id]))
            ->assertOk()
            ->assertSee('Pool Down Action')
            ->assertSee('Failover')
            ->assertSee('Pool Members (1)')
            ->assertSee('Node &lt;A&gt;', false)
            ->assertSee('192.0.2.5:80')
            ->assertDontSee('Other Node')
            ->assertSee('type=device_bigip_ltm_allpm_conns', false)
            ->assertSee('id=' . $pool->id, false);
    }

    public function testF5PoolMembersSectionLinksPools(): void
    {
        $device = Device::factory()->create(['os' => 'f5']);
        $pool = $this->createComponent($device, 'f5-ltm-pool', 'Pool 1');
        $this->createComponent($device, 'f5-ltm-poolmember', 'Pool 1', ['nodename' => 'Node A', 'IP' => '192.0.2.5', 'port' => 80], status: 2, error: 'Offline');
        $this->createComponent($device, 'f5-ltm-poolmember', 'Pool 1', ['nodename' => '', 'IP' => '192.0.2.6', 'port' => 80]);

        $response = $this->actingAs($this->admin())
            ->get(route('device.loadbalancer', ['device' => $device, 'type' => 'pool-members']))
            ->assertOk()
            ->assertSee('Pool Members (2)')
            ->assertSee('Node A')
            // members without a node name are named after their pool
            ->assertSeeInOrder(['192.0.2.6:80', 'type=pools'], false)
            ->assertSee('>Pool 1</a></td>', false)
            ->assertSee('type=pools&amp;id=' . $pool->id, false);

        $this->assertLabel('label-danger', 'Offline', (string) $response->getContent());
    }

    public function testF5SectionWithoutDetailsShowsItemGraphs(): void
    {
        $device = Device::factory()->create(['os' => 'f5']);
        $wide = $this->createComponent($device, 'f5-gtm-wide', 'www.example.com');

        $this->actingAs($this->admin())
            ->get(route('device.loadbalancer', ['device' => $device]))
            ->assertOk()
            ->assertSee('Wide IPs (1)')
            ->assertSee('type=wide-ips&amp;id=' . $wide->id, false)
            ->assertSee('type=device_bigip_gtm_allwide_requests', false);

        $this->actingAs($this->admin())
            ->get(route('device.loadbalancer', ['device' => $device, 'type' => 'wide-ips', 'id' => $wide->id]))
            ->assertOk()
            ->assertSee('www.example.com')
            ->assertSee('type=device_bigip_gtm_wide_requests', false)
            ->assertDontSee('type=device_bigip_gtm_allwide_requests', false);
    }

    public function testNetscalerVirtualServers(): void
    {
        $device = Device::factory()->create(['os' => 'netscaler']);
        $vsvr = $this->createVserver($device, ['vsvr_name' => 'web <vs>', 'vsvr_state' => 'outOfService', 'vsvr_clients' => 3]);

        $response = $this->actingAs($this->admin())
            ->get(route('device.loadbalancer', ['device' => $device, 'graph' => 'netscalervsvr_conns']))
            ->assertOk()
            ->assertSee('Virtual Servers (1)')
            ->assertSee('web &lt;vs&gt;', false)
            ->assertSee('192.0.2.10:80')
            ->assertSeeInOrder(['Traffic In', 'Traffic Out', '8 kbps', '16 kbps'])
            ->assertSee('type=netscalervsvr_conns', false)
            ->assertDontSee('type=netscalervsvr_bits', false);

        $this->assertLabel('label-warning', 'outOfService', (string) $response->getContent());

        $this->actingAs($this->admin())
            ->get(route('device.loadbalancer', ['device' => $device, 'id' => $vsvr->vsvr_id]))
            ->assertOk()
            ->assertSee('Clients')
            ->assertSee('type=netscalervsvr_bits', false)
            ->assertSee('type=netscalervsvr_hitmiss', false);
    }

    public function testListSortsByAnyColumn(): void
    {
        $device = Device::factory()->create(['os' => 'netscaler']);
        foreach (['alpha' => [9000, 'up'], 'beta' => [100000, 'down'], 'gamma' => [20, 'outOfService']] as $name => [$bps, $state]) {
            $this->createVserver($device, ['vsvr_name' => $name, 'vsvr_bps_in' => $bps, 'vsvr_state' => $state]);
        }
        $list = ['device' => $device];

        // traffic sorts by bps, not by its formatted text
        $this->actingAs($this->admin())
            ->get(route('device.loadbalancer', $list + ['sort' => 'traffic_in', 'order' => 'desc']))
            ->assertOk()
            ->assertSeeInOrder(['beta', 'alpha', 'gamma'])
            ->assertSee('sort=traffic_in&amp;order=asc', false)
            ->assertSee('fa-sort-down', false);

        $this->actingAs($this->admin())
            ->get(route('device.loadbalancer', $list + ['sort' => 'status', 'order' => 'desc']))
            ->assertOk()
            ->assertSeeInOrder(['beta', 'gamma', 'alpha']);

        $this->actingAs($this->admin())
            ->get(route('device.loadbalancer', $list + ['sort' => 'name', 'order' => 'desc', 'graph' => 'netscalervsvr_bits']))
            ->assertOk()
            ->assertSeeInOrder(['gamma', 'beta', 'alpha'])
            ->assertSee('graph=netscalervsvr_bits&amp;sort=traffic_in&amp;order=asc', false);

        // empty values arrive as null (ConvertEmptyStringsToNull) and fall back to the defaults
        $this->actingAs($this->admin())
            ->get(route('device.loadbalancer', $list) . '?sort=&order=')
            ->assertOk()
            ->assertSeeInOrder(['alpha', 'beta', 'gamma'])
            ->assertSee('fa-sort-up', false);

        $this->actingAs($this->admin())
            ->get(route('device.loadbalancer', $list + ['sort' => 'not-a-column']))
            ->assertOk()
            ->assertSeeInOrder(['alpha', 'beta', 'gamma'])
            ->assertDontSee('fa-sort-', false);
    }

    public function testColumnsKeepTheirKeysWhenTranslated(): void
    {
        // titles without ascii letters used to become empty sort keys and collide
        app('translator')->addLines(['*.Traffic In' => '入站流量', '*.Traffic Out' => '出站流量'], 'zh-CN');
        $device = Device::factory()->create(['os' => 'netscaler']);
        $this->createVserver($device, ['vsvr_name' => 'alpha', 'vsvr_bps_in' => 10]);
        $this->createVserver($device, ['vsvr_name' => 'beta', 'vsvr_bps_in' => 30]);

        $this->actingAs($this->admin())
            ->withSession(['preferences' => ['locale' => 'zh-CN']])
            ->get(route('device.loadbalancer', ['device' => $device, 'sort' => 'traffic_in', 'order' => 'desc']))
            ->assertOk()
            ->assertSeeInOrder(['入站流量', '出站流量', 'beta', 'alpha'])
            ->assertSee('sort=traffic_in&amp;order=asc', false)
            ->assertSee('sort=traffic_out&amp;order=asc', false);
    }

    public function testSectionCountsAreQueriedOnce(): void
    {
        $device = Device::factory()->create(['os' => 'f5']);
        $this->createComponent($device, 'f5-ltm-vs', 'VS 1');

        DB::enableQueryLog();
        $this->actingAs($this->admin())
            ->get(route('device.loadbalancer', ['device' => $device]))
            ->assertOk()
            ->assertSee('Virtual Servers (1)');

        $countQueries = collect(DB::getQueryLog())->filter(fn (array $query) => str_contains($query['query'], 'count(*) as total'));
        $this->assertCount(1, $countQueries);
    }

    public function testUnknownSectionAndItemFallBack(): void
    {
        $device = Device::factory()->create(['os' => 'acsw']);
        LoadbalancerVserver::unguarded(fn () => LoadbalancerVserver::create(['device_id' => $device->device_id, 'classmap_id' => 7, 'classmap' => 'vip-1', 'serverstate' => 'inService']));

        $this->actingAs($this->admin())
            ->get(route('device.loadbalancer', ['device' => $device, 'type' => 'certificates', 'id' => 999]))
            ->assertOk()
            ->assertSee('vip-1')
            ->assertSee('graph=vserver_bits', false);

        $this->actingAs($this->admin())
            ->get(route('device.loadbalancer', ['device' => $device, 'type' => 'virtual-servers', 'id' => 7]))
            ->assertOk()
            ->assertSee('vip-1')
            ->assertSee('type=vserver_bits', false)
            ->assertDontSee('graph=vserver_bits', false);
    }

    public function testUserWithoutAccessIsForbidden(): void
    {
        $device = Device::factory()->create(['os' => 'f5']);
        $this->createComponent($device, 'f5-ltm-vs', 'VS 1');

        $this->actingAs(User::factory()->create(['enabled' => 1]))
            ->get(route('device.loadbalancer', ['device' => $device]))
            ->assertForbidden();
    }

    public function testOptionsComeFromTheQueryString(): void
    {
        $device = Device::factory()->create(['os' => 'acsw']);
        LoadbalancerVserver::unguarded(fn () => LoadbalancerVserver::create(['device_id' => $device->device_id, 'classmap_id' => 7, 'classmap' => 'vip-1', 'serverstate' => 'inService']));
        LoadbalancerRserver::unguarded(fn () => LoadbalancerRserver::create(['device_id' => $device->device_id, 'farm_id' => 'farm-1', 'StateDescr' => 'Server is now operational']));

        // legacy /key=value path segments redirect to the query string
        $this->actingAs($this->admin())
            ->get("/device/device=$device->device_id/tab=loadbalancer/type=pools/")
            ->assertRedirect(route('device.loadbalancer', ['device' => $device, 'type' => 'pools']))
            ->assertStatus(301);

        $this->actingAs($this->admin())
            ->get(route('device.loadbalancer', ['device' => $device, 'type' => 'pools']))
            ->assertOk()
            ->assertSee('farm-1')
            ->assertDontSee('vip-1');

        $this->actingAs($this->admin())
            ->get(route('device.loadbalancer', ['device' => $device, 'id' => 'abc', 'order' => 'sideways']))
            ->assertSessionHasErrors(['id', 'order']);
    }

    public function testCiscoAceVirtualServersAndPools(): void
    {
        $device = Device::factory()->create(['os' => 'acsw']);
        LoadbalancerVserver::unguarded(fn () => LoadbalancerVserver::create(['device_id' => $device->device_id, 'classmap_id' => 7, 'classmap' => 'vip <a>', 'serverstate' => 'inService']));
        LoadbalancerRserver::unguarded(fn () => LoadbalancerRserver::create(['device_id' => $device->device_id, 'farm_id' => 'farm-1', 'StateDescr' => 'Server is down']));

        $this->actingAs($this->admin())
            ->get(route('device.loadbalancer', ['device' => $device, 'graph' => 'vserver_conns']))
            ->assertOk()
            ->assertSeeInOrder(['Virtual Servers (1)', 'Pools (1)'])
            ->assertSee('vip &lt;a&gt;', false)
            ->assertSee('type=vserver_conns', false)
            ->assertSee('id=7', false);

        $response = $this->actingAs($this->admin())
            ->get(route('device.loadbalancer', ['device' => $device, 'type' => 'pools']))
            ->assertOk()
            ->assertSee('farm-1')
            ->assertSee('graph=rserver_curr', false)
            ->assertDontSee('type=rserver_', false);

        $this->assertLabel('label-danger', 'Server is down', (string) $response->getContent());
    }

    public function testAcosSectionsListSlbObjects(): void
    {
        $device = Device::factory()->create(['os' => 'acos']);
        $vs = $this->createComponent($device, 'acos-slb-vs', 'vs <web>', ['address' => '10.0.0.10'], status: 2, error: 'Virtual Server is Down');
        $pool = $this->createComponent($device, 'acos-slb-pool', 'sg-web');
        $this->createComponent($device, 'acos-slb-vport', 'vs <web>:80', ['vsname' => 'vs <web>', 'address' => '10.0.0.10', 'port' => 80, 'servicegroup' => 'sg-web']);
        $this->createComponent($device, 'acos-slb-member', 'web1:80', ['poolname' => 'sg-web', 'servername' => 'web1', 'port' => 80, 'priority' => 1]);
        $this->createComponent($device, 'acos-slb-server', 'web1', ['address' => '10.1.0.1', 'healthmonitor' => 'ping']);
        $this->createComponent($device, 'acos-slb-vs', 'Disabled VS', disabled: 1);

        $response = $this->actingAs($this->admin())
            ->get(route('device.loadbalancer', ['device' => $device]))
            ->assertOk()
            ->assertSeeInOrder(['Virtual Servers (1)', 'Virtual Services (1)', 'Pools (1)', 'Pool Members (1)', 'Real Servers (1)'])
            ->assertSee('vs &lt;web&gt;', false)
            ->assertSee('10.0.0.10')
            ->assertSee('type=device_acos_slb_allvs_curconns', false)
            ->assertSee('graph=device_acos_slb_vs_curconns', false)
            ->assertDontSee('Disabled VS');

        $this->assertLabel('label-danger', 'Virtual Server is Down', (string) $response->getContent());

        $this->actingAs($this->admin())
            ->get(route('device.loadbalancer', ['device' => $device, 'type' => 'virtual-services']))
            ->assertOk()
            ->assertSee('10.0.0.10:80')
            ->assertSee('type=pools&amp;id=' . $pool->id, false);

        $this->actingAs($this->admin())
            ->get(route('device.loadbalancer', ['device' => $device, 'type' => 'real-servers']))
            ->assertOk()
            ->assertSee('Health Monitor')
            ->assertSee('10.1.0.1')
            ->assertSee('ping');

        $this->actingAs($this->admin())
            ->get(route('device.loadbalancer', ['device' => $device, 'type' => 'virtual-servers', 'id' => $vs->id]))
            ->assertOk()
            ->assertSee('type=device_acos_slb_vs_bytesin', false)
            ->assertSee('id=' . $vs->id, false);
    }

    public function testAcosPoolDetailShowsOnlyItsMembers(): void
    {
        $device = Device::factory()->create(['os' => 'acos']);
        $pool = $this->createComponent($device, 'acos-slb-pool', 'sg-web');
        $this->createComponent($device, 'acos-slb-pool', 'sg-web2');
        $this->createComponent($device, 'acos-slb-server', 'web1', ['address' => '10.1.0.1']);
        $this->createComponent($device, 'acos-slb-member', 'web1:80', ['poolname' => 'sg-web', 'servername' => 'web1', 'port' => 80, 'priority' => 2], status: 2, error: 'Pool Member is Down');
        $this->createComponent($device, 'acos-slb-member', 'other:80', ['poolname' => 'sg-web2', 'servername' => 'other', 'port' => 80]);

        $this->actingAs($this->admin())
            ->get(route('device.loadbalancer', ['device' => $device, 'type' => 'pools', 'id' => $pool->id]))
            ->assertOk()
            ->assertSee('Pool Members (1)')
            ->assertSee('web1')
            ->assertSee('10.1.0.1:80')
            ->assertSee('Pool Member is Down')
            ->assertDontSee('other')
            ->assertSee('type=device_acos_slb_allpm_curconns', false);
    }

    /**
     * @param  array<string, mixed>  $prefs
     */
    private function createComponent(Device $device, string $type, string $label, array $prefs = [], int $status = 0, string $error = '', int $disabled = 0): Component
    {
        $component = Component::factory()->create([
            'device_id' => $device->device_id,
            'type' => $type,
            'label' => $label,
            'status' => $status,
            'error' => $error,
            'disabled' => $disabled,
        ]);

        foreach ($prefs as $attribute => $value) {
            ComponentPref::create(['component' => $component->id, 'attribute' => $attribute, 'value' => $value]);
        }

        return $component;
    }

    /**
     * @param  array<model-property<NetscalerVserver>, mixed>  $attributes
     */
    private function createVserver(Device $device, array $attributes): NetscalerVserver
    {
        return NetscalerVserver::unguarded(fn () => NetscalerVserver::create($attributes + [
            'device_id' => $device->device_id,
            'vsvr_ip' => '192.0.2.10',
            'vsvr_port' => 80,
            'vsvr_type' => 'http',
            'vsvr_state' => 'up',
            'vsvr_clients' => 0,
            'vsvr_server' => 0,
            'vsvr_req_rate' => 0,
            'vsvr_bps_in' => 1000,
            'vsvr_bps_out' => 2000,
        ]));
    }

    private function assertLabel(string $class, string $text, string $html): void
    {
        $this->assertMatchesRegularExpression('/class="label ' . $class . '">\s*' . preg_quote($text, '/') . '\s*</', $html);
    }

    private function admin(): User
    {
        $admin = User::factory()->create(['enabled' => 1]);
        $admin->assignRole('admin');

        return $admin;
    }
}
