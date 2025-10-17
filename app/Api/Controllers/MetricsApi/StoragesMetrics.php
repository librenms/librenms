<?php

namespace App\Api\Controllers\MetricsApi;

use App\Models\Storage;
use App\Models\Device;
use Illuminate\Http\Request;

class StoragesMetrics
{
    use MetricsHelpers;

    public function render(Request $request): string
    {
        $lines = [];

        // Gather global metrics
        $total = Storage::count();

        // Append global metrics
        $lines[] = '# HELP librenms_storages_total Total number of storage entries';
        $lines[] = '# TYPE librenms_storages_total gauge';
        $lines[] = "librenms_storages_total {$total}";

        $used_lines = [];
        $free_lines = [];
        $total_lines = [];
        $perc_lines = [];

        // Gather device info mapping for labels
        $deviceIds = Storage::select('device_id')->distinct()->pluck('device_id');
        $devices = Device::select('device_id', 'hostname', 'sysName')->whereIn('device_id', $deviceIds)->get()->keyBy('device_id');

        foreach (Storage::select('storage_id', 'device_id', 'storage_descr', 'storage_size', 'storage_used', 'storage_free', 'storage_perc')->cursor() as $s) {
            $dev = $devices->get($s->device_id);
            $device_hostname = $dev ? $this->escapeLabel((string) $dev->hostname) : '';
            $device_sysName = $dev ? $this->escapeLabel((string) $dev->sysName) : '';

            $labels = sprintf('storage_id="%s",device_id="%s",device_hostname="%s",device_sysName="%s",storage_descr="%s"',
                $s->storage_id,
                $s->device_id,
                $device_hostname,
                $device_sysName,
                $this->escapeLabel((string) $s->storage_descr)
            );

            $used_lines[] = "librenms_storage_used_bytes{{$labels}} " . ((float) $s->storage_used ?: 0);
            $free_lines[] = "librenms_storage_free_bytes{{$labels}} " . ((float) $s->storage_free ?: 0);
            $total_lines[] = "librenms_storage_total_bytes{{$labels}} " . ((float) $s->storage_size ?: 0);
            $perc_lines[] = "librenms_storage_used_percent{{$labels}} " . ((float) $s->storage_perc ?: 0);
        }

        // Append per-storage metrics
        $lines[] = '# HELP librenms_storage_used_bytes Used bytes in storage';
        $lines[] = '# TYPE librenms_storage_used_bytes gauge';
        $lines = array_merge($lines, $used_lines);

        $lines[] = '# HELP librenms_storage_free_bytes Free bytes in storage';
        $lines[] = '# TYPE librenms_storage_free_bytes gauge';
        $lines = array_merge($lines, $free_lines);

        $lines[] = '# HELP librenms_storage_total_bytes Total bytes in storage';
        $lines[] = '# TYPE librenms_storage_total_bytes gauge';
        $lines = array_merge($lines, $total_lines);

        $lines[] = '# HELP librenms_storage_used_percent Percent used in storage';
        $lines[] = '# TYPE librenms_storage_used_percent gauge';
        $lines = array_merge($lines, $perc_lines);

        return implode("\n", $lines) . "\n";
    }
}
