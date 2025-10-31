<?php

namespace App\Api\Controllers\MetricsApi;

use App\Models\Device;
use Illuminate\Http\Request;

class DevicesMetrics
{
    use Traits\MetricsHelpers;

    public function render(Request $request): string
    {
        $lines = [];

        // Parse filters
        $filters = $this->parseDeviceFilters($request);

        // Gather global metrics
        $totalQ = Device::query();
        $upQ = Device::query()->where('status', 1);
        $downQ = Device::query()->where('status', 0);
        $total = $this->applyDeviceFilter($totalQ, $filters['device_ids'])->count();
        $up = $this->applyDeviceFilter($upQ, $filters['device_ids'])->count();
        $down = $this->applyDeviceFilter($downQ, $filters['device_ids'])->count();

        // Append global metrics
        $this->appendMetricBlock($lines, 'librenms_devices_total', 'Total number of devices', 'gauge', ["librenms_devices_total {$total}"]);
        $this->appendMetricBlock($lines, 'librenms_devices_up', 'Number of devices currently up', 'gauge', ["librenms_devices_up {$up}"]);
        $this->appendMetricBlock($lines, 'librenms_devices_down', 'Number of devices currently down', 'gauge', ["librenms_devices_down {$down}"]);

        // Prepare per-device arrays
        $device_up_lines = [];
        $polled_timetaken_lines = [];
        $discovered_timetaken_lines = [];
        $ping_timetaken_lines = [];
        $uptime_lines = [];

        // Gather per-device metrics
        $deviceQuery = Device::select('device_id', 'hostname', 'sysName', 'type', 'status', 'last_polled_timetaken', 'last_discovered_timetaken', 'last_ping_timetaken', 'uptime');
        $deviceQuery = $this->applyDeviceFilter($deviceQuery, $filters['device_ids']);
        foreach ($deviceQuery->cursor() as $device) {
            $labels = sprintf('device_id="%s",device_hostname="%s",device_sysName="%s",device_type="%s"',
                $device->device_id,
                $this->escapeLabel((string) $device->hostname),
                $this->escapeLabel((string) $device->sysName),
                $this->escapeLabel((string) $device->type));

            $device_up_lines[] = "librenms_device_up{{$labels}} " . ($device->status ? '1' : '0');

            $lastPolledTimeTaken = $device->status ? ((int) $device->last_polled_timetaken ?: 0) : 0;
            $polled_timetaken_lines[] = "librenms_last_polled_timetaken_seconds{{$labels}} {$lastPolledTimeTaken}";

            $lastDiscoveredTimeTaken = $device->status ? ((int) $device->last_discovered_timetaken ?: 0) : 0;
            $discovered_timetaken_lines[] = "librenms_last_discovered_timetaken_seconds{{$labels}} {$lastDiscoveredTimeTaken}";

            $lastPingTimeTaken = $device->status ? ((int) $device->last_ping_timetaken ?: 0) : 0;
            $ping_timetaken_lines[] = "librenms_last_ping_timetaken_seconds{{$labels}} {$lastPingTimeTaken}";

            $uptime = $device->status ? ((int) $device->uptime ?: 0) : 0;
            $uptime_lines[] = "librenms_device_uptime_seconds{{$labels}} {$uptime}";
        }

        // Append per-device metrics
        $this->appendMetricBlock($lines, 'librenms_device_up', 'Whether a device is up (1) or not (0)', 'gauge', $device_up_lines);
        $this->appendMetricBlock($lines, 'librenms_last_polled_timetaken_seconds', 'Last polled time taken in seconds', 'gauge', $polled_timetaken_lines);
        $this->appendMetricBlock($lines, 'librenms_last_discovered_timetaken_seconds', 'Last discovered time taken in seconds', 'gauge', $discovered_timetaken_lines);
        $this->appendMetricBlock($lines, 'librenms_last_ping_timetaken_seconds', 'Last ping time taken in seconds', 'gauge', $ping_timetaken_lines);
        $this->appendMetricBlock($lines, 'librenms_device_uptime_seconds', 'Device uptime in seconds (0 if down)', 'gauge', $uptime_lines);

        return implode("\n", $lines) . "\n";
    }
}
