<?php

namespace LibreNMS\Agent\Unix\Mdadm;

use App\Facades\Rrd;
use App\Models\Application;
use App\Models\MdadmArray;
use App\Models\Sensor;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Cache;

/**
 * Data layer for mdadm HTML views.
 *
 * Loads, resolves and caches all data needed to render any mdadm view
 * (device app page, device overview, global dashboard).
 *
 * -------------------------------------------------------------------------
 * TODO: Caching
 * -------------------------------------------------------------------------
 * - Cache driver must support object serialisation (file, redis, database).
 *   The array driver is per-process only and will not help across web requests.
 *
 * - The cached object includes the full Eloquent Collection ($allSensors).
 *   If cache size becomes a concern, switch to caching a plain array snapshot
 *   and reconstructing on load.
 *
 * - invalidate() must be called from the poller/discoverer after each run
 *   so the next page load reflects fresh data.  Currently nothing calls it
 *   automatically — wire it into Unix::pollApplication() / discoverApplication()
 *   once the cache driver is confirmed.
 *
 * - forAll() creates one cache entry per device.  A global "all devices" key
 *   is not maintained; any global view assembles fresh HtmlData objects (each
 *   individually cached).
 *
 * - The 5-minute TTL matches the default poll interval.  If the poll interval
 *   is changed in config, this TTL should be adjusted to match.
 * -------------------------------------------------------------------------
 */
class HtmlData
{
    private const CACHE_TTL = 300; // seconds (5 min — matches default poll interval)

    public readonly bool $isLegacy;
    public readonly array $arraysMeta;       // [arrayName => rawMeta array]
    public readonly array $arraysDevices;    // [arrayName => [devKey => metaDev array]]
    public readonly array $arrayUuidByName;  // [arrayName => uuid]
    public readonly array $arrayData;        // [arrayName => sensor+diskio map]
    public readonly array $appMetrics;       // flat metric key => value
    public readonly Collection $allSensors;

    /** @var Collection<string, MdadmArray> MdadmArray rows keyed by uuid, with drives eager-loaded. */
    private Collection $dbArrays;

    private function __construct(
        public readonly Application $app,
        public readonly array $device,
    ) {
        $this->loadDbData();
        [$this->arraysMeta, $this->arraysDevices, $this->arrayUuidByName] = $this->buildDiscoveryArrays();
        [$this->allSensors, $this->arrayData, $this->isLegacy] = $this->buildSensors();
        $this->appMetrics = $this->app->metrics->pluck('value', 'metric')->toArray();
    }

    // -------------------------------------------------------------------------
    // Label / class / info lookup tables (constants)
    // -------------------------------------------------------------------------

    private const ARRAY_HEALTH_LABELS = [
        0 => 'Healthy', 1 => 'Degraded', 2 => 'Failed',   3 => 'Missing',
        4 => 'Clear',   5 => 'Inactive', 6 => 'Suspended', 7 => 'Readonly',
        8 => 'Read Auto', 9 => 'Write Pending',
    ];

    private const ARRAY_HEALTH_CLASSES = [
        0 => 'default',
        1 => 'warning', 7 => 'warning', 8 => 'warning', 9 => 'warning',
        2 => 'danger',  3 => 'danger',  4 => 'danger',  5 => 'danger',  6 => 'danger',
    ];

    private const ARRAY_HEALTH_INFOS = [
        0 => 'Array is operational (clean/active/active-idle).',
        1 => 'Array is running but degraded or restricted.',
        2 => 'Array is not serving normal I/O safely.',
        3 => 'Array has a missing member.',
        4 => 'No devices/size/level (stopped).',
        5 => 'Array is inactive; I/O fails.',
        6 => 'Array is suspended; I/O blocked.',
        7 => 'Readonly; write requests fail.',
        8 => 'Read-auto; readonly until write activates array.',
        9 => 'Write-pending; writes blocked until active.',
    ];

    private const OPERATION_LABELS = [
        0 => 'Idle',    1 => 'Clean',        2 => 'Active',       3 => 'Check',
        4 => 'Resync',  5 => 'Recover',      6 => 'Repair',       7 => 'Inactive',
        8 => 'Readonly', 9 => 'Clear',       10 => 'Read Auto',   11 => 'Write Pending',
        12 => 'Active Idle', 13 => 'Suspended',
    ];

    private const OPERATION_CLASSES = [
        0 => 'default', 1 => 'default', 2 => 'default', 3 => 'default',
        4 => 'default', 10 => 'default', 12 => 'default',
        5 => 'warning', 6 => 'warning', 7 => 'warning', 8 => 'warning',
        9 => 'warning', 11 => 'warning',
        13 => 'danger',
    ];

    private const OPERATION_INFOS = [
        0  => 'No sync operation running.',
        1  => 'Array is clean.',
        2  => 'Array is active.',
        3  => 'Consistency check running; reads all blocks and checks redundancy.',
        4  => 'Resync running; recalculating redundancy after unclean shutdown or creation.',
        5  => 'Recovery running; building a hot spare to replace a failed or missing device.',
        6  => 'Repair running; full check and repair, write-intent bitmap not used.',
        12 => 'Active with no recent writes.',
    ];

    private const DEVICE_HEALTH_LABELS = [
        0 => 'In Sync', 1 => 'Active',   2 => 'Write Mostly',     3 => 'Spare',
        4 => 'Rebuilding', 5 => 'Want Replacement', 6 => 'Replacement',
        7 => 'Write Error', 8 => 'Blocked', 9 => 'Faulty',        10 => 'Missing',
    ];

    private const DEVICE_HEALTH_CLASSES = [
        0 => 'default', 1 => 'default', 2 => 'default',
        3 => 'warning', 4 => 'warning', 5 => 'warning', 6 => 'warning',
        7 => 'danger',  8 => 'danger',  9 => 'danger',  10 => 'danger',
    ];

    private const DEVICE_HEALTH_INFOS = [
        0  => 'Member is fully in sync with the array.',
        1  => 'Member is active.',
        2  => 'Member is write-mostly; only read if no other options.',
        3  => 'Member is spare; working but not a full member.',
        4  => 'Member is rebuilding/recovering.',
        5  => 'Member wants replacement due to errors or user request.',
        6  => 'Member is a replacement device for another active member with the same raid_disk.',
        7  => 'Member has write errors.',
        8  => 'Member is blocked; failed but not yet acknowledged.',
        9  => 'Member is faulty; kicked from active use due to detected fault.',
        10 => 'Member is missing.',
    ];

    /** Resolve human-readable label, Bootstrap class, and info string for a sensor entry. */
    private static function resolveEntry(string $type, int $val): array
    {
        return match ($type) {
            'mdadm_array_health_status'    => [
                'label' => self::ARRAY_HEALTH_LABELS[$val] ?? 'Unknown',
                'class' => self::ARRAY_HEALTH_CLASSES[$val] ?? 'default',
                'info'  => self::ARRAY_HEALTH_INFOS[$val] ?? 'Health state unknown.',
            ],
            'mdadm_array_operation_status' => [
                'label' => self::OPERATION_LABELS[$val] ?? 'Unknown',
                'class' => self::OPERATION_CLASSES[$val] ?? 'default',
                'info'  => self::OPERATION_INFOS[$val] ?? 'Operation state unknown.',
            ],
            'mdadm_array_mismatch', 'mdadm_device_error' => [
                'label' => (string) max(0, $val),
                'class' => $val > 0 ? 'warning' : 'default',
                'info'  => '',
            ],
            'mdadm_device_health_status'   => [
                'label' => self::DEVICE_HEALTH_LABELS[$val] ?? 'Unknown',
                'class' => self::DEVICE_HEALTH_CLASSES[$val] ?? 'default',
                'info'  => self::DEVICE_HEALTH_INFOS[$val] ?? 'Device health state unknown.',
            ],
            default => ['label' => (string) $val, 'class' => 'default', 'info' => ''],
        };
    }

    // -------------------------------------------------------------------------
    // Caching factory methods
    // -------------------------------------------------------------------------

    /** Load data for one device+app, cached for 5 minutes. */
    public static function forDevice(Application $app, array $device): self
    {
        return Cache::remember(
            self::cacheKey($device['device_id'], $app->app_id),
            self::CACHE_TTL,
            fn () => new self($app, $device)
        );
    }

    /**
     * Scoped to one array — shares the per-device cache entry.
     * Use ->array($arrayName) on the returned object to get the filtered data.
     */
    public static function forArray(Application $app, array $device, string $arrayName): self
    {
        return self::forDevice($app, $device);
    }

    /**
     * Scoped to one drive — shares the per-device cache entry.
     * Use ->drive($arrayName, $driveKey) on the returned object.
     */
    public static function forDrive(Application $app, array $device, string $arrayName, string $driveKey): self
    {
        return self::forDevice($app, $device);
    }

    /**
     * All devices with a mdadm app.
     * Returns HtmlData[] keyed by device_id; each entry is individually cached.
     *
     * @return array<int, self>
     */
    public static function forAll(): array
    {
        return Application::where('app_type', 'mdadm')
            ->with('device')
            ->get()
            ->filter(fn ($app) => $app->device !== null)
            ->mapWithKeys(fn ($app) => [
                $app->device_id => self::forDevice($app, $app->device->toArray()),
            ])
            ->all();
    }

    /** Flush the cache for this device+app so the next request reloads fresh data. */
    public function invalidate(): void
    {
        Cache::forget(self::cacheKey($this->device['device_id'], $this->app->app_id));
    }

    // -------------------------------------------------------------------------
    // Scope accessors
    // -------------------------------------------------------------------------

    /** All known array names (discovery order). */
    public function arrayNames(): array
    {
        return array_keys($this->arrayData);
    }

    /**
     * Full sensor+diskio map for one array.
     * Keys: mdadm_array_health_status, mdadm_array_operation_status,
     *       mdadm_array_mismatch, diskio, devices.
     * Each sensor entry: ['val' => int, 'label' => string, 'class' => string, 'info' => string, 'sensor' => Sensor]
     */
    public function array(string $arrayName): array
    {
        return $this->arrayData[$arrayName] ?? [];
    }

    /**
     * Sensor + meta data for one drive within an array.
     * Returns ['sensors' => [...], 'meta' => [...]]
     */
    public function drive(string $arrayName, string $driveKey): array
    {
        return [
            'sensors' => $this->arrayData[$arrayName]['devices'][$driveKey] ?? [],
            'meta'    => $this->arraysDevices[$arrayName][$driveKey] ?? [],
        ];
    }

    /**
     * Parsed sync scalars for one array.
     * Keys: action, speed_bps, done_bytes, total_bytes, completed_pct, is_syncing
     */
    public function syncDataForArray(string $arrayName): array
    {
        $uuid = (string) ($this->arrayUuidByName[$arrayName] ?? '');
        $dbRow = $this->dbArrays->get($uuid);
        $dbRow = $dbRow instanceof MdadmArray ? $dbRow : null;
        $action = $dbRow !== null ? strtolower(trim((string) $dbRow->sync_action)) : '';

        return [
            'action'        => $action,
            'speed_bps'     => $dbRow !== null ? (int) ($dbRow->sync_speed_bps ?? 0) : 0,
            'speed_min_bps' => $dbRow !== null ? (int) ($dbRow->sync_speed_min_bps ?? 0) : 0,
            'speed_max_bps' => $dbRow !== null ? (int) ($dbRow->sync_speed_max_bps ?? 0) : 0,
            'done_bytes'    => $dbRow !== null ? (int) ($dbRow->sync_done_bytes ?? 0) : 0,
            'total_bytes'   => $dbRow !== null ? (int) ($dbRow->sync_total_bytes ?? 0) : 0,
            'completed_pct' => $dbRow !== null ? (float) ($dbRow->sync_completed_pct ?? 0) : 0.0,
            'last_action'   => $dbRow !== null ? (string) ($dbRow->sync_last_action ?? '') : '',
            'is_syncing'    => $action !== '' && $action !== 'idle',
        ];
    }

    // -------------------------------------------------------------------------
    // Data loading (private)
    // -------------------------------------------------------------------------

    /**
     * Load MdadmArray rows (with drives eager-loaded) for this app.
     * Stored in $this->dbArrays keyed by uuid so subsequent loaders can
     * use it without additional queries.
     */
    private function loadDbData(): void
    {
        $this->dbArrays = MdadmArray::where('app_id', $this->app->app_id)
            ->with('drives')
            ->get()
            ->keyBy('uuid');
    }

    /** @return array{array<string,array<string,mixed>>, array<string,array<string,mixed>>, array<string,string>} */
    private function buildDiscoveryArrays(): array
    {
        $arraysMeta = [];
        $arraysDevices = [];
        $arrayUuidByName = [];

        foreach ($this->dbArrays as $uuid => $dbRow) {
            $uuid = (string) $uuid;
            $arrayName = (string) ($dbRow->name ?? $uuid);
            if ($arrayName === '') {
                continue;
            }

            $arraysMeta[$arrayName] = [
                'array_name'         => $dbRow->name,
                'uuid'               => $dbRow->uuid,
                'raid_level'         => $dbRow->level,
                'state'              => $dbRow->state,
                'size_bytes'         => $dbRow->size_bytes,
                'raid_disks'         => $dbRow->raid_disks,
                'metadata_version'   => $dbRow->metadata_version,
                'consistency_policy' => $dbRow->consistency_policy,
                'chunk_size'         => $dbRow->chunk_size,
                'active_devices'     => $dbRow->active_devices,
                'working_devices'    => $dbRow->working_devices,
                'spare_devices'      => $dbRow->spare_devices,
                'failed_devices'     => $dbRow->failed_devices,
                'degraded'           => $dbRow->degraded,
                'mismatch_cnt'       => $dbRow->mismatch_cnt,
            ];

            $arrayDevs = [];
            foreach ($dbRow->drives as $drive) {
                $arrayDevs[$drive->dev_id] = [
                    'path'            => $drive->path,
                    'state'           => $drive->state,
                    'state_flags'     => $drive->state_flags,
                    'errors'          => $drive->errors,
                    'is_missing'      => $drive->is_missing,
                    'size_bytes'      => $drive->size_bytes,
                    'device_role'     => $drive->device_role,
                    'slot'            => $drive->slot,
                    'id_model'        => $drive->id_model,
                    'id_serial_short' => $drive->id_serial_short,
                ];
            }
            $arraysDevices[$arrayName] = $arrayDevs;
            $arrayUuidByName[$arrayName] = $uuid;
        }

        return [$arraysMeta, $arraysDevices, $arrayUuidByName];
    }

    /** @return array{Collection, array<string,mixed>, bool} */
    private function buildSensors(): array
    {
        // Seed from discovery so every known array is present even before sensors exist
        $arrayData = array_fill_keys(array_keys($this->arraysMeta), []);

        $sensors = Sensor::where('device_id', $this->device['device_id'])
            ->where('sensor_oid', 'like', 'app:mdadm:%')
            ->orderBy('sensor_descr')
            ->get();

        foreach ($sensors as $sensor) {
            $group = (string) $sensor->group;
            $sensorNav = (string) ($sensor->sensor_navigation ?? '');
            $val = (int) ($sensor->sensor_current ?? -1);
            $type = (string) $sensor->sensor_type;
            $entry = array_merge(['val' => $val, 'sensor' => $sensor], self::resolveEntry($type, $val));

            if (str_contains($group, '::devices')) {
                $arrayName = str_replace(['Mdadm ', '::devices'], ['', ''], $group);
                $parts = explode('_', (string) $sensor->sensor_index);
                $devKey = $parts[1] ?? (string) $sensor->sensor_descr;
                $arrayData[$arrayName]['devices'][$devKey][$type] = $entry;
            } else {
                $arrayName = $this->arrayNameFromGroupOrNavigation($group, $sensorNav);
                if ($arrayName === '') {
                    continue;
                }
                $arrayData[$arrayName][$type] = $entry;
            }
        }

        // Legacy fallback: populate from RRD file names when no sensors exist
        $isLegacy = $sensors->isEmpty();
        if ($isLegacy && empty($arrayData)) {
            foreach (Rrd::getRrdApplicationArrays($this->device, $this->app->app_id, 'mdadm') as $name) {
                $arrayData[$name] = [];
            }
        }

        return [$sensors, $arrayData, $isLegacy];
    }

    // -------------------------------------------------------------------------
    // Private helpers
    // -------------------------------------------------------------------------

    private static function cacheKey(int $deviceId, int $appId): string
    {
        return "mdadm.htmldata.{$deviceId}.{$appId}";
    }

    private function arrayNameFromGroupOrNavigation(string $group, string $sensorNavigation): string
    {
        $normalizedGroup = trim(str_replace('Mdadm ', '', $group));
        if ($normalizedGroup !== '') {
            return str_replace('::devices', '', $normalizedGroup);
        }

        if (preg_match('/array=([^\/]+)\//', $sensorNavigation, $matches) === 1) {
            return rawurldecode((string) $matches[1]);
        }

        return '';
    }
}
