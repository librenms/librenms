<?php

namespace App\Api\Controllers\MetricsApi;

use App\Models\Device;
use Illuminate\Http\Request;

class Controller
{
    /**
     * Prometheus metrics for devices
     * Path: /api/v0/metrics/devices
     */
    public function devices(Request $request)
    {
        $total = Device::count();
        $up = Device::where('status', 1)->count();
        $down = Device::where('status', 0)->count();

        $lines = [];

        $lines[] = '# HELP librenms_devices_total Total number of devices';
        $lines[] = '# TYPE librenms_devices_total gauge';
        $lines[] = "librenms_devices_total {$total}";

        $lines[] = '# HELP librenms_devices_up Number of devices currently up';
        $lines[] = '# TYPE librenms_devices_up gauge';
        $lines[] = "librenms_devices_up {$up}";

        $lines[] = '# HELP librenms_devices_down Number of devices currently down';
        $lines[] = '# TYPE librenms_devices_down gauge';
        $lines[] = "librenms_devices_down {$down}";

        // Per-device metrics
        $lines[] = '# HELP librenms_device_up Whether a device is up (1) or not (0)';
        $lines[] = '# TYPE librenms_device_up gauge';

        $lines[] = '# HELP librenms_device_last_polled_seconds Last polled time as Unix timestamp';
        $lines[] = '# TYPE librenms_device_last_polled_seconds gauge';

        $lines[] = '# HELP librenms_device_uptime_seconds Device uptime in seconds (0 if down)';
        $lines[] = '# TYPE librenms_device_uptime_seconds gauge';

        foreach (Device::select('device_id', 'hostname', 'status', 'last_polled', 'uptime')->cursor() as $device) {
            $labels = sprintf('device_id="%s",hostname="%s"', $device->device_id, $this->escapeLabel((string) $device->hostname));

            $lines[] = "librenms_device_up{{$labels}} " . ($device->status ? '1' : '0');

            $lastPolled = $device->last_polled ? $device->last_polled->getTimestamp() : 0;
            $lines[] = "librenms_device_last_polled_seconds{{$labels}} {$lastPolled}";

            $uptime = $device->status ? ((int) $device->uptime ?: 0) : 0;
            $lines[] = "librenms_device_uptime_seconds{{$labels}} {$uptime}";
        }

        $body = implode("\n", $lines) . "\n";

        return response($body, 200, ['Content-Type' => 'text/plain; version=0.0.4; charset=utf-8']);
    }

    private function escapeLabel(string $v): string
    {
        return str_replace(["\\", '"', "\n"], ["\\\\", '\\"', '\\n'], $v);
    }
}
