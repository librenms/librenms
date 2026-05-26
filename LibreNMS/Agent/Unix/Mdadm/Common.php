<?php

namespace LibreNMS\Agent\Unix\Mdadm;

use App\Models\MdadmArray;
use App\Models\MdadmDrive;
use App\Models\StateTranslation;
use Illuminate\Database\Eloquent\Collection;
use LibreNMS\Agent\Application;
use LibreNMS\Enum\Severity;
use LibreNMS\Exceptions\JsonAppExtendErroredException;
use LibreNMS\Exceptions\JsonAppMissingKeysException;
use LibreNMS\RRD\RrdDefinition;
use LibreNMS\Util\Debug;
use SnmpQuery;

class Common extends Application
{
    // Agent script exit codes (see agent script constants)
    private const EXIT_DEPENDENCY_MISSING = 1;
    private const EXIT_NO_ARRAYS = 2;
    private const EXIT_PERMISSION_DENIED = 3;
    private const EXIT_OUTPUT_WRITE_FAILURE = 4;
    private const EXIT_CONFIG_ERROR = 5;
    private const EXIT_PARTIAL_FAILURE = 6;
    private const EXIT_NO_CONFIGURED_DEVICES = 7;

    // MDADM-MIB sentinel for an unassigned 32-bit value (e.g. spare slot).
    private const SNMP_UINT32_MAX = 4294967295;

    // MDADM-MIB sentinel for a 64-bit "none"/"max" gauge (e.g. resync_start, sync_max,
    // recovery_start). Compared as a string because it exceeds PHP_INT_MAX.
    private const SNMP_UINT64_MAX = '18446744073709551615';

    // app->data key recording the poll transport: 'snmp' (v3 MDADM-MIB) or 'json' (v1/v2 extend).
    private const SOURCE_KEY = 'mdadm_source';
    private const SOURCE_SNMP = 'snmp';
    private const SOURCE_JSON = 'json';

    // The mdadm sensor types this app discovers/polls (one source for sync + stale-cleanup).
    private const SENSOR_TYPES = [
        'mdadm_array_health_status',
        'mdadm_array_operation_status',
        'mdadm_array_mismatch',
        'mdadm_device_health_status',
        'mdadm_device_error',
    ];

    // RRD heartbeat (seconds) for mdadm array/drive datasets.
    private const RRD_HEARTBEAT = 600;

    // MDADM-MIB enum -> kernel string maps. The downstream mappers expect the
    // same sysfs/mdadm strings the legacy JSON agent emitted, so translate the
    // numeric SNMP enums (net-snmp -Oe) back to those strings here.
    private const ARRAY_STATE_MAP = [
        1 => 'clear', 2 => 'inactive', 3 => 'suspended', 4 => 'readonly',
        5 => 'read-auto', 6 => 'clean', 7 => 'active', 8 => 'write-pending',
        9 => 'active-idle', 10 => 'degraded', 11 => 'failed',
    ];
    private const RAID_LEVEL_MAP = [
        1 => 'linear', 2 => 'raid0', 3 => 'raid1', 4 => 'raid4',
        5 => 'raid5', 6 => 'raid6', 10 => 'raid10',
    ];
    private const CONSISTENCY_POLICY_MAP = [
        1 => 'resync', 2 => 'journal', 3 => 'ppl', 4 => 'none', 5 => 'bitmap',
    ];
    private const SYNC_ACTION_MAP = [
        0 => 'idle', 1 => 'resync', 2 => 'recover', 3 => 'check', 4 => 'repair', 5 => 'reshape',
    ];
    private const DEVICE_ROLE_MAP = [
        1 => 'active', 2 => 'spare', 3 => 'faulty', 4 => 'missing', 5 => 'writemostly', 6 => 'replacement',
    ];
    private const BITMAP_TYPE_MAP = [
        0 => 'none', 1 => 'internal', 2 => 'external', 3 => 'lockless',
    ];
    private const JOURNAL_MODE_MAP = [
        0 => 'write-through', 1 => 'write-back',
    ];

    private bool $discoveryCompleted = false;
    /** @var array<string, mixed> */
    private array $payload = [];
    /** @var array<string, mixed> keyed by array uuid */
    private array $plarray = [];
    private ?int $agentExitCode = null;
    /** @var Collection<string, MdadmArray> MdadmArray rows (with drives) as they existed before this poll cycle, keyed by uuid. */
    private Collection $dbArraysPrev;
    /** @var array<string, mixed> */
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

    /** @return list<StateTranslation> */
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

    /** @return list<StateTranslation> */
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

    /** @return list<StateTranslation> */
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

    /** @param array<string, mixed> $array */
    private function mapArrayHealth(array $array, int $maxDeviceHealth): int
    {
        if (! isset($array['state'], $array['failed_devices'], $array['degraded'])) {
            return -1;
        }

        $arrayState = str_replace('_', '-', strtolower(trim((string) ($array['state'] ?? ''))));

        $stateMap = [
            'clear'         => 4,
            'inactive'      => 5,
            'suspended'     => 6,
            'readonly'      => 7,
            'read-only'     => 7,
            'read-auto'     => 8,
            'write-pending' => 9,
        ];
        if (isset($stateMap[$arrayState])) {
            return $stateMap[$arrayState];
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

    /** @param list<int> $devHealth */
    private function maxKnownDeviceHealth(array $devHealth): int
    {
        $values = array_filter($devHealth, static fn ($v) => $v >= 0);

        return $values === [] ? -1 : max($values);
    }

    /** @param array<string, mixed> $array */
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

        if (isset($operationMap[$operation])) {
            return $operationMap[$operation];
        }

        // Inactive/readonly arrays have no sync action; reflect array state instead.
        $state = str_replace('_', '-', strtolower(trim((string) ($array['state'] ?? ''))));

        return ['inactive' => 7, 'readonly' => 8, 'read-only' => 8][$state] ?? -1;
    }

    /** @param array<string, mixed> $device */
    private function mapDeviceHealth(array $device): int
    {
        if (($device['is_missing'] ?? null) === true) {
            return 10;
        }

        $flags = array_map('strtolower', $device['state_flags'] ?? []);
        $state = strtolower(trim((string) ($device['state'] ?? '')));

        foreach (['faulty' => 9, 'blocked' => 8, 'write_error' => 7, 'want_replacement' => 5, 'replacement' => 6] as $flag => $val) {
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
        $payload = $this->fetchMdadmPayload();
        $exitCode = $this->agentExitCode;

        // EXIT_PARTIAL_FAILURE (6): data is still emitted — fall through to normal processing.
        if ($exitCode !== null && $exitCode !== self::EXIT_PARTIAL_FAILURE) {
            if ($this->skipDueToTransientError($exitCode, 'discovery')) {
                return;
            }
            if (in_array($exitCode, [self::EXIT_DEPENDENCY_MISSING, self::EXIT_NO_ARRAYS, self::EXIT_NO_CONFIGURED_DEVICES], true) || $payload === null) {
                $this->cleanupAllSensorsAndArrays();
            }

            return;
        }

        if ($payload === null) {
            $this->cleanupAllSensorsAndArrays();

            return;
        }

        $version = (int) ($payload['version'] ?? 0);
        if ($version >= 1 && $version < 3) {
            $this->recordSource(self::SOURCE_JSON);
            (new V2($this->os, $this->app, $this->agent_data))->discoverLegacy($payload);

            return;
        }
        if ($version < 1) {
            return;
        }
        $this->recordSource(self::SOURCE_SNMP);
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
        // The transport is decided once at discovery and stored on the app, so
        // poll goes straight to the right source instead of probing both.
        if (($this->getAppData()[self::SOURCE_KEY] ?? self::SOURCE_SNMP) === self::SOURCE_JSON) {
            $this->pollLegacyJson();

            return;
        }

        $payload = $this->fetchSnmpHealthPayload();
        $exitCode = $this->agentExitCode;

        // EXIT_PARTIAL_FAILURE (6): data is still emitted — fall through to normal processing.
        if ($exitCode !== null && $exitCode !== self::EXIT_PARTIAL_FAILURE) {
            $this->skipDueToTransientError($exitCode, 'poll');

            return;
        }

        if ($payload === null) {
            return;
        }
        $this->initPollState($payload);
        $this->runPoll();
        $this->runPollRrd();
        $this->runPollDb();
        \update_application($this->app, 'ok', $this->collectMetrics());
    }

    /** Poll a legacy v1/v2 agent over the JSON extend (source flag = 'json'). */
    private function pollLegacyJson(): void
    {
        $payload = $this->fetchLegacyJsonPayload();
        $exitCode = $this->agentExitCode;

        // EXIT_PARTIAL_FAILURE (6): data is still emitted — fall through to normal processing.
        if ($exitCode !== null && $exitCode !== self::EXIT_PARTIAL_FAILURE) {
            $this->skipDueToTransientError($exitCode, 'poll');

            return;
        }

        if ($payload === null) {
            return;
        }

        $v2 = new V2($this->os, $this->app, $this->agent_data);
        $v2->pollLegacy($payload);
        $v2->pollDbLegacy($payload);
    }

    /**
     * Skip handling for a transient agent error: log and skip the cycle without
     * touching existing sensors/data. Returns true when the exit code is one of
     * the transient errors (caller should bail), false otherwise.
     */
    private function skipDueToTransientError(?int $exitCode, string $context): bool
    {
        if (in_array($exitCode, [self::EXIT_PERMISSION_DENIED, self::EXIT_OUTPUT_WRITE_FAILURE, self::EXIT_CONFIG_ERROR], true)) {
            echo "  mdadm: skipping {$context} — transient agent error (code {$exitCode})" . PHP_EOL;

            return true;
        }

        return false;
    }

    /**
     * Remove all mdadm sensors and DB arrays for this app.
     * Called when the agent is unreachable so stale data does not persist.
     */
    private function cleanupAllSensorsAndArrays(): void
    {
        $this->logStaleSensorRemovals('app:mdadm:', []);
        app()->forgetInstance('sensor-discovery');
        $this->syncSensors(...self::SENSOR_TYPES);
        $this->deleteStaleAgentSensors('app:mdadm:', self::SENSOR_TYPES, []);
        MdadmArray::where('app_id', $this->app->app_id)->delete();
    }

    /**
     * Discovery fetch: probe the data source and return the meta payload.
     *
     * Tries MDADM-MIB (v3) first, then the legacy JSON extend (v1/v2). The
     * resolved source is recorded by discover() so poll() goes straight to the
     * right transport without re-probing.
     *
     * @return array<string, mixed>|null
     */
    private function fetchMdadmPayload(): ?array
    {
        $this->agentExitCode = null;

        // v3+ data is served by the pass_persist agent via MDADM-MIB.
        $snmp = $this->fetchSnmpMetaPayload();
        if ($snmp !== null) {
            return $snmp;
        }

        // No MDADM-MIB on this host: fall back to the legacy JSON extend, which
        // only serves v1/v2 agents now. Any v3 JSON is ignored (data must come
        // from SNMP), so it is treated as no data.
        $json = $this->fetchLegacyJsonPayload();
        if ($json !== null && (int) ($json['version'] ?? 0) >= 3) {
            return null;
        }

        return $json;
    }

    /** Persist which transport poll() should use for this app: 'snmp' (v3) or 'json' (v1/v2). */
    private function recordSource(string $source): void
    {
        $this->saveAppData(array_merge($this->getAppData(), [self::SOURCE_KEY => $source]));
    }

    /**
     * A fresh SnmpQuery configured for MDADM-MIB.
     *
     * mibDir() adds mibs/librenms to the search path; mibs() actually loads the
     * MDADM-MIB module (it is not in the default base mib list) so net-snmp can
     * resolve the symbolic MDADM-MIB::... OIDs.
     */
    private function snmpQuery(): \LibreNMS\Data\Source\SnmpQueryInterface
    {
        return SnmpQuery::mibDir('librenms')->mibs(['MDADM-MIB'])->hideMib();
    }

    /**
     * Fetch the legacy v1/v2 JSON extend payload.
     *
     * Kept separate from the base Application::fetchPayload() because that method
     * catches JsonAppException and resolves the error itself (calling
     * update_application), swallowing the agent exit code. Common.php needs that
     * code via JsonAppExtendErroredException::getCode() to drive agentExitCode.
     *
     * @return array<string, mixed>|null
     */
    private function fetchLegacyJsonPayload(): ?array
    {
        $this->agentExitCode = null;

        try {
            return \json_app_get($this->os->getDeviceArray(), 'mdadm', 1);
        } catch (JsonAppExtendErroredException $e) {
            $this->agentExitCode = $e->getCode();
            echo '  mdadm: agent exit code ' . $this->agentExitCode . ': ' . $e->getMessage() . PHP_EOL;

            return $e->getParsedJson() ?: null;
        } catch (JsonAppMissingKeysException $e) {
            return $e->getParsedJson() ?: null;
        } catch (\Exception $e) {
            return null;
        }
    }

    /**
     * Read the MDADM-MIB scalar metadata (mdadmMetadata subtree).
     *
     * Returns version/error/counters, or null when the MIB is not served on
     * this host (the caller then falls back to the legacy JSON extend).
     *
     * @return array<string, mixed>|null
     */
    private function snmpScalars(): ?array
    {
        $meta = $this->snmpQuery()->walk('MDADM-MIB::mdadmMetadata');
        if (! $meta->isValid()) {
            return null; // MDADM-MIB not served here.
        }

        $error = (int) $meta->value('mdadmError');
        if ($error !== 0) {
            $this->agentExitCode = $error;
            echo '  mdadm: agent error code ' . $error . ': ' . $meta->value('mdadmErrorString') . PHP_EOL;
        }

        return [
            'version'  => (int) $meta->value('mdadmVersion'),
            'error'    => $error,
            'counters' => [
                'arrays'          => (int) $meta->value('mdadmArrayCount'),
                'arrays_syncing'  => (int) $meta->value('mdadmSyncingArrayCount'),
                'degraded_arrays' => (int) $meta->value('mdadmDegradedArrayCount'),
                'devices_total'   => (int) $meta->value('mdadmTotalMemberCount'),
            ],
        ];
    }

    /**
     * Discovery fetch: identity and static configuration only.
     *
     * Walks the slow-changing meta tables (mdadmArrayMetaTable,
     * mdadmDeviceMetaTable). Arrays are keyed by UUID and devices by kernel
     * name, and each carries its MDADM-MIB snmp_index so poll can map the
     * health tables back without re-walking the meta tables.
     * Returns null when the MIB is not served (caller falls back to JSON).
     *
     * @return array<string, mixed>|null
     */
    private function fetchSnmpMetaPayload(): ?array
    {
        $scalars = $this->snmpScalars();
        if ($scalars === null) {
            return null;
        }

        $arrayMeta = $this->snmpQuery()->walk('MDADM-MIB::mdadmArrayMetaTable')->table(1);
        $devMeta = $this->snmpQuery()->walk('MDADM-MIB::mdadmDeviceMetaTable')->table(2);

        $arrays = [];
        foreach ($arrayMeta as $arrayIndex => $m) {
            $uuid = (string) ($m['mdadmArrayUuid'] ?? $arrayIndex);

            $devices = [];
            foreach (($devMeta[$arrayIndex] ?? []) as $memberIndex => $dm) {
                $devName = (string) ($dm['mdadmMemberDeviceName'] ?? $memberIndex);
                $slot = (int) ($dm['mdadmMemberSlot'] ?? self::SNMP_UINT32_MAX);

                $devices[$devName] = [
                    'snmp_index'      => (int) $memberIndex,
                    'device_name'     => $devName,
                    'slot'            => $slot === self::SNMP_UINT32_MAX ? null : $slot,
                    'size_bytes'      => (int) ($dm['mdadmMemberSizeBytes'] ?? 0),
                    'id_model'        => (string) ($dm['mdadmMemberIdModel'] ?? ''),
                    'id_serial_short' => (string) ($dm['mdadmMemberIdSerial'] ?? ''),
                    'device_role'     => self::DEVICE_ROLE_MAP[(int) ($dm['mdadmMemberRole'] ?? 0)] ?? '',
                    'offset_sectors'  => $this->gauge64OrNull($dm['mdadmMemberOffsetBlocks'] ?? null),
                    'ppl_sector'      => $this->gauge64OrNull($dm['mdadmMemberPplSector'] ?? null),
                    'ppl_size'        => $this->gauge64OrNull($dm['mdadmMemberPplSize'] ?? null),
                ];
            }

            $arrays[$uuid] = [
                'snmp_index' => (int) $arrayIndex,
                'array'      => [
                    'name'               => (string) ($m['mdadmArrayShortName'] ?? ''),
                    'array_name'         => (string) ($m['mdadmArrayName'] ?? ''),
                    'uuid'               => $uuid,
                    'raid_level'         => self::RAID_LEVEL_MAP[(int) ($m['mdadmArrayRaidLevel'] ?? 0)] ?? '',
                    'raid_disks'         => (int) ($m['mdadmArrayRaidDisks'] ?? 0),
                    'size_bytes'         => (int) ($m['mdadmArraySizeBytes'] ?? 0),
                    'metadata_version'   => (string) ($m['mdadmArrayMetadataVersion'] ?? ''),
                    'consistency_policy' => self::CONSISTENCY_POLICY_MAP[(int) ($m['mdadmArrayConsistencyPolicy'] ?? 0)] ?? '',
                    'chunk_size'         => (int) ($m['mdadmArrayChunkSize'] ?? 0),
                    // mdadmArrayLayout is signed: -1 (not applicable to this RAID level) -> null.
                    'layout'             => isset($m['mdadmArrayLayout']) && (int) $m['mdadmArrayLayout'] >= 0 ? (int) $m['mdadmArrayLayout'] : null,
                    'resync_start'       => $this->gauge64OrNull($m['mdadmArrayResyncStart'] ?? null),
                    'reshape_position'   => $this->gauge64OrNull($m['mdadmArrayReshapePosition'] ?? null),
                    'bitmap_type'        => isset($m['mdadmArrayBitmapType']) ? (self::BITMAP_TYPE_MAP[(int) $m['mdadmArrayBitmapType']] ?? '') : '',
                    'bitmap_location'    => (string) ($m['mdadmArrayBitmapLocation'] ?? ''),
                    'bitmap_chunksize'   => (int) ($m['mdadmArrayBitmapChunksize'] ?? 0),
                    'bitmap_metadata'    => (string) ($m['mdadmArrayBitmapMetadata'] ?? ''),
                    'bitmap_time_base'   => (int) ($m['mdadmArrayBitmapTimeBase'] ?? 0),
                ],
                'devices' => $devices,
            ];
        }

        return $this->wrapSnmpPayload($scalars, $arrays);
    }

    /**
     * Wrap SNMP scalar metadata and an arrays table into the shared payload envelope.
     *
     * @param  array<string, mixed>  $scalars
     * @param  array<array-key, mixed>  $arrays
     * @return array<string, mixed>
     */
    private function wrapSnmpPayload(array $scalars, array $arrays): array
    {
        return [
            'version' => $scalars['version'],
            'error'   => $scalars['error'],
            'data'    => [
                'counters' => $scalars['counters'],
                'tables'   => ['arrays' => $arrays],
            ],
        ];
    }

    /**
     * Poll fetch: dynamic health and synchronisation only.
     *
     * Walks the frequently-changing health/sync tables (mdadmArrayHealthTable,
     * mdadmArraySyncTable, mdadmDeviceHealthTable), keyed by MDADM-MIB
     * snmp_index (array index, then member index). initPollState() maps these
     * back to UUID/dev_id using the indices persisted at discovery.
     * Returns null when the MIB is not served (caller falls back to JSON).
     *
     * @return array<string, mixed>|null
     */
    private function fetchSnmpHealthPayload(): ?array
    {
        $this->agentExitCode = null;
        $scalars = $this->snmpScalars();
        if ($scalars === null) {
            return null;
        }

        $arrayHealth = $this->snmpQuery()->walk('MDADM-MIB::mdadmArrayHealthTable')->table(1);
        $arraySync = $this->snmpQuery()->walk('MDADM-MIB::mdadmArraySyncTable')->table(1);
        $devHealth = $this->snmpQuery()->walk('MDADM-MIB::mdadmDeviceHealthTable')->table(2);

        $arrays = [];
        foreach ($arrayHealth as $arrayIndex => $h) {
            $s = $arraySync[$arrayIndex] ?? [];

            $devices = [];
            foreach (($devHealth[$arrayIndex] ?? []) as $memberIndex => $dh) {
                $state = (string) ($dh['mdadmMemberState'] ?? '');
                $devices[(int) $memberIndex] = [
                    'state'                 => $state,
                    'state_flags'           => $this->splitStateFlags($state),
                    'errors'                => (int) ($dh['mdadmMemberErrors'] ?? 0),
                    'is_missing'            => ((int) ($dh['mdadmMemberIsMissing'] ?? 2)) === 1,
                    'events'                => (int) ($dh['mdadmMemberEvents'] ?? 0),
                    'recovery_start'        => $this->gauge64OrNull($dh['mdadmMemberRecoveryStartBlocks'] ?? null),
                    'bad_block_count'       => (int) ($dh['mdadmMemberBadBlockCount'] ?? 0),
                    'unack_bad_block_count' => (int) ($dh['mdadmMemberUnackBadBlockCount'] ?? 0),
                ];
            }

            $arrays[(int) $arrayIndex] = [
                'array' => [
                    'state'               => self::ARRAY_STATE_MAP[(int) ($h['mdadmArrayState'] ?? 0)] ?? '',
                    'degraded'            => (int) ($h['mdadmArrayDegradedCount'] ?? 0),
                    'active_devices'      => (int) ($h['mdadmArrayActiveDevices'] ?? 0),
                    'working_devices'     => (int) ($h['mdadmArrayWorkingDevices'] ?? 0),
                    'spare_devices'       => (int) ($h['mdadmArraySpareDevices'] ?? 0),
                    'failed_devices'      => (int) ($h['mdadmArrayFailedDevices'] ?? 0),
                    'mismatch_cnt'        => (int) ($h['mdadmArrayMismatchCount'] ?? 0),
                    'is_mounted'          => $this->truthValue($h['mdadmArrayIsMounted'] ?? null),
                    'mount_points'        => (string) ($h['mdadmArrayMountPoints'] ?? ''),
                    'is_swap'             => $this->truthValue($h['mdadmArrayIsSwap'] ?? null),
                    'bitmap_backlog'      => (int) ($h['mdadmArrayBitmapBacklog'] ?? 0),
                    'bitmap_max_backlog'  => (int) ($h['mdadmArrayBitmapMaxBacklog'] ?? 0),
                    'bitmap_can_clear'    => $this->truthValue($h['mdadmArrayBitmapCanClear'] ?? null),
                    'stripe_cache_size'   => (int) ($h['mdadmArrayStripeCacheSize'] ?? 0),
                    'stripe_cache_active' => (int) ($h['mdadmArrayStripeCacheActive'] ?? 0),
                    'journal_mode'        => isset($h['mdadmArrayJournalMode']) ? (self::JOURNAL_MODE_MAP[(int) $h['mdadmArrayJournalMode']] ?? '') : '',
                    'sync'                => [
                        'action'        => self::SYNC_ACTION_MAP[(int) ($s['mdadmArraySyncAction'] ?? 0)] ?? 'idle',
                        'last_action'   => self::SYNC_ACTION_MAP[(int) ($s['mdadmArraySyncLastAction'] ?? 0)] ?? 'idle',
                        'completed_pct' => ((int) ($s['mdadmArraySyncCompletedCentipct'] ?? 0)) / 100,
                        'done_bytes'    => (int) ($s['mdadmArraySyncDoneBytes'] ?? 0),
                        'total_bytes'   => (int) ($s['mdadmArraySyncTotalBytes'] ?? 0),
                        'speed_bps'     => (int) ($s['mdadmArraySyncSpeedBps'] ?? 0),
                        'speed_min_bps' => (int) ($s['mdadmArraySyncSpeedMinBps'] ?? 0),
                        'speed_max_bps' => (int) ($s['mdadmArraySyncSpeedMaxBps'] ?? 0),
                        'min_sectors'   => $this->gauge64OrNull($s['mdadmArraySyncMin'] ?? null),
                        'max_sectors'   => $this->gauge64OrNull($s['mdadmArraySyncMax'] ?? null),
                    ],
                ],
                'devices' => $devices,
            ];
        }

        return $this->wrapSnmpPayload($scalars, $arrays);
    }

    /** Cast a 64-bit MDADM-MIB gauge to int, mapping the all-ones "none"/"max" sentinel to null. */
    private function gauge64OrNull(mixed $value): ?int
    {
        $raw = (string) ($value ?? '');

        return ($raw === '' || $raw === self::SNMP_UINT64_MAX) ? null : (int) $raw;
    }

    /** Cast an SNMP TruthValue (1=true, 2=false) to bool; null when absent. */
    private function truthValue(mixed $value): ?bool
    {
        if ($value === null || $value === '') {
            return null;
        }

        return (int) $value === 1;
    }

    /**
     * Split a comma-separated md member state flag string into trimmed, non-empty flags.
     *
     * @return list<string>
     */
    private function splitStateFlags(string $state): array
    {
        return array_values(array_filter(
            array_map(trim(...), explode(',', $state)),
            static fn ($flag) => $flag !== ''
        ));
    }

    /** @param array<string, mixed> $payload */
    private function initState(array $payload): void
    {
        $this->payload = $payload;
        $this->plarray = $payload['data']['tables']['arrays'] ?? [];
        $this->dbArraysPrev = MdadmArray::where('app_id', $this->app->app_id)
            ->with('drives')
            ->get()
            ->keyBy('uuid');
    }

    /**
     * Build poll state from the SNMP health payload (keyed by MDADM-MIB index).
     *
     * The health/sync tables are indexed by snmp_index, not UUID, so map them
     * back to UUID/dev_id using the indices persisted on the DB rows at
     * discovery. The assembled plarray keeps the same shape the run* methods
     * expect: array identity (name/array_name/uuid) merged from the DB row with
     * the dynamic health/sync fields, devices keyed by dev_id.
     *
     * @param  array<string, mixed>  $payload
     */
    private function initPollState(array $payload): void
    {
        $this->payload = $payload;
        $this->dbArraysPrev = MdadmArray::where('app_id', $this->app->app_id)
            ->with('drives')
            ->get()
            ->keyBy('uuid');

        $snmpArrays = $payload['data']['tables']['arrays'] ?? [];
        $this->plarray = [];

        foreach ($this->dbArraysPrev as $uuid => $arrayRow) {
            $entry = $snmpArrays[(int) $arrayRow->snmp_index] ?? null;
            if ($entry === null) {
                continue; // array not present in this health walk (e.g. new since discovery)
            }

            $array = ($entry['array'] ?? []) + [
                'name'       => (string) $arrayRow->md_id,
                'array_name' => $arrayRow->array_name,
                'uuid'       => (string) $uuid,
            ];

            $devices = [];
            foreach ($arrayRow->drives as $drive) {
                $devices[$drive->dev_id] = $entry['devices'][(int) $drive->snmp_index] ?? [];
            }

            $this->plarray[(string) $uuid] = ['array' => $array, 'devices' => $devices];
        }
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

    /**
     * Append any v3 datasets missing from a legacy v1/v2 RRD file.
     *
     * Old files contain: level, size, disc_count, hotspare_count, degraded, sync_speed, sync_completed
     * This adds the v3-only datasets that are absent; existing datasets (including legacy ones) are untouched.
     * `degraded` is present in both layouts under the same name and is therefore skipped automatically.
     */
    private function migrateLegacyArrayRrd(string $hostname, string $arrayName): void
    {
        $rrdFile = \App\Facades\Rrd::name($hostname, ['app', 'mdadm', $this->app->app_id, $arrayName]);
        if (! \App\Facades\Rrd::checkRrdExists($rrdFile)) {
            return;
        }

        // v3-only datasets = all array datasets except `degraded`, which legacy files already have.
        $newDatasets = [];
        foreach (self::arrayRrdDatasets() as [$name, $type, $heartbeat, $min, $max]) {
            if ($name === 'degraded') {
                continue;
            }
            $newDatasets[] = ['name' => $name, 'type' => $type, 'heartbeat' => $heartbeat, 'min' => $min, 'max' => $max];
        }

        \App\Facades\Rrd::addDatasets($rrdFile, $newDatasets);
    }

    private function runDiscovery(): void
    {
        $this->discovery = [];
        $this->discovery['array_count'] = $this->payload['data']['counters']['arrays'];
        $this->discovery['device_count'] = $this->payload['data']['counters']['devices_total'] ?? 0;

        app()->forgetInstance('sensor-discovery');

        $hostname = $this->os->getDeviceArray()['hostname'] ?? '';

        foreach (array_keys($this->plarray) as $uuid) {
            $arrayName = (string) ($this->plarray[$uuid]['array']['name'] ?? '');
            if ($arrayName !== '' && $hostname !== '') {
                $this->migrateLegacyArrayRrd($hostname, $arrayName);
            }

            $this->discovery['arrays'][(string) $uuid] = [
                'devices_count' => count($this->plarray[$uuid]['devices']),
                'devices'       => [],
            ];
            $this->discoveryArray((string) $uuid);
        }

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

        $this->logStaleSensorRemovals('app:mdadm:', $expectedOids);
        $this->syncSensors(...self::SENSOR_TYPES);

        $this->deleteStaleAgentSensors(
            oidPrefix: 'app:mdadm:',
            knownTypes: self::SENSOR_TYPES,
            expectedOids: $expectedOids,
        );
    }

    /** @return array<array{0:string,1:string,2:int,3:int|null,4:int|null}> name/type/heartbeat/min/max tuples */
    private static function arrayRrdDatasets(): array
    {
        return [
            ['active',        'GAUGE',  self::RRD_HEARTBEAT, 0, null],
            ['spare',         'GAUGE',  self::RRD_HEARTBEAT, 0, null],
            ['failed',        'GAUGE',  self::RRD_HEARTBEAT, 0, null],
            ['degraded',      'GAUGE',  self::RRD_HEARTBEAT, 0, null],
            ['mismatch',      'GAUGE',  self::RRD_HEARTBEAT, 0, null],
            ['done_sectors',  'DERIVE', self::RRD_HEARTBEAT, 0, null],
            ['completed_pct', 'GAUGE',  self::RRD_HEARTBEAT, 0, 100],
            ['speed_bps',     'GAUGE',  self::RRD_HEARTBEAT, 0, null],
        ];
    }

    /** @return array<array{0:string,1:string,2:int,3:int|null,4:int|null}> name/type/heartbeat/min/max tuples */
    private static function driveRrdDatasets(): array
    {
        return [
            ['errors', 'DERIVE', self::RRD_HEARTBEAT, 0, null],
        ];
    }

    /** @param array<array{0: string, 1: string, 2: int, 3: int|null, 4: int|null}> $datasets */
    private static function buildRrdDef(array $datasets): RrdDefinition
    {
        $def = RrdDefinition::make();
        foreach ($datasets as [$name, $type, , $min, $max]) {
            $def->addDataset($name, $type, $min, $max);
        }

        return $def;
    }

    private function discoveryArray(string $uuid): void
    {
        $array = $this->plarray[$uuid]['array'] ?? [];
        $devices = $this->plarray[$uuid]['devices'] ?? [];
        $devHealth = [];

        $arrayName = (string) ($array['name'] ?? $uuid);
        $arrayGroup = "Mdadm $arrayName";
        $arrayNav = 'tab=apps/app=mdadm/array=' . rawurlencode($arrayName) . '/';

        $this->discovery['arrays'][$uuid]['name'] = $arrayName;
        $this->discovery['arrays'][$uuid]['devices']['rrdkey'] = substr($uuid, 0, 8);
        $this->discovery['arrays'][$uuid]['rrd_ds'] = [
            'linux-mdadm-array'  => array_column(self::arrayRrdDatasets(), 0),
            'linux-mdadm-drives' => array_column(self::driveRrdDatasets(), 0),
        ];

        foreach ($devices as $deviceKey => $deviceData) {
            $deviceHealth = $this->mapDeviceHealth(is_array($deviceData) ? $deviceData : []);
            $devHealth[] = $deviceHealth;
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

        $maxDeviceHealth = $this->maxKnownDeviceHealth($devHealth);
        $arrayHealthIndex = $uuid . '_health';
        $arrayOperationIndex = $uuid . '_operation';
        $arrayMismatchIndex = $uuid . '_mismatch';

        $this->discoverSensor(
            class: 'count',
            type: 'mdadm_array_mismatch',
            index: $arrayMismatchIndex,
            oid: "app:mdadm:$arrayMismatchIndex",
            descr: "$arrayGroup Mismatch",
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
            group: $arrayGroup,
            navigation: $arrayNav,
        )->withStateTranslations('mdadm_array_operation_status', $this->arrayOperationTranslations());

        $this->discoverSensor(
            class: 'state',
            type: 'mdadm_array_health_status',
            index: $arrayHealthIndex,
            oid: "app:mdadm:$arrayHealthIndex",
            descr: "$arrayGroup Health",
            group: $arrayGroup,
            navigation: $arrayNav,
        )->withStateTranslations('mdadm_array_health_status', $this->arrayHealthTranslations());
    }

    /** @param array<string, mixed> $deviceData */
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
            group: "$arrayGroup::devices",
            navigation: $arrayNav,
        )->withStateTranslations('mdadm_device_health_status', $this->deviceHealthTranslations());

        $this->discoverSensor(
            class: 'count',
            type: 'mdadm_device_error',
            index: $deviceErrorsIndex,
            oid: "app:mdadm:$deviceErrorsIndex",
            descr: "$arrayGroup $devId errors",
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
            $devHealth = [];

            foreach ($arrayRow->drives as $drive) {
                $devId = $drive->dev_id;
                $dev = $devices[$devId] ?? [];
                $deviceHealth = $this->mapDeviceHealth($dev);
                $devHealth[] = $deviceHealth;
                $sensorValues[$uuid . '_' . $devId . '_health'] = $deviceHealth;
                $sensorValues[$uuid . '_' . $devId . '_errors'] = (int) ($dev['errors'] ?? 0);
            }

            $maxDeviceHealth = $this->maxKnownDeviceHealth($devHealth);
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
                    'snmp_index'         => isset($entry['snmp_index']) ? (int) $entry['snmp_index'] : null,
                    'array_name'         => ($array['array_name'] ?? '') !== '' ? (string) $array['array_name'] : null,
                    'md_id'              => (string) ($array['name'] ?? ''),
                    'level'              => (string) ($array['raid_level'] ?? ''),
                    'size_bytes'         => isset($array['size_bytes']) ? (int) $array['size_bytes'] : null,
                    'raid_disks'         => isset($array['raid_disks']) ? (int) $array['raid_disks'] : null,
                    'metadata_version'   => isset($array['metadata_version']) ? (string) $array['metadata_version'] : null,
                    'consistency_policy' => isset($array['consistency_policy']) ? (string) $array['consistency_policy'] : null,
                    'chunk_size'         => isset($array['chunk_size']) ? (int) $array['chunk_size'] : null,
                    'layout'             => $array['layout'] ?? null,
                    'resync_start_sectors'     => $array['resync_start'] ?? null,
                    'reshape_position_sectors' => $array['reshape_position'] ?? null,
                    'bitmap_type'        => ($array['bitmap_type'] ?? '') !== '' ? (string) $array['bitmap_type'] : null,
                    'bitmap_location'    => ($array['bitmap_location'] ?? '') !== '' ? (string) $array['bitmap_location'] : null,
                    'bitmap_chunksize'   => isset($array['bitmap_chunksize']) ? (int) $array['bitmap_chunksize'] : null,
                    'bitmap_metadata'    => ($array['bitmap_metadata'] ?? '') !== '' ? (string) $array['bitmap_metadata'] : null,
                    'bitmap_time_base'   => isset($array['bitmap_time_base']) ? (int) $array['bitmap_time_base'] : null,
                ]
            );

            $seenArrayIds[] = $arrayRow->id;
            $this->logDiscoveryArray($arrayRow);

            $seenDevIds = [];

            foreach ($devices as $devId => $devData) {
                $devData = is_array($devData) ? $devData : [];

                $driveRow = MdadmDrive::updateOrCreate(
                    ['mdadm_array_id' => $arrayRow->id, 'dev_id' => (string) $devId],
                    [
                        'device_id'       => $deviceId,
                        'app_id'          => $appId,
                        'snmp_index'      => isset($devData['snmp_index']) ? (int) $devData['snmp_index'] : null,
                        'path'            => (string) ($devData['device_name'] ?? ''),
                        'size_bytes'      => isset($devData['size_bytes']) ? (int) $devData['size_bytes'] : (isset($devData['size_blocks']) ? (int) $devData['size_blocks'] * 1024 : null),
                        'slot'            => isset($devData['slot']) ? (int) $devData['slot'] : null,
                        'id_model'        => isset($devData['id_model']) ? (string) $devData['id_model'] : null,
                        'id_serial_short' => isset($devData['id_serial_short']) ? (string) $devData['id_serial_short'] : null,
                        'device_role'     => isset($devData['device_role']) ? (string) $devData['device_role'] : null,
                        'offset_sectors'  => $devData['offset_sectors'] ?? null,
                        'ppl_sector'      => $devData['ppl_sector'] ?? null,
                        'ppl_size_sectors' => $devData['ppl_size'] ?? null,
                    ]
                );

                $this->logDiscoveryDrive((string) $devId, $driveRow);

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

    /** Human-readable GiB size for verbose discovery logging, or 'null'. */
    private function humanGiB(?int $bytes): string
    {
        return $bytes !== null ? sprintf('%.1f GiB', $bytes / (1024 ** 3)) : 'null';
    }

    private function logDiscoveryArray(MdadmArray $row): void
    {
        if (! Debug::isVerbose()) {
            return;
        }

        echo sprintf(
            '      DB array  id=%-4d  %s  (%s)  size=%s  meta=%s  chunk=%s' . PHP_EOL,
            $row->id,
            $row->md_id ?? '(no name)',
            $row->level ?? 'null',
            $this->humanGiB($row->size_bytes),
            $row->metadata_version ?? 'null',
            $row->chunk_size !== null ? $row->chunk_size . 'B' : 'null'
        );
    }

    private function logDiscoveryDrive(string $devId, MdadmDrive $row): void
    {
        if (! Debug::isVerbose()) {
            return;
        }

        echo sprintf(
            '        DB drive  %s  path=%s  slot=%s  size=%s  model=%s  serial=%s' . PHP_EOL,
            $devId,
            $row->path ?? 'null',
            $row->slot !== null ? (string) $row->slot : 'null',
            $this->humanGiB($row->size_bytes),
            $row->id_model ?? 'null',
            $row->id_serial_short ?? 'null'
        );
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
                'sync_min_sectors'   => $sync['min_sectors'] ?? null,
                'sync_max_sectors'   => $sync['max_sectors'] ?? null,
                'is_mounted'         => $array['is_mounted'] ?? null,
                'mount_points'       => ($array['mount_points'] ?? '') !== '' ? (string) $array['mount_points'] : null,
                'is_swap'            => $array['is_swap'] ?? null,
                'bitmap_backlog'     => isset($array['bitmap_backlog']) ? (int) $array['bitmap_backlog'] : null,
                'bitmap_max_backlog' => isset($array['bitmap_max_backlog']) ? (int) $array['bitmap_max_backlog'] : null,
                'bitmap_can_clear'   => $array['bitmap_can_clear'] ?? null,
                'stripe_cache_size'   => isset($array['stripe_cache_size']) ? (int) $array['stripe_cache_size'] : null,
                'stripe_cache_active' => isset($array['stripe_cache_active']) ? (int) $array['stripe_cache_active'] : null,
                'journal_mode'       => ($array['journal_mode'] ?? '') !== '' ? (string) $array['journal_mode'] : null,
            ]);

            foreach ($devices as $devId => $devData) {
                $devData = is_array($devData) ? $devData : [];

                $driveRow = $arrayRow->drives->firstWhere('dev_id', (string) $devId);
                if ($driveRow === null) {
                    continue;
                }

                // device_role comes from the meta table and is written at discovery; poll updates health only.
                $driveRow->update([
                    'state'                 => (string) ($devData['state'] ?? ''),
                    'state_flags'           => is_array($devData['state_flags'] ?? null) ? $devData['state_flags'] : null,
                    'errors'                => isset($devData['errors']) ? (int) $devData['errors'] : null,
                    'is_missing'            => (bool) ($devData['is_missing'] ?? false),
                    'events'                => isset($devData['events']) ? (int) $devData['events'] : null,
                    'recovery_start_sectors' => $devData['recovery_start'] ?? null,
                    'bad_block_count'       => isset($devData['bad_block_count']) ? (int) $devData['bad_block_count'] : null,
                    'unack_bad_block_count' => isset($devData['unack_bad_block_count']) ? (int) $devData['unack_bad_block_count'] : null,
                ]);
            }
        }
    }

    /** @return array<string, mixed> */
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
