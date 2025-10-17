<?php

namespace App\Api\Controllers\MetricsApi;

use App\Models\Customoid;
use App\Models\Device;
use Illuminate\Http\Request;

class CustomoidsMetrics
{
    use MetricsHelpers;

    public function render(Request $request): string
    {
        $lines = [];

        // Gather global metrics
        $total = Customoid::count();

        // Append global metrics
        $lines[] = '# HELP librenms_customoids_total Total number of customoids';
        $lines[] = '# TYPE librenms_customoids_total gauge';
        $lines[] = "librenms_customoids_total {$total}";

        // Prepare per-customoid metrics arrays
        $value_lines = [];
        $limit_warn_lines = [];
        $limit_crit_lines = [];

        $deviceIds = Customoid::select('device_id')->distinct()->pluck('device_id');
        $devices = Device::select('device_id', 'hostname', 'sysName', 'type')->whereIn('device_id', $deviceIds)->get()->keyBy('device_id');

        foreach (Customoid::select('customoid_id', 'device_id', 'customoid_descr', 'customoid_current', 'customoid_multiplier', 'customoid_divisor', 'customoid_limit_warn', 'customoid_limit')->cursor() as $c) {
            $dev = $devices->get($c->device_id);
            $device_hostname = $dev ? $this->escapeLabel((string) $dev->hostname) : '';
            $device_sysName = $dev ? $this->escapeLabel((string) $dev->sysName) : '';

            $labels = sprintf('customoid_id="%s",device_id="%s",device_hostname="%s",device_sysName="%s",customoid_descr="%s"',
                $c->customoid_id,
                $c->device_id,
                $device_hostname,
                $device_sysName,
                $this->escapeLabel((string) $c->customoid_descr)
            );

            // Apply multiplier/divisor
            $mult = (int) ($c->customoid_multiplier ?: 1);
            $div = (int) ($c->customoid_divisor ?: 1);
            $value = $c->customoid_current !== null ? ((float) $c->customoid_current * $mult / max(1, $div)) : null;

            $value_lines[] = "librenms_customoid_value{{$labels}} " . ($value !== null ? $value : 0);
            $limit_warn_lines[] = "librenms_customoid_limit_warn{{$labels}} " . ((float) ($c->customoid_limit_warn ?? 0));
            $limit_crit_lines[] = "librenms_customoid_limit_crit{{$labels}} " . ((float) ($c->customoid_limit ?? 0));
        }

        // Append per-customoid metrics
        $lines[] = '# HELP librenms_customoid_value Custom oid current value';
        $lines[] = '# TYPE librenms_customoid_value gauge';
        $lines = array_merge($lines, $value_lines);

        $lines[] = '# HELP librenms_customoid_limit_warn Customoid warning threshold';
        $lines[] = '# TYPE librenms_customoid_limit_warn gauge';
        $lines = array_merge($lines, $limit_warn_lines);

        $lines[] = '# HELP librenms_customoid_limit_crit Customoid critical threshold';
        $lines[] = '# TYPE librenms_customoid_limit_crit gauge';
        $lines = array_merge($lines, $limit_crit_lines);

        return implode("\n", $lines) . "\n";
    }
}
