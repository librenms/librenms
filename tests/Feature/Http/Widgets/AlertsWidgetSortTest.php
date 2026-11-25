<?php

namespace LibreNMS\Tests\Feature\Http\Widgets;

use App\Models\AlertFault;
use App\Models\AlertRule;
use App\Models\Device;
use App\Models\User;
use Dom\HTMLDocument;
use Illuminate\Foundation\Testing\RefreshDatabase;
use LibreNMS\Enum\AlertState;
use LibreNMS\Tests\TestCase;
use PHPUnit\Framework\Attributes\TestWith;
use Spatie\Permission\Models\Role;

final class AlertsWidgetSortTest extends TestCase
{
    use RefreshDatabase;

    /** @param array<string, mixed> $settings */
    #[TestWith([['sort' => 'severity'], 'severity'], 'severity')]
    #[TestWith([[], 'timestamp'], 'unset')]
    #[TestWith([['sort' => ''], 'timestamp'], 'timestamp')]
    #[TestWith([['sort' => 1], 'timestamp'], 'integer one')]
    #[TestWith([['sort' => '1'], 'timestamp'], 'string one')]
    #[TestWith([['sort' => 0], 'timestamp'], 'integer zero')]
    #[TestWith([['sort' => '0'], 'timestamp'], 'string zero')]
    public function testSavedSortIsSelectedAndAppliedToAlerts(array $settings, string $column): void
    {
        $admin = User::factory()->create(['enabled' => 1]);
        $admin->assignRole(Role::findOrCreate('admin'));
        $dashboard = $admin->dashboards()->create(['dashboard_name' => 'Alerts']);
        $widget = $dashboard->widgets()->create([
            'user_id' => $admin->user_id,
            'widget' => 'alerts',
            'col' => 0,
            'row' => 0,
            'size_x' => 4,
            'size_y' => 3,
            'title' => '',
            'settings' => $settings,
        ]);

        $this->actingAs($admin);
        $widgetEndpoint = route('widget.alerts');
        $widgetRequest = ['id' => $widget->user_widget_id];
        $settingsHtml = $this->postJson($widgetEndpoint, $widgetRequest + ['settings' => true])
            ->assertOk()
            ->json('html');
        $settingsView = HTMLDocument::createFromString($settingsHtml, LIBXML_NOERROR);
        $selectedOptions = $settingsView->querySelectorAll('select[name="sort"] option[selected]');
        $this->assertCount(1, $selectedOptions);
        $this->assertSame($column === 'severity' ? 'severity' : '', $selectedOptions->item(0)->getAttribute('value'));

        $widgetHtml = $this->postJson($widgetEndpoint, $widgetRequest)->assertOk()->json('html');
        $widgetView = HTMLDocument::createFromString($widgetHtml, LIBXML_NOERROR);
        $endpoint = route('table.alerts');
        $this->assertStringContainsString('url: "' . $endpoint . '"', $widgetHtml);

        // Bootgrid builds the initial sort request from the column headers.
        $sort = [];
        foreach ($widgetView->querySelectorAll('thead th[data-order]') as $header) {
            $sort[$header->getAttribute('data-column-id')] = $header->getAttribute('data-order');
        }
        $this->assertSame([$column => 'desc'], $sort);

        $device = Device::factory()->create(['disabled' => 0]);
        $olderCritical = $this->createFault($device, 'critical', '2026-01-01 00:00:00');
        $newerCritical = $this->createFault($device, 'critical', '2026-01-02 00:00:00');
        $warning = $this->createFault($device, 'warning', '2026-01-03 00:00:00');

        $this->postJson($endpoint, ['sort' => $sort, 'device_id' => $device->device_id])
            ->assertOk()
            ->assertJsonPath('rows.*.alert_id', $column === 'severity'
                ? [$newerCritical->id, $olderCritical->id, $warning->id]
                : [$warning->id, $newerCritical->id, $olderCritical->id]);
    }

    private function createFault(Device $device, string $severity, string $timestamp): AlertFault
    {
        $rule = AlertRule::factory()->create(['severity' => $severity]);

        return AlertFault::create([
            'rule_id' => $rule->id,
            'device_id' => $device->device_id,
            'entity_key' => (string) $device->device_id,
            'state' => AlertState::ACTIVE,
            'open' => 1,
            'timestamp' => $timestamp,
        ]);
    }
}
