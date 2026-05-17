<?php

namespace LibreNMS\Agent\Unix\Mdadm;

use App\Models\MdadmArray;
use App\Models\MdadmDrive;
use App\Models\StateTranslation;
use Illuminate\Database\Eloquent\Collection;
use LibreNMS\Agent\Application;
use LibreNMS\Enum\Severity;
use LibreNMS\RRD\RrdDefinition;
use LibreNMS\Util\Debug;

class Common extends Application
{
    private bool $discoveryCompleted = false;
    private array $payload = [];
    private array $plarray = [];
    private array $Working = [];
    /** @var Collection<string, MdadmArray> MdadmArray rows (with drives) as they existed before this poll cycle, keyed by uuid. */
    private Collection $dbArraysPrev;
    private array $discovery = [
        'sync'        => [],
        'array_count' => 0,
        'arrays'      => [],
    ];

    // -------------------------------------------------------------------------
    // State translation tables
    // -------------------------------------------------------------------------

    private static function state(string $descr, int $value, Severity $severity): StateTranslation
    {
        return StateTranslation::define($descr, $value, $severity);
    }

    private function arrayHealthTranslations(): array
    {
        return [
            self::state('Healthy', 0, Severity::Ok),
            self::state('Degraded', 1, Severity::Warning),
            self::state('Failed Devices', 2, Severity::Error),
            self::state('Missing Device', 3, Severity::Error),
            self::state('Clear', 4, Severity::Error),
            self::state('Inactive', 5, Severity::Error),
            self::state('Suspended', 6, Severity::Error),
            self::state('Readonly', 7, Severity::Warning),
            self::state('Read Auto', 8, Severity::Warning),
            self::state('Write Pending', 9, Severity::Warning),
            self::state('Unknown', -1, Severity::Unknown),
        ];
    }

    private function arrayOperationTranslations(): array
    {
        return [
            self::state('Idle', 0, Severity::Ok),
            self::state('Clean', 1, Severity::Ok),
            self::state('Active', 2, Severity::Ok),
            self::state('Check', 3, Severity::Warning),
            self::state('Resync', 4, Severity::Warning),
            self::state('Recover', 5, Severity::Warning),
            self::state('Repair', 6, Severity::Warning),
            self::state('Inactive', 7, Severity::Ok),
            self::state('Readonly', 8, Severity::Error),
            self::state('Clear', 9, Severity::Ok),
            self::state('Read Auto', 10, Severity::Ok),
            self::state('Write Pending', 11, Severity::Warning),
            self::state('Active Idle', 12, Severity::Ok),
            self::state('Suspended', 13, Severity::Warning),
            self::state('Unknown', -1, Severity::Unknown),
        ];
    }

    private function deviceHealthTranslations(): array
    {
        return [
            self::state('In Sync', 0, Severity::Ok),
            self::state('Active', 1, Severity::Ok),
            self::state('Write Mostly', 2, Severity::Ok),
            self::state('Spare', 3, Severity::Ok),
            self::state('Rebuilding', 4, Severity::Warning),
            self::state('Want Replacement', 5, Severity::Warning),
            self::state('Replacement', 6, Severity::Warning),
            self::state('Write Error', 7, Severity::Error),
            self::state('Blocked', 8, Severity::Error),
            self::state('Faulty', 9, Severity::Error),
            self::state('Missing', 10, Severity::Error),
            self::state('Unknown', -1, Severity::Unknown),
        ];
    }

    // -------------------------------------------------------------------------
    // Health / operation mappers
    // -------------------------------------------------------------------------

    private function mapArrayHealth(array $array, int $maxDeviceHealth): int
    {
        if (! isset($array['state'], $array['failed_devices'], $array['degraded'])) {
            return -1;
        }

        $arrayState = str_replace('_', '-', strtolower(trim((string) ($array['state'] ?? ''))));

        if ($arrayState === 'clear') {
            return 4;
        }
        if ($arrayState === 'inactive') {
            return 5;
        }
        if ($arrayState === 'suspended') {
            return 6;
        }
        if (in_array($arrayState, ['readonly', 'read-only'], true)) {
            return 7;
        }
        if ($arrayState === 'read-auto') {
            return 8;
        }
        if ($arrayState === 'write-pending') {
            return 9;
        }
        if ($maxDeviceHealth === 10) {
            return 3;
        }
        if ($maxDeviceHealth >= 9) {
            return 2;
        }

        $failedDevices = (int) $array['failed_devices'];
        $degraded = (int) $array['degraded'];
        $activeDevices = (int) ($array['active_devices'] ?? 0);
        $workingDevices = (int) ($array['working_devices'] ?? 0);

        if ($degraded > 0 || $failedDevices > 0) {
            return ($activeDevices === 0 || $workingDevices === 0) ? 2 : 1;
        }

        return 0;
    }

    private function maxKnownDeviceHealth(string $uuid): int
    {
        $values = array_filter(
            $this->Working[$uuid]['devHealth'] ?? [],
            static fn ($v) => is_int($v) && $v >= 0
        );

        return $values === [] ? -1 : max($values);
    }

    private function mapArrayOperation(array $array): int
    {
        $operation = str_replace('_', '-', strtolower(trim((string) ($array['sync']['action'] ?? ''))));
        $operationMap = [
            'idle'        => 0,
            'clean'       => 1,
            'active'      => 2,
            'check'       => 3,
            'resync'      => 4,
            'recover'     => 5,
            'recovery'    => 5,
            'repair'      => 6,
            'active-idle' => 12,
        ];

        return $operationMap[$operation] ?? -1;
    }

    private function mapDeviceHealth(array $device): int
    {
        if (($device['is_missing'] ?? null) === true) {
            return 7;
        }

        $flags = array_map('strtolower', $device['state_flags'] ?? []);
        $state = strtolower(trim((string) ($device['state'] ?? '')));

        foreach (['faulty' => 8, 'blocked' => 9, 'write_error' => 10, 'want_replacement' => 5, 'replacement' => 6] as $flag => $val) {
            if (in_array($flag, $flags, true)) {
                return $val;
            }
        }

        foreach (['rebuild' => 4, 'recover' => 4, 'spare' => 3, 'active sync' => 0] as $fragment => $val) {
            if (str_contains($state, $fragment)) {
                return $val;
            }
        }

        foreach (['spare' => 3, 'in_sync' => 0, 'clean' => 0, 'active' => 1, 'writemostly' => 2, 'write_mostly' => 2] as $flag => $val) {
            if (in_array($flag, $flags, true)) {
                return $val;
            }
        }

        return ['clean' => 0, 'active' => 1][$state] ?? -1;
    }

    public function discover(): void
    {
        $payload = $this->fetchPayload('mdadm', 1);
        if ($payload === null || ($payload['version'] ?? 0) < 3) {
            return;
        }
        $this->initState($payload);
        $this->runDiscovery();
        $this->runDiscoveryDb();
        $this->discoveryCompleted = true;
    }

    public function shouldPoll(): bool
    {
        return MdadmArray::where('app_id', $this->app->app_id)->exists();
    }

    public function poll(): void
    {
        $payload = $this->fetchPayload('mdadm', 1);
        if ($payload === null) {
            return;
        }
        $version = $payload['version'] ?? 0;
        if ($version < 2) {
            (new V1($this->os, $this->app, $this->agent_data))->pollLegacy($payload);

            return;
        }
        if ($version < 3) {
            (new V2($this->os, $this->app, $this->agent_data))->pollLegacy($payload);

            return;
        }
        $this->initState($payload);
        $this->runPoll();
        $this->runPollRrd();
        $this->runPollDb();
        \update_application($this->app, 'ok', $this->collectMetrics());
    }

    private function initState(array $payload): void
    {
        $this->payload = $payload;
        $this->plarray = $payload['data']['tables']['arrays'] ?? [];
        $this->dbArraysPrev = MdadmArray::where('app_id', $this->app->app_id)
            ->with('drives')
            ->get()
            ->keyBy('uuid');
    }

    public function printDiscoverySummary(): void
    {
        echo PHP_EOL;

        if (! $this->discoveryCompleted || ! Debug::isVerbose()) {
            return;
        }

        $arrays = $this->discovery['arrays'] ?? [];
        echo '    Arrays: ' . count($arrays) . PHP_EOL;

        foreach ($arrays as $uuid => $discoveryEntry) {
            $arr = $this->plarray[(string) $uuid]['array'] ?? [];
            $name = (string) ($arr['name'] ?? $uuid);
            $level = (string) ($arr['raid_level'] ?? '');
            $state = (string) ($arr['state'] ?? '');
            $active = (int) ($arr['active_devices'] ?? 0);
            $spare = (int) ($arr['spare_devices'] ?? 0);
            $failed = (int) ($arr['failed_devices'] ?? 0);
            $mismatch = (int) ($arr['mismatch_cnt'] ?? 0);

            echo sprintf(
                '      %s (%s, %s)  active:%d spare:%d failed:%d mismatch:%d' . PHP_EOL,
                $name, $level, $state, $active, $spare, $failed, $mismatch
            );

            foreach ($this->plarray[(string) $uuid]['devices'] ?? [] as $devId => $devData) {
                $devData = is_array($devData) ? $devData : [];
                $path = (string) ($devData['device_name'] ?? $devId);
                $devState = (string) ($devData['state'] ?? '');
                $errors = (int) ($devData['errors'] ?? 0);

                echo sprintf(
                    '        %s  %s  %s  errors:%d' . PHP_EOL,
                    $devId, $path, $devState, $errors
                );
            }
        }
    }

    private function runDiscovery(): void
    {
        $this->discovery = [];
        $this->discovery['array_count'] = $this->payload['data']['counters']['arrays'];
        $this->discovery['device_count'] = $this->payload['data']['counters']['devices_total'] ?? 0;

        app()->forgetInstance('sensor-discovery');

        foreach (array_keys($this->plarray) as $uuid) {
            $this->discovery['arrays'][(string) $uuid] = [
                'devices_count' => count($this->plarray[$uuid]['devices']),
                'devices'       => [],
            ];
            $this->discoveryArray((string) $uuid);
        }

        $this->syncSensors(
            'mdadm_array_health_status',
            'mdadm_array_operation_status',
            'mdadm_array_mismatch',
            'mdadm_device_health_status',
            'mdadm_device_error',
        );

        $expectedOids = [];
        foreach (array_keys($this->plarray) as $uuid) {
            $expectedOids[] = "app:mdadm:{$uuid}_health";
            $expectedOids[] = "app:mdadm:{$uuid}_operation";
            $expectedOids[] = "app:mdadm:{$uuid}_mismatch";
            foreach (array_keys($this->plarray[$uuid]['devices'] ?? []) as $devId) {
                $expectedOids[] = "app:mdadm:{$uuid}_{$devId}_health";
                $expectedOids[] = "app:mdadm:{$uuid}_{$devId}_errors";
            }
        }

        $this->deleteStaleAgentSensors(
            oidPrefix: 'app:mdadm:',
            knownTypes: ['mdadm_array_health_status', 'mdadm_array_operation_status', 'mdadm_array_mismatch', 'mdadm_device_health_status', 'mdadm_device_error'],
            expectedOids: $expectedOids,
        );
    }

    /** @return array<array{0:string,1:string,2:int|null,3:int|null}> name/type/min/max tuples */
    private static function arrayRrdDatasets(): array
    {
        return [
            ['active',        'GAUGE',  0,   null],
            ['spare',         'GAUGE',  0,   null],
            ['failed',        'GAUGE',  0,   null],
            ['degraded',      'GAUGE',  0,   null],
            ['mismatch',      'GAUGE',  0,   null],
            ['done_sectors',  'DERIVE', 0,   null],
            ['completed_pct', 'GAUGE',  0,   100],
            ['speed_bps',     'GAUGE',  0,   null],
        ];
    }

    /** @return array<array{0:string,1:string,2:int|null,3:int|null}> name/type/min/max tuples */
    private static function driveRrdDatasets(): array
    {
        return [
            ['errors', 'DERIVE', 0, null],
        ];
    }

    private static function buildRrdDef(array $datasets): RrdDefinition
    {
        $def = RrdDefinition::make();
        foreach ($datasets as [$name, $type, $min, $max]) {
            $def->addDataset($name, $type, $min, $max);
        }

        return $def;
    }

    private function discoveryArray(string $uuid): void
    {
        $array = $this->plarray[$uuid]['array'] ?? [];
        $devices = $this->plarray[$uuid]['devices'] ?? [];
        $this->Working[$uuid]['devHealth'] = [];

        $arrayName = (string) ($array['name'] ?? $uuid);
        $arrayGroup = "Mdadm $arrayName";
        $arrayNav = 'tab=apps/app=mdadm/array=' . rawurlencode($arrayName) . '/';

        $this->discovery['arrays'][$uuid]['name'] = $arrayName;
        $this->discovery['arrays'][$uuid]['devices']['rrdkey'] = substr($uuid, 0, 8);
        $this->discovery['arrays'][$uuid]['rrd_ds'] = [
            'linux-mdadm-array'  => array_column(self::arrayRrdDatasets(), 0),
            'linux-mdadm-drives' => array_column(self::driveRrdDatasets(), 0),
        ];
        $this->Working[$uuid]['arrayNavigation'] = $arrayNav;

        foreach ($devices as $deviceKey => $deviceData) {
            $deviceHealth = $this->mapDeviceHealth(is_array($deviceData) ? $deviceData : []);
            $this->Working[$uuid]['devHealth'][] = $deviceHealth;
            $this->discovery['arrays'][$uuid]['devices'][] = (string) $deviceKey;

            $this->discoveryDevice(
                $uuid,
                (string) $deviceKey,
                is_array($deviceData) ? $deviceData : [],
                $arrayGroup,
                $arrayNav,
                $deviceHealth
            );
        }

        $maxDeviceHealth = $this->maxKnownDeviceHealth($uuid);
        $arrayHealthIndex = $uuid . '_health';
        $arrayOperationIndex = $uuid . '_operation';
        $arrayMismatchIndex = $uuid . '_mismatch';

        $this->discoverSensor(
            class: 'count',
            type: 'mdadm_array_mismatch',
            index: $arrayMismatchIndex,
            oid: "app:mdadm:$arrayMismatchIndex",
            descr: "$arrayGroup Mismatch",
            current: (int) ($array['mismatch_cnt'] ?? 0),
            group: $arrayGroup,
            navigation: $arrayNav,
            highLimit: 1,
        );

        $this->discoverSensor(
            class: 'state',
            type: 'mdadm_array_operation_status',
            index: $arrayOperationIndex,
            oid: "app:mdadm:$arrayOperationIndex",
            descr: "$arrayGroup Operation",
            current: $this->mapArrayOperation($array),
            group: $arrayGroup,
            navigation: $arrayNav,
        )->withStateTranslations('mdadm_array_operation_status', $this->arrayOperationTranslations());

        $this->discoverSensor(
            class: 'state',
            type: 'mdadm_array_health_status',
            index: $arrayHealthIndex,
            oid: "app:mdadm:$arrayHealthIndex",
            descr: "$arrayGroup Health",
            current: $this->mapArrayHealth($array, $maxDeviceHealth),
            group: $arrayGroup,
            navigation: $arrayNav,
        )->withStateTranslations('mdadm_array_health_status', $this->arrayHealthTranslations());
    }

    private function discoveryDevice(
        string $uuid,
        string $devId,
        array $deviceData,
        string $arrayGroup,
        string $arrayNav,
        int $deviceHealth
    ): void {
        $deviceHealthIndex = $uuid . '_' . $devId . '_health';
        $deviceErrorsIndex = $uuid . '_' . $devId . '_errors';

        $this->discoverSensor(
            class: 'state',
            type: 'mdadm_device_health_status',
            index: $deviceHealthIndex,
            oid: "app:mdadm:$deviceHealthIndex",
            descr: "$arrayGroup $devId Health",
            current: $deviceHealth,
            group: "$arrayGroup::devices",
            navigation: $arrayNav,
        )->withStateTranslations('mdadm_device_health_status', $this->deviceHealthTranslations());

        $this->discoverSensor(
            class: 'count',
            type: 'mdadm_device_error',
            index: $deviceErrorsIndex,
            oid: "app:mdadm:$deviceErrorsIndex",
            descr: "$arrayGroup $devId errors",
            current: (int) ($deviceData['errors'] ?? 0),
            group: "$arrayGroup::devices",
            navigation: $arrayNav,
        );
    }

    private function runPoll(): void
    {
        $sensorValues = [];

        foreach ($this->dbArraysPrev as $uuid => $arrayRow) {
            $uuid = (string) $uuid;
            $array = $this->plarray[$uuid]['array'] ?? [];
            $devices = $this->plarray[$uuid]['devices'] ?? [];
            $this->Working[$uuid]['devHealth'] = [];

            foreach ($arrayRow->drives as $drive) {
                $devId = $drive->dev_id;
                $dev = $devices[$devId] ?? [];
                $deviceHealth = $this->mapDeviceHealth($dev);
                $this->Working[$uuid]['devHealth'][] = $deviceHealth;
                $sensorValues[$uuid . '_' . $devId . '_health'] = $deviceHealth;
                $sensorValues[$uuid . '_' . $devId . '_errors'] = (int) ($dev['errors'] ?? 0);
            }

            $maxDeviceHealth = $this->maxKnownDeviceHealth($uuid);
            $sensorValues[$uuid . '_health'] = $this->mapArrayHealth($array, $maxDeviceHealth);
            $sensorValues[$uuid . '_operation'] = $this->mapArrayOperation($array);
            $sensorValues[$uuid . '_mismatch'] = (int) ($array['mismatch_cnt'] ?? 0);
        }

        $this->updateSensorValues($sensorValues, 'app:mdadm:');
    }

    private function runPollRrd(): void
    {
        $appId = $this->app->app_id;
        $driveDef = self::buildRrdDef(self::driveRrdDatasets());

        foreach ($this->dbArraysPrev as $uuid => $arrayRow) {
            $uuid = (string) $uuid;
            $array = $this->plarray[$uuid]['array'] ?? [];
            $arrayName = (string) ($array['name'] ?? '');
            if ($arrayName === '') {
                continue;
            }

            $sync = $array['sync'] ?? [];
            $syncAction = strtolower(trim((string) ($sync['action'] ?? 'idle')));
            $isSyncing = $syncAction !== '' && $syncAction !== 'idle';

            $prevAction = $arrayRow->sync_action !== null ? (string) $arrayRow->sync_action : null;
            $this->logIfChanged($arrayName, 'sync action', $prevAction, $syncAction);

            $this->putRrd('app', [
                'name'     => 'mdadm',
                'app_id'   => $appId,
                'rrd_def'  => self::buildRrdDef(self::arrayRrdDatasets()),
                'rrd_name' => ['app', 'mdadm', $appId, $arrayName],
            ], [
                'active'        => (int) ($array['active_devices'] ?? null),
                'spare'         => (int) ($array['spare_devices'] ?? null),
                'failed'        => (int) ($array['failed_devices'] ?? null),
                'degraded'      => (int) ($array['degraded'] ?? null),
                'mismatch'      => (int) ($array['mismatch_cnt'] ?? null),
                'done_sectors'  => $isSyncing ? (int) ($sync['done_bytes'] ?? null) : null,
                'completed_pct' => (float) ($sync['completed_pct'] ?? null),
                'speed_bps'     => (int) ($sync['speed_bps'] ?? null),
            ]);

            foreach ($this->plarray[$uuid]['devices'] ?? [] as $devId => $devData) {
                $this->putRrd('app', [
                    'name'     => 'mdadm',
                    'app_id'   => $appId,
                    'rrd_def'  => $driveDef,
                    'rrd_name' => ['app', 'mdadm', $appId, $arrayName, (string) $devId],
                ], [
                    'errors' => (int) ($devData['errors'] ?? 0),
                ]);
            }
        }
    }

    private function runDiscoveryDb(): void
    {
        $deviceId = $this->os->getDeviceId();
        $appId = $this->app->app_id;
        $seenArrayIds = [];

        foreach ($this->plarray as $uuid => $entry) {
            $array = $entry['array'] ?? [];
            $devices = is_array($entry['devices'] ?? null) ? $entry['devices'] : [];

            $arrayRow = MdadmArray::updateOrCreate(
                ['app_id' => $appId, 'uuid' => (string) $uuid],
                [
                    'device_id'          => $deviceId,
                    'array_name'         => ($array['array_name'] ?? '') !== '' ? (string) $array['array_name'] : null,
                    'name'               => (string) ($array['name'] ?? ''),
                    'level'              => (string) ($array['raid_level'] ?? ''),
                    'size_bytes'         => isset($array['size_bytes']) ? (int) $array['size_bytes'] : null,
                    'raid_disks'         => isset($array['raid_disks']) ? (int) $array['raid_disks'] : null,
                    'metadata_version'   => isset($array['metadata_version']) ? (string) $array['metadata_version'] : null,
                    'consistency_policy' => isset($array['consistency_policy']) ? (string) $array['consistency_policy'] : null,
                    'chunk_size'         => isset($array['chunk_size']) ? (int) $array['chunk_size'] : null,
                ]
            );

            $seenArrayIds[] = $arrayRow->id;

            if (Debug::isVerbose()) {
                $sizeHuman = $arrayRow->size_bytes !== null
                    ? sprintf('%.1f GiB', $arrayRow->size_bytes / (1024 ** 3))
                    : 'null';
                echo sprintf(
                    '      DB array  id=%-4d  %s  (%s)  size=%s  meta=%s  chunk=%s' . PHP_EOL,
                    $arrayRow->id,
                    $arrayRow->name ?? '(no name)',
                    $arrayRow->level ?? 'null',
                    $sizeHuman,
                    $arrayRow->metadata_version ?? 'null',
                    $arrayRow->chunk_size !== null ? $arrayRow->chunk_size . 'B' : 'null'
                );
            }

            $seenDevIds = [];

            foreach ($devices as $devId => $devData) {
                $devData = is_array($devData) ? $devData : [];

                $driveRow = MdadmDrive::updateOrCreate(
                    ['mdadm_array_id' => $arrayRow->id, 'dev_id' => (string) $devId],
                    [
                        'device_id'       => $deviceId,
                        'app_id'          => $appId,
                        'path'            => (string) ($devData['device_name'] ?? ''),
                        'size_bytes'      => isset($devData['size_blocks']) ? (int) $devData['size_blocks'] * 512 : null,
                        'slot'            => isset($devData['slot']) ? (int) $devData['slot'] : null,
                        'id_model'        => isset($devData['id_model']) ? (string) $devData['id_model'] : null,
                        'id_serial_short' => isset($devData['id_serial_short']) ? (string) $devData['id_serial_short'] : null,
                    ]
                );

                if (Debug::isVerbose()) {
                    $driveSizeHuman = $driveRow->size_bytes !== null
                        ? sprintf('%.1f GiB', $driveRow->size_bytes / (1024 ** 3))
                        : 'null';
                    echo sprintf(
                        '        DB drive  %s  path=%s  slot=%s  size=%s  model=%s  serial=%s' . PHP_EOL,
                        $devId,
                        $driveRow->path ?? 'null',
                        $driveRow->slot !== null ? (string) $driveRow->slot : 'null',
                        $driveSizeHuman,
                        $driveRow->id_model ?? 'null',
                        $driveRow->id_serial_short ?? 'null'
                    );
                }

                $seenDevIds[] = (string) $devId;
            }

            MdadmDrive::where('mdadm_array_id', $arrayRow->id)
                ->whereNotIn('dev_id', $seenDevIds)
                ->delete();
        }

        MdadmArray::where('app_id', $appId)
            ->whereNotIn('id', $seenArrayIds)
            ->delete();
    }

    private function runPollDb(): void
    {

        foreach ($this->plarray as $uuid => $entry) {
            $array = $entry['array'] ?? [];
            $devices = is_array($entry['devices'] ?? null) ? $entry['devices'] : [];
            $sync = $array['sync'] ?? [];

            $arrayRow = $this->dbArraysPrev->get((string) $uuid);
            if (! $arrayRow instanceof MdadmArray) {
                continue;
            }

            $arrayRow->update([
                'state'              => (string) ($array['state'] ?? ''),
                'active_devices'     => isset($array['active_devices']) ? (int) $array['active_devices'] : null,
                'working_devices'    => isset($array['working_devices']) ? (int) $array['working_devices'] : null,
                'spare_devices'      => isset($array['spare_devices']) ? (int) $array['spare_devices'] : null,
                'failed_devices'     => isset($array['failed_devices']) ? (int) $array['failed_devices'] : null,
                'degraded'           => isset($array['degraded']) ? (int) $array['degraded'] : null,
                'mismatch_cnt'       => isset($array['mismatch_cnt']) ? (int) $array['mismatch_cnt'] : null,
                'sync_action'        => (string) ($sync['action'] ?? ''),
                'sync_completed_pct' => isset($sync['completed_pct']) ? (float) $sync['completed_pct'] : null,
                'sync_speed_bps'     => isset($sync['speed_bps']) ? (int) $sync['speed_bps'] : null,
                'sync_speed_min_bps' => isset($sync['speed_min_bps']) ? (int) $sync['speed_min_bps'] : null,
                'sync_speed_max_bps' => isset($sync['speed_max_bps']) ? (int) $sync['speed_max_bps'] : null,
                'sync_done_bytes'    => isset($sync['done_bytes']) ? (int) $sync['done_bytes'] : null,
                'sync_total_bytes'   => isset($sync['total_bytes']) ? (int) $sync['total_bytes'] : null,
                'sync_last_action'   => isset($sync['last_action']) ? (string) $sync['last_action'] : null,
            ]);

            foreach ($devices as $devId => $devData) {
                $devData = is_array($devData) ? $devData : [];

                $driveRow = $arrayRow->drives->firstWhere('dev_id', (string) $devId);
                if ($driveRow === null) {
                    continue;
                }

                $driveRow->update([
                    'state'       => (string) ($devData['state'] ?? ''),
                    'state_flags' => is_array($devData['state_flags'] ?? null) ? $devData['state_flags'] : null,
                    'errors'      => isset($devData['errors']) ? (int) $devData['errors'] : null,
                    'is_missing'  => (bool) ($devData['is_missing'] ?? false),
                    'device_role' => isset($devData['device_role']) ? (string) $devData['device_role'] : null,
                ]);
            }
        }

    }

    private function collectMetrics(): array
    {
        $counters = $this->payload['data']['counters'] ?? [];
        $metrics = [
            'arrays'          => (int) ($counters['arrays'] ?? 0),
            'arrays_syncing'  => (int) ($counters['arrays_syncing'] ?? 0),
            'degraded_arrays' => (int) ($counters['degraded_arrays'] ?? 0),
            'devices_total'   => (int) ($counters['devices_total'] ?? 0),
        ];

        foreach ($this->plarray as $uuid => $entry) {
            $array = $entry['array'] ?? [];
            $arrayName = (string) ($array['name'] ?? $uuid);
            if ($arrayName === '') {
                continue;
            }
            $sync = $array['sync'] ?? [];
            $metrics[$arrayName] = [
                'active_devices'     => (int) ($array['active_devices'] ?? 0),
                'spare_devices'      => (int) ($array['spare_devices'] ?? 0),
                'failed_devices'     => (int) ($array['failed_devices'] ?? 0),
                'working_devices'    => (int) ($array['working_devices'] ?? 0),
                'sync_completed_pct' => (float) ($sync['completed_pct'] ?? 0),
            ];
        }

        return $metrics;
    }

    private function logIfChanged(string $arrayName, string $label, mixed $prev, mixed $curr): void
    {
        if ($prev === null || $prev === $curr) {
            return;
        }
        $this->logEvent('notice', "mdadm $arrayName: $label changed ($prev -> $curr)");
    }
}
