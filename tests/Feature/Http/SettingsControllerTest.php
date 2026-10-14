<?php

namespace LibreNMS\Tests\Feature\Http;

use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Testing\TestResponse;
use LibreNMS\Tests\TestCase;
use Spatie\Permission\Models\Role;

final class SettingsControllerTest extends TestCase
{
    use DatabaseTransactions;

    protected function setUp(): void
    {
        parent::setUp();

        Role::findOrCreate('admin');
        Role::findOrCreate('user');
    }

    /**
     * @param  array<int, string>  $parameters
     * @return TestResponse<\Illuminate\Http\Response>
     */
    private function getSettings(array $parameters, string $role = 'admin'): TestResponse
    {
        $user = User::factory()->create(['enabled' => 1]);
        $user->assignRole($role);

        return $this->actingAs($user)->get(route('settings', $parameters));
    }

    /**
     * @param  TestResponse<\Illuminate\Http\Response>  $response
     */
    private function assertActive(TestResponse $response, string $tab, string $section, string $setting): void
    {
        $response->assertOk()
            ->assertViewHas('active_tab', $tab)
            ->assertViewHas('active_section', $section)
            ->assertViewHas('active_setting', $setting);
    }

    public function testTabAndSection(): void
    {
        $this->assertActive($this->getSettings(['poller', 'snmp']), 'poller', 'snmp', '');
    }

    public function testSettingOnly(): void
    {
        $this->assertActive($this->getSettings(['snmp.max_repeaters']), 'poller', 'snmp', 'snmp.max_repeaters');
    }

    public function testSettingWithTabAndSection(): void
    {
        $this->assertActive($this->getSettings(['poller', 'snmp', 'snmp.max_repeaters']), 'poller', 'snmp', 'snmp.max_repeaters');
    }

    public function testSettingCorrectsWrongTabAndSection(): void
    {
        $this->assertActive($this->getSettings(['alerting', 'general', 'snmp.max_repeaters']), 'poller', 'snmp', 'snmp.max_repeaters');
    }

    public function testUnknownSettingIsIgnored(): void
    {
        $this->assertActive($this->getSettings(['poller', 'snmp', 'not.a.setting']), 'poller', 'snmp', '');
    }

    public function testUserCannotView(): void
    {
        $this->getSettings(['snmp.max_repeaters'], 'user')->assertForbidden();
    }
}
