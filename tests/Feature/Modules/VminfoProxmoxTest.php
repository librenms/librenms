<?php

namespace LibreNMS\Tests\Feature\Modules;

use App\Facades\DeviceCache;
use App\Facades\LibrenmsConfig;
use App\Models\Device;
use App\Models\DevicePollingMethod;
use App\Models\Secret;
use Illuminate\Http\Client\Request;
use Illuminate\Support\Facades\Http;
use LibreNMS\Enum\PollingMethodType;
use LibreNMS\Enum\PowerState;
use LibreNMS\Enum\ProcessType;
use LibreNMS\Enum\SecretType;
use LibreNMS\Interfaces\Data\DataStorageInterface;
use LibreNMS\Interfaces\Data\WriteInterface;
use LibreNMS\Modules\Vminfo;
use LibreNMS\OS;
use LibreNMS\Polling\ConnectivityHelper;
use LibreNMS\Polling\ModuleStatus;
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
            'secret_type' => SecretType::Proxmox,
            'data' => ['token_id' => 'librenms@pve!monitoring', 'token_secret' => 'abc123'],
        ]);
        $this->proxmoxMethod = DevicePollingMethod::factory()->create([
            'device_id' => $this->device->device_id,
            'method_type' => PollingMethodType::Proxmox,
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
            self::API . 'nodes/localhost/qemu' => Http::response(['data' => [
                ['vmid' => 100, 'name' => 'web', 'maxmem' => 4294967296, 'cpus' => 2, 'status' => 'running'],
                ['vmid' => 101, 'name' => 'db', 'maxmem' => 2147483648, 'cpus' => 1, 'status' => 'stopped'],
            ]]),
            self::API . 'nodes/localhost/qemu/100/agent/get-osinfo' => Http::response(['data' => ['result' => ['pretty-name' => 'Debian GNU/Linux 12 (bookworm)']]]),
            self::API . 'nodes/localhost/qemu/101/agent/get-osinfo' => Http::response(null, 500), // agent not running
            self::API . 'nodes/localhost/qemu/100/status/current' => Http::response(['data' => [
                'status' => 'running', 'cpu' => 0.25, 'mem' => 1073741824, 'maxmem' => 4294967296,
                'disk' => 0, 'maxdisk' => 34359738368, 'netin' => 1000, 'netout' => 2000,
                'blockstat' => [
                    'scsi0' => ['rd_bytes' => 100, 'wr_bytes' => 200, 'rd_operations' => 1, 'wr_operations' => 2],
                    'scsi1' => ['rd_bytes' => 10, 'wr_bytes' => 20, 'rd_operations' => 3, 'wr_operations' => 4],
                ],
            ]]),
            self::API . 'nodes/localhost/qemu/101/status/current' => Http::response(['data' => ['status' => 'stopped']]),
            self::API . 'nodes/localhost/qemu/100/agent/get-fsinfo' => Http::response(['data' => ['result' => [
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
        $this->assertSame('', $vms[1]->vmwVmGuestOS);
        $this->assertEquals(4096, $vms[0]->vmwVmMemSize);
        $this->assertSame(PowerState::ON, $vms[0]->vmwVmState);
        $this->assertSame(PowerState::OFF, $vms[1]->vmwVmState);
        Http::assertSent(fn (Request $request) => $request->hasHeader('Authorization', 'PVEAPIToken=librenms@pve!monitoring=abc123'));

        $written = [];
        $datastore = Mockery::mock(DataStorageInterface::class, WriteInterface::class);
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
        $this->assertSame(6, $written['vm.diskio'][100]['write_ops']);
    }

    public function testUsesTheProxmoxPollingMethod(): void
    {
        $this->fakeApi();
        $module = new Vminfo;
        $status = new ModuleStatus(true);

        $this->assertTrue($module->shouldDiscover($this->os(), $status, $this->connectivity()));
        $this->assertFalse($module->shouldPoll($this->os(), $status, $this->connectivity())); // no VMs discovered

        $module->discover($this->os());
        $this->assertTrue($module->shouldPoll($this->os(), $status, $this->connectivity()));

        // no Proxmox polling method
        $this->proxmoxMethod->delete();
        $this->assertFalse($module->shouldDiscover($this->os(), $status, $this->connectivity()));
        $this->assertFalse($module->shouldPoll($this->os(), $status, $this->connectivity()));
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
        $status = new ModuleStatus(true);

        $this->assertFalse($module->shouldDiscover($this->os(), $status, $this->connectivity()));
        $this->assertFalse($module->shouldPoll($this->os(), $status, $this->connectivity()));
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

    private function connectivity(): ConnectivityHelper
    {
        return new ConnectivityHelper(DeviceCache::get($this->device->device_id));
    }

    private function os(): OS
    {
        // simulate a fresh discovery/poller run
        $device = DeviceCache::refresh($this->device->device_id)->toArray();

        return OS::make($device);
    }
}
