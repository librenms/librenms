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
        $lines = [];
            
        // Gather global metrics
        $total = Device::count();
        $up = Device::where('status', 1)->count();
        $down = Device::where('status', 0)->count();


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
        
        // Gather per-device metrics
        foreach (Device::select('device_id', 'hostname', 'sysName', 'type', 'status', 'last_polled_timetaken', 'last_discovered_timetaken', 'last_ping_timetaken', 'uptime')->cursor() as $device) {
            $labels = sprintf('device_id="%s",hostname="%s",sysName="%s",type="%s"', 
                $device->device_id,
                $this->escapeLabel((string) $device->hostname),
                $this->escapeLabel((string) $device->sysName),
                $this->escapeLabel((string) $device->type));

            // librenms_device_up
            $device_up_lines[] = "librenms_device_up{{$labels}} " . ($device->status ? '1' : '0');

            // librenms_last_polled_timetaken
            $lastPolledTimeTaken = $device->status ? ((int) $device->last_polled_timetaken ?: 0) : 0;
            $polled_timetaken_lines[] = "librenms_last_polled_timetaken_seconds{{$labels}} {$lastPolledTimeTaken}";

            // librenms_last_discovered_timetaken
            $lastDiscoveredTimeTaken = $device->status ? ((int) $device->last_discovered_timetaken ?: 0) : 0;
            $discovered_timetaken_lines[] = "librenms_last_discovered_timetaken_seconds{{$labels}} {$lastDiscoveredTimeTaken}";

            // librenms_last_ping_timetaken
            $lastPingTimeTaken = $device->status ? ((int) $device->last_ping_timetaken ?: 0) : 0;
            $ping_timetaken_lines[] = "librenms_last_ping_timetaken_seconds{{$labels}} {$lastPingTimeTaken}";

            // librenms_device_uptime
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

        // Combine all lines into the response body
        $body = implode("\n", $lines) . "\n";

        // Return the response with appropriate headers
        return response($body, 200, ['Content-Type' => 'text/plain; version=0.0.4; charset=utf-8']);
    }

    private function escapeLabel(string $v): string
    {
        return str_replace(["\\", '"', "\n"], ["\\\\", '\\"', '\\n'], $v);
    }
}
