<?php

namespace LibreNMS\Tests\Unit\Http\Controllers\Widgets;

use App\Http\Controllers\Widgets\AvailabilityMapController;
use App\Models\Device;
use App\Models\Service;
use LibreNMS\Tests\TestCase;
use ReflectionClass;

final class AvailabilityMapControllerTest extends TestCase
{
    public function testDevicesAreSortedByDrawnState(): void
    {
        $uptime_warn = 86400;
        $devices = [
            'up' => ['status' => 1, 'uptime' => 500000],
            'disabled' => ['disabled' => 1, 'status' => 0],
            'ignored-up' => ['ignore_status' => 1, 'status' => 0],
            'warn' => ['status' => 1, 'uptime' => 100],
            'ignored-down' => ['ignore' => 1, 'status' => 0],
            'down' => ['status' => 0],
        ];

        $controller = $this->controller();
        $rank = $this->constant('DEVICE_STATE_ORDER');
        $data = [];
        foreach ($devices as $expected_state => $attributes) {
            $device = (new Device())->forceFill($attributes);
            [$state] = $this->invoke($controller, 'parseDeviceState', $device, $uptime_warn);
            $this->assertSame($expected_state, $state);
            $data[] = ['status' => $rank[$state], 'label' => $state];
        }

        $this->invoke($controller, 'sort', $data);

        $this->assertSame(
            ['down', 'warn', 'ignored-down', 'up', 'ignored-up', 'disabled'],
            array_column($data, 'label')
        );
    }

    public function testServicesAreSortedCriticalFirst(): void
    {
        $controller = $this->controller();
        $rank = $this->constant('SERVICE_STATE_ORDER');
        $data = [];
        foreach ([0, 2, 1] as $service_status) {
            $service = (new Service())->forceFill(['service_status' => $service_status]);
            [$state] = $this->invoke($controller, 'parseServiceState', $service);
            $data[] = ['status' => $rank[$state], 'label' => $state];
        }

        $this->invoke($controller, 'sort', $data);

        $this->assertSame(['down', 'warn', 'up'], array_column($data, 'label'));
    }

    private function controller(): AvailabilityMapController
    {
        $controller = new AvailabilityMapController();
        $property = (new ReflectionClass($controller))->getParentClass()->getProperty('settings');
        $property->setValue($controller, ['order_by' => 'status']);

        return $controller;
    }

    private function invoke(AvailabilityMapController $controller, string $method, mixed &...$args): mixed
    {
        return (new ReflectionClass($controller))->getMethod($method)->invokeArgs($controller, $args);
    }

    private function constant(string $name): array
    {
        return (new ReflectionClass(AvailabilityMapController::class))->getConstant($name);
    }
}
