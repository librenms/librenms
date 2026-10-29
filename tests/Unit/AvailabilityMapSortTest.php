<?php

namespace LibreNMS\Tests\Unit;

use App\Http\Controllers\Widgets\AvailabilityMapController;
use LibreNMS\Tests\TestCase;

final class AvailabilityMapSortTest extends TestCase
{
    /**
     * @param  array<int, array{state: string, label: string}>  $data
     * @return array<int, string>
     */
    private function sortedLabels(array $data): array
    {
        $controller = new AvailabilityMapController;
        $settings = new \ReflectionProperty(AvailabilityMapController::class, 'settings');
        $settings->setValue($controller, ['order_by' => 'status']);
        $sort = new \ReflectionMethod(AvailabilityMapController::class, 'sort');
        $sort->invokeArgs($controller, [&$data]);

        return array_column($data, 'label');
    }

    public function testStatusSortUsesDrawnState(): void
    {
        // warn (up, recently rebooted) must sit right after down, not
        // among the up tiles it shares a raw status value with (#20648)
        $this->assertSame(
            ['c-down', 'a-warn', 'b-up', 'e-ignored-up', 'd-disabled'],
            $this->sortedLabels([
                ['state' => 'up', 'label' => 'b-up'],
                ['state' => 'warn', 'label' => 'a-warn'],
                ['state' => 'down', 'label' => 'c-down'],
                ['state' => 'disabled', 'label' => 'd-disabled'],
                ['state' => 'ignored-up', 'label' => 'e-ignored-up'],
            ])
        );
    }

    public function testMaintenanceTilesSortAsOwnGroup(): void
    {
        // a device in maintenance is drawn grey in the new view, so it must
        // not sort inside the down or up blocks it would otherwise join
        $this->assertSame(
            ['d-down', 'i-ignored-down', 'm-maintenance', 'u-up'],
            $this->sortedLabels([
                ['state' => 'up', 'label' => 'u-up'],
                ['state' => 'maintenance', 'label' => 'm-maintenance'],
                ['state' => 'down', 'label' => 'd-down'],
                ['state' => 'ignored-down', 'label' => 'i-ignored-down'],
            ])
        );
    }

    public function testStatusSortPutsCriticalServicesFirst(): void
    {
        // services used to sort OK first (service_status ascending);
        // critical must come first, as before #14073
        $this->assertSame(
            ['svc-down', 'svc-warn', 'svc-up'],
            $this->sortedLabels([
                ['state' => 'up', 'label' => 'svc-up'],
                ['state' => 'down', 'label' => 'svc-down'],
                ['state' => 'warn', 'label' => 'svc-warn'],
            ])
        );
    }
}
