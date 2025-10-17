<?php

namespace App\Api\Controllers\MetricsApi;

use App\Models\Sensor;
use App\Models\Device;
use Illuminate\Http\Request;

class SensorsMetrics
{
    use MetricsHelpers;

    public function render(Request $request): string
    {
        $lines = [];

        $total = Sensor::count();
        $lines[] = '# HELP librenms_sensors_total Total number of sensors';
        $lines[] = '# TYPE librenms_sensors_total gauge';
        $lines[] = "librenms_sensors_total {$total}";

        $value_lines = [];
        $limit_warn_lines = [];
        $limit_crit_lines = [];

        $deviceIds = Sensor::select('device_id')->distinct()->pluck('device_id');
        $devices = Device::select('device_id', 'hostname', 'sysName', 'type')->whereIn('device_id', $deviceIds)->get()->keyBy('device_id');

        foreach (Sensor::select('sensor_id', 'device_id', 'sensor_class', 'sensor_type', 'sensor_descr', 'sensor_current', 'sensor_divisor', 'sensor_multiplier', 'sensor_limit_warn', 'sensor_limit', 'group')->cursor() as $s) {
            $dev = $devices->get($s->device_id);
            $device_hostname = $dev ? $this->escapeLabel((string) $dev->hostname) : '';
            $device_sysName = $dev ? $this->escapeLabel((string) $dev->sysName) : '';

            $labels = sprintf('sensor_id="%s",device_id="%s",device_hostname="%s",device_sysName="%s",sensor_class="%s",sensor_type="%s",sensor_descr="%s",group="%s"',
                $s->sensor_id,
                $s->device_id,
                $device_hostname,
                $device_sysName,
                $this->escapeLabel((string) $s->sensor_class),
                $this->escapeLabel((string) $s->sensor_type),
                $this->escapeLabel((string) $s->sensor_descr),
                $this->escapeLabel((string) $s->group)
            );

            // apply multiplier/divisor
            $mult = (int) ($s->sensor_multiplier ?: 1);
            $div = (int) ($s->sensor_divisor ?: 1);
            $value = $s->sensor_current !== null ? ((float) $s->sensor_current * $mult / max(1, $div)) : null;

            $value_lines[] = "librenms_sensor_value{{$labels}} " . ($value !== null ? $value : 0);
            $limit_warn_lines[] = "librenms_sensor_limit_warn{{$labels}} " . ((float) ($s->sensor_limit_warn ?? 0));
            $limit_crit_lines[] = "librenms_sensor_limit_crit{{$labels}} " . ((float) ($s->sensor_limit ?? 0));
        }

        $lines[] = '# HELP librenms_sensor_value Current sensor value (units vary by sensor)';
        $lines[] = '# TYPE librenms_sensor_value gauge';
        $lines = array_merge($lines, $value_lines);

        $lines[] = '# HELP librenms_sensor_limit_warn Sensor warning threshold';
        $lines[] = '# TYPE librenms_sensor_limit_warn gauge';
        $lines = array_merge($lines, $limit_warn_lines);

        $lines[] = '# HELP librenms_sensor_limit_crit Sensor critical threshold';
        $lines[] = '# TYPE librenms_sensor_limit_crit gauge';
        $lines = array_merge($lines, $limit_crit_lines);

        return implode("\n", $lines) . "\n";
    }
}
