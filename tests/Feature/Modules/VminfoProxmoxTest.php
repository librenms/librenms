<?php

namespace LibreNMS\Tests\Feature\Modules;

use App\Facades\DeviceCache;
use App\Facades\LibrenmsConfig;
use App\Facades\Rrd;
use App\Models\Device;
use App\Models\DevicePollingMethod;
use App\Models\Secret;
use App\Models\User;
use GuzzleHttp\Promise\PromiseInterface;
use Illuminate\Http\Client\Request;
use Illuminate\Support\Facades\Http;
use LibreNMS\Enum\PollingMethodType;
use LibreNMS\Enum\PowerState;
use LibreNMS\Enum\ProcessType;
use LibreNMS\Enum\SecretType;
use LibreNMS\Interfaces\Data\WriteInterface;
use LibreNMS\Modules\Vminfo;
use LibreNMS\OS;
use LibreNMS\Tests\InMemoryDbTestCase;
use LibreNMS\Util\ModuleList;
use Mockery;

final class VminfoProxmoxTest extends InMemoryDbTestCase
{
    private const API = 'https://pve.example.com:8006/api2/json/';

    private Device $device;
    private DevicePollingMethod $proxmoxMethod;

    protected function setUp(): void
    {
        parent::setUp();
        config(['app.key' => 'base64:' . base64_encode(random_bytes(32))]); // secret data is encrypted

        $this->device = Device::factory()->create(['os' => 'generic', 'hostname' => 'pve.example.com']);
        $secret = Secret::factory()->create([
            'secret_type' => SecretType::ProxmoxApi,
            'data' => ['token_id' => 'librenms@pve!monitoring', 'token_secret' => 'abc123'],
        ]);
        $this->proxmoxMethod = DevicePollingMethod::factory()->create([
            'device_id' => $this->device->device_id,
            'method_type' => PollingMethodType::ProxmoxApi,
            'secret_id' => $secret->id,
            'affects_availability' => false,
            'settings' => [],
        ]);
        DeviceCache::setPrimary($this->device->device_id);
    }

    private function fakeApi(): void
    {
        Http::fake([
            self::API . 'version' => Http::response(['data' => ['version' => '8.2.4']]),
            self::API . 'nodes' => self::nodes(),
            self::API . 'cluster/resources?type=vm' => self::clusterGuests([['vmid' => 100, 'name' => 'web', 'status' => 'running'], ['vmid' => 101, 'name' => 'db', 'status' => 'stopped']]),
            self::API . 'nodes/pve1/lxc' => Http::response(['data' => []]),
            self::API . 'nodes/pve1/qemu/100/agent/get-osinfo' => Http::response(['data' => ['result' => ['pretty-name' => 'Debian GNU/Linux 12 (bookworm)']]]),
            self::API . 'nodes/pve1/qemu?full=1' => Http::response(['data' => [
                [
                    'vmid' => 100, 'name' => 'web', 'cpus' => 2, 'status' => 'running', 'cpu' => 0.25, 'mem' => 1073741824, 'maxmem' => 4294967296,
                    'disk' => 0, 'maxdisk' => 34359738368, 'netin' => 1000, 'netout' => 2000,
                    'blockstat' => [
                        'scsi0' => ['rd_bytes' => 100, 'wr_bytes' => 200, 'rd_operations' => 1, 'wr_operations' => 2],
                        'scsi1' => ['rd_bytes' => 10, 'wr_bytes' => 20, 'rd_operations' => 3, 'wr_operations' => 4],
                    ],
                ],
                ['vmid' => 101, 'name' => 'db', 'maxmem' => 2147483648, 'cpus' => 1, 'status' => 'stopped'],
            ]]),
            self::API . 'nodes/pve1/qemu/100/agent/get-fsinfo' => Http::response(['data' => ['result' => [
                ['type' => 'ext4', 'used-bytes' => 500, 'total-bytes' => 1000],
                ['type' => 'tmpfs', 'used-bytes' => 5, 'total-bytes' => 10],
            ]]]),
        ]);
    }

    public function testDiscoverAndPoll(): void
    {
        $this->fakeApi();
        $module = new Vminfo;
        $module->discover($this->os());

        $vms = $this->device->vminfo()->orderBy('vmwVmVMID')->get();
        $this->assertSame(['web', 'db'], $vms->pluck('vmwVmDisplayName')->all());
        $this->assertSame('Debian GNU/Linux 12 (bookworm)', $vms[0]->vmwVmGuestOS);
        $this->assertNull($vms[1]->vmwVmGuestOS);
        $this->assertEquals(4096, $vms[0]->vmwVmMemSize);
        $this->assertSame(PowerState::ON, $vms[0]->vmwVmState);
        $this->assertSame(PowerState::OFF, $vms[1]->vmwVmState);
        Http::assertSent(fn (Request $request) => $request->hasHeader('Authorization', 'PVEAPIToken=librenms@pve!monitoring=abc123'));

        $written = [];
        $datastore = Mockery::mock(WriteInterface::class);
        $datastore->shouldReceive('write')->andReturnUsing(function (string $measurement, array $fields, array $tags) use (&$written): void {
            $written[$measurement][$tags['id']] = $fields;
        });

        $module->poll($this->os(), $datastore);

        // stopped VMs are not polled
        $this->assertSame(['100'], array_map(strval(...), array_keys($written['vm.cpu'])));
        $this->assertEquals(['usage' => 25], $written['vm.cpu'][100]);
        $this->assertEquals(['used' => 1073741824, 'total' => 4294967296], $written['vm.memory'][100]);
        $this->assertEquals(['used' => 500, 'total' => 1000], $written['vm.disk'][100]); // guest agent, pseudo filesystems skipped
        $this->assertEquals(['in' => 1000, 'out' => 2000], $written['vm.net'][100]);
        $this->assertSame(110, $written['vm.diskio'][100]['read_bytes']);
        $this->assertEquals(['read_bytes' => 110, 'write_bytes' => 220], $written['vm.diskio'][100]);
        $this->assertSame(6, $written['vm.diskops'][100]['write_ops']);
    }

    public function testUsesTheProxmoxPollingMethod(): void
    {
        $this->fakeApi();
        $module = new Vminfo;

        $this->assertTrue($module->shouldDiscover($this->os()));
        $this->assertFalse($module->shouldPoll($this->os())); // no VMs discovered

        $module->discover($this->os());
        $this->assertTrue($module->shouldPoll($this->os()));

        // no Proxmox polling method
        $this->proxmoxMethod->delete();
        $this->assertFalse($module->shouldDiscover($this->os()));
        $this->assertFalse($module->shouldPoll($this->os()));
    }

    public function testOnlySyncsTheVmsOfItsType(): void
    {
        $this->fakeApi();
        $this->createVm('vmware', 5, 'esx-guest'); // from another source
        $this->createVm('proxmox', 999, 'removed');

        (new Vminfo)->discover($this->os());

        $this->assertEqualsCanonicalizing(
            ['vmware-5', 'proxmox-100', 'proxmox-101'],
            $this->device->vminfo()->get()->map->getCompositeKey()->all()
        );
    }

    public function testProxmoxEnablesTheVminfoModule(): void
    {
        LibrenmsConfig::set('discovery_modules.vminfo', false);
        LibrenmsConfig::set('poller_modules.vminfo', false);
        $modules = new ModuleList;

        $status = $modules->moduleStatus(ProcessType::Discovery, 'vminfo', $this->device);
        $this->assertTrue($status->isEnabled());
        $this->assertSame('by polling method', $status->reason());
        $this->assertTrue($modules->moduleStatus(ProcessType::Poller, 'vminfo', $this->device)->isEnabled());

        // the device setting overrides the polling method
        $this->device->setAttrib('discover_vminfo', false);
        $this->assertFalse($modules->moduleStatus(ProcessType::Discovery, 'vminfo', $this->device)->isEnabled());

        // a disabled method does not enable modules
        $this->proxmoxMethod->update(['enabled' => false]);
        $device = Device::find($this->device->device_id);
        $this->assertFalse($modules->moduleStatus(ProcessType::Poller, 'vminfo', $device)->isEnabled());
    }

    public function testDoesNotRunWhenTheApiCheckFails(): void
    {
        Http::fake(['*' => Http::response(null, 401)]);
        $this->createVm('proxmox', 100, 'web');

        $module = new Vminfo;

        $this->assertFalse($module->shouldDiscover($this->os()));
        $this->assertFalse($module->shouldPoll($this->os()));
    }

    public function testKeepsVmsWhenTheVmListFails(): void
    {
        Http::fake([
            self::API . 'version' => Http::response(['data' => ['version' => '8.2.4']]),
            self::API . 'nodes' => self::nodes(),
            self::API . 'cluster/resources?type=vm' => Http::response(null, 403),
        ]);
        $this->createVm('proxmox', 100, 'web');

        (new Vminfo)->discover($this->os());

        $this->assertSame(['web'], $this->device->vminfo()->pluck('vmwVmDisplayName')->all());
        $this->assertSame(1, $this->device->eventlogs()->where('type', 'vminfo')->count());
    }

    public function testSkipsVmsRemovedSinceDiscovery(): void
    {
        Http::fake([
            self::API . 'version' => Http::response(['data' => ['version' => '8.2.4']]),
            self::API . 'nodes' => self::nodes(),
            self::API . 'nodes/pve1/qemu?full=1' => Http::response(['data' => [['vmid' => 101, 'status' => 'running', 'cpu' => 0.5]]]),
            self::API . 'nodes/pve1/qemu/101/agent/get-fsinfo' => Http::response(null, 500),
        ]);
        $this->createVm('proxmox', 100, 'web');
        $this->createVm('proxmox', 101, 'db');

        $written = [];
        $datastore = Mockery::mock(WriteInterface::class);
        $datastore->shouldReceive('write')->andReturnUsing(function (string $measurement, array $fields, array $tags) use (&$written): void {
            $written[$measurement][$tags['id']] = $fields;
        });

        (new Vminfo)->poll($this->os(), $datastore);

        $this->assertSame([101], array_keys($written['vm.cpu']));
    }

    public function testPollOnlyMakesOneStatusRequestAndAsksRunningVmAgents(): void
    {
        $this->fakeApi();
        $this->createVm('proxmox', 100, 'web');
        $this->createVm('proxmox', 101, 'db');
        $datastore = Mockery::mock(WriteInterface::class)->shouldIgnoreMissing();

        (new Vminfo)->poll($this->os(), $datastore);

        Http::assertSentCount(4); // the check, the nodes, the VM list and the agent of the running VM
        Http::assertNotSent(fn (Request $request) => str_contains($request->url(), '/status/current'));
        Http::assertNotSent(fn (Request $request) => str_contains($request->url(), 'qemu/101/agent'));
    }

    public function testAgentRequestsUseTheClientOptions(): void
    {
        $sent = [];
        Http::fake(function (Request $request, array $options) use (&$sent) {
            if (str_contains($request->url(), '/agent/')) {
                $sent[] = ['auth' => $request->header('Authorization')[0] ?? null, 'verify' => $options['verify'] ?? null, 'timeout' => $options['timeout'] ?? null, 'hasProxy' => isset($options['proxy'])];
            }

            if (str_ends_with($request->url(), '/version')) {
                return Http::response(['data' => ['version' => '8.2.4']]);
            }

            return str_ends_with($request->url(), '/nodes')
                ? self::nodes()
                : Http::response(['data' => [['vmid' => 100, 'type' => 'qemu', 'node' => 'pve1', 'name' => 'web', 'maxmem' => 1048576, 'cpus' => 1, 'status' => 'running']]]);
        });

        (new Vminfo)->discover($this->os());

        $this->assertSame([['auth' => 'PVEAPIToken=librenms@pve!monitoring=abc123', 'verify' => false, 'timeout' => 10, 'hasProxy' => true]], $sent);
    }

    public function testWritesToTheFileTheGraphsRead(): void
    {
        $this->fakeApi();
        $this->createVm('proxmox', 100, 'web');

        $names = [];
        $datastore = Mockery::mock(WriteInterface::class);
        $datastore->shouldReceive('write')->andReturnUsing(function (string $measurement, array $fields, array $tags, array $meta) use (&$names): void {
            $names[$measurement] = $meta['rrd_name'];
        });

        (new Vminfo)->poll($this->os(), $datastore);

        $this->assertCount(6, $names);
        foreach ($names as $measurement => $rrdName) {
            $this->assertSame(Rrd::name('pve.example.com', "$measurement-proxmox-100")->relativePath(), Rrd::name('pve.example.com', $rrdName)->relativePath());
        }
    }

    private function fakeContainers(): void
    {
        $containers = [
            [
                'vmid' => 126, 'name' => 'test', 'type' => 'lxc', 'status' => 'running', 'cpus' => 1, 'cpu' => 0.5,
                'mem' => 2310144, 'maxmem' => 536870912, 'disk' => 13864960, 'maxdisk' => 8350298112,
                'netin' => 218508, 'netout' => 866, 'diskread' => 5128192, 'diskwrite' => 16384,
            ],
            ['vmid' => 127, 'name' => 'off', 'type' => 'lxc', 'status' => 'stopped', 'cpus' => 2, 'maxmem' => 1073741824],
        ];

        Http::fake([
            self::API . 'version' => Http::response(['data' => ['version' => '9.2.24']]),
            self::API . 'nodes' => self::nodes(),
            self::API . 'nodes/pve1/qemu?full=1' => Http::response(['data' => [
                ['vmid' => 100, 'name' => 'web', 'maxmem' => 4294967296, 'cpus' => 2, 'status' => 'stopped'],
            ]]),
            self::API . 'cluster/resources?type=vm' => self::clusterGuests([['vmid' => 100, 'name' => 'web', 'maxmem' => 4294967296, 'cpus' => 2, 'status' => 'stopped']], $containers),
            self::API . 'nodes/pve1/lxc' => Http::response(['data' => $containers]),
            self::API . 'nodes/pve1/lxc/126/config' => Http::response(['data' => ['ostype' => 'alpine']]),
            self::API . 'nodes/pve1/lxc/127/config' => Http::response(null, 500),
        ]);
    }

    public function testDiscoversContainers(): void
    {
        $this->fakeContainers();

        (new Vminfo)->discover($this->os());

        $containers = $this->device->vminfo()->where('vm_type', 'proxmox-lxc')->orderBy('vmwVmVMID')->get();
        $this->assertSame(['test', 'off'], $containers->pluck('vmwVmDisplayName')->all());
        $this->assertSame(['alpine', null], $containers->pluck('vmwVmGuestOS')->all());
        $this->assertEquals(512, $containers[0]->vmwVmMemSize);
        $this->assertSame(PowerState::ON, $containers[0]->vmwVmState);
        $this->assertSame(PowerState::OFF, $containers[1]->vmwVmState);
        $this->assertSame(1, $this->device->vminfo()->where('vm_type', 'proxmox')->count());
    }

    public function testPollsContainersWithoutTheFullOptionOrAgent(): void
    {
        $this->fakeContainers();
        $this->createVm('proxmox-lxc', 126, 'test');
        $this->createVm('proxmox-lxc', 127, 'off');

        $written = [];
        $datastore = Mockery::mock(WriteInterface::class);
        $datastore->shouldReceive('write')->andReturnUsing(function (string $measurement, array $fields, array $tags) use (&$written): void {
            $written[$measurement][$tags['id']] = $fields;
        });

        (new Vminfo)->poll($this->os(), $datastore);

        $this->assertSame([126], array_keys($written['vm.cpu']));
        $this->assertEquals(['usage' => 50], $written['vm.cpu'][126]);
        $this->assertEquals(['used' => 2310144, 'total' => 536870912], $written['vm.memory'][126]);
        $this->assertEquals(['used' => 13864960, 'total' => 8350298112], $written['vm.disk'][126]);
        $this->assertEquals(['read_bytes' => 5128192, 'write_bytes' => 16384], $written['vm.diskio'][126]);
        $this->assertEquals(['in' => 218508, 'out' => 866], $written['vm.net'][126]);
        $this->assertArrayNotHasKey('vm.diskops', $written);
        $this->assertSame(PowerState::OFF, $this->device->vminfo()->where('vmwVmVMID', 127)->first()->vmwVmState);
        Http::assertNotSent(fn (Request $request) => str_contains($request->url(), 'full=1') || str_contains($request->url(), '/agent/') || str_contains($request->url(), '/qemu'));
    }

    public function testKeepsTheGuestOsWhenItCanNotBeRead(): void
    {
        $this->fakeApi();
        $this->createVm('proxmox', 101, 'db');
        $this->device->vminfo()->where('vmwVmVMID', 101)->update(['vmwVmGuestOS' => 'Arch Linux']);

        (new Vminfo)->discover($this->os());

        $this->assertSame('Arch Linux', $this->device->vminfo()->where('vmwVmVMID', 101)->first()->vmwVmGuestOS);
    }

    public function testPausedVmIsSuspendedAndHasNoMetricsOrAgentCalls(): void
    {
        Http::fake([
            self::API . 'version' => Http::response(['data' => ['version' => '8.2.4']]),
            self::API . 'nodes' => self::nodes(),
            self::API . 'nodes/pve1/qemu?full=1' => Http::response(['data' => [
                ['vmid' => 100, 'name' => 'web', 'maxmem' => 1048576, 'cpus' => 1, 'status' => 'running', 'qmpstatus' => 'paused', 'cpu' => 0.5],
            ]]),
            self::API . 'cluster/resources?type=vm' => self::clusterGuests([['vmid' => 100, 'name' => 'web', 'status' => 'running']]),
            self::API . 'nodes/pve1/lxc' => Http::response(['data' => []]),
        ]);
        $module = new Vminfo;

        $module->discover($this->os());
        $this->assertSame(PowerState::SUSPENDED, $this->device->vminfo()->first()->vmwVmState);

        $datastore = Mockery::mock(WriteInterface::class);
        $datastore->shouldNotReceive('write');
        $module->poll($this->os(), $datastore);

        $this->assertSame(PowerState::SUSPENDED, $this->device->vminfo()->first()->vmwVmState);
        Http::assertNotSent(fn (Request $request) => str_contains($request->url(), '/agent/'));
    }

    public function testTemplatesAreNotDiscovered(): void
    {
        Http::fake([
            self::API . 'version' => Http::response(['data' => ['version' => '8.2.4']]),
            self::API . 'nodes' => self::nodes(),
            self::API . 'nodes/pve1/qemu?full=1' => Http::response(['data' => [
                ['vmid' => 100, 'name' => 'web', 'maxmem' => 1048576, 'cpus' => 1, 'status' => 'stopped'],
                ['vmid' => 900, 'name' => 'vm-template', 'maxmem' => 1048576, 'cpus' => 1, 'status' => 'stopped', 'template' => 1],
            ]]),
            self::API . 'cluster/resources?type=vm' => self::clusterGuests(
                [['vmid' => 100, 'name' => 'web', 'status' => 'stopped'], ['vmid' => 900, 'name' => 'vm-template', 'status' => 'stopped', 'template' => 1]],
                [['vmid' => 901, 'name' => 'ct-template', 'maxmem' => 1048576, 'cpus' => 1, 'status' => 'stopped', 'template' => 1]],
            ),
        ]);

        (new Vminfo)->discover($this->os());

        $this->assertSame(['web'], $this->device->vminfo()->pluck('vmwVmDisplayName')->all());
    }

    /**
     * @param  list<array<string, mixed>>  $filesystems
     * @return array<string, mixed>
     */
    private function pollVmDisk(array $filesystems): array
    {
        Http::fake([
            self::API . 'version' => Http::response(['data' => ['version' => '8.2.4']]),
            self::API . 'nodes' => self::nodes(),
            self::API . 'nodes/pve1/qemu?full=1' => Http::response(['data' => [['vmid' => 100, 'status' => 'running', 'disk' => 5, 'maxdisk' => 10]]]),
            self::API . 'nodes/pve1/qemu/100/agent/get-fsinfo' => Http::response(['data' => ['result' => $filesystems]]),
        ]);
        $this->createVm('proxmox', 100, 'web');

        $disk = [];
        $datastore = Mockery::mock(WriteInterface::class);
        $datastore->shouldReceive('write')->andReturnUsing(function (string $measurement, array $fields) use (&$disk): void {
            if ($measurement === 'vm.disk') {
                $disk = $fields;
            }
        });

        (new Vminfo)->poll($this->os(), $datastore);

        return $disk;
    }

    public function testFilesystemsWithoutSizesFallBackToTheHost(): void
    {
        $disk = $this->pollVmDisk([
            ['name' => 'vol1', 'mountpoint' => 'System Reserved', 'type' => 'NTFS'],
            ['name' => 'loop0', 'mountpoint' => '/snap/core', 'type' => 'squashfs', 'used-bytes' => 5, 'total-bytes' => 5],
        ]);

        $this->assertEquals(['used' => 5, 'total' => 10], $disk);
    }

    public function testMountsOfOneDeviceAreCountedOnce(): void
    {
        $disk = $this->pollVmDisk([
            ['name' => 'sda1', 'mountpoint' => '/', 'type' => 'btrfs', 'used-bytes' => 100, 'total-bytes' => 1000],
            ['name' => 'sda1', 'mountpoint' => '/home', 'type' => 'btrfs', 'used-bytes' => 100, 'total-bytes' => 1000],
            ['name' => 'sdb1', 'mountpoint' => '/data', 'type' => 'ext4', 'used-bytes' => 20, 'total-bytes' => 200],
        ]);

        $this->assertEquals(['used' => 120, 'total' => 1200], $disk);
    }

    public function testPollUpdatesThePowerState(): void
    {
        Http::fake([
            self::API . 'version' => Http::response(['data' => ['version' => '8.2.4']]),
            self::API . 'nodes' => self::nodes(),
            self::API . 'nodes/pve1/qemu?full=1' => Http::response(['data' => [['vmid' => 100, 'status' => 'stopped']]]),
        ]);
        $this->createVm('proxmox', 100, 'web');

        (new Vminfo)->poll($this->os(), Mockery::mock(WriteInterface::class));

        $this->assertSame(PowerState::OFF, $this->device->vminfo()->first()->vmwVmState);
    }

    public function testStuckGuestAgentDoesNotFailTheOtherVms(): void
    {
        Http::fake([
            self::API . 'version' => Http::response(['data' => ['version' => '8.2.4']]),
            self::API . 'nodes' => self::nodes(),
            self::API . 'nodes/pve1/qemu?full=1' => Http::response(['data' => [
                ['vmid' => 100, 'name' => 'web', 'maxmem' => 4294967296, 'cpus' => 2, 'status' => 'running', 'disk' => 5, 'maxdisk' => 10],
            ]]),
            self::API . 'cluster/resources?type=vm' => self::clusterGuests([['vmid' => 100, 'name' => 'web', 'status' => 'running']]),
            self::API . 'nodes/pve1/lxc' => Http::response(['data' => []]),
            self::API . 'nodes/pve1/qemu/100/agent/*' => Http::failedConnection(),
        ]);
        $module = new Vminfo;

        $module->discover($this->os());
        $this->assertNull($this->device->vminfo()->first()->vmwVmGuestOS);

        $written = [];
        $datastore = Mockery::mock(WriteInterface::class);
        $datastore->shouldReceive('write')->andReturnUsing(function (string $measurement, array $fields, array $tags) use (&$written): void {
            $written[$measurement][$tags['id']] = $fields;
        });

        $module->poll($this->os(), $datastore);

        $this->assertEquals(['used' => 5, 'total' => 10], $written['vm.disk'][100]); // hypervisor fallback
    }

    public function testCountersDiscardResetsWhenAVmRestarts(): void
    {
        $this->fakeApi();
        $this->createVm('proxmox', 100, 'web');

        $definitions = [];
        $datastore = Mockery::mock(WriteInterface::class);
        $datastore->shouldReceive('write')->andReturnUsing(function (string $measurement, array $fields, array $tags, array $meta) use (&$definitions): void {
            $definitions[$measurement] = (string) $meta['rrd_def'];
        });

        (new Vminfo)->poll($this->os(), $datastore);

        foreach (['vm.net', 'vm.diskio', 'vm.diskops'] as $metric) {
            $this->assertStringContainsString(':DERIVE:', $definitions[$metric]);
            $this->assertStringNotContainsString(':COUNTER:', $definitions[$metric]);
        }
    }

    public function testDiscoversAndPollsTheGuestsOfEveryNode(): void
    {
        $this->fakeCluster();
        $module = new Vminfo;

        $module->discover($this->os());

        $this->assertEquals(['100' => 'pve1', '200' => 'pve2'], $this->device->vminfo()->pluck('node', 'vmwVmVMID')->all());
        $this->assertSame('On node 2', $this->device->vminfo()->where('vmwVmVMID', 200)->first()->vmwVmGuestOS); // read through its own node

        $written = [];
        $datastore = Mockery::mock(WriteInterface::class);
        $datastore->shouldReceive('write')->andReturnUsing(function (string $measurement, array $fields, array $tags) use (&$written): void {
            $written[$measurement][$tags['id']] = $fields;
        });

        $module->poll($this->os(), $datastore);

        $this->assertEquals([100, 200], array_keys($written['vm.cpu']));
    }

    public function testAMigratedVmKeepsItsRowAndMetricsFile(): void
    {
        $this->fakeCluster();
        $this->createVm('proxmox', 200, 'db');
        $vm = $this->device->vminfo()->first();
        $vm->update(['node' => 'pve1']); // discovered before it migrated to pve2

        $names = [];
        $datastore = Mockery::mock(WriteInterface::class);
        $datastore->shouldReceive('write')->andReturnUsing(function (string $measurement, array $fields, array $tags, array $meta) use (&$names): void {
            $names[$measurement] = $meta['rrd_name'];
        });

        (new Vminfo)->poll($this->os(), $datastore);

        $this->assertSame('pve2', $vm->refresh()->node); // known without waiting for discovery
        $this->assertSame(['vm.cpu', 'proxmox', '200'], array_map(strval(...), $names['vm.cpu']));
    }

    public function testKeepsWhatIsKnownOfAGuestOnAnOfflineNode(): void
    {
        $this->fakeOfflineNode();
        $this->createVm('proxmox', 200, 'db');
        $this->createVm('proxmox-lxc', 201, 'ct');

        (new Vminfo)->discover($this->os());

        $vm = $this->device->vminfo()->where('vmwVmVMID', 200)->first();
        $this->assertSame('db', $vm->vmwVmDisplayName);
        $this->assertEquals(1024, $vm->vmwVmMemSize);
        $this->assertSame(PowerState::UNKNOWN, $vm->vmwVmState);
        $this->assertSame('ct', $this->device->vminfo()->where('vmwVmVMID', 201)->first()->vmwVmDisplayName);
        Http::assertNotSent(fn (Request $request) => str_contains($request->url(), 'nodes/pve2'));
    }

    public function testAddsANewGuestOnAnOfflineNode(): void
    {
        $this->fakeOfflineNode();

        (new Vminfo)->discover($this->os());

        $this->assertEqualsCanonicalizing(['proxmox-200', 'proxmox-lxc-201', 'proxmox-100'], $this->device->vminfo()->get()->map->getCompositeKey()->all());
        $this->assertSame('', $this->device->vminfo()->where('vmwVmVMID', 200)->first()->vmwVmDisplayName);
    }

    public function testANodeThatFailsDoesNotStopTheOtherNodes(): void
    {
        Http::fake([
            self::API . 'version' => Http::response(['data' => ['version' => '8.2.4']]),
            self::API . 'nodes' => self::nodes(['pve1', 'pve2']),
            self::API . 'nodes/pve1/qemu?full=1' => Http::response(['data' => [['vmid' => 100, 'status' => 'running', 'cpu' => 0.5]]]),
            self::API . 'nodes/pve2/qemu?full=1' => Http::response(null, 595),
        ]);
        $this->createVm('proxmox', 100, 'web');
        $this->createVm('proxmox', 200, 'db');

        $written = [];
        $datastore = Mockery::mock(WriteInterface::class);
        $datastore->shouldReceive('write')->andReturnUsing(function (string $measurement, array $fields, array $tags) use (&$written): void {
            $written[$measurement][$tags['id']] = $fields;
        });

        (new Vminfo)->poll($this->os(), $datastore);

        $this->assertSame([100], array_keys($written['vm.cpu']));
    }

    public function testAFailingSourceDoesNotStopTheOthers(): void
    {
        Http::fake([
            self::API . 'version' => Http::response(['data' => ['version' => '8.2.4']]),
            self::API . 'cluster/resources?type=vm' => Http::response(null, 500),
        ]);
        $this->device->update(['os' => 'linux']);
        LibrenmsConfig::set('enable_libvirt', true);
        LibrenmsConfig::set('libvirt_protocols', []); // finds no VMs
        $this->createVm('proxmox', 100, 'web');
        $this->createVm('libvirt', 5, 'removed');

        (new Vminfo)->discover($this->os());

        $this->assertSame(['proxmox-100'], $this->device->vminfo()->get()->map->getCompositeKey()->all());
    }

    public function testAFailedPollIsLoggedInsteadOfThrown(): void
    {
        Http::fake([
            self::API . 'version' => Http::response(['data' => ['version' => '8.2.4']]),
            self::API . 'nodes' => Http::response(null, 500),
        ]);
        $this->createVm('proxmox', 100, 'web');
        $datastore = Mockery::mock(WriteInterface::class);
        $datastore->shouldNotReceive('write');

        (new Vminfo)->poll($this->os(), $datastore);

        $this->assertSame(1, $this->device->eventlogs()->where('type', 'vminfo')->count());
    }

    public function testVmsOfSnmpSourcesAreKeptWhenSnmpIsDownAndLibvirtIsEnabled(): void
    {
        $this->proxmoxMethod->delete();
        $this->device->update(['os' => 'linux']);
        LibrenmsConfig::set('enable_libvirt', true);
        LibrenmsConfig::set('libvirt_protocols', []); // finds no VMs
        $this->createVm('vmware', 5, 'esx-guest');
        $this->createVm('xcp-ng', 6, 'xcp-guest');
        $this->createVm('libvirt', 7, 'removed');

        (new Vminfo)->discover($this->os());

        $this->assertEqualsCanonicalizing(['vmware-5', 'xcp-ng-6'], $this->device->vminfo()->get()->map->getCompositeKey()->all());
    }

    public function testRemovingTheMethodDeletesItsVms(): void
    {
        $this->createVm('proxmox', 100, 'web');
        $this->createVm('proxmox-lxc', 101, 'ct');
        $this->createVm('vmware', 5, 'esx-guest'); // from another source

        $this->actingAs(User::factory()->admin()->create(['enabled' => 1]))
            ->deleteJson(route('device.edit.polling.destroy', ['device' => $this->device, 'methodType' => 'proxmox-api']))
            ->assertOk();

        $this->assertSame(['vmware-5'], $this->device->vminfo()->get()->map->getCompositeKey()->all());
    }

    public function testDisablingTheMethodKeepsItsVms(): void
    {
        $this->proxmoxMethod->update(['enabled' => false]);
        $this->createVm('proxmox', 100, 'web');

        $module = new Vminfo;
        $this->assertFalse($module->shouldDiscover($this->os()));

        $this->assertSame(1, $this->device->vminfo()->count());
    }

    public function testLibvirtDoesNotNeedSnmp(): void
    {
        $this->proxmoxMethod->delete();
        $this->device->update(['os' => 'linux']);
        $module = new Vminfo;

        LibrenmsConfig::set('enable_libvirt', false);
        $this->assertFalse($module->shouldDiscover($this->os()));

        LibrenmsConfig::set('enable_libvirt', true);
        $this->assertTrue($module->shouldDiscover($this->os()));
    }

    /**
     * Guest 100 runs on pve1 and guest 200 on pve2.
     */
    private function fakeCluster(): void
    {
        Http::fake([
            self::API . 'version' => Http::response(['data' => ['version' => '8.2.4']]),
            self::API . 'nodes' => self::nodes(['pve1', 'pve2']),
            self::API . 'cluster/resources?type=vm' => Http::response(['data' => [
                ['vmid' => 100, 'type' => 'qemu', 'node' => 'pve1', 'name' => 'web', 'status' => 'running', 'maxcpu' => 2, 'maxmem' => 1048576],
                ['vmid' => 200, 'type' => 'qemu', 'node' => 'pve2', 'name' => 'db', 'status' => 'running', 'maxcpu' => 2, 'maxmem' => 1048576],
            ]]),
            self::API . 'nodes/pve1/qemu?full=1' => Http::response(['data' => [['vmid' => 100, 'name' => 'web', 'status' => 'running', 'cpus' => 2, 'maxmem' => 1048576, 'cpu' => 0.5]]]),
            self::API . 'nodes/pve2/qemu?full=1' => Http::response(['data' => [['vmid' => 200, 'name' => 'db', 'status' => 'running', 'cpus' => 2, 'maxmem' => 1048576, 'cpu' => 0.5]]]),
            self::API . 'nodes/pve2/qemu/200/agent/get-osinfo' => Http::response(['data' => ['result' => ['pretty-name' => 'On node 2']]]),
        ]);
    }

    /**
     * Guest 100 runs on pve1, the cluster only knows the id and node of the guests on pve2, which is offline.
     */
    private function fakeOfflineNode(): void
    {
        Http::fake([
            self::API . 'version' => Http::response(['data' => ['version' => '8.2.4']]),
            self::API . 'nodes' => Http::response(['data' => [['node' => 'pve1', 'status' => 'online'], ['node' => 'pve2', 'status' => 'offline']]]),
            self::API . 'cluster/resources?type=vm' => Http::response(['data' => [
                ['vmid' => 100, 'type' => 'qemu', 'node' => 'pve1', 'name' => 'web', 'status' => 'running', 'maxcpu' => 2, 'maxmem' => 1048576],
                ['vmid' => 200, 'type' => 'qemu', 'node' => 'pve2', 'status' => 'unknown'],
                ['vmid' => 201, 'type' => 'lxc', 'node' => 'pve2', 'status' => 'unknown'],
            ]]),
            self::API . 'nodes/pve1/qemu?full=1' => Http::response(['data' => [['vmid' => 100, 'name' => 'web', 'status' => 'running', 'cpus' => 2, 'maxmem' => 1048576]]]),
        ]);
    }

    /**
     * @param  list<string>  $nodes
     */
    private static function nodes(array $nodes = ['pve1']): PromiseInterface
    {
        return Http::response(['data' => array_map(fn (string $node): array => ['node' => $node, 'status' => 'online'], $nodes)]);
    }

    /**
     * The guest list of the cluster, with all guests on pve1.
     *
     * @param  list<array<string, mixed>>  $vms
     * @param  list<array<string, mixed>>  $containers
     */
    private static function clusterGuests(array $vms, array $containers = []): PromiseInterface
    {
        return Http::response(['data' => [
            ...array_map(fn (array $vm): array => ['type' => 'qemu', 'node' => 'pve1'] + $vm, $vms),
            ...array_map(fn (array $container): array => ['type' => 'lxc', 'node' => 'pve1'] + $container, $containers),
        ]]);
    }

    private function createVm(string $type, int $vmId, string $name): void
    {
        $this->device->vminfo()->create([
            'vm_type' => $type,
            'vmwVmVMID' => $vmId,
            'vmwVmDisplayName' => $name,
            'vmwVmGuestOS' => '',
            'vmwVmMemSize' => 1024,
            'vmwVmCpus' => 1,
            'vmwVmState' => PowerState::ON,
        ]);
    }

    private function os(): OS
    {
        // simulate a fresh discovery/poller run
        $device = DeviceCache::refresh($this->device->device_id)->toArray();

        return OS::make($device);
    }
}
