<?php

namespace App\Api\Controllers\MetricsApi;

use App\Models\Device;
use App\Models\Port;
use BackedEnum;
use Illuminate\Http\Request;

class PortsMetrics
{
    use Traits\MetricsHelpers;

    private function normalizePortStatus(mixed $status): string
    {
        if ($status instanceof BackedEnum) {
            return strtolower((string) $status->value);
        }

        return strtolower((string) $status);
    }

    public function render(Request $request): string
    {
        $lines = [];

        // Determine scope (global vs detail) and parse filters
        $scope = $this->parseScope($request);
        $includeDetail = $scope === 'detail';

        // Parse filters
        $filters = $this->parseDeviceFilters($request);

        // Gather global metrics
        $totalQ = Port::query();
        $total = $this->applyDeviceFilter($totalQ, $filters['device_ids'])->count();

        // Append global metrics
        $this->appendMetricBlock($lines, 'librenms_ports_total', 'Total number of ports', 'gauge', "librenms_ports_total {$total}");

        // Default to global metrics only; detailed per-access-point metrics are opt-in via ?scope=detail
        if (! $includeDetail) {
            return implode("\n", $lines) . "\n";
        }

        // Prepare per-port metric arrays
        $admin_lines = [];
        $oper_lines = [];
        $speed_lines = [];
        $in_octets_lines = [];
        $out_octets_lines = [];
        $in_ucast_pkt_lines = [];
        $out_ucast_pkt_lines = [];
        $in_errors_lines = [];
        $out_errors_lines = [];

        // Gather per-port counters from Redis poller snapshots
        $snapshots = $this->readRedisPortPayloadSnapshots($filters['device_ids']);
        if (empty($snapshots)) {
            return implode("\n", $lines) . "\n";
        }

        $deviceIds = collect(array_values(array_unique(array_map(fn (array $snapshot) => (int) $snapshot['device_id'], $snapshots))));
        $devices = Device::select('device_id', 'hostname', 'sysName', 'type')->whereIn('device_id', $deviceIds)->get()->keyBy('device_id');

        $ports = Port::select('port_id', 'device_id', 'ifName', 'ifDescr', 'ifIndex', 'ifType', 'ifAlias', 'ifAdminStatus', 'ifOperStatus', 'ifSpeed')
            ->whereIn('device_id', $deviceIds)
            ->get();

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

            $port = null;
            if ($snapshotIfIndex !== '') {
                $port = $portsByDeviceIfIndex[$snapshotDeviceId . ':' . $snapshotIfIndex] ?? null;
            }
            if (! $port && $snapshotIfName !== '') {
                $port = $portsByDeviceIfName[$snapshotDeviceId . ':' . $snapshotIfName] ?? null;
            }
            if (! $port) {
                continue;
            }

            $p = $port;
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

            $adminStatus = $this->normalizePortStatus($p->ifAdminStatus);
            $operStatus = $this->normalizePortStatus($p->ifOperStatus);
            $isAdminUp = $adminStatus === 'up' || str_starts_with($adminStatus, 'up(') || $adminStatus === '1';
            $isOperUp = $operStatus === 'up' || str_starts_with($operStatus, 'up(') || $operStatus === '1';

            $admin_lines[] = "librenms_ports_admin_up{{$labels}} " . ($isAdminUp ? '1' : '0');
            $oper_lines[] = "librenms_ports_oper_up{{$labels}} " . ($isOperUp ? '1' : '0');
            $speed_lines[] = "librenms_ports_speed_bits_per_second{{$labels}} " . ((int) $p->ifSpeed ?: 0);
            $in_octets_lines[] = "librenms_ports_ifInOctets{{$labels}} " . ((int) ($fields['INOCTETS'] ?? $fields['ifInOctets'] ?? 0));
            $out_octets_lines[] = "librenms_ports_ifOutOctets{{$labels}} " . ((int) ($fields['OUTOCTETS'] ?? $fields['ifOutOctets'] ?? 0));
            $in_ucast_pkt_lines[] = "librenms_ports_ifInUcastPkts{{$labels}} " . ((int) ($fields['INUCASTPKTS'] ?? $fields['ifInUcastPkts'] ?? 0));
            $out_ucast_pkt_lines[] = "librenms_ports_ifOutUcastPkts{{$labels}} " . ((int) ($fields['OUTUCASTPKTS'] ?? $fields['ifOutUcastPkts'] ?? 0));
            $in_errors_lines[] = "librenms_ports_ifInErrors{{$labels}} " . ((int) ($fields['INERRORS'] ?? $fields['ifInErrors'] ?? 0));
            $out_errors_lines[] = "librenms_ports_ifOutErrors{{$labels}} " . ((int) ($fields['OUTERRORS'] ?? $fields['ifOutErrors'] ?? 0));
        }

        // Append per-port metrics
        $this->appendMetricBlock($lines, 'librenms_ports_admin_up', 'Whether admin status is up (1) or not (0)', 'gauge', $admin_lines);
        $this->appendMetricBlock($lines, 'librenms_ports_oper_up', 'Whether oper status is up (1) or not (0)', 'gauge', $oper_lines);
        $this->appendMetricBlock($lines, 'librenms_ports_speed_bits_per_second', 'Port speed in bits per second', 'gauge', $speed_lines);
        $this->appendMetricBlock($lines, 'librenms_ports_ifInOctets', 'In octets', 'counter', $in_octets_lines);
        $this->appendMetricBlock($lines, 'librenms_ports_ifOutOctets', 'Out octets', 'counter', $out_octets_lines);
        $this->appendMetricBlock($lines, 'librenms_ports_ifInUcastPkts', 'In unicast packets', 'counter', $in_ucast_pkt_lines);
        $this->appendMetricBlock($lines, 'librenms_ports_ifOutUcastPkts', 'Out unicast packets', 'counter', $out_ucast_pkt_lines);
        $this->appendMetricBlock($lines, 'librenms_ports_ifInErrors', 'In errors', 'counter', $in_errors_lines);
        $this->appendMetricBlock($lines, 'librenms_ports_ifOutErrors', 'Out errors', 'counter', $out_errors_lines);

        return implode("\n", $lines) . "\n";
    }
}
