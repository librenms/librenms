<?php

namespace LibreNMS\Tests\Feature\View;

use App\Models\Device;
use App\Models\DevicePollingMethod;
use App\Models\Mempool;
use App\Models\Storage;
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

    public function testDataTabsAreShownWhenTheDeviceHasThatData(): void
    {
        $device = $this->deviceWithMethods(PollingMethodType::UnixAgent);
        Mempool::factory()->for($device)->create();
        Storage::factory()->for($device)->create();

        $tabs = (new EditTabs($device))->tabs;

        $this->assertArrayHasKey('mempools', $tabs);
        $this->assertArrayHasKey('storage', $tabs);
        $this->assertArrayNotHasKey('processors', $tabs);
        $this->assertArrayNotHasKey('ports', $tabs);
    }

    public function testApplicationsTabNeedsSnmp(): void
    {
        $this->assertArrayHasKey('apps', (new EditTabs($this->deviceWithMethods(PollingMethodType::Snmp)))->tabs);
        $this->assertArrayNotHasKey('apps', (new EditTabs($this->deviceWithMethods(PollingMethodType::UnixAgent)))->tabs);
    }

    public function testModulesTabIsAlwaysShown(): void
    {
        $tabs = (new EditTabs($this->deviceWithMethods()))->tabs;

        $this->assertArrayHasKey('modules', $tabs);
        foreach (['ports', 'apps', 'storage', 'processors', 'mempools'] as $tab) {
            $this->assertArrayNotHasKey($tab, $tabs);
        }
    }

    public function testLegacyEditPageUsesTabs(): void
    {
        Role::findOrCreate('admin');
        $admin = User::factory()->create(['enabled' => 1]);
        $admin->assignRole('admin');
        $device = $this->deviceWithMethods(PollingMethodType::UnixAgent);
        Mempool::factory()->for($device)->create();

        $response = $this->actingAs($admin)
            ->get("/device/device=$device->device_id/tab=edit/section=alert-rules/")
            ->assertOk()
            ->assertSee(route('device.edit.mempools', $device), false)
            ->assertDontSee(route('device.edit.storage', $device), false);

        $this->assertMatchesRegularExpression('#<span class="pagemenu-selected">\s*<a href="[^"]*section=alert-rules"#', (string) $response->getContent());
    }
}
