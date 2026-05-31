<?php

namespace App\Api\Controllers\MetricsApi;

use App\Models\Device;
use App\Models\Port;
use App\Models\PortStatistic;
use Illuminate\Http\Request;

class PortsStatisticsMetrics
{
    use Traits\MetricsHelpers;

    public function render(Request $request): string
    {
        $lines = [];

        // Determine scope (global vs detail) and parse filters
        $scope = $this->parseScope($request);
        $includeDetail = $scope === 'detail';

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

        // Append global metrics
        $this->appendMetricBlock($lines, 'librenms_ports_statistics_total', 'Total number of ports_statistics rows', 'gauge', [(string) $total]);

        // Default to global metrics only; detailed per-access-point metrics are opt-in via ?scope=detail
        if (! $includeDetail) {
            return implode("\n", $lines) . "\n";
        }

        // Prepare per-port stats arrays
        $in_nucast_lines = [];
        $out_nucast_lines = [];
        $in_discards_lines = [];
        $out_discards_lines = [];
        $in_unknown_proto_lines = [];
        $in_broadcast_lines = [];
        $out_broadcast_lines = [];
        $in_multicast_lines = [];
        $out_multicast_lines = [];

        // Gather per-port counters from Redis poller snapshots
        $snapshots = $this->readRedisPortPayloadSnapshots($filters['device_ids']);
        if (empty($snapshots)) {
            return implode("\n", $lines) . "\n";
        }

        $deviceIds = collect(array_values(array_unique(array_map(fn (array $snapshot) => (int) $snapshot['device_id'], $snapshots))));
        $ports = Port::select('port_id', 'device_id', 'ifName', 'ifDescr', 'ifIndex', 'ifType', 'ifAlias')
            ->whereIn('device_id', $deviceIds)
            ->get();
        $devices = Device::select('device_id', 'hostname', 'sysName', 'type')->whereIn('device_id', $deviceIds)->get()->keyBy('device_id');

        $portsByDeviceIfIndex = [];
        $portsByDeviceIfName = [];
        foreach ($ports as $port) {
            $portDeviceId = (int) $port->device_id;
            $portIfIndex = (string) $port->ifIndex;
            $portIfName = (string) $port->ifName;
            if ($portIfIndex !== '') {
                $portsByDeviceIfIndex[$portDeviceId . ':' . $portIfIndex] = $port;
            }
            if ($portIfName !== '') {
                $portsByDeviceIfName[$portDeviceId . ':' . $portIfName] = $port;
            }
        }

        foreach ($snapshots as $snapshot) {
            $snapshotDeviceId = (int) $snapshot['device_id'];
            $tags = is_array($snapshot['tags'] ?? null) ? $snapshot['tags'] : [];
            $fields = is_array($snapshot['fields'] ?? null) ? $snapshot['fields'] : [];
            $snapshotIfIndex = isset($tags['ifIndex']) ? (string) $tags['ifIndex'] : '';
            $snapshotIfName = isset($tags['ifName']) ? (string) $tags['ifName'] : '';

            $p = null;
            if ($snapshotIfIndex !== '') {
                $p = $portsByDeviceIfIndex[$snapshotDeviceId . ':' . $snapshotIfIndex] ?? null;
            }
            if (! $p && $snapshotIfName !== '') {
                $p = $portsByDeviceIfName[$snapshotDeviceId . ':' . $snapshotIfName] ?? null;
            }
            if (! $p) {
                continue;
            }

            $dev = $devices->get($p->device_id);
            $device_hostname = $dev ? $this->escapeLabel((string) $dev->hostname) : '';
            $device_sysName = $dev ? $this->escapeLabel((string) $dev->sysName) : '';
            $device_type = $dev ? $this->escapeLabel((string) $dev->type) : '';

            $labels = sprintf('port_id="%s",device_id="%s",device_hostname="%s",device_sysName="%s",device_type="%s",ifName="%s",ifDescr="%s",ifIndex="%s",ifType="%s",ifAlias="%s"',
                $p->port_id,
                $p->device_id,
                $device_hostname,
                $device_sysName,
                $device_type,
                $this->escapeLabel((string) $p->ifName),
                $this->escapeLabel((string) $p->ifDescr),
                $this->escapeLabel((string) $p->ifIndex),
                $this->escapeLabel((string) $p->ifType),
                $this->escapeLabel((string) $p->ifAlias)
            );

            $in_nucast_lines[] = "librenms_ports_statistics_ifInNUcastPkts{{$labels}} " . ((int) ($fields['INNUCASTPKTS'] ?? $fields['ifInNUcastPkts'] ?? 0));
            $out_nucast_lines[] = "librenms_ports_statistics_ifOutNUcastPkts{{$labels}} " . ((int) ($fields['OUTNUCASTPKTS'] ?? $fields['ifOutNUcastPkts'] ?? 0));
            $in_discards_lines[] = "librenms_ports_statistics_ifInDiscards{{$labels}} " . ((int) ($fields['INDISCARDS'] ?? $fields['ifInDiscards'] ?? 0));
            $out_discards_lines[] = "librenms_ports_statistics_ifOutDiscards{{$labels}} " . ((int) ($fields['OUTDISCARDS'] ?? $fields['ifOutDiscards'] ?? 0));
            $in_unknown_proto_lines[] = "librenms_ports_statistics_ifInUnknownProtos{{$labels}} " . ((int) ($fields['INUNKNOWNPROTOS'] ?? $fields['ifInUnknownProtos'] ?? 0));
            $in_broadcast_lines[] = "librenms_ports_statistics_ifInBroadcastPkts{{$labels}} " . ((int) ($fields['INBROADCASTPKTS'] ?? $fields['ifInBroadcastPkts'] ?? 0));
            $out_broadcast_lines[] = "librenms_ports_statistics_ifOutBroadcastPkts{{$labels}} " . ((int) ($fields['OUTBROADCASTPKTS'] ?? $fields['ifOutBroadcastPkts'] ?? 0));
            $in_multicast_lines[] = "librenms_ports_statistics_ifInMulticastPkts{{$labels}} " . ((int) ($fields['INMULTICASTPKTS'] ?? $fields['ifInMulticastPkts'] ?? 0));
            $out_multicast_lines[] = "librenms_ports_statistics_ifOutMulticastPkts{{$labels}} " . ((int) ($fields['OUTMULTICASTPKTS'] ?? $fields['ifOutMulticastPkts'] ?? 0));
        }

        // Append per-port metrics
        $this->appendMetricBlock($lines, 'librenms_ports_statistics_ifInNUcastPkts', 'In non-unicast packets', 'counter', $in_nucast_lines);
        $this->appendMetricBlock($lines, 'librenms_ports_statistics_ifOutNUcastPkts', 'Out non-unicast packets', 'counter', $out_nucast_lines);
        $this->appendMetricBlock($lines, 'librenms_ports_statistics_ifInDiscards', 'In discards', 'counter', $in_discards_lines);
        $this->appendMetricBlock($lines, 'librenms_ports_statistics_ifOutDiscards', 'Out discards', 'counter', $out_discards_lines);
        $this->appendMetricBlock($lines, 'librenms_ports_statistics_ifInUnknownProtos', 'In unknown protocols', 'counter', $in_unknown_proto_lines);
        $this->appendMetricBlock($lines, 'librenms_ports_statistics_ifInBroadcastPkts', 'In broadcast packets', 'counter', $in_broadcast_lines);
        $this->appendMetricBlock($lines, 'librenms_ports_statistics_ifOutBroadcastPkts', 'Out broadcast packets', 'counter', $out_broadcast_lines);
        $this->appendMetricBlock($lines, 'librenms_ports_statistics_ifInMulticastPkts', 'In multicast packets', 'counter', $in_multicast_lines);
        $this->appendMetricBlock($lines, 'librenms_ports_statistics_ifOutMulticastPkts', 'Out multicast packets', 'counter', $out_multicast_lines);

        return implode("\n", $lines) . "\n";
    }
}
