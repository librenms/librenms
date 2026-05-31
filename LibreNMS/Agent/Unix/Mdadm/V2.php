<?php

namespace LibreNMS\Agent\Unix\Mdadm;

use App\Models\MdadmArray;
use App\Models\MdadmDrive;
use App\Models\StateTranslation;
use LibreNMS\Agent\Application;
use LibreNMS\Enum\Severity;
use LibreNMS\RRD\RrdDefinition;
use LibreNMS\Util\Debug;

/**
 * Handles agent script versions 1 and 2 (legacy payload format).
 *
 * v1 example output:
 *   {"version":"1","error":"0","errorString":"",
 *    "data":[{"name":"md127","level":"raid1","size":"535822336",
 *             "disc_count":"2","hotspare_count":"0",
 *             "device_list":["loop0","loop1"],"missing_device_list":[],
 *             "state":"clean","action":"idle","degraded":"0",
 *             "sync_speed":"0","sync_completed":"100"}]}
 *
 * v2 example output (same shape; key and type differences noted below):
 *   {"data":[{"name":"md127","level":"raid1","size":535822336,
 *             "disc_count":2,"hotspare_count":0,
 *             "device_list":["loop0","loop1"],"missing_devices_list":[],
 *             "state":"clean","action":"idle","degraded":0,
 *             "sync_speed":0,"sync_completed":100}],
 *    "error":0,"errorString":"","version":"2"}
 *
 * v2 error examples:
 *   {"data":[],"error":1,"errorString":"jq_missing!","version":"2"}
 *   {"data":[],"error":2,"errorString":"mdadm array not found!","version":"2"}
 *
 * Field notes:
 *   - `error` / `errorString`  v1 always 0/""; v2 non-zero signals an agent error — processing is skipped
 *   - `size`                   bytes; agent computes /sys/block/<md>/size (512-byte sectors) × 512
 *   - `sync_speed`             B/s; agent reads /sys/…/md/sync_speed (KB/s) × 1024
 *   - `sync_completed`         0–100 %; agent computes done/total × 100 (integer) from sync_completed sysfs file
 *   - `hotspare_count`         slave count minus raid_disks (inferred, may be negative on partial arrays)
 *   - `degraded`               boolean 0/1 from sysfs, NOT a count — use missing list for counts
 *   - No real UUID             → synthetic key 'v2:<name>' for both versions
 *   - v1 uses `missing_device_list`; v2 uses `missing_devices_list` — normalized on entry
 *   - v1 values are strings; v2 values are numbers — PHP casts handle both
 */
class V2 extends Application
{
    /**
     * @param  array<string, mixed>  $payload
     */
    public function discoverLegacy(array $payload): void
    {
        $payload = self::normalize($payload);
        $errorCode = (int) ($payload['error'] ?? 0);
        if ($errorCode !== 0 && $errorCode !== 2) {
            if (Debug::isVerbose()) {
                echo '  mdadm: skipping discovery — tool error ' . $errorCode . ': ' . ($payload['errorString'] ?? '') . PHP_EOL;
            }

            return;
        }
        if ($errorCode === 2 && Debug::isVerbose()) {
            echo '  mdadm: no arrays found (' . ($payload['errorString'] ?? '') . ') — cleaning up stale records' . PHP_EOL;
        }

        $deviceId = $this->os->getDeviceId();
        $appId = $this->app->app_id;
        $seenArrayIds = [];
        $expectedOids = [];

        app()->forgetInstance('sensor-discovery');

        foreach ($payload['data'] ?? [] as $data) {
            if (! is_array($data)) {
                continue;
            }
            $arrayName = (string) ($data['name'] ?? '');
            if ($arrayName === '') {
                continue;
            }

            [$discCount, $hotspare, $failedTotal, $missingExplicit, $action, $isSyncing] = self::parseCounters($data);

            if (Debug::isVerbose()) {
                echo sprintf(
                    '  mdadm: array %-12s  level=%-6s state=%-12s  disks=%d active=%d spare=%d failed=%d' . PHP_EOL,
                    $arrayName,
                    (string) ($data['level'] ?? ''),
                    (string) ($data['state'] ?? ''),
                    $discCount,
                    max(0, $discCount - $hotspare - $failedTotal),
                    $hotspare,
                    $failedTotal
                );
            }

            $arrayRow = MdadmArray::updateOrCreate(
                ['app_id' => $appId, 'uuid' => 'v2:' . $arrayName],
                [
                    'device_id' => $deviceId,
                    'md_id' => $arrayName,
                    'level' => (string) ($data['level'] ?? ''),
                    'size_bytes' => (int) ($data['size'] ?? 0),
                    'raid_disks' => $discCount,
                    'state' => (string) ($data['state'] ?? ''),
                    'active_devices' => max(0, $discCount - $hotspare - $failedTotal),
                    'working_devices' => max(0, $discCount - $failedTotal),
                    'spare_devices' => $hotspare,
                    'failed_devices' => $failedTotal,
                    'degraded' => null,
                    'sync_action' => $action,
                    'sync_completed_pct' => (float) ($data['sync_completed'] ?? 0),
                    'sync_speed_bps' => $isSyncing ? (int) ($data['sync_speed'] ?? 0) : null,
                ]
            );

            $seenArrayIds[] = $arrayRow->id;
            $seenDevIds = [];

            foreach ((array) ($data['device_list'] ?? []) as $devName) {
                $devName = (string) $devName;
                if ($devName === '') {
                    continue;
                }
                MdadmDrive::updateOrCreate(
                    ['mdadm_array_id' => $arrayRow->id, 'dev_id' => $devName],
                    ['device_id' => $deviceId, 'app_id' => $appId, 'path' => $devName, 'is_missing' => false]
                );
                $seenDevIds[] = $devName;
            }

            foreach ((array) ($data['missing_devices_list'] ?? []) as $devName) {
                $devName = (string) $devName;
                if ($devName === '') {
                    continue;
                }
                MdadmDrive::updateOrCreate(
                    ['mdadm_array_id' => $arrayRow->id, 'dev_id' => $devName],
                    ['device_id' => $deviceId, 'app_id' => $appId, 'path' => $devName, 'is_missing' => true]
                );
                $seenDevIds[] = $devName;
            }

            MdadmDrive::where('mdadm_array_id', $arrayRow->id)
                ->whereNotIn('dev_id', $seenDevIds)
                ->delete();

            // Sensors
            $uuid = 'v2:' . $arrayName;
            $group = "Mdadm $arrayName";
            $healthIdx = $uuid . '_health';
            $opIdx = $uuid . '_operation';
            $expectedOids[] = "app:mdadm:$healthIdx";
            $expectedOids[] = "app:mdadm:$opIdx";

            $this->discoverSensor(
                class: 'state',
                type: 'mdadm_array_health_status',
                index: $healthIdx,
                oid: "app:mdadm:$healthIdx",
                descr: "$group Health",
                current: self::mapHealth((string) ($data['state'] ?? ''), $missingExplicit, (int) ($data['degraded'] ?? 0)),
                group: $group,
            )->withStateTranslations('mdadm_array_health_status', self::healthTranslations());

            $this->discoverSensor(
                class: 'state',
                type: 'mdadm_array_operation_status',
                index: $opIdx,
                oid: "app:mdadm:$opIdx",
                descr: "$group Operation",
                current: self::mapOperation($action, (string) ($data['state'] ?? '')),
                group: $group,
            )->withStateTranslations('mdadm_array_operation_status', self::operationTranslations());

            // Per-device health: present → Present (0), missing → Missing (10)
            $devGroup = "$group::devices";
            foreach ((array) ($data['device_list'] ?? []) as $devName) {
                $devName = (string) $devName;
                if ($devName === '') {
                    continue;
                }
                $devIdx = $uuid . '_' . $devName . '_health';
                $expectedOids[] = "app:mdadm:$devIdx";
                if (Debug::isVerbose()) {
                    echo "    mdadm: device $arrayName/$devName  Present" . PHP_EOL;
                }
                $this->discoverSensor(
                    class: 'state',
                    type: 'mdadm_device_health_status',
                    index: $devIdx,
                    oid: "app:mdadm:$devIdx",
                    descr: "$group $devName Health",
                    current: 0,
                    group: $devGroup,
                )->withStateTranslations('mdadm_device_health_status', self::deviceHealthTranslations());
            }
            foreach ((array) ($data['missing_devices_list'] ?? []) as $devName) {
                $devName = (string) $devName;
                if ($devName === '') {
                    continue;
                }
                $devIdx = $uuid . '_' . $devName . '_health';
                $expectedOids[] = "app:mdadm:$devIdx";
                if (Debug::isVerbose()) {
                    echo "    mdadm: device $arrayName/$devName  Missing" . PHP_EOL;
                }
                $this->discoverSensor(
                    class: 'state',
                    type: 'mdadm_device_health_status',
                    index: $devIdx,
                    oid: "app:mdadm:$devIdx",
                    descr: "$group $devName Health",
                    current: 10,
                    group: $devGroup,
                )->withStateTranslations('mdadm_device_health_status', self::deviceHealthTranslations());
            }
        }

        $staleCount = MdadmArray::where('app_id', $appId)->whereNotIn('id', $seenArrayIds)->count();
        if ($staleCount > 0 && Debug::isVerbose()) {
            echo "  mdadm: removing $staleCount stale array(s)" . PHP_EOL;
        }
        MdadmArray::where('app_id', $appId)
            ->whereNotIn('id', $seenArrayIds)
            ->delete();

        $this->logStaleSensorRemovals('app:mdadm:', $expectedOids);
        $this->syncSensors('mdadm_array_health_status', 'mdadm_array_operation_status', 'mdadm_device_health_status');
        $this->deleteStaleAgentSensors(
            oidPrefix: 'app:mdadm:',
            knownTypes: ['mdadm_array_health_status', 'mdadm_array_operation_status', 'mdadm_device_health_status'],
            expectedOids: $expectedOids,
        );
    }

    public function poll(): void
    {
        $payload = $this->fetchPayload('mdadm', 1);
        if ($payload !== null) {
            $this->pollLegacy($payload);
            $this->pollDbLegacy($payload);
        }
    }

    /**
     * RRD poll — legacy graph data for the array.
     *
     * @param  array<string, mixed>  $payload
     */
    public function pollLegacy(array $payload): void
    {
        $payload = self::normalize($payload);
        if (($err = self::agentError($payload)) !== null) {
            \update_application($this->app, $err, []);

            return;
        }
        $name = 'mdadm';
        $rrd_def = RrdDefinition::make()
            ->addDataset('level', 'GAUGE', 0)
            ->addDataset('size', 'GAUGE', 0)
            ->addDataset('disc_count', 'GAUGE', 0)
            ->addDataset('hotspare_count', 'GAUGE', 0)
            ->addDataset('degraded', 'GAUGE', 0)
            ->addDataset('sync_speed', 'GAUGE', 0)
            ->addDataset('sync_completed', 'GAUGE', 0);

        $metrics = [];
        foreach ($payload['data'] ?? [] as $data) {
            if (! is_array($data)) {
                continue;
            }
            $array_name = (string) ($data['name'] ?? '');
            if ($array_name === '') {
                continue;
            }
            $fields = [
                'level' => str_replace('raid', '', (string) ($data['level'] ?? '')),
                'size' => (int) ($data['size'] ?? 0),
                'disc_count' => (int) ($data['disc_count'] ?? 0),
                'hotspare_count' => (int) ($data['hotspare_count'] ?? 0),
                'degraded' => (int) ($data['degraded'] ?? 0),
                'sync_speed' => (int) ($data['sync_speed'] ?? 0),
                'sync_completed' => (float) ($data['sync_completed'] ?? 0),
            ];
            $metrics[$array_name] = $fields;
            $tags = [
                'name' => $array_name,
                'app_id' => $this->app->app_id,
                'rrd_def' => $rrd_def,
                'rrd_name' => ['app', $name, $this->app->app_id, $array_name],
            ];
            app('Datastore')->put($this->os->getDeviceArray(), 'app', $tags, $fields);
        }

        $degradedCount = 0;
        $syncingCount = 0;
        foreach ($metrics as $m) {
            if ($m['degraded'] > 0) {
                $degradedCount++;
            }
            if ($m['sync_speed'] > 0) {
                $syncingCount++;
            }
        }
        $metrics['arrays'] = count($metrics);
        $metrics['devices_total'] = (int) array_sum(array_column($metrics, 'disc_count'));
        $metrics['degraded_arrays'] = $degradedCount;
        $metrics['arrays_syncing'] = $syncingCount;

        \update_application($this->app, 'OK', $metrics);
    }

    /**
     * Keep MdadmArray state columns and sensor values current on every poll cycle.
     *
     * @param  array<string, mixed>  $payload
     */
    public function pollDbLegacy(array $payload): void
    {
        $payload = self::normalize($payload);
        if (($err = self::agentError($payload)) !== null) {
            if (Debug::isVerbose()) {
                echo '  mdadm: skipping poll — ' . $err . PHP_EOL;
            }

            return;
        }
        $appId = $this->app->app_id;
        $sensorValues = [];

        foreach ($payload['data'] ?? [] as $data) {
            if (! is_array($data)) {
                continue;
            }
            $arrayName = (string) ($data['name'] ?? '');
            if ($arrayName === '') {
                continue;
            }

            $arrayRow = MdadmArray::where('app_id', $appId)->where('uuid', 'v2:' . $arrayName)->first();
            if (! $arrayRow instanceof MdadmArray) {
                continue;
            }

            [$discCount, $hotspare, $failedTotal, $missingExplicit, $action, $isSyncing] = self::parseCounters($data);

            $arrayRow->update([
                'state' => (string) ($data['state'] ?? ''),
                'active_devices' => max(0, $discCount - $hotspare - $failedTotal),
                'working_devices' => max(0, $discCount - $failedTotal),
                'spare_devices' => $hotspare,
                'failed_devices' => $failedTotal,
                'degraded' => (int) ($data['degraded'] ?? 0),
                'sync_action' => $action,
                'sync_completed_pct' => (float) ($data['sync_completed'] ?? 0),
                'sync_speed_bps' => $isSyncing ? (int) ($data['sync_speed'] ?? 0) : null,
            ]);

            $uuid = 'v2:' . $arrayName;
            $sensorValues[$uuid . '_health'] = self::mapHealth(
                (string) ($data['state'] ?? ''),
                $missingExplicit,
                (int) ($data['degraded'] ?? 0)
            );
            $sensorValues[$uuid . '_operation'] = self::mapOperation($action, (string) ($data['state'] ?? ''));

            $presentDevs = array_map(strval(...), (array) ($data['device_list'] ?? []));
            $missingDevs = array_map(strval(...), (array) ($data['missing_devices_list'] ?? []));

            foreach (MdadmDrive::where('mdadm_array_id', $arrayRow->id)->pluck('dev_id') as $devName) {
                $devName = (string) $devName;
                if (in_array($devName, $missingDevs, true)) {
                    $sensorValues[$uuid . '_' . $devName . '_health'] = 10;
                    if (Debug::isVerbose()) {
                        echo "    mdadm: device $arrayName/$devName  Missing" . PHP_EOL;
                    }
                } elseif (in_array($devName, $presentDevs, true)) {
                    $sensorValues[$uuid . '_' . $devName . '_health'] = 0;
                    if (Debug::isVerbose()) {
                        echo "    mdadm: device $arrayName/$devName  Present" . PHP_EOL;
                    }
                } else {
                    // Physically removed from sysfs — not in either list; mark Unknown until next discovery
                    $sensorValues[$uuid . '_' . $devName . '_health'] = -1;
                    if (Debug::isVerbose()) {
                        echo "    mdadm: device $arrayName/$devName  removed from sysfs — marking Unknown" . PHP_EOL;
                    }
                }
            }
        }

        if ($sensorValues !== []) {
            $this->updateSensorValues($sensorValues, 'app:mdadm:');
        }
    }

    // -------------------------------------------------------------------------
    // State mappers
    // -------------------------------------------------------------------------

    private static function mapHealth(string $state, int $failedDevices, int $degraded): int
    {
        $s = str_replace('_', '-', strtolower(trim($state)));

        return match (true) {
            $s === 'clear' => 4,
            $s === 'inactive' => 5,
            $s === 'suspended' => 6,
            in_array($s, ['readonly', 'read-only'], true) => 7,
            $s === 'read-auto' => 8,
            $s === 'write-pending' => 9,
            $failedDevices > 0 => 2,
            $degraded > 0 => 1,
            default => 0,
        };
    }

    private static function mapOperation(string $action, string $state): int
    {
        $a = str_replace('_', '-', strtolower(trim($action)));
        $s = str_replace('_', '-', strtolower(trim($state)));

        $map = [
            'idle' => 0,
            'clean' => 1,
            'active' => 2,
            'check' => 3,
            'resync' => 4,
            'recover' => 5,
            'recovery' => 5,
            'repair' => 6,
            'active-idle' => 12,
        ];

        return $map[$a] ?? match (true) {
            $s === 'inactive' => 7,
            in_array($s, ['readonly', 'read-only'], true) => 8,
            default => -1,
        };
    }

    // -------------------------------------------------------------------------
    // State translations
    // -------------------------------------------------------------------------

    /** @return StateTranslation[] */
    private static function healthTranslations(): array
    {
        $s = static fn (string $d, int $v, Severity $sev) => StateTranslation::define($d, $v, $sev);

        return [
            $s('Healthy', 0, Severity::Ok),
            $s('Degraded', 1, Severity::Warning),
            $s('Failed', 2, Severity::Error),
            $s('Clear', 4, Severity::Error),
            $s('Inactive', 5, Severity::Error),
            $s('Suspended', 6, Severity::Error),
            $s('Readonly', 7, Severity::Warning),
            $s('Read Auto', 8, Severity::Warning),
            $s('Write Pending', 9, Severity::Warning),
            $s('Unknown', -1, Severity::Unknown),
        ];
    }

    /** @return StateTranslation[] */
    private static function operationTranslations(): array
    {
        $s = static fn (string $d, int $v, Severity $sev) => StateTranslation::define($d, $v, $sev);

        return [
            $s('Idle', 0, Severity::Ok),
            $s('Clean', 1, Severity::Ok),
            $s('Active', 2, Severity::Ok),
            $s('Check', 3, Severity::Warning),
            $s('Resync', 4, Severity::Warning),
            $s('Recover', 5, Severity::Warning),
            $s('Repair', 6, Severity::Warning),
            $s('Inactive', 7, Severity::Ok),
            $s('Readonly', 8, Severity::Error),
            $s('Active Idle', 12, Severity::Ok),
            $s('Unknown', -1, Severity::Unknown),
        ];
    }

    /** @return StateTranslation[] */
    private static function deviceHealthTranslations(): array
    {
        $s = static fn (string $d, int $v, Severity $sev) => StateTranslation::define($d, $v, $sev);

        return [
            $s('Present', 0, Severity::Ok),
            $s('Missing', 10, Severity::Error),
            $s('Unknown', -1, Severity::Unknown),
        ];
    }

    // -------------------------------------------------------------------------
    // Helpers
    // -------------------------------------------------------------------------

    /**
     * @param  array<string, mixed>  $data
     * @return array{int, int, int, int, string, bool}
     *                                                 [discCount, hotspare, failedTotal, missingExplicit, action, isSyncing]
     *
     * hotspare is inferred from actual slave count, not the agent field (which can be negative
     * when a device is physically removed and drops off sysfs before the agent runs).
     *
     * missingExplicit = count(missing_devices_list): devices with a broken sysfs symlink.
     * failedTotal     = missingExplicit + removedCount: all disks not accounted for by disc_count.
     *
     * Use missingExplicit for health mapping (degraded vs failed distinction);
     * use failedTotal for active/working device stats.
     */
    private static function parseCounters(array $data): array
    {
        $discCount = (int) ($data['disc_count'] ?? 0);
        $missingExplicit = count((array) ($data['missing_devices_list'] ?? []));
        $actualSlaves = count((array) ($data['device_list'] ?? [])) + $missingExplicit;
        $hotspare = max(0, $actualSlaves - $discCount);
        $removedCount = max(0, $discCount - $actualSlaves);
        $failedTotal = $missingExplicit + $removedCount;
        $action = (string) ($data['action'] ?? 'idle');
        $isSyncing = $action !== '' && $action !== 'idle';

        return [$discCount, $hotspare, $failedTotal, $missingExplicit, $action, $isSyncing];
    }

    /**
     * Return the errorString if the `error` field is non-zero, null otherwise. v1 is always 0.
     *
     * @param  array<string, mixed>  $payload
     */
    private static function agentError(array $payload): ?string
    {
        $code = (int) ($payload['error'] ?? 0);

        return $code !== 0 ? (string) ($payload['errorString'] ?? "agent error $code") : null;
    }

    /**
     * Normalize v1 key `missing_device_list` → `missing_devices_list`.
     *
     * @param  array<string, mixed>  $payload
     * @return array<string, mixed>
     */
    private static function normalize(array $payload): array
    {
        foreach ($payload['data'] ?? [] as $i => $item) {
            if (is_array($item) && array_key_exists('missing_device_list', $item) && ! array_key_exists('missing_devices_list', $item)) {
                $payload['data'][$i]['missing_devices_list'] = $item['missing_device_list'];
                unset($payload['data'][$i]['missing_device_list']);
            }
        }

        return $payload;
    }
}
