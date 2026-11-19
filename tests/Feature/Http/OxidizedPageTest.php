<?php

namespace LibreNMS\Tests\Feature\Http;

use App\Facades\LibrenmsConfig;
use App\Models\Device;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Http;
use LibreNMS\Tests\TestCase;
use PHPUnit\Framework\Attributes\TestWith;
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
            ->assertDontSee('unknown.example.com')
            ->assertViewHas('nodes', fn ($nodes) => $nodes->count() === 1 && $nodes[0]['device']->is($visible));
    }

    public function testPageShowsUnreachableOxidized(): void
    {
        Http::fake(['oxidized.test/*' => fn () => throw new \Illuminate\Http\Client\ConnectionException('down')]);

        $this->actingAs($this->admin())
            ->get(route('oxidized'))
            ->assertOk()
            ->assertSee('Oxidized is not reachable');
    }

    public function testSearchHidesInaccessibleDevicesAndRetainsUnknownNodes(): void
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

        $this->actingAs($this->admin())->postJson(route('oxidized.reload'))
            ->assertOk()
            ->assertExactJson(['message' => 'Oxidized node list was reloaded']);
        Http::assertSent(fn ($request) => str_ends_with($request->url(), '/reload.json'));
    }

    #[TestWith([200, true])]
    #[TestWith([404, false])]
    #[TestWith([500, false])]
    public function testNodeListReachabilityDistinguishesEmptyListsFromFailures(int $status, bool $reachable): void
    {
        Http::preventStrayRequests();
        Http::fake(['oxidized.test/nodes?format=json' => Http::response([], $status)]);

        $this->actingAs($this->admin())->get(route('oxidized'))
            ->assertOk()
            ->assertViewHas('reachable', $reachable)
            ->assertViewHas('nodes', fn ($nodes) => $nodes->isEmpty());

        Http::assertSentCount(1);
    }

    public function testDisabledNodeListIsNotReachable(): void
    {
        LibrenmsConfig::set('oxidized.enabled', false);
        Http::preventStrayRequests();

        $this->actingAs($this->admin())->get(route('oxidized'))
            ->assertOk()
            ->assertViewHas('reachable', false);

        Http::assertNothingSent();
    }

    public function testNodeTimesPreserveMissingValuesAndConvertNonEmptyTimes(): void
    {
        config(['app.timezone' => 'America/Chicago']);
        foreach (['missing', 'empty', 'valid', 'invalid'] as $hostname) {
            Device::factory()->create(['hostname' => $hostname]);
        }
        Http::preventStrayRequests();
        Http::fake(['oxidized.test/nodes?format=json' => Http::response([
            ['name' => 'missing'],
            ['name' => 'empty', 'time' => ''],
            ['name' => 'valid', 'time' => '2026-11-01 10:00:00 UTC'],
            ['name' => 'invalid', 'time' => 'never'],
        ])]);

        $this->actingAs($this->admin())->get(route('oxidized'))
            ->assertOk()
            ->assertViewHas('nodes', fn ($nodes) => $nodes->pluck('time')->all() === ['', '', '2026-11-01 04:00:00 CST', 'never']);

        Http::assertSentCount(1);
    }

    #[TestWith([false, true])]
    #[TestWith([true, false])]
    public function testDisabledReloadReturns409WithoutSendingRequest(bool $enabled, bool $reloadEnabled): void
    {
        LibrenmsConfig::set('oxidized.enabled', $enabled);
        LibrenmsConfig::set('oxidized.reload_nodes', $reloadEnabled);
        Http::preventStrayRequests();

        $this->actingAs($this->admin())->postJson(route('oxidized.reload'))
            ->assertStatus(409)
            ->assertExactJson(['message' => 'Oxidized node list reload is disabled']);

        Http::assertNothingSent();
    }

    #[TestWith([404])]
    #[TestWith([500])]
    public function testFailedReloadReturns503(int $status): void
    {
        LibrenmsConfig::set('oxidized.reload_nodes', true);
        Http::preventStrayRequests();
        Http::fake(['oxidized.test/reload.json' => Http::response('', $status)]);

        $this->actingAs($this->admin())->postJson(route('oxidized.reload'))
            ->assertStatus(503)
            ->assertExactJson(['message' => 'Oxidized node list could not be reloaded']);

        Http::assertSentCount(1);
    }

    public function testUnreachableReloadReturns503(): void
    {
        LibrenmsConfig::set('oxidized.reload_nodes', true);
        Http::preventStrayRequests();
        Http::fake(['oxidized.test/reload.json' => fn () => throw new \Illuminate\Http\Client\ConnectionException('down')]);

        $this->actingAs($this->admin())->postJson(route('oxidized.reload'))
            ->assertStatus(503)
            ->assertExactJson(['message' => 'Oxidized node list could not be reloaded']);
    }

    private function admin(): User
    {
        $admin = User::factory()->create(['enabled' => 1]);
        $admin->assignRole('admin');

        return $admin;
    }
}
