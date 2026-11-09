<?php

namespace LibreNMS\Tests\Feature\Http;

use App\Facades\LibrenmsConfig;
use App\Facades\Rrd;
use App\Models\Device;
use App\Models\User;
use App\Models\Vminfo;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\File;
use LibreNMS\Tests\TestCase;
use Spatie\Permission\Models\Role;

final class VmInfoTabTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        Role::findOrCreate('admin');
        Role::findOrCreate('user');
    }

    public function testShowsTheRecordedGraphsOfAVm(): void
    {
        $device = Device::factory()->create(['hostname' => 'pve.example.com']);
        $vm = Vminfo::factory()->for($device)->create(['vm_type' => 'proxmox', 'vmwVmVMID' => 100, 'vmwVmDisplayName' => 'web']);

        $rrdDir = sys_get_temp_dir() . '/librenms-vm-test-' . uniqid();
        LibrenmsConfig::set('rrd_dir', $rrdDir);
        LibrenmsConfig::set('rrdcached', '');

        try {
            foreach (['vm.cpu', 'vm.net'] as $metric) {
                $path = Rrd::metricName($vm->metricIdentity($metric))->fullPath();
                File::ensureDirectoryExists(dirname($path));
                touch($path);
            }

            $this->actingAs($this->admin())
                ->get(route('device.vm.show', ['device' => $device, 'vminfo' => $vm]))
                ->assertOk()
                ->assertSee('web')
                ->assertSee('All virtual machines')
                ->assertSee('vminfo_cpu')
                ->assertSee('vminfo_net')
                ->assertDontSee('vminfo_mem') // not recorded
                ->assertDontSee('No graphs have been recorded');
        } finally {
            File::deleteDirectory($rrdDir);
        }
    }

    public function testGraphPageShowsTheVmAsText(): void
    {
        $vm = Vminfo::factory()->create(['vm_type' => 'proxmox', 'vmwVmDisplayName' => 'web']);

        $this->actingAs($this->admin())
            ->get("/graphs?type=vminfo_cpu&id=$vm->id")
            ->assertOk()
            ->assertSee(':: VM :: web')
            ->assertDontSee('&lt;a', false);
    }

    public function testVmWithoutGraphs(): void
    {
        $vm = Vminfo::factory()->create(['vm_type' => 'vmware']);
        LibrenmsConfig::set('rrd_dir', sys_get_temp_dir() . '/librenms-vm-test-' . uniqid());
        LibrenmsConfig::set('rrdcached', '');

        $this->actingAs($this->admin())
            ->get(route('device.vm.show', ['device' => $vm->device_id, 'vminfo' => $vm]))
            ->assertOk()
            ->assertSee('No graphs have been recorded')
            ->assertDontSee('vminfo_cpu');
    }

    public function testVmOfAnotherDeviceIsNotFound(): void
    {
        $device = Device::factory()->create();
        $vm = Vminfo::factory()->create();

        $this->actingAs($this->admin())
            ->get(route('device.vm.show', ['device' => $device, 'vminfo' => $vm]))
            ->assertNotFound();
    }

    public function testUserWithoutAccessIsForbidden(): void
    {
        $vm = Vminfo::factory()->create();
        $user = User::factory()->create(['enabled' => 1]);
        $user->assignRole('user');

        $this->actingAs($user)
            ->get(route('device.vm.show', ['device' => $vm->device_id, 'vminfo' => $vm]))
            ->assertForbidden();
    }

    public function testTableLinksToTheVmGraphs(): void
    {
        $vm = Vminfo::factory()->create();

        $response = $this->actingAs($this->admin())->postJson(route('table.vminfo'), [
            'device_id' => $vm->device_id,
            'current' => 1,
            'rowCount' => 50,
            'searchPhrase' => '',
        ]);

        $response->assertOk();
        $this->assertStringContainsString(
            route('device.vm.show', ['device' => $vm->device_id, 'vminfo' => $vm->id]),
            $response->json('rows.0.graphs')
        );
    }

    public function testAllVmsGraphViewShowsTheVmsThatRecordedTheGraph(): void
    {
        $recorded = Vminfo::factory()->create(['vm_type' => 'proxmox', 'vmwVmVMID' => 100]);
        $unrecorded = Vminfo::factory()->create(['vm_type' => 'vmware']);
        $rrdDir = $this->useTemporaryRrdDir();

        try {
            $this->recordMetric($recorded, 'vm.cpu');

            $this->actingAs($this->admin())
                ->get(route('vminfo.index', ['graph' => 'cpu']))
                ->assertOk()
                ->assertSee('vminfo_cpu')
                ->assertSee(route('device.vm.show', ['device' => $recorded->device_id, 'vminfo' => $recorded->id]))
                ->assertDontSee(route('device.vm.show', ['device' => $unrecorded->device_id, 'vminfo' => $unrecorded->id]))
                ->assertDontSee('data-url="' . route('table.vminfo') . '"', false);
        } finally {
            File::deleteDirectory($rrdDir);
        }
    }

    public function testDeviceGraphViewOnlyShowsTheVmsOfThatDevice(): void
    {
        $device = Device::factory()->create();
        $local = Vminfo::factory()->for($device)->create(['vm_type' => 'proxmox', 'vmwVmVMID' => 100]);
        $other = Vminfo::factory()->create(['vm_type' => 'proxmox', 'vmwVmVMID' => 101]);
        $rrdDir = $this->useTemporaryRrdDir();

        try {
            $this->recordMetric($local, 'vm.net');
            $this->recordMetric($other, 'vm.net');

            $this->actingAs($this->admin())
                ->get(route('device', ['device' => $device, 'tab' => 'vm', 'graph' => 'net']))
                ->assertOk()
                ->assertSee('vminfo_net')
                ->assertSee(route('device.vm.show', ['device' => $device, 'vminfo' => $local->id]))
                ->assertDontSee(route('device.vm.show', ['device' => $other->device_id, 'vminfo' => $other->id]));
        } finally {
            File::deleteDirectory($rrdDir);
        }
    }

    public function testListViewLinksToEachGraphViewKeepingTheFilter(): void
    {
        $filter = ['vm_type' => ['eq' => 'proxmox']];

        $this->actingAs($this->admin())
            ->get(route('vminfo.index', ['filter' => $filter]))
            ->assertOk()
            ->assertSee('data-url="' . route('table.vminfo') . '"', false)
            ->assertSee(route('vminfo.index', ['graph' => 'mem', 'filter' => $filter]))
            ->assertSee(route('vminfo.index', ['graph' => 'diskio_ops', 'filter' => $filter]));
    }

    public function testUnknownGraphIsRejected(): void
    {
        $this->actingAs($this->admin())
            ->get(route('vminfo.index', ['graph' => 'bogus']))
            ->assertSessionHasErrors('graph');
    }

    private function useTemporaryRrdDir(): string
    {
        $rrdDir = sys_get_temp_dir() . '/librenms-vm-test-' . uniqid();
        LibrenmsConfig::set('rrd_dir', $rrdDir);
        LibrenmsConfig::set('rrdcached', '');

        return $rrdDir;
    }

    private function recordMetric(Vminfo $vm, string $metric): void
    {
        $path = Rrd::metricName($vm->metricIdentity($metric))->fullPath();
        File::ensureDirectoryExists(dirname($path));
        touch($path);
    }

    private function admin(): User
    {
        $admin = User::factory()->create(['enabled' => 1]);
        $admin->assignRole('admin');

        return $admin;
    }
}
