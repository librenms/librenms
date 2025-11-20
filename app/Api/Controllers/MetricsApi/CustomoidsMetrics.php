<?php

namespace App\Api\Controllers\MetricsApi;

use App\Models\Customoid;
use App\Models\Device;
use Illuminate\Http\Request;

class CustomoidsMetrics
{
    use Traits\MetricsHelpers;

    public function render(Request $request): string
    {
        $lines = [];

        // Parse filters
        $filters = $this->parseDeviceFilters($request);

        // Gather global metrics
        $totalQ = Customoid::query();
        $total = $this->applyDeviceFilter($totalQ, $filters['device_ids'])->count();

        // Append global metrics
        $this->appendMetricBlock($lines, 'librenms_customoids_total', 'Total number of customoids', 'gauge', ["librenms_customoids_total {$total}"]);

        // Prepare per-customoid metrics arrays grouped by datatype
        $gauge_value_lines = [];
        $counter_value_lines = [];
        $gauge_limit_warn_lines = [];
        $gauge_limit_crit_lines = [];
        $counter_limit_warn_lines = [];
        $counter_limit_crit_lines = [];

        $deviceIdsQuery = Customoid::select('device_id')->distinct();
        $deviceIdsQuery = $this->applyDeviceFilter($deviceIdsQuery, $filters['device_ids']);
        $deviceIds = $deviceIdsQuery->pluck('device_id');
        $devices = Device::select('device_id', 'hostname', 'sysName', 'type')->whereIn('device_id', $deviceIds)->get()->keyBy('device_id');

        $coQuery = Customoid::select('customoid_id', 'device_id', 'customoid_descr', 'customoid_current', 'customoid_multiplier', 'customoid_divisor', 'customoid_limit_warn', 'customoid_limit', 'customoid_datatype');
        $coQuery = $this->applyDeviceFilter($coQuery, $filters['device_ids']);
        foreach ($coQuery->cursor() as $c) {
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

            // Determine datatype; treat non-GAUGE as counter-like
            $datatype = strtoupper((string) ($c->customoid_datatype ?? 'GAUGE'));
            if ($datatype === 'GAUGE') {
                $gauge_value_lines[] = "librenms_customoids_value{{$labels}} " . ($value ?? 0);
                $gauge_limit_warn_lines[] = "librenms_customoids_limit_warn{{$labels}} " . ((float) ($c->customoid_limit_warn ?? 0));
                $gauge_limit_crit_lines[] = "librenms_customoids_limit_crit{{$labels}} " . ((float) ($c->customoid_limit ?? 0));
            } else {
                // treat as counter
                $counter_value_lines[] = "librenms_customoids_value_counter{{$labels}} " . ($value ?? 0);
                $counter_limit_warn_lines[] = "librenms_customoids_limit_warn_counter{{$labels}} " . ((float) ($c->customoid_limit_warn ?? 0));
                $counter_limit_crit_lines[] = "librenms_customoids_limit_crit_counter{{$labels}} " . ((float) ($c->customoid_limit ?? 0));
            }
        }

        // Append gauge-type customoids
        if (! empty($gauge_value_lines)) {
            $this->appendMetricBlock($lines, 'librenms_customoids_value', 'Custom oid current value', 'gauge', $gauge_value_lines);
            $this->appendMetricBlock($lines, 'librenms_customoids_limit_warn', 'Customoid warning threshold', 'gauge', $gauge_limit_warn_lines);
            $this->appendMetricBlock($lines, 'librenms_customoids_limit_crit', 'Customoid critical threshold', 'gauge', $gauge_limit_crit_lines);
        }

        // Append counter-type customoids (use distinct metric names to avoid TYPE conflicts)
        if (! empty($counter_value_lines)) {
            $this->appendMetricBlock($lines, 'librenms_customoids_value_counter', 'Custom oid current value (counter-like)', 'counter', $counter_value_lines);
            $this->appendMetricBlock($lines, 'librenms_customoids_limit_warn_counter', 'Customoid warning threshold (counter-like)', 'counter', $counter_limit_warn_lines);
            $this->appendMetricBlock($lines, 'librenms_customoids_limit_crit_counter', 'Customoid critical threshold (counter-like)', 'counter', $counter_limit_crit_lines);
        }

        return implode("\n", $lines) . "\n";
    }
}
