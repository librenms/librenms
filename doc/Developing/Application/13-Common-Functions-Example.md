# 1.3 Complete Application Handler Example

This page shows a complete, self-contained handler that exercises every method on the `Application` base class. Read it together with [1.1 Creating App-Based Sensors](11-App-Based-Sensors.md) and [1.2 Database Storing](12-Database-storing.md).

The example app (`diskmon`) monitors a set of named disks. The agent reports disk health and I/O metrics. A second table (`diskmon_drives`) stores a persistent row per drive so the data is queryable outside of LibreNMS sensors.

---

## File layout

```
LibreNMS/Agent/Unix/Diskmon/Common.php   ← handler class
app/Models/DiskmonDrive.php              ← Eloquent model
database/migrations/…_create_diskmon_drives_table.php
resources/definitions/agent/unix.yaml   ← registration
```

---

## Registration

```yaml
# resources/definitions/agent/unix.yaml
diskmon:
  handler: LibreNMS\Agent\Unix\Diskmon\Common
```

---

## Migration

```php
<?php
// database/migrations/2026_05_17_130000_create_diskmon_drives_table.php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('diskmon_drives', function (Blueprint $table) {
            $table->id();
            $table->unsignedInteger('device_id')->index();
            $table->unsignedInteger('app_id')->index();
            $table->string('disk_id', 64);
            $table->string('path')->nullable();
            $table->string('state', 64)->nullable();
            $table->unsignedInteger('errors')->nullable();
            $table->timestamps();

            $table->unique(['app_id', 'disk_id']);
            $table->foreign('device_id')
                  ->references('device_id')->on('devices')
                  ->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('diskmon_drives');
    }
};
```

---

## Eloquent model

```php
<?php
// app/Models/DiskmonDrive.php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DiskmonDrive extends Model
{
    protected $table = 'diskmon_drives';

    protected $fillable = ['device_id', 'app_id', 'disk_id', 'path', 'state', 'errors'];

    public function device(): BelongsTo
    {
        return $this->belongsTo(Device::class, 'device_id', 'device_id');
    }
}
```

---

## Handler class

The handler below is annotated at every method to explain when and why each base-class function is called.

```php
<?php
// LibreNMS/Agent/Unix/Diskmon/Common.php

namespace LibreNMS\Agent\Unix\Diskmon;

use App\Models\DiskmonDrive;
use App\Models\StateTranslation;
use LibreNMS\Agent\Application;
use LibreNMS\Enum\Severity;
use LibreNMS\RRD\RrdDefinition;
use LibreNMS\Util\Debug;

class Common extends Application
{
    // -------------------------------------------------------------------------
    // State flag: set to true at the end of a successful discover() run.
    // Used by printDiscoverySummary() to decide whether verbose output is safe.
    // -------------------------------------------------------------------------
    private bool $discoveryCompleted = false;

    // Populated by initState() and used throughout the handler.
    private array $payload    = [];
    private array $disks      = [];   // $payload['data']['disks']
    private array $discovery  = [];   // persisted across poll cycles via app->data
    private array $appdata    = [];


    // -------------------------------------------------------------------------
    // shouldDiscover()
    //
    // Gate called before discover(). Return false to skip this cycle entirely.
    // Use it for version checks, feature flags, or any payload pre-condition
    // that cannot be known without fetching the payload first.
    //
    // Here we require payload version >= 2. Version 1 lacked the disk table
    // structure that our sensor schema depends on.
    // -------------------------------------------------------------------------
    public function shouldDiscover(): bool
    {
        $payload = $this->fetchPayload('diskmon', 1);

        return $payload !== null && ($payload['version'] ?? 0) >= 2;
    }


    // -------------------------------------------------------------------------
    // shouldPoll()
    //
    // Gate called before poll(). Return false to skip this cycle entirely.
    // The canonical use is to guard against polling before discovery has run:
    // if $app->data has no discovery map yet, the poll has no sensors to update.
    //
    // getAppData() reads the JSON blob stored in applications.data.
    // -------------------------------------------------------------------------
    public function shouldPoll(): bool
    {
        return ! empty($this->getAppData()['discovery']['disks'] ?? []);
    }


    // -------------------------------------------------------------------------
    // discover()
    //
    // Runs ~4×/day. Responsible for:
    //   1. Fetching the payload and initialising shared state.
    //   2. Resetting the sensor-discovery singleton.
    //   3. Calling discoverSensor() + withStateTranslations() for each entity.
    //   4. Syncing to the DB and removing stale sensors.
    //   5. Persisting the discovery map to app->data for poll() to consume.
    // -------------------------------------------------------------------------
    public function discover(): void
    {
        $payload = $this->fetchPayload('diskmon', 2);
        if ($payload === null) {
            return;
        }

        $this->initState($payload);

        // Reset the sensor-discovery singleton so sensors from a previous
        // handler do not bleed into this one.
        app()->forgetInstance('sensor-discovery');

        $expectedOids = [];

        foreach ($this->disks as $diskId => $disk) {
            $healthIndex = "{$diskId}_health";
            $oid         = "app:diskmon:{$healthIndex}";

            // discoverSensor() registers the sensor in the singleton buffer.
            // Named parameters make the intent clear; positional works too.
            $this->discoverSensor(
                class:   'state',
                type:    'diskmon_drive_health',
                index:   $healthIndex,
                oid:     $oid,
                descr:   "Diskmon $diskId health",
                current: $this->mapDriveHealth($disk),
                group:   'Diskmon',
                navigation: 'tab=apps/app=diskmon/',
            // withStateTranslations() must be called immediately after
            // discoverSensor(). The first argument must match 'type' above.
            )->withStateTranslations('diskmon_drive_health', [
                StateTranslation::define('Healthy',  0, Severity::Ok),
                StateTranslation::define('Degraded', 1, Severity::Warning),
                StateTranslation::define('Failed',   2, Severity::Error),
                StateTranslation::define('Missing',  3, Severity::Error),
                StateTranslation::define('Unknown',  -1, Severity::Unknown),
            ]);

            $expectedOids[] = $oid;

            // Track which disks were discovered so poll() can iterate them
            // without re-parsing the payload schema.
            $this->discovery['disks'][$diskId] = [
                'path' => (string) ($disk['path'] ?? ''),
            ];
        }

        // syncSensors() flushes the buffer to the database.
        // Pass every sensor type this handler owns; each is scoped separately.
        $this->syncSensors('diskmon_drive_health');

        // deleteStaleAgentSensors() removes sensors that were previously
        // created by this handler but are no longer expected — for example,
        // a disk was removed from the agent's report.
        $this->deleteStaleAgentSensors(
            oidPrefix:    'app:diskmon:',
            knownTypes:   ['diskmon_drive_health'],
            expectedOids: $expectedOids,
        );

        // Persist discovery map and raw payload snapshot so poll() can
        // resolve sensor values without rediscovering structure each cycle.
        $data              = $this->appdata;
        $data['discovery'] = $this->discovery;
        $this->saveAppData($data);

        $this->discoveryCompleted = true;
    }


    // -------------------------------------------------------------------------
    // printDiscoverySummary()
    //
    // Called by Unix::discoverApplication() after discover() returns.
    // The base-class default just echoes a newline to end the dot-progress line.
    //
    // Override to print verbose output when Debug::isVerbose() is true.
    // The user enables verbose mode with the -v flag:
    //   ./lnms device:discover {id} -m applications -v
    //
    // Normal output:
    //   diskmon: .....
    //
    // Verbose output (-v):
    //   diskmon: .....
    //     Disks: 3
    //       sda  /dev/sda  healthy  errors:0
    //       sdb  /dev/sdb  healthy  errors:0
    //       sdc  /dev/sdc  degraded errors:12
    // -------------------------------------------------------------------------
    public function printDiscoverySummary(): void
    {
        echo PHP_EOL;

        if (! $this->discoveryCompleted || ! Debug::isVerbose()) {
            return;
        }

        $disks = $this->discovery['disks'] ?? [];
        echo '    Disks: ' . count($disks) . PHP_EOL;

        foreach ($this->disks as $diskId => $disk) {
            $path   = (string) ($disk['path'] ?? $diskId);
            $state  = (string) ($disk['state'] ?? '');
            $errors = (int)    ($disk['errors'] ?? 0);

            echo sprintf(
                '      %s  %s  %s  errors:%d' . PHP_EOL,
                $diskId, $path, $state, $errors
            );
        }
    }


    // -------------------------------------------------------------------------
    // poll()
    //
    // Runs every ~5 minutes. Should be fast:
    //   - No sensor schema work (that is discovery's job).
    //   - Read payload, map index → value, call updateSensorValues().
    //   - Write RRDs.
    //   - Persist raw snapshot to app->data for graphs and the DB table.
    //   - Call update_application() last.
    // -------------------------------------------------------------------------
    public function poll(): void
    {
        $payload = $this->fetchPayload('diskmon', 2);
        if ($payload === null) {
            return;
        }

        $this->initState($payload);

        // Build sensor value map: sensor_index => raw value.
        $sensorValues = [];
        foreach ($this->discovery['disks'] as $diskId => $_) {
            $disk = $this->disks[$diskId] ?? [];
            $sensorValues["{$diskId}_health"] = $this->mapDriveHealth($disk);
        }

        // updateSensorValues() writes sensor_current to the DB, emits RRD
        // for each sensor, applies divisor/multiplier, and logs threshold
        // crossings and state changes.
        $this->updateSensorValues($sensorValues, 'app:diskmon:');

        // putRrd() writes a custom multi-dataset RRD that is not tied to a
        // single sensor. The rrd_name list determines the file path:
        //   {rrd_dir}/{hostname}/app-diskmon-{app_id}-{diskId}.rrd
        foreach ($this->disks as $diskId => $disk) {
            $this->putRrd('app', [
                'name'     => 'diskmon',
                'app_id'   => $this->app->app_id,
                'rrd_def'  => RrdDefinition::make()
                    ->addDataset('errors', 'DERIVE', 0)
                    ->addDataset('reads',  'GAUGE',  0)
                    ->addDataset('writes', 'GAUGE',  0),
                'rrd_name' => ['app', 'diskmon', $this->app->app_id, $diskId],
            ], [
                'errors' => (int) ($disk['errors'] ?? 0),
                'reads'  => (int) ($disk['reads']  ?? 0),
                'writes' => (int) ($disk['writes'] ?? 0),
            ]);
        }

        // Persist to dedicated MySQL table (one row per drive).
        $this->pollDb();

        // update_application() writes app_state/app_status and flat metrics
        // to the applications and application_metrics tables.
        \update_application($this->app, 'ok', [
            'disk_count' => count($this->disks),
        ]);
    }


    // -------------------------------------------------------------------------
    // cleanup()
    //
    // Called when the application is removed from a device.
    // The base class deletes all poller_type='agent' sensors.
    // Override here to also remove our dedicated table rows.
    // -------------------------------------------------------------------------
    public function cleanup(): int
    {
        DiskmonDrive::where('app_id', $this->app->app_id)->delete();

        return parent::cleanup();
    }


    // -------------------------------------------------------------------------
    // Private helpers
    // -------------------------------------------------------------------------

    private function initState(array $payload): void
    {
        $this->payload  = $payload;
        $this->disks    = $payload['data']['disks'] ?? [];
        $this->appdata  = $this->getAppData();
        $this->discovery = $this->appdata['discovery'] ?? ['disks' => []];
    }

    private function mapDriveHealth(array $disk): int
    {
        $state = strtolower(trim((string) ($disk['state'] ?? '')));

        return match ($state) {
            'healthy'  => 0,
            'degraded' => 1,
            'failed'   => 2,
            'missing'  => 3,
            default    => -1,
        };
    }

    private function pollDb(): void
    {
        $deviceId = $this->os->getDeviceId();
        $appId    = $this->app->app_id;
        $seen     = [];

        foreach ($this->disks as $diskId => $disk) {
            DiskmonDrive::updateOrCreate(
                ['app_id' => $appId, 'disk_id' => (string) $diskId],
                [
                    'device_id' => $deviceId,
                    'path'      => (string) ($disk['path']   ?? ''),
                    'state'     => (string) ($disk['state']  ?? ''),
                    'errors'    => isset($disk['errors']) ? (int) $disk['errors'] : null,
                ]
            );
            $seen[] = (string) $diskId;
        }

        // Remove drives that have disappeared from the agent report.
        DiskmonDrive::where('app_id', $appId)
            ->whereNotIn('disk_id', $seen)
            ->delete();
    }
}
```

---

## Method reference

| Method | When to call | What it does |
|---|---|---|
| `fetchPayload(string $extend, int $minVersion)` | Top of `discover()` and `poll()` | Fetches JSON from SNMP extend or agent cache; returns `null` and sets app state to ERROR on failure |
| `getAppData()` | To read cross-cycle state | Returns `applications.data` cast to array |
| `saveAppData(array $data)` | End of `discover()` | Persists `$data` to `applications.data` |
| `discoverSensor(...)` | Inside `discover()` loop | Registers a sensor in the discovery buffer |
| `withStateTranslations(string $type, array $states)` | Immediately after `discoverSensor()` for state sensors | Attaches numeric↔label mapping |
| `syncSensors(string ...$types)` | After the discovery loop | Flushes buffer to DB; create/update/delete per type |
| `deleteStaleAgentSensors(...)` | After `syncSensors()` | Removes sensors whose OID is no longer expected |
| `updateSensorValues(array $values, string $oidPrefix)` | Inside `poll()` | Bulk-updates `sensor_current`, writes RRDs, fires threshold events |
| `putRrd(string $type, array $tags, array $fields)` | Inside `poll()` | Writes a multi-dataset RRD not tied to a single sensor |
| `logEvent(Severity\|string $level, string $message)` | Anywhere | Appends an entry to the device event log |
| `shouldDiscover(): bool` | Override | Gate: return `false` to skip discover entirely |
| `shouldPoll(): bool` | Override | Gate: return `false` to skip poll entirely; canonical use is to guard when `discovery` in `app->data` is empty |
| `printDiscoverySummary(): void` | Override | Called after `discover()` by the dispatcher; default emits a newline; override to print verbose output when `Debug::isVerbose()` |
| `cleanup(): int` | Override | Called when app is removed; base deletes agent sensors; extend to delete custom table rows |

---

## Discovery output

When running `./lnms device:discover {id} -m applications`, the dispatcher prints:

```
Applications:
  diskmon: .....
```

The dots come from `ModuleModelObserver` (sensor creates `+`, updates `U`, no-ops `.`).

With `-v`:

```
Applications:
  diskmon: .....
    Disks: 3
      sda  /dev/sda  healthy  errors:0
      sdb  /dev/sdb  healthy  errors:0
      sdc  /dev/sdc  degraded errors:12
```

The verbose block is produced by `printDiscoverySummary()`. The `discoveryCompleted` flag ensures the method is silent when `discover()` returned early (payload unavailable, version check failed, etc.).

---

## shouldPoll() guard pattern

Without the guard, a poll cycle that runs before discovery has completed will find no sensors and silently do nothing — but still consume a poll slot. With the guard:

```php
public function shouldPoll(): bool
{
    return ! empty($this->getAppData()['discovery']['disks'] ?? []);
}
```

The first poll after a fresh device add is skipped cleanly. Once discovery writes the map to `app->data`, every subsequent poll proceeds normally.

A version-gated variant is useful when the payload schema changes:

```php
public function shouldPoll(): bool
{
    $data = $this->getAppData();

    return ($data['schema_version'] ?? 0) >= 2
        && ! empty($data['discovery']['disks'] ?? []);
}
```
