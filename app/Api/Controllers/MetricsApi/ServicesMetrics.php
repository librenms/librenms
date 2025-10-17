<?php

namespace App\Api\Controllers\MetricsApi;

use App\Models\Service;
use App\Models\Device;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ServicesMetrics
{
    use MetricsHelpers;

    public function render(Request $request): string
    {
        $lines = [];

        // Gather global metrics
        $total = Service::count();

        // Append global metrics
        $lines[] = '# HELP librenms_services_total Total number of services configured';
        $lines[] = '# TYPE librenms_services_total gauge';
        $lines[] = "librenms_services_total {$total}";

        // counts by status (0=OK,1=WARNING,2=CRITICAL)
        $lines[] = '# HELP librenms_services_by_status Number of services by status (0=OK,1=WARNING,2=CRITICAL)';
        $lines[] = '# TYPE librenms_services_by_status gauge';
        $statuses = Service::select('service_status', DB::raw('count(*) as cnt'))->groupBy('service_status')->get();
        /** @var \stdClass $s */
        foreach ($statuses as $s) {
            $lines[] = sprintf('librenms_services_by_status{status="%s"} %d', $s->service_status, $s->cnt);
        }

        // ignored and disabled
        $ignored = Service::where('service_ignore', 1)->count();
        $disabled = Service::where('service_disabled', 1)->count();
        $lines[] = '# HELP librenms_services_ignored Number of ignored services';
        $lines[] = '# TYPE librenms_services_ignored gauge';
        $lines[] = "librenms_services_ignored {$ignored}";
        $lines[] = '# HELP librenms_services_disabled Number of disabled services';
        $lines[] = '# TYPE librenms_services_disabled gauge';
        $lines[] = "librenms_services_disabled {$disabled}";

        // per-device counts by status (may be high-cardinality)
        $deviceIds = Service::select('device_id')->distinct()->pluck('device_id');
        $devices = Device::select('device_id', 'hostname', 'sysName')->whereIn('device_id', $deviceIds)->get()->keyBy('device_id');

        $lines[] = '# HELP librenms_services_by_device_and_status Number of services per device by status';
        $lines[] = '# TYPE librenms_services_by_device_and_status gauge';
        $rows = Service::select('device_id', 'service_status', DB::raw('count(*) as cnt'))->groupBy('device_id', 'service_status')->cursor();
        /** @var \stdClass $r */
        foreach ($rows as $r) {
            $dev = $devices->get($r->device_id);
            $device_hostname = $dev ? $this->escapeLabel((string) $dev->hostname) : '';
            $device_sysName = $dev ? $this->escapeLabel((string) $dev->sysName) : '';
            $labels = sprintf('device_id="%s",device_hostname="%s",device_sysName="%s",status="%s"',
                $r->device_id,
                $device_hostname,
                $device_sysName,
                $r->service_status
            );
            $lines[] = "librenms_services_by_device_and_status{{$labels}} {$r->cnt}";
        }

        return implode("\n", $lines) . "\n";
    }
}
