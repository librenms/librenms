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
        $header = TRUE;
        foreach (Device::select('device_id', 'hostname', 'sysName', 'type', 'status', 'last_polled_timetaken', 'uptime')->cursor() as $device) {
            $labels = sprintf('device_id="%s",hostname="%s"', $device->device_id, $this->escapeLabel((string) $device->hostname));

            if ($header) {
                $lines[] = '# HELP librenms_device_up Whether a device is up (1) or not (0)';
                $lines[] = '# TYPE librenms_device_up gauge';
            }
            $lines[] = "librenms_device_up{{$labels}} " . ($device->status ? '1' : '0');

            if ($header) {
                $lines[] = '# HELP librenms_last_polled_timetaken Last polled time taken in seconds';
                $lines[] = '# TYPE librenms_last_polled_timetaken gauge';
            }
            $lastPolledTimeTaken = $device->status ? ((int) $device->last_polled_timetaken ?: 0) : 0;
            $lines[] = "librenms_last_polled_timetaken_seconds{{$labels}} {$lastPolledTimeTaken}";

            if ($header) {
                $lines[] = '# HELP librenms_last_discovered_timetaken Last discovered time taken in seconds';
                $lines[] = '# TYPE librenms_last_discovered_timetaken gauge';
            }
            $lastDiscoveredTimeTaken = $device->status ? ((int) $device->last_discovered_timetaken ?: 0) : 0;
            $lines[] = "librenms_last_discovered_timetaken_seconds{{$labels}} {$lastDiscoveredTimeTaken}";

            if ($header) {
                $lines[] = '# HELP librenms_last_ping_timetaken Last ping time taken in seconds';
                $lines[] = '# TYPE librenms_last_ping_timetaken gauge';
            }
            $lastPingTimeTaken = $device->status ? ((int) $device->last_ping_timetaken ?: 0) : 0;
            $lines[] = "librenms_last_ping_timetaken_seconds{{$labels}} {$lastPingTimeTaken}";

            if ($header) {
                $lines[] = '# HELP librenms_device_uptime_seconds Device uptime in seconds (0 if down)';
                $lines[] = '# TYPE librenms_device_uptime_seconds gauge';
            }
            $uptime = $device->status ? ((int) $device->uptime ?: 0) : 0;
            $lines[] = "librenms_device_uptime_seconds{{$labels}} {$uptime}";
        }
        $header = FALSE;

        $body = implode("\n", $lines) . "\n";

        return response($body, 200, ['Content-Type' => 'text/plain; version=0.0.4; charset=utf-8']);
    }

    private function escapeLabel(string $v): string
    {
        return str_replace(["\\", '"', "\n"], ["\\\\", '\\"', '\\n'], $v);
    }
}
