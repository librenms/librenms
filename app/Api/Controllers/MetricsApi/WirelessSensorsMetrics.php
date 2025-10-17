<?php

namespace App\Api\Controllers\MetricsApi;

use App\Models\Device;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class WirelessSensorsMetrics
{
    use MetricsHelpers;

    public function render(Request $request): string
    {
        $lines = [];

        // Gather global metrics
        $total = DB::table('wireless_sensors')->count();
        
        // Append global metrics
        $lines[] = '# HELP librenms_wireless_sensors_total Total number of wireless sensors';
        $lines[] = '# TYPE librenms_wireless_sensors_total gauge';
        $lines[] = "librenms_wireless_sensors_total {$total}";

        $value_lines = [];
        $warn_lines = [];
        $crit_lines = [];

        // Gather device info mapping for labels
        $deviceIds = DB::table('wireless_sensors')->distinct()->pluck('device_id');
        $devices = Device::select('device_id', 'hostname', 'sysName')->whereIn('device_id', $deviceIds)->get()->keyBy('device_id');

        $rows = DB::table('wireless_sensors')->select('sensor_id','device_id','sensor_class','sensor_type','sensor_descr','sensor_current','sensor_multiplier','sensor_divisor','sensor_limit_warn','sensor_limit')->cursor();

        foreach ($rows as $s) {
            $dev = $devices->get($s->device_id);
            $device_hostname = $dev ? $this->escapeLabel((string) $dev->hostname) : '';
            $device_sysName = $dev ? $this->escapeLabel((string) $dev->sysName) : '';

            $labels = sprintf('sensor_id="%s",device_id="%s",device_hostname="%s",device_sysName="%s",sensor_class="%s",sensor_type="%s",sensor_descr="%s"',
                $s->sensor_id,
                $s->device_id,
                $device_hostname,
                $device_sysName,
                $this->escapeLabel((string) $s->sensor_class),
                $this->escapeLabel((string) $s->sensor_type),
                $this->escapeLabel((string) $s->sensor_descr)
            );

            $mult = (int) ($s->sensor_multiplier ?: 1);
            $div = (int) ($s->sensor_divisor ?: 1);
            $value = $s->sensor_current !== null ? ((float) $s->sensor_current * $mult / max(1, $div)) : null;

            $value_lines[] = "librenms_wireless_sensor_value{{$labels}} " . ($value !== null ? $value : 0);
            $warn_lines[] = "librenms_wireless_sensor_limit_warn{{$labels}} " . ((float) ($s->sensor_limit_warn ?? 0));
            $crit_lines[] = "librenms_wireless_sensor_limit_crit{{$labels}} " . ((float) ($s->sensor_limit ?? 0));
        }

        // Append per-wireless-sensor metrics
        $lines[] = '# HELP librenms_wireless_sensor_value Current wireless sensor value';
        $lines[] = '# TYPE librenms_wireless_sensor_value gauge';
        $lines = array_merge($lines, $value_lines);

        $lines[] = '# HELP librenms_wireless_sensor_limit_warn Wireless sensor warning threshold';
        $lines[] = '# TYPE librenms_wireless_sensor_limit_warn gauge';
        $lines = array_merge($lines, $warn_lines);

        $lines[] = '# HELP librenms_wireless_sensor_limit_crit Wireless sensor critical threshold';
        $lines[] = '# TYPE librenms_wireless_sensor_limit_crit gauge';
        $lines = array_merge($lines, $crit_lines);

        return implode("\n", $lines) . "\n";
    }
}
