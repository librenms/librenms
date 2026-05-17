# 1.1 Creating App-Based Sensors

## Introduction

This chapter describes how to create sensors for application-based pollers in LibreNMS. Application pollers receive data via the LibreNMS agent or JSON app endpoints.

The recommended approach is to extend `LibreNMS\Agent\Application`, which provides consistent patterns for sensor discovery, polling, RRD writes, and event logging. A manual approach using the sensor discovery singleton directly is documented in the source reference chapter when needed.

## The Application Base Class

Create a class that extends `LibreNMS\Agent\Application`. Implement at minimum `poll()`. Override `discover()` for apps that create sensors.

```php
namespace LibreNMS\Agent\Unix\MyApp;

use LibreNMS\Agent\Application;

class MyApp extends Application
{
    public function discover(): void { ... }
    public function poll(): void { ... }
}
```

!!! note
    Keep discovery indexes aligned with your payload shape. For mdadm this is usually array and device oriented (`md0_health`, `md0_sda_health`), while UPS-style payloads often use entity and phase keys (`ups1_output_L1_voltage`, `ups2_battery_runtime`).

### Registration

Register your handler in `resources/definitions/agent/unix.yaml`:

```yaml
myapp:
  handler: LibreNMS\Agent\Unix\MyApp\MyApp
```

The dispatcher in `LibreNMS/OS/Shared/Unix.php` reads this file at runtime and instantiates the handler.

### discover()

Runs approximately 4 times per day. Responsible for creating and syncing sensors and writing discovery metadata to `$app->data`. Reset the sensor-discovery singleton before calling `discoverSensor()`:

```php
public function discover(): void
{
    $payload = $this->fetchPayload('myapp', 1);
    if ($payload === null) {
        return;
    }

    app()->forgetInstance('sensor-discovery');

    $expectedOids = [];
    foreach ($payload['data']['instances'] as $id => $instance) {
        $statusIndex = "{$id}_status";
        $loadIndex   = "{$id}_load";

        $this->discoverSensor(
            class: 'state',
            type: 'myapp_online_status',
            index: $statusIndex,
            oid: "app:myapp:{$statusIndex}",
            descr: "MyApp $id status",
            current: (int) $instance['online'],
        )->withStateTranslations('myapp_online_status', [
            StateTranslation::define('Online',  0, Severity::Ok),
            StateTranslation::define('Offline', 1, Severity::Error),
        ]);

        $this->discoverSensor(
            class: 'load',
            type: 'app',
            index: $loadIndex,
            oid: "app:myapp:{$loadIndex}",
            descr: "MyApp $id load",
            current: $instance['load'],
        );

        $expectedOids[] = "app:myapp:{$statusIndex}";
        $expectedOids[] = "app:myapp:{$loadIndex}";
    }

    $this->syncSensors('myapp_online_status', 'app');
    $this->deleteStaleAgentSensors(
        oidPrefix: 'app:myapp:',
        knownTypes: ['myapp_online_status', 'app'],
        expectedOids: $expectedOids,
    );

    // Optional: if your app has dynamic RRD datastore needs,
    // you can perform an immediate poll pass after discovery to
    // populate first values and create related datasets right away.
    // Keep this optional and use only when immediate initialization
    // is required for your app behavior.
    $this->poll();
}
```

### poll()

Runs every 5 minutes. Update sensor values and write app metrics only - do not re-run discovery logic:

```php
public function poll(): void
{
    $payload = $this->fetchPayload('myapp', 1);
    if ($payload === null) {
        return;
    }

    $values = [];
    foreach ($payload['data']['instances'] as $id => $instance) {
        $values["{$id}_status"] = (int) $instance['online'];
        $values["{$id}_load"]   = $instance['load'];
    }

    $this->updateSensorValues($values, 'app:myapp:');
    update_application($this->app, 'ok', ['total_load' => array_sum(array_column($payload['data']['instances'], 'load'))]);
}
```

### discoverSensor()

Named-parameter wrapper around the sensor-discovery singleton. Parameters mirror `discover_sensor()` in the legacy includes:

| Parameter | Required | Description |
|-----------|----------|-------------|
| `class` | yes | Sensor class: `load`, `voltage`, `state`, `count`, etc. |
| `type` | yes | `'app'` for numeric; unique state name for state sensors |
| `index` | yes | Unique sensor index on this device |
| `oid` | yes | Stable identity: `app:<appname>:<index>` |
| `descr` | yes | Human-readable description shown in the UI |
| `current` | no | Current value at discovery time |
| `group` | no | Groups sensors under a shared heading |
| `navigation` | no | URL fragment for the sensor link (e.g. `tab=apps/app=myapp/`) |
| `lowLimit` | no | Low critical threshold |
| `lowWarnLimit` | no | Low warning threshold |
| `warnLimit` | no | High warning threshold |
| `highLimit` | no | High critical threshold |
| `divisor` | no | Divide raw value before storing (default 1) |
| `multiplier` | no | Multiply raw value before storing (default 1) |

Returns `$this`, allowing fluent chaining with `->withStateTranslations()`.

### withStateTranslations()

Chain immediately after `discoverSensor()` when `sensor_class` is `state`. The first argument must match the `type` passed to `discoverSensor()`:

```php
$this->discoverSensor(
    class: 'state',
    type: 'myapp_online_status',
    ...
)->withStateTranslations('myapp_online_status', [
    StateTranslation::define('Online',  0, Severity::Ok),
    StateTranslation::define('Offline', 1, Severity::Error),
]);
```

`StateTranslation::define(string $descr, int $value, Severity $severity)` maps:

| Severity | `state_generic_value` |
|---|---|
| `Ok` | 0 |
| `Warning` | 1 |
| `Error` | 2 |
| `Unknown` | 3 |

### syncSensors()

Syncs the discovery buffer to the database - creating new sensors, updating existing ones, and deleting sensors removed from the buffer. Pass one or more type strings; each type is synced in isolation:

```php
$this->syncSensors('myapp_online_status', 'app');
```

Numeric sensors use `'app'` as the type. State sensors use their unique state-type name. Each state type requires its own sync scope so cleanup does not affect other apps.

### deleteStaleAgentSensors()

Removes sensors whose OID starts with `$oidPrefix` but whose OID is no longer in `$expectedOids`, or whose type is not in `$knownTypes`. Call after `syncSensors()`:

```php
$this->deleteStaleAgentSensors(
    oidPrefix: 'app:myapp:',
    knownTypes: ['myapp_online_status', 'app'],
    expectedOids: $expectedOids,  // built during the discovery loop
);
```

This cleans up sensors for instances that have been removed from the agent.

### updateSensorValues()

Bulk-updates `sensor_current`, writes RRD, applies divisor/multiplier scaling from the sensor row, and generates eventlog entries for threshold crossings and state changes:

```php
$this->updateSensorValues([
    'myinstance_status' => 0,
    'myinstance_load'   => 42,
], 'app:myapp:');
```

The map key is `sensor_index`, the value is the raw reading before scaling. Values of `NaN` or `-32768` are treated as `0`.

### putRrd()

Write to an RRD that does not correspond to a single sensor (for example, multi-dataset application graphs):

```php
use LibreNMS\RRD\RrdDefinition;

$this->putRrd('app', [
    'name'     => 'myapp',
    'app_id'   => $this->app->app_id,
    'rrd_def'  => RrdDefinition::make()
        ->addDataset('read_bps', 'GAUGE', 0)
        ->addDataset('write_bps', 'GAUGE', 0),
    'rrd_name' => ['app', 'myapp', $this->app->app_id, 'traffic'],
], [
    'read_bps'  => $payload['read_bytes'],
    'write_bps' => $payload['write_bytes'],
]);
```

### fetchPayload()

Fetches JSON from the SNMP extend, falling back to the unix-agent data cache. Returns `null` and sets the app state to `ERROR` on unrecoverable failure:

```php
$payload = $this->fetchPayload('myapp', 1);
if ($payload === null) {
    return;
}
```

The second argument is the minimum supported version of the agent script. The payload is returned as an array.

### shouldDiscover() and shouldPoll()

Override these to add pre-conditions. Returning `false` skips the phase entirely:

```php
public function shouldDiscover(): bool
{
    // Only run discovery when payload version 3+ is present
    return ($this->getAppData()['version'] ?? 0) >= 3;
}
```

### cleanup()

Called when an application is removed from a device. The base implementation deletes all `poller_type='agent'` sensors for the device. Override if your app writes to other tables:

```php
public function cleanup(): int
{
    // custom cleanup if needed
    return parent::cleanup();
}
```
