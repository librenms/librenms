<?php

namespace App\Api\Controllers\MetricsApi;

use App\Models\Device;
use Illuminate\Http\Request;

class DevicesMetrics
{
    use MetricsHelpers;

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
        $lines[] = '# HELP librenms_devices_total Total number of devices';
        $lines[] = '# TYPE librenms_devices_total gauge';
        $lines[] = "librenms_devices_total {$total}";

        $lines[] = '# HELP librenms_devices_up Number of devices currently up';
        $lines[] = '# TYPE librenms_devices_up gauge';
        $lines[] = "librenms_devices_up {$up}";

        $lines[] = '# HELP librenms_devices_down Number of devices currently down';
        $lines[] = '# TYPE librenms_devices_down gauge';
        $lines[] = "librenms_devices_down {$down}";

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
        $lines[] = '# HELP librenms_device_up Whether a device is up (1) or not (0)';
        $lines[] = '# TYPE librenms_device_up gauge';
        $lines = array_merge($lines, $device_up_lines);

        $lines[] = '# HELP librenms_last_polled_timetaken_seconds Last polled time taken in seconds';
        $lines[] = '# TYPE librenms_last_polled_timetaken_seconds gauge';
        $lines = array_merge($lines, $polled_timetaken_lines);

        $lines[] = '# HELP librenms_last_discovered_timetaken_seconds Last discovered time taken in seconds';
        $lines[] = '# TYPE librenms_last_discovered_timetaken_seconds gauge';
        $lines = array_merge($lines, $discovered_timetaken_lines);

        $lines[] = '# HELP librenms_last_ping_timetaken_seconds Last ping time taken in seconds';
        $lines[] = '# TYPE librenms_last_ping_timetaken_seconds gauge';
        $lines = array_merge($lines, $ping_timetaken_lines);

        $lines[] = '# HELP librenms_device_uptime_seconds Device uptime in seconds (0 if down)';
        $lines[] = '# TYPE librenms_device_uptime_seconds gauge';
        $lines = array_merge($lines, $uptime_lines);

        return implode("\n", $lines) . "\n";
    }
}
