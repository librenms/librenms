<?php

namespace App\Services;

use App\Models\Device;
use Carbon\CarbonImmutable;

/** Read the poller's database/RRD values; never initiate an SNMP request. */
class PolledDeviceMetricService
{
    public function __construct(private readonly CurrentRrdMetricService $rrd) {}

    public function read(Device $device): array
    {
        $date = $device->last_polled?->toIso8601String();
        $value = fn ($number) => ['available' => $number !== null, 'value' => $number === null ? null : (float) $number, 'sampled_at' => $date];
        $processors = $device->processors;
        $cpu = $value($processors->whereNotNull('processor_usage')->avg('processor_usage'));
        $pool = $device->mempools->first(fn ($p) => stripos($p->mempool_descr, 'physical') !== false)
            ?? $device->mempools->first(fn ($p) => ! preg_match('/swap|virtual|cache|buffer/i', $p->mempool_descr));
        $memory = $value($pool?->mempool_perc);
        $memory['used_bytes'] = $pool?->mempool_used === null ? null : (float) $pool->mempool_used;
        $memory['total_bytes'] = $pool?->mempool_total === null ? null : (float) $pool->mempool_total;
        $states = [];
        foreach (['user' => 'User', 'nice' => 'Nice', 'system' => 'System', 'idle' => 'Idle', 'wait' => 'Wait', 'steal' => 'Steal'] as $key => $name) {
            $states[$key] = $this->rrd->latestAverage($this->rrd->filename($device->hostname, 'ucd_ssCpuRaw' . $name));
        }
        $wait = $value(null);
        $stateMetrics = self::cpuStates($states);
        if ($stateMetrics !== null) {
            $cpu = array_merge($cpu, $stateMetrics['cpu']);
            $wait = $stateMetrics['io_wait'];
        }
        $loadPoints = $this->rrd->latestAverages($this->rrd->filename($device->hostname, 'ucd_load'), ['1min', '5min', '15min']);
        $load = ['available' => false];
        foreach (['one' => '1min', 'five' => '5min', 'fifteen' => '15min'] as $key => $ds) {
            $point = $loadPoints[$ds] ?? null;
            $load[$key] = $point ? max(0, $point->get($ds) / 100) : null;
            $load['available'] = $load['available'] || $point !== null;
        }
        $diskSamples = [];
        foreach (LiveSnmpMetricService::selectDisks($device->diskIo->toArray()) as $disk) {
            $diskSamples[] = $this->rrd->latestAverages($this->rrd->filename($device->hostname, ['ucd_diskio', $disk['diskio_descr']]), ['read', 'written', 'reads', 'writes']);
        }
        $ports = $device->ports->filter(fn ($p) => ! $p->deleted && ! $p->disabled && ! $p->ignore && $p->ifOperStatus === \LibreNMS\Enum\IfOperStatus::Up && $p->ifAdminStatus === \LibreNMS\Enum\IfOperStatus::Up && $p->ifType !== 'softwareLoopback');
        $network = ['available' => $ports->isNotEmpty(), 'interface_count' => $ports->count(), 'sampled_at' => $date,
            'in_bps' => $ports->isEmpty() ? null : $ports->sum(fn ($p) => max(0, (float) $p->ifInOctets_rate)) * 8,
            'out_bps' => $ports->isEmpty() ? null : $ports->sum(fn ($p) => max(0, (float) $p->ifOutOctets_rate)) * 8];

        return ['source' => 'poller_database_rrd', 'sampled_at' => $date, 'processor_count' => $processors->count() ?: null,
            'cpu' => $cpu, 'memory' => $memory, 'io_wait' => $wait, 'load' => $load, 'disk' => self::diskRates($diskSamples), 'network' => $network];
    }

    public static function cpuStates(array $points): ?array
    {
        $timestamp = isset($points['idle']) ? $points['idle']->timestamp : null;
        foreach (['user', 'nice', 'system', 'idle', 'wait'] as $key) {
            if ($timestamp === null || ! isset($points[$key]) || $points[$key]->timestamp !== $timestamp) {
                return null;
            }
        }
        $rates = [];
        foreach ($points as $key => $point) {
            $rates[$key] = $point?->timestamp === $timestamp ? max(0, $point->get('value')) : 0;
        }
        $total = array_sum($rates);
        if ($total <= 0) {
            return null;
        }
        $percent = fn ($rate) => round($rate / $total * 100, 4);
        $base = ['available' => true, 'sampled_at' => CarbonImmutable::createFromTimestampUTC($timestamp)->toIso8601String()];

        return ['cpu' => array_merge($base, ['value' => $percent($rates['user'] + $rates['nice'] + $rates['system'] + ($rates['steal'] ?? 0)),
            'user' => $percent($rates['user'] + $rates['nice']), 'system' => $percent($rates['system']), 'idle' => $percent($rates['idle']),
            'steal' => isset($points['steal']) && $points['steal']->timestamp === $timestamp ? $percent($rates['steal']) : null]),
            'io_wait' => array_merge($base, ['value' => $percent($rates['wait'])])];
    }

    public static function diskRates(array $samples): array
    {
        $valid = array_values(array_filter($samples, fn ($s) => isset($s['read'], $s['written']) && $s['read']->timestamp === $s['written']->timestamp));
        $result = ['available' => $valid !== [], 'sampled_at' => $valid ? CarbonImmutable::createFromTimestampUTC(min(array_map(fn ($s) => $s['read']->timestamp, $valid)))->toIso8601String() : null,
            'disk_count' => count($valid), 'partial' => count($valid) < count($samples)];
        foreach (['read_bytes_per_second' => 'read', 'write_bytes_per_second' => 'written', 'read_iops' => 'reads', 'write_iops' => 'writes'] as $key => $ds) {
            $result[$key] = $valid && count(array_filter($valid, fn ($s) => isset($s[$ds]) && $s[$ds]->timestamp === $s['read']->timestamp)) === count($valid)
                ? array_sum(array_map(fn ($s) => max(0, $s[$ds]->get($ds)), $valid)) : null;
        }

        return $result;
    }
}
