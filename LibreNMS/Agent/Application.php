<?php

namespace LibreNMS\Agent;

use App\Models\Application as ApplicationModel;
use App\Models\Eventlog;
use App\Models\Sensor;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use LibreNMS\Enum\Sensor as SensorEnum;
use LibreNMS\Enum\Severity;
use LibreNMS\Exceptions\JsonAppException;
use LibreNMS\Exceptions\JsonAppMissingKeysException;
use LibreNMS\OS;
use LibreNMS\RRD\RrdDefinition;
use LibreNMS\Util\Number;

abstract class Application
{
    public function __construct(
        protected OS $os,
        protected ApplicationModel $app,
        protected array $agent_data = [],
    ) {
    }

    /**
     * Gate for discovery. Return false to skip discover() for this run.
     * Override to add version checks, feature flags, or any pre-condition.
     */
    public function shouldDiscover(): bool
    {
        return true;
    }

    /** Runs ~4×/day. Create/sync sensors and topology. */
    public function discover(): void
    {
    }

    /**
     * Called after discover() to print a summary line to stdout.
     * The default ends the current dot-progress line with a newline.
     * Override to add verbose detail when Debug::isVerbose() is true.
     */
    public function printDiscoverySummary(): void
    {
        echo PHP_EOL;
    }

    /**
     * Gate for polling. Return false to skip poll() for this run.
     * Default: always run. Override to add pre-conditions such as
     * checking that discovery has already populated app->data.
     */
    public function shouldPoll(): bool
    {
        return true;
    }

    /** Runs every ~5 min. Update values only. */
    abstract public function poll(): void;

    /**
     * Delete all DB data owned by this app handler for the current device.
     * Called when the application is removed or the app type changes.
     * Returns the number of rows deleted.
     */
    public function cleanup(): int
    {
        return Sensor::where('device_id', $this->os->getDeviceId())
            ->where('poller_type', 'agent')
            ->delete();
    }

    // -------------------------------------------------------------------------
    // App data persistence
    // -------------------------------------------------------------------------

    protected function getAppData(): array
    {
        return (array) ($this->app->data ?? []);
    }

    protected function saveAppData(array $data): void
    {
        $this->app->data = $data;
        $this->app->save();
    }

    // -------------------------------------------------------------------------
    // Sensor discovery
    // -------------------------------------------------------------------------

    /**
     * Register a sensor for discovery.
     * Parameters mirror discover_sensor() in includes/discovery/functions.inc.php.
     * Returns $this for fluent ->withStateTranslations() chaining.
     */
    protected function discoverSensor(
        string $class,
        string $type,
        string $index,
        string $oid,
        string $descr,
        int|float $current = 0,
        string $poller_type = 'agent',
        ?string $group = null,
        ?string $navigation = null,
        int|float $divisor = 1,
        int|float $multiplier = 1,
        int|float|null $lowLimit = null,
        int|float|null $lowWarnLimit = null,
        int|float|null $warnLimit = null,
        int|float|null $highLimit = null,
        string $rrd_type = 'GAUGE',
    ): static {
        app('sensor-discovery')->discover(new Sensor([
            'device_id'           => $this->os->getDeviceId(),
            'poller_type'         => $poller_type,
            'sensor_class'        => $class,
            'sensor_type'         => $type,
            'sensor_index'        => $index,
            'sensor_oid'          => $oid,
            'sensor_descr'        => $descr,
            'sensor_current'      => $current,
            'group'               => $group,
            'sensor_navigation'   => $navigation,
            'sensor_divisor'      => $divisor,
            'sensor_multiplier'   => $multiplier,
            'sensor_limit_low'    => $lowLimit,
            'sensor_limit_low_warn' => $lowWarnLimit,
            'sensor_limit_warn'   => $warnLimit,
            'sensor_limit'        => $highLimit,
            'rrd_type'            => $rrd_type,
        ]));

        return $this;
    }

    /**
     * Register state translations for the last discovered sensor.
     * Fluent — call immediately after discoverSensor().
     */
    protected function withStateTranslations(string $stateName, array $translations): static
    {
        app('sensor-discovery')->withStateTranslations($stateName, $translations);

        return $this;
    }

    /** Sync all sensors registered since the last sync, filtered by type(s). */
    protected function syncSensors(string ...$types): void
    {
        foreach ($types as $type) {
            app('sensor-discovery')->sync(sensor_type: $type);
        }
    }

    /**
     * Delete sensors whose OID starts with $oidPrefix but are no longer expected.
     * Removes rows where the OID is not in $expectedOids OR the type is not in $knownTypes.
     */
    protected function deleteStaleAgentSensors(
        string $oidPrefix,
        array $knownTypes,
        array $expectedOids,
    ): int {
        $deleted = Sensor::where('device_id', $this->os->getDeviceId())
            ->where('sensor_oid', 'like', $oidPrefix . '%')
            ->where(function ($q) use ($expectedOids, $knownTypes): void {
                $q->whereNotIn('sensor_oid', $expectedOids)
                  ->orWhereNotIn('sensor_type', $knownTypes);
            })
            ->delete();

        if ($deleted > 0) {
            $this->logEvent('notice', "Removed $deleted stale sensor(s)");
        }

        return $deleted;
    }

    // -------------------------------------------------------------------------
    // Sensor polling
    // -------------------------------------------------------------------------

    /**
     * Update sensor_current + write RRD for a map of sensor_index => raw value.
     * Mirrors record_sensor_data() in includes/polling/functions.inc.php:
     * - NaN / -32768 guard via Number::extract()
     * - divisor / multiplier scaling
     * - rrd_type taken from the sensor row
     * - threshold crossing events (sensor_alert)
     * - state change events with human-readable labels
     * - sensor_prev + lastupdate persisted via Eloquent save()
     *
     * @param  array<string, int|float>  $values      ['sensor_index' => raw_value, ...]
     * @param  string                    $oidPrefix   Scopes the DB query (e.g. 'app:mdadm:')
     */
    protected function updateSensorValues(array $values, string $oidPrefix): void
    {
        $device = $this->os->getDeviceArray();
        $sensors = Sensor::where('device_id', $device['device_id'])
            ->where('sensor_oid', 'like', $oidPrefix . '%')
            ->get()
            ->keyBy('sensor_index');

        foreach ($values as $index => $rawValue) {
            $sensor = $sensors[$index] ?? null;
            if ($sensor === null) {
                continue;
            }

            $unit = SensorEnum::from($sensor->sensor_class)->unit();
            $class = trans('sensors.' . $sensor->sensor_class . '.short');

            $value = Number::extract($rawValue);
            if ($value == -32768 || is_nan((float) $value)) {
                $value = 0;
            }

            if ($sensor->sensor_divisor && $value !== 0) {
                $value /= $sensor->sensor_divisor;
            }
            if ($sensor->sensor_multiplier) {
                $value *= $sensor->sensor_multiplier;
            }

            $prevValue = $sensor->sensor_current;

            Log::info("$value $unit");

            app('Datastore')->put($device, 'sensor', [
                'sensor_class' => $sensor->sensor_class,
                'sensor_type'  => $sensor->sensor_type,
                'sensor_descr' => $sensor->sensor_descr,
                'sensor_index' => $sensor->sensor_index,
                'rrd_name'     => \get_sensor_rrd_name($device, $sensor->toArray()),
                'rrd_def'      => RrdDefinition::make()->addDataset('sensor', $sensor->rrd_type ?? 'GAUGE'),
            ], ['sensor' => $value]);

            // Threshold crossing alerts
            if ($sensor->sensor_alert) {
                if ($sensor->sensor_limit_low != '' && $prevValue > $sensor->sensor_limit_low && $value < $sensor->sensor_limit_low) {
                    Eventlog::log("$class under threshold: $value $unit (< {$sensor->sensor_limit_low} $unit)", $device['device_id'], $sensor->sensor_class, Severity::Warning, $sensor->sensor_id);
                } elseif ($sensor->sensor_limit != '' && $prevValue < $sensor->sensor_limit && $value > $sensor->sensor_limit) {
                    Eventlog::log("$class above threshold: $value $unit (> {$sensor->sensor_limit} $unit)", $device['device_id'], $sensor->sensor_class, Severity::Warning, $sensor->sensor_id);
                }
            }

            // State change event with human-readable labels
            if ($sensor->sensor_class === 'state' && $prevValue != $value) {
                $trans = array_column(
                    DB::select(
                        'SELECT `state_translations`.`state_value`, `state_translations`.`state_descr` FROM `sensors_to_state_indexes` LEFT JOIN `state_translations` USING (`state_index_id`) WHERE `sensors_to_state_indexes`.`sensor_id`=? AND `state_translations`.`state_value` IN (?,?)',
                        [$sensor->sensor_id, $value, $prevValue]
                    ),
                    'state_descr',
                    'state_value'
                );
                Eventlog::log(
                    $class . ' sensor ' . ($sensor->sensor_descr ?? '') . ' has changed from ' . ($trans[$prevValue] ?? '#unamed state#') . " ($prevValue) to " . ($trans[$value] ?? '#unamed state#') . " ($value)",
                    $device['device_id'],
                    $class,
                    Severity::Notice,
                    $sensor->sensor_id
                );
            }

            // Persist sensor_current, sensor_prev, lastupdate
            if ($value != $prevValue) {
                $sensor->sensor_current = $value;
                $sensor->sensor_prev = $prevValue;
                $sensor->lastupdate = DB::raw('NOW()');
                $sensor->save();
            }
        }
    }

    // -------------------------------------------------------------------------
    // RRD
    // -------------------------------------------------------------------------

    protected function putRrd(string $type, array $tags, array $fields): void
    {
        app('Datastore')->put($this->os->getDeviceArray(), $type, $tags, $fields);
    }

    // -------------------------------------------------------------------------
    // Logging
    // -------------------------------------------------------------------------

    protected function logEvent(
        Severity|string|int $level,
        string $message,
        string $type = 'application',
    ): void {
        Eventlog::log(
            $message,
            $this->os->getDeviceId(),
            $type,
            $this->resolveSeverity($level),
        );
    }

    protected function resolveSeverity(Severity|string|int $level): Severity
    {
        if ($level instanceof Severity) {
            return $level;
        }

        if (is_int($level)) {
            return Severity::tryFrom($level) ?? Severity::Info;
        }

        return match (strtolower(trim($level))) {
            'unknown'           => Severity::Unknown,
            'ok'                => Severity::Ok,
            'info'              => Severity::Info,
            'notice'            => Severity::Notice,
            'warning'           => Severity::Warning,
            'error', 'critical' => Severity::Error,
            default             => Severity::Info,
        };
    }

    // -------------------------------------------------------------------------
    // Payload fetch
    // -------------------------------------------------------------------------

    /**
     * Fetch JSON payload from SNMP extend, falling back to unix-agent cache.
     * Returns null and calls update_application() on unrecoverable error.
     */
    protected function fetchPayload(string $extend_name, int $min_version = 1): ?array
    {
        try {
            return \json_app_get($this->os->getDeviceArray(), $extend_name, $min_version);
        } catch (JsonAppMissingKeysException $e) {
            return $e->getParsedJson();
        } catch (JsonAppException $e) {
            $cached = $this->agent_data[$extend_name] ?? null;
            if ($cached !== null) {
                $decoded = json_decode($cached, true);
                if (is_array($decoded)) {
                    return $decoded;
                }
                \update_application($this->app, 'ERROR: Invalid JSON from agent data', []);

                return null;
            }
            \update_application($this->app, $e->getCode() . ':' . $e->getMessage(), []);

            return null;
        }
    }
}
