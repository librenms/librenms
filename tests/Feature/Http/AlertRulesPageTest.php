<?php

namespace LibreNMS\Tests\Feature\Http;

use App\Models\AlertRule;
use App\Models\Device;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use LibreNMS\Tests\TestCase;
use Spatie\Permission\Models\Role;

final class AlertRulesPageTest extends TestCase
{
    use DatabaseTransactions;

    protected function setUp(): void
    {
        parent::setUp();

        Role::findOrCreate('admin');
        Role::findOrCreate('user');
        AlertRule::query()->delete(); // the test database may hold the default rules
    }

    public function testIndexListsRules(): void
    {
        $rule = AlertRule::factory()->create(['name' => 'Rule <b>', 'severity' => 'warning']);
        $rule->devices()->attach(Device::factory()->create(['hostname' => 'mapped.example.com']));

        $this->actingAs($this->admin())
            ->get(route('alert-rules.index'))
            ->assertOk()
            ->assertSee('Rule &lt;b&gt;', false)
            ->assertSee('mapped.example.com')
            ->assertSee('Warning')
            ->assertSee('id="create-alert"', false)
            ->assertDontSee('create the default alert rules');
    }

    public function testDevicePageOnlyListsRulesForTheDevice(): void
    {
        $device = Device::factory()->create();
        AlertRule::factory()->create(['name' => 'Global rule']);
        AlertRule::factory()->create(['name' => 'Other device rule'])->devices()->attach(Device::factory()->create());
        AlertRule::factory()->create(['name' => 'This device rule'])->devices()->attach($device);

        $content = $this->actingAs($this->admin())
            ->get(route('device.edit.alert-rules', $device))
            ->assertOk()
            ->getContent();

        // the rules table, not the dialog that copies any existing rule
        $table = \Illuminate\Support\Str::betweenFirst((string) $content, 'id="alert-rules-table"', '</table>');
        $this->assertStringContainsString('Global rule', $table);
        $this->assertStringContainsString('This device rule', $table);
        $this->assertStringNotContainsString('Other device rule', $table);
    }

    public function testCreateDefaultRules(): void
    {
        $admin = $this->admin();

        $this->actingAs($admin)
            ->get(route('alert-rules.index'))
            ->assertSee('create the default alert rules');

        $this->actingAs($admin)
            ->post(route('alert-rules.defaults'))
            ->assertRedirect();

        $this->assertTrue(AlertRule::where('name', 'Device rebooted')->exists());
        $this->assertNotEmpty(AlertRule::where('name', 'Device rebooted')->value('query'));
    }

    public function testCreatingDefaultsRequiresPermission(): void
    {
        $user = User::factory()->create(['enabled' => 1]);
        $user->assignRole('user');

        $this->actingAs($user)->post(route('alert-rules.defaults'))->assertForbidden();
        $this->assertFalse(AlertRule::exists());
    }

    private function admin(): User
    {
        $admin = User::factory()->create(['enabled' => 1]);
        $admin->assignRole('admin');

        return $admin;
    }
}
