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

        // Group outputs by rrd_type
        $gauge_value_lines = [];
        $gauge_limit_warn_lines = [];
        $gauge_limit_crit_lines = [];
        $counter_value_lines = [];
        $counter_limit_warn_lines = [];
        $counter_limit_crit_lines = [];

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

            $rrd = strtoupper((string) ($s->rrd_type ?? 'GAUGE'));
            if ($rrd === 'GAUGE') {
                $gauge_value_lines[] = "librenms_wireless_sensor_value{{$labels}} " . ($value !== null ? $value : 0);
                $gauge_limit_warn_lines[] = "librenms_wireless_sensor_limit_warn{{$labels}} " . ((float) ($s->sensor_limit_warn ?? 0));
                $gauge_limit_crit_lines[] = "librenms_wireless_sensor_limit_crit{{$labels}} " . ((float) ($s->sensor_limit ?? 0));
            } else {
                $counter_value_lines[] = "librenms_wireless_sensor_value_counter{{$labels}} " . ($value !== null ? $value : 0);
                $counter_limit_warn_lines[] = "librenms_wireless_sensor_limit_warn_counter{{$labels}} " . ((float) ($s->sensor_limit_warn ?? 0));
                $counter_limit_crit_lines[] = "librenms_wireless_sensor_limit_crit_counter{{$labels}} " . ((float) ($s->sensor_limit ?? 0));
            }
        }

        // Append per-wireless-sensor metrics
        // Append gauge sensors
        if (! empty($gauge_value_lines)) {
            $lines[] = '# HELP librenms_wireless_sensor_value Current wireless sensor value (units vary by sensor)';
            $lines[] = '# TYPE librenms_wireless_sensor_value gauge';
            $lines = array_merge($lines, $gauge_value_lines);

            $lines[] = '# HELP librenms_wireless_sensor_limit_warn Sensor warning threshold';
            $lines[] = '# TYPE librenms_wireless_sensor_limit_warn gauge';
            $lines = array_merge($lines, $gauge_limit_warn_lines);

            $lines[] = '# HELP librenms_wireless_sensor_limit_crit Sensor critical threshold';
            $lines[] = '# TYPE librenms_wireless_sensor_limit_crit gauge';
            $lines = array_merge($lines, $gauge_limit_crit_lines);
        }

        // Append counter-like sensors
        if (! empty($counter_value_lines)) {
            $lines[] = '# HELP librenms_wireless_sensor_value_counter Current wireless sensor value (counter-like)';
            $lines[] = '# TYPE librenms_wireless_sensor_value_counter counter';
            $lines = array_merge($lines, $counter_value_lines);

            $lines[] = '# HELP librenms_wireless_sensor_limit_warn_counter Sensor warning threshold (counter-like)';
            $lines[] = '# TYPE librenms_wireless_sensor_limit_warn_counter counter';
            $lines = array_merge($lines, $counter_limit_warn_lines);

            $lines[] = '# HELP librenms_wireless_sensor_limit_crit_counter Sensor critical threshold (counter-like)';
            $lines[] = '# TYPE librenms_wireless_sensor_limit_crit_counter counter';
            $lines = array_merge($lines, $counter_limit_crit_lines);
        }

        $lines[] = '# HELP librenms_wireless_sensor_limit_warn Wireless sensor warning threshold';
        $lines[] = '# TYPE librenms_wireless_sensor_limit_warn gauge';
        $lines = array_merge($lines, $warn_lines);

        $lines[] = '# HELP librenms_wireless_sensor_limit_crit Wireless sensor critical threshold';
        $lines[] = '# TYPE librenms_wireless_sensor_limit_crit gauge';
        $lines = array_merge($lines, $crit_lines);

        return implode("\n", $lines) . "\n";
    }
}
