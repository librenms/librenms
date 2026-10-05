<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Device;
use App\Services\CurrentRrdMetricService;
use App\Services\LiveSnmpMetricService;
use App\Services\PolledDeviceMetricService;
use Carbon\CarbonImmutable;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

/**
 * Current device metrics for dashboards: a cached summary from the poller's
 * data, and on-demand SNMP samples (CPU, load, memory, disk and network rates)
 * for clients that show a device live.
 */
class DeviceMetricsController extends Controller
{
    private const CPU_STATES = [
        'user' => 'ssCpuRawUser',
        'nice' => 'ssCpuRawNice',
        'system' => 'ssCpuRawSystem',
        'idle' => 'ssCpuRawIdle',
        'wait' => 'ssCpuRawWait',
        'kernel' => 'ssCpuRawKernel',
        'interrupt' => 'ssCpuRawInterrupt',
        'soft_irq' => 'ssCpuRawSoftIRQ',
        'steal' => 'ssCpuRawSteal',
    ];

    public function __construct(
        private readonly CurrentRrdMetricService $metrics,
        private readonly LiveSnmpMetricService $liveMetrics,
    ) {
    }

    /** The poller's latest values without any SNMP request, cached for 30 seconds. */
    public function summary(string $device): JsonResponse
    {
        $device = $this->findDevice($device);
        $metrics = Cache::remember("device-metric-summary:v1:{$device->device_id}", 30,
            fn () => app(PolledDeviceMetricService::class)->read($device));

        return $this->data($device, ['sampled_at' => $metrics['sampled_at'], 'metrics' => $metrics]);
    }

    /**
     * The most recent device-scoped SNMP sample without waiting for SNMP.
     *
     * With refresh=1 the response is sent first and this worker then takes one
     * counter snapshot, so repeated lightweight reads form an on-demand sampler
     * while every user-visible request stays cache-fast.
     */
    public function realtime(Request $request, string $device): JsonResponse
    {
        $device = $this->findDevice($device);

        $refresh = $request->boolean('refresh');
        if ($refresh && ! app()->runningUnitTests()) {
            $deviceId = (int) $device->device_id;
            app()->terminating(function () use ($deviceId): void {
                $target = Device::find($deviceId);
                if ($target !== null) {
                    app(LiveSnmpMetricService::class)->refreshCache($target);
                }
            });
        }

        return $this->data($device, [
            'refresh_scheduled' => $refresh,
            'metrics' => $this->liveMetrics->cached($device),
            'database_network' => $this->databaseNetwork($device),
        ]);
    }

    /** One synchronous SNMP sample of every supported metric. */
    public function live(string $device): JsonResponse
    {
        $device = $this->findDevice($device);

        return $this->locked("live-snmp-metrics:{$device->device_id}", 'A live sample for this device is already running.',
            fn () => $this->data($device, ['metrics' => $this->liveMetrics->collect($device)]));
    }

    /** One synchronous SNMP sample of the CPU and load metrics only. */
    public function liveLoad(string $device): JsonResponse
    {
        $device = $this->findDevice($device);

        return $this->locked("live-snmp-load:{$device->device_id}", 'A live load sample for this device is already running.',
            fn () => $this->data($device, ['metrics' => $this->liveMetrics->collect($device, false)]));
    }

    /** One synchronous SNMP sample of the interface counters. */
    public function liveNetwork(string $device): JsonResponse
    {
        $device = $this->findDevice($device);

        return $this->locked("live-snmp-network:{$device->device_id}", 'A live network sample for this device is already running.',
            function () use ($device) {
                $sample = $this->liveMetrics->collectNetwork($device);

                return $this->data($device, [
                    'sampled_at' => $sample['sampled_at'],
                    'duration_ms' => $sample['duration_ms'],
                    'network' => $sample['network'],
                    'database_network' => $this->databaseNetwork($device),
                ]);
            });
    }

    /** The current I/O wait percentage from the recent UCD CPU state RRDs. */
    public function ioWait(string $device): JsonResponse
    {
        $device = $this->findDevice($device);

        $now = time();
        $samples = [];
        foreach (self::CPU_STATES as $state => $rrdName) {
            $samples[$state] = $this->metrics->latestAverage(
                $this->metrics->filename($device->hostname, 'ucd_' . $rrdName),
                'value',
                $now,
            );
        }

        $wait = $samples['wait'];
        if ($wait === null) {
            return $this->ioWaitResponse($device, null, null, null, 'No recent UCD I/O wait sample is available.');
        }

        $total = 0.0;
        foreach ($samples as $sample) {
            if ($sample !== null && $sample->timestamp === $wait->timestamp) {
                $total += max(0.0, (float) $sample->get('value'));
            }
        }

        if ($total <= 0.0) {
            return $this->ioWaitResponse($device, null, null, null, 'No matching CPU state samples are available.');
        }

        $rawValue = max(0.0, (float) $wait->get('value'));

        return $this->ioWaitResponse($device, round(min(100.0, $rawValue / $total * 100.0), 4), round($rawValue, 6),
            CarbonImmutable::createFromTimestampUTC($wait->timestamp)->toIso8601String(), null);
    }

    /** Devices are addressed by id or hostname, like the v0 API. */
    private function findDevice(string $identifier): Device
    {
        $device = ctype_digit($identifier)
            ? Device::find((int) $identifier)
            : Device::findByHostname($identifier);
        abort_if($device === null, 404, "Device $identifier does not exist");
        $this->authorize('view', $device);

        return $device;
    }

    /** Runs one sampler at a time per device; a second caller is told to retry. */
    private function locked(string $key, string $busyMessage, callable $sample): JsonResponse
    {
        $lock = Cache::lock($key, 15);
        if (! $lock->get()) {
            return response()->json(['errors' => [['status' => '429', 'title' => 'Too Many Requests', 'detail' => $busyMessage]]], 429);
        }

        try {
            return $sample();
        } finally {
            $lock->release();
        }
    }

    /** @return array<string, mixed> */
    private function databaseNetwork(Device $device): array
    {
        $ports = $device->ports()
            ->select(['ifInOctets_rate', 'ifOutOctets_rate'])
            ->where('deleted', 0)
            ->where('disabled', 0)
            ->where('ignore', 0)
            ->where('ifOperStatus', 'up')
            ->where('ifAdminStatus', 'up')
            ->where('ifType', '!=', 'softwareLoopback')
            ->get();
        $inBps = round($ports->sum(fn ($port) => max(0, (float) $port->ifInOctets_rate)) * 8, 2);
        $outBps = round($ports->sum(fn ($port) => max(0, (float) $port->ifOutOctets_rate)) * 8, 2);

        return [
            'available' => $inBps > 0 || $outBps > 0,
            'in_bps' => $inBps > 0 ? $inBps : null,
            'out_bps' => $outBps > 0 ? $outBps : null,
            'interface_count' => $ports->count(),
            'sampled_at' => $device->last_polled?->toIso8601String(),
            'source' => 'ports_database',
        ];
    }

    private function ioWaitResponse(Device $device, ?float $value, ?float $rawValue, ?string $timestamp, ?string $reason): JsonResponse
    {
        $metric = [
            'name' => 'io_wait',
            'available' => $value !== null,
            'value' => $value,
            'raw_value' => $rawValue,
            'unit' => 'percent',
            'timestamp' => $timestamp,
            'graph' => 'device_ucd_io_wait',
            'source' => 'ucd_cpu_rrd',
        ];
        if ($reason !== null) {
            $metric['reason'] = $reason;
        }

        return $this->data($device, ['metric' => $metric]);
    }

    /** @param  array<string, mixed>  $payload */
    private function data(Device $device, array $payload): JsonResponse
    {
        return response()->json(['data' => ['device_id' => (int) $device->device_id, 'hostname' => $device->hostname] + $payload]);
    }
}
