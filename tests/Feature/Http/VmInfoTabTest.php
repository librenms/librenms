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
                $path = Rrd::name($device->hostname, [$metric, ...$vm->tags()])->fullPath();
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

    private function admin(): User
    {
        $admin = User::factory()->create(['enabled' => 1]);
        $admin->assignRole('admin');

        return $admin;
    }
}
