<?php

namespace App\View\Components\Device\Overview;

use App\Models\Device;
use App\Models\Service;
use Closure;
use Illuminate\Contracts\View\View;
use Illuminate\View\Component;

class Services extends Component
{
    /**
     * @var array{total: int, ok: int, warning: int, critical: int}
     */
    public array $counts;

    /** @var array<int, array{service: \App\Models\Service, status: string, name: string}> */
    public array $serviceLabels;

    public string $servicesUrl;

    public function __construct(public Device $device)
    {
        $this->counts = [
            'total' => $device->services->count(),
            'ok' => $device->services->where('service_status', 0)->count(),
            'warning' => $device->services->where('service_status', 1)->count(),
            'critical' => $device->services->where('service_status', 2)->count(),
        ];
        $this->servicesUrl = route('device', ['device' => $device->device_id, 'tab' => 'services']);
        $this->serviceLabels = $device->services->map(function (Service $service): array {
            $type = strtolower((string) $service->service_type);

            return [
                'service' => $service,
                'status' => match ((int) $service->service_status) {
                    0 => 'success',
                    1 => 'warning',
                    2 => 'danger',
                    default => 'default',
                },
                'name' => filled($service->service_name) && $service->service_name !== $type
                    ? "{$service->service_name} ($type)"
                    : $type,
            ];
        })->values()->all();
    }

    public function render(): View|Closure|string
    {
        return view('components.device.overview.services');
    }
}
