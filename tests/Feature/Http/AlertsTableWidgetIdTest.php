<?php

namespace LibreNMS\Tests\Feature\Http;

use App\Models\AlertFault;
use App\Models\AlertLog;
use App\Models\AlertRule;
use App\Models\Device;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use LibreNMS\Enum\AlertState;
use LibreNMS\Tests\TestCase;
use Spatie\Permission\Models\Role;

class AlertsTableWidgetIdTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        Role::findOrCreate('admin');
    }

    public function testAlertsTableNamespacesIncidentIdsByWidgetId(): void
    {
        $fault = $this->makeFault();

        $rows = $this->actingAs($this->admin())
            ->postJson(route('table.alerts'), ['widget_id' => 47])
            ->assertOk()
            ->json('rows');

        $this->assertRowHasIncidentId($rows[0], 47, $fault->id);
    }

    public function testAlertsTableKeepsIncidentIdsWithoutWidgetId(): void
    {
        $fault = $this->makeFault();

        $rows = $this->actingAs($this->admin())
            ->postJson(route('table.alerts'))
            ->assertOk()
            ->json('rows');

        $this->assertRowHasIncidentId($rows[0], null, $fault->id);
    }

    public function testAlertlogTableNamespacesIncidentIdsByWidgetId(): void
    {
        $log = $this->makeAlertLog();

        $rows = $this->actingAs($this->admin())
            ->postJson(route('table.alertlog'), ['widget_id' => 47])
            ->assertOk()
            ->json('rows');

        $this->assertRowHasIncidentId($rows[0], 47, $log->id);
    }

    public function testAlertlogTableKeepsIncidentIdsWithoutWidgetId(): void
    {
        $log = $this->makeAlertLog();

        $rows = $this->actingAs($this->admin())
            ->postJson(route('table.alertlog'))
            ->assertOk()
            ->json('rows');

        $this->assertRowHasIncidentId($rows[0], null, $log->id);
    }

    private function makeFault(): AlertFault
    {
        $device = Device::factory()->create();
        $rule = AlertRule::factory()->create();

        return AlertFault::create([
            'rule_id' => $rule->id,
            'device_id' => $device->device_id,
            'entity_key' => (string) $device->device_id,
            'state' => AlertState::ACTIVE,
            'open' => 1,
            'alerted' => 1,
            'details' => ['rule' => [['name' => 'test']]],
        ]);
    }

    private function makeAlertLog(): AlertLog
    {
        $device = Device::factory()->create();
        $rule = AlertRule::factory()->create();

        return AlertLog::factory()->create([
            'device_id' => $device->device_id,
            'rule_id' => $rule->id,
            'details' => ['rule' => [['name' => 'test']]],
        ]);
    }

    /**
     * @param  array<string, mixed>  $row
     */
    private function assertRowHasIncidentId(array $row, ?int $widgetId, int $entityId): void
    {
        $incidentId = $widgetId !== null ? "incident{$widgetId}-{$entityId}" : "incident{$entityId}";

        $this->assertStringContainsString("data-target=\"#{$incidentId}\"", $row['details']);
        $this->assertStringContainsString("id=\"{$incidentId}\"", $row['hostname']);
    }

    private function admin(): User
    {
        $admin = User::factory()->create(['enabled' => 1]);
        $admin->assignRole('admin');

        return $admin;
    }
}
