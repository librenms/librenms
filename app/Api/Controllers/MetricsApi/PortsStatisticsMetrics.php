<?php

namespace App\Api\Controllers\MetricsApi;

use App\Models\PortStatistic;
use App\Models\Port;
use App\Models\Device;
use Illuminate\Http\Request;

class PortsStatisticsMetrics
{
    use MetricsHelpers;

    public function render(Request $request): string
    {
        $lines = [];

        // Parse filters
        $filters = $this->parseDeviceFilters($request);

        // Gather global metrics
        if ($filters['device_ids']) {
            // PortStatistic doesn't have device_id; translate device_ids -> port_ids
            $portIdsForFilter = Port::whereIn('device_id', $filters['device_ids']->all())->pluck('port_id');
            $total = PortStatistic::whereIn('port_id', $portIdsForFilter)->count();
        } else {
            $total = PortStatistic::count();
        }

        $lines[] = '# HELP librenms_ports_statistics_total Total number of ports_statistics rows';
        $lines[] = '# TYPE librenms_ports_statistics_total gauge';
        $lines[] = "librenms_ports_statistics_total {$total}";

        // Prepare arrays
        $in_nucast_lines = [];
        $out_nucast_lines = [];
        $in_discards_lines = [];
        $out_discards_lines = [];
        $in_unknown_proto_lines = [];
        $in_broadcast_lines = [];
        $out_broadcast_lines = [];
        $in_multicast_lines = [];
        $out_multicast_lines = [];

        // Preload device/port labels mapping
        if ($filters['device_ids']) {
            $portIds = Port::whereIn('device_id', $filters['device_ids']->all())->pluck('port_id');
        } else {
            $portIds = PortStatistic::select('port_id')->distinct()->pluck('port_id');
        }
        $ports = Port::select('port_id', 'device_id', 'ifName', 'ifDescr', 'ifIndex', 'ifType', 'ifAlias')->whereIn('port_id', $portIds)->get()->keyBy('port_id');
        $deviceIds = $ports->pluck('device_id')->unique();
        $devices = Device::select('device_id', 'hostname', 'sysName', 'type')->whereIn('device_id', $deviceIds)->get()->keyBy('device_id');

        if ($filters['device_ids']) {
            $psQuery = PortStatistic::whereIn('port_id', $portIds);
        } else {
            $psQuery = PortStatistic::query();
        }
        foreach ($psQuery->cursor() as $ps) {
            $p = $ports->get($ps->port_id);
            $dev = $p ? $devices->get($p->device_id) : null;
            $device_hostname = $dev ? $this->escapeLabel((string) $dev->hostname) : '';
            $device_sysName = $dev ? $this->escapeLabel((string) $dev->sysName) : '';
            $device_type = $dev ? $this->escapeLabel((string) $dev->type) : '';

            $labels = sprintf('port_id="%s",device_id="%s",device_hostname="%s",device_sysName="%s",device_type="%s",ifName="%s",ifDescr="%s",ifIndex="%s",ifType="%s",ifAlias="%s"',
                $ps->port_id,
                $p ? $p->device_id : '',
                $device_hostname,
                $device_sysName,
                $device_type,
                $this->escapeLabel((string) ($p->ifName ?? '')),
                $this->escapeLabel((string) ($p->ifDescr ?? '')),
                $this->escapeLabel((string) ($p->ifIndex ?? '')),
                $this->escapeLabel((string) ($p->ifType ?? '')),
                $this->escapeLabel((string) ($p->ifAlias ?? ''))
            );

            $in_nucast_lines[] = "librenms_port_ifInNUcastPkts{{$labels}} " . ((int) $ps->ifInNUcastPkts ?: 0);
            $out_nucast_lines[] = "librenms_port_ifOutNUcastPkts{{$labels}} " . ((int) $ps->ifOutNUcastPkts ?: 0);
            $in_discards_lines[] = "librenms_port_ifInDiscards{{$labels}} " . ((int) $ps->ifInDiscards ?: 0);
            $out_discards_lines[] = "librenms_port_ifOutDiscards{{$labels}} " . ((int) $ps->ifOutDiscards ?: 0);
            $in_unknown_proto_lines[] = "librenms_port_ifInUnknownProtos{{$labels}} " . ((int) $ps->ifInUnknownProtos ?: 0);
            $in_broadcast_lines[] = "librenms_port_ifInBroadcastPkts{{$labels}} " . ((int) $ps->ifInBroadcastPkts ?: 0);
            $out_broadcast_lines[] = "librenms_port_ifOutBroadcastPkts{{$labels}} " . ((int) $ps->ifOutBroadcastPkts ?: 0);
            $in_multicast_lines[] = "librenms_port_ifInMulticastPkts{{$labels}} " . ((int) $ps->ifInMulticastPkts ?: 0);
            $out_multicast_lines[] = "librenms_port_ifOutMulticastPkts{{$labels}} " . ((int) $ps->ifOutMulticastPkts ?: 0);
        }

        // Append per-port metrics
        $lines[] = '# HELP librenms_port_ifInNUcastPkts In non-unicast packets';
        $lines[] = '# TYPE librenms_port_ifInNUcastPkts counter';
        $lines = array_merge($lines, $in_nucast_lines);

        $lines[] = '# HELP librenms_port_ifOutNUcastPkts Out non-unicast packets';
        $lines[] = '# TYPE librenms_port_ifOutNUcastPkts counter';
        $lines = array_merge($lines, $out_nucast_lines);

        $lines[] = '# HELP librenms_port_ifInDiscards In discards';
        $lines[] = '# TYPE librenms_port_ifInDiscards counter';
        $lines = array_merge($lines, $in_discards_lines);

        $lines[] = '# HELP librenms_port_ifOutDiscards Out discards';
        $lines[] = '# TYPE librenms_port_ifOutDiscards counter';
        $lines = array_merge($lines, $out_discards_lines);

        $lines[] = '# HELP librenms_port_ifInUnknownProtos In unknown protocols';
        $lines[] = '# TYPE librenms_port_ifInUnknownProtos counter';
        $lines = array_merge($lines, $in_unknown_proto_lines);

        $lines[] = '# HELP librenms_port_ifInBroadcastPkts In broadcast packets';
        $lines[] = '# TYPE librenms_port_ifInBroadcastPkts counter';
        $lines = array_merge($lines, $in_broadcast_lines);

        $lines[] = '# HELP librenms_port_ifOutBroadcastPkts Out broadcast packets';
        $lines[] = '# TYPE librenms_port_ifOutBroadcastPkts counter';
        $lines = array_merge($lines, $out_broadcast_lines);

        $lines[] = '# HELP librenms_port_ifInMulticastPkts In multicast packets';
        $lines[] = '# TYPE librenms_port_ifInMulticastPkts counter';
        $lines = array_merge($lines, $in_multicast_lines);

        $lines[] = '# HELP librenms_port_ifOutMulticastPkts Out multicast packets';
        $lines[] = '# TYPE librenms_port_ifOutMulticastPkts counter';
        $lines = array_merge($lines, $out_multicast_lines);

        return implode("\n", $lines) . "\n";
    }
}
