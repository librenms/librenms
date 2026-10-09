<?php

namespace LibreNMS\Tests\Feature\Http;

use App\Facades\LibrenmsConfig;
use App\Models\Device;
use App\Models\Mempool;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Lang;
use Illuminate\Testing\TestResponse;
use LibreNMS\Tests\TestCase;
use Spatie\Permission\Models\Role;

final class EditModulesControllerTest extends TestCase
{
    use DatabaseTransactions;

    protected function setUp(): void
    {
        parent::setUp();

        Role::findOrCreate('admin');
        Role::findOrCreate('user');
    }

    private function admin(): User
    {
        $admin = User::factory()->create(['enabled' => 1]);
        $admin->assignRole('admin');

        return $admin;
    }

    private function user(): User
    {
        $user = User::factory()->create(['enabled' => 1]);
        $user->assignRole('user');

        return $user;
    }

    /**
     * Module rows passed to the view, keyed by module name
     *
     * @param  TestResponse<\Illuminate\Http\Response>  $response
     * @return array<string, array<string, mixed>>
     */
    private function modulesFrom(TestResponse $response): array
    {
        $modules = $response->viewData('modules');
        $this->assertIsArray($modules);

        return array_column($modules, null, 'module');
    }

    public function testAdminCanViewModulesPage(): void
    {
        $device = Device::factory()->create();

        $response = $this->actingAs($this->admin())
            ->get(route('device.edit.modules', $device))
            ->assertOk()
            ->assertSee('Discovery &amp; Polling Modules', false);

        $modules = $this->modulesFrom($response);
        $this->assertArrayHasKey('mempools', $modules);
        $this->assertArrayNotHasKey('core', $modules);
        $this->assertSame('Mempools', $modules['mempools']['name']);
    }

    public function testAllModulesHaveFriendlyNames(): void
    {
        foreach (['poller_modules', 'discovery_modules'] as $configKey) {
            foreach (array_keys(LibrenmsConfig::get($configKey)) as $module) {
                if ($module === 'core') {
                    continue;
                }

                $this->assertTrue(Lang::has("settings.settings.$configKey.$module.description", 'en'), "Missing lang/en/settings.php description for $configKey.$module");
            }
        }
    }

    public function testModuleSettingsReflectGlobalOsAndDevice(): void
    {
        $device = Device::factory()->create(['os' => 'linux']);
        LibrenmsConfig::set('poller_modules.mempools', true);
        LibrenmsConfig::set('os.linux.poller_modules.mempools', false);
        LibrenmsConfig::set('discovery_modules.mempools', false);
        $device->setAttrib('poll_mempools', 1);

        $response = $this->actingAs($this->admin())->get(route('device.edit.modules', $device))->assertOk();

        $mempools = $this->modulesFrom($response)['mempools'];
        $this->assertSame(['global' => true, 'os' => false, 'device' => true], $mempools['polling']);
        $this->assertSame(['global' => false, 'os' => null, 'device' => null], $mempools['discovery']);
    }

    public function testModuleOnlyInOneListIsNotApplicableInTheOther(): void
    {
        $device = Device::factory()->create();
        LibrenmsConfig::set('poller_modules', ['core' => true, 'only-polled' => true]);
        LibrenmsConfig::set('discovery_modules', ['core' => true, 'only-discovered' => false]);

        $response = $this->actingAs($this->admin())->get(route('device.edit.modules', $device))->assertOk();

        $modules = $this->modulesFrom($response);
        $this->assertSame(['only-discovered', 'only-polled'], array_keys($modules));
        $this->assertNull($modules['only-polled']['discovery']);
        $this->assertNull($modules['only-discovered']['polling']);
    }

    public function testHasDataReflectsModuleData(): void
    {
        $device = Device::factory()->create();

        $response = $this->actingAs($this->admin())->get(route('device.edit.modules', $device));
        $this->assertFalse($this->modulesFrom($response)['mempools']['has_data']);

        Mempool::factory()->for($device)->create();

        $response = $this->actingAs($this->admin())->get(route('device.edit.modules', $device));
        $this->assertTrue($this->modulesFrom($response)['mempools']['has_data']);
    }

    public function testUserCannotViewModulesPage(): void
    {
        $device = Device::factory()->create();

        $this->actingAs($this->user())
            ->get(route('device.edit.modules', $device))
            ->assertForbidden();
    }

    public function testAdminCanOverrideAndClearModule(): void
    {
        $device = Device::factory()->create();
        LibrenmsConfig::set('discovery_modules.mempools', true);

        $this->actingAs($this->admin())
            ->putJson(route('device.edit.modules.update', [$device, 'mempools']), ['discovery' => 'false'])
            ->assertOk()
            ->assertJson(['discovery' => false]);
        $this->assertSame('0', $device->fresh()->getAttrib('discover_mempools'));

        $this->actingAs($this->admin())
            ->putJson(route('device.edit.modules.update', [$device, 'mempools']), ['discovery' => 'clear'])
            ->assertOk()
            ->assertJson(['discovery' => true]);
        $this->assertNull($device->fresh()->getAttrib('discover_mempools'));
    }

    public function testInvalidUpdatesAreRejected(): void
    {
        $device = Device::factory()->create();

        $this->actingAs($this->admin())
            ->putJson(route('device.edit.modules.update', [$device, 'mempools']), ['polling' => 'maybe'])
            ->assertUnprocessable();

        $this->actingAs($this->admin())
            ->putJson(route('device.edit.modules.update', [$device, 'not-a-module']), ['polling' => 'true'])
            ->assertNotFound();

        $this->actingAs($this->admin())
            ->putJson(route('device.edit.modules.update', [$device, 'core']), ['polling' => 'false'])
            ->assertNotFound();

        $this->assertEmpty($device->fresh()->getAttribs());
    }

    public function testUserCannotUpdateModule(): void
    {
        $device = Device::factory()->create();

        $this->actingAs($this->user())
            ->putJson(route('device.edit.modules.update', [$device, 'mempools']), ['polling' => 'false'])
            ->assertForbidden();

        $this->assertNull($device->fresh()->getAttrib('poll_mempools'));
    }

    public function testAdminCanDeleteModuleData(): void
    {
        $device = Device::factory()->create();
        Mempool::factory()->for($device)->count(2)->create();

        $this->actingAs($this->admin())
            ->deleteJson(route('device.edit.modules.delete', [$device, 'mempools']))
            ->assertOk()
            ->assertJson(['deleted' => 2]);

        $this->assertSame(0, $device->mempools()->count());
    }

    public function testDeleteRejectsUnknownModule(): void
    {
        $device = Device::factory()->create();

        $this->actingAs($this->admin())
            ->deleteJson(route('device.edit.modules.delete', [$device, 'not-a-module']))
            ->assertNotFound();
    }

    public function testUserCannotDeleteModuleData(): void
    {
        $device = Device::factory()->create();
        Mempool::factory()->for($device)->create();

        $this->actingAs($this->user())
            ->deleteJson(route('device.edit.modules.delete', [$device, 'mempools']))
            ->assertForbidden();

        $this->assertSame(1, $device->mempools()->count());
    }
}
