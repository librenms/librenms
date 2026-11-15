<?php

namespace LibreNMS\Tests\Feature\Http;

use App\Facades\LibrenmsConfig;
use App\Models\Device;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Http;
use LibreNMS\Tests\TestCase;
use Spatie\Permission\Models\Role;

final class OxidizedPageTest extends TestCase
{
    use DatabaseTransactions;

    protected function setUp(): void
    {
        parent::setUp();

        Role::findOrCreate('admin');
        Role::findOrCreate('user');
        LibrenmsConfig::set('oxidized.enabled', true);
        LibrenmsConfig::set('oxidized.url', 'http://oxidized.test');
    }

    public function testNodeListOnlyShowsAccessibleDevices(): void
    {
        $visible = Device::factory()->create(['hostname' => 'visible.example.com']);
        Device::factory()->create(['hostname' => 'hidden.example.com']);

        Http::fake(['oxidized.test/nodes*' => Http::response([
            ['name' => 'visible.example.com', 'status' => 'success', 'time' => '2026-11-01 10:00:00 UTC', 'model' => 'ios', 'group' => 'core'],
            ['name' => 'hidden.example.com', 'status' => 'success', 'time' => '2026-11-01 10:00:00 UTC', 'model' => 'ios', 'group' => 'core'],
            ['name' => 'unknown.example.com', 'status' => 'success', 'time' => 'never', 'model' => 'ios', 'group' => 'core'],
        ])]);

        $user = User::factory()->create(['enabled' => 1]);
        $user->assignRole('user');
        $user->devicesOwned()->attach($visible);

        $this->actingAs($user)
            ->get(route('oxidized'))
            ->assertOk()
            ->assertSee('visible.example.com')
            ->assertDontSee('hidden.example.com')
            ->assertDontSee('unknown.example.com');
    }

    public function testPageShowsUnreachableOxidized(): void
    {
        Http::fake(['oxidized.test/*' => fn () => throw new \Illuminate\Http\Client\ConnectionException('down')]);

        $this->actingAs($this->admin())
            ->get(route('oxidized'))
            ->assertOk()
            ->assertSee('Oxidized is not reachable');
    }

    public function testSearchLinksDevicesAndHidesInaccessibleOnes(): void
    {
        $visible = Device::factory()->create(['hostname' => 'visible.example.com']);
        Device::factory()->create(['hostname' => 'hidden.example.com']);

        Http::fake(['oxidized.test/nodes/conf_search*' => Http::response([
            ['node' => 'visible.example.com', 'full_name' => 'visible.example.com'],
            ['node' => 'hidden.example.com', 'full_name' => 'hidden.example.com'],
            ['node' => 'other', 'full_name' => 'core/other'],
        ])]);

        $user = User::factory()->create(['enabled' => 1]);
        $user->assignRole('user');
        $user->givePermissionTo(\Spatie\Permission\Models\Permission::findOrCreate('oxidized.search'));
        $user->devicesOwned()->attach($visible);

        $this->actingAs($user)
            ->postJson(route('oxidized.search'), ['search' => 'password'])
            ->assertOk()
            ->assertExactJson(['results' => [
                ['name' => $visible->display, 'url' => route('device', ['device' => $visible->device_id, 'tab' => 'config'])],
                ['name' => 'core/other', 'url' => null],
            ]]);

        Http::assertSent(fn ($request) => $request['search_in_conf_textbox'] === 'password');
    }

    public function testSearchRequiresPermission(): void
    {
        $user = User::factory()->create(['enabled' => 1]);
        $user->assignRole('user');

        $this->actingAs($user)
            ->postJson(route('oxidized.search'), ['search' => 'password'])
            ->assertForbidden();
    }

    public function testReloadRequiresPermission(): void
    {
        $user = User::factory()->create(['enabled' => 1]);
        $user->assignRole('user');

        $this->actingAs($user)->postJson(route('oxidized.reload'))->assertForbidden();

        LibrenmsConfig::set('oxidized.reload_nodes', true);
        Http::fake(['oxidized.test/reload.json' => Http::response('{}')]);

        $this->actingAs($this->admin())->postJson(route('oxidized.reload'))->assertOk();
        Http::assertSent(fn ($request) => str_ends_with($request->url(), '/reload.json'));
    }

    private function admin(): User
    {
        $admin = User::factory()->create(['enabled' => 1]);
        $admin->assignRole('admin');

        return $admin;
    }
}
