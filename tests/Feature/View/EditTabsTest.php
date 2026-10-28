<?php

namespace LibreNMS\Tests\Feature\View;

use App\Models\Device;
use App\Models\DevicePollingMethod;
use App\Models\User;
use App\View\Components\Device\EditTabs;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use LibreNMS\Enum\PollingMethodType;
use LibreNMS\Tests\TestCase;
use Spatie\Permission\Models\Role;

final class EditTabsTest extends TestCase
{
    use DatabaseTransactions;

    private function deviceWithMethods(PollingMethodType ...$types): Device
    {
        $device = Device::factory()->create();
        foreach ($types as $type) {
            DevicePollingMethod::factory()->for($device)->create(['method_type' => $type]);
        }

        return $device;
    }

    public function testSnmpDeviceHasModuleTabs(): void
    {
        $tabs = (new EditTabs($this->deviceWithMethods(PollingMethodType::Snmp)))->tabs;

        foreach (['ports', 'apps', 'modules', 'storage', 'processors', 'mempools'] as $tab) {
            $this->assertArrayHasKey($tab, $tabs);
        }
    }

    public function testUnixAgentDeviceOnlyHasTabsForAgentModules(): void
    {
        $tabs = (new EditTabs($this->deviceWithMethods(PollingMethodType::UnixAgent)))->tabs;

        $this->assertArrayHasKey('mempools', $tabs);
        $this->assertArrayHasKey('modules', $tabs);
        foreach (['ports', 'apps', 'storage', 'processors'] as $tab) {
            $this->assertArrayNotHasKey($tab, $tabs);
        }
    }

    public function testDeviceWithoutMethodsOnlyHasTheModulesTab(): void
    {
        $tabs = (new EditTabs($this->deviceWithMethods()))->tabs;

        foreach (['ports', 'apps', 'storage', 'processors', 'mempools'] as $tab) {
            $this->assertArrayNotHasKey($tab, $tabs);
        }
        $this->assertArrayHasKey('modules', $tabs);
        $this->assertArrayHasKey('alert-rules', $tabs);
    }

    public function testLegacyEditPageUsesTabs(): void
    {
        Role::findOrCreate('admin');
        $admin = User::factory()->create(['enabled' => 1]);
        $admin->assignRole('admin');
        $device = $this->deviceWithMethods(PollingMethodType::UnixAgent);

        $response = $this->actingAs($admin)
            ->get("/device/device=$device->device_id/tab=edit/section=alert-rules/")
            ->assertOk()
            ->assertSee(route('device.edit.mempools', $device), false)
            ->assertDontSee(route('device.edit.storage', $device), false);

        $this->assertMatchesRegularExpression('#<span class="pagemenu-selected">\s*<a href="[^"]*section=alert-rules"#', (string) $response->getContent());
    }
}
