---
title: 1.1 Creating App-Based Sensors
description: How to create sensors from application payloads by extending LibreNMS\Agent\Application.
tags:
  - developing
  - applications
  - sensors
---

# 1.1 Creating App-Based Sensors

This chapter shows the recommended way to create sensors from application payloads: extend `LibreNMS\Agent\Application`.

Use this pattern for new JSON or agent-based applications.

## Minimal handler

```php
<?php

namespace LibreNMS\Agent\Unix\MyApp;

use App\Models\StateTranslation;
use LibreNMS\Agent\Application;
use LibreNMS\Enum\Severity;

class Common extends Application
{
    public function discover(): void
    {
        $payload = $this->fetchPayload('myapp', 1);
        if ($payload === null) {
            return;
        }

        app()->forgetInstance('sensor-discovery');

        $expectedOids = [];

        foreach ($payload['data']['instances'] ?? [] as $id => $instance) {
            $statusIndex = "{$id}_status";
            $loadIndex = "{$id}_load";

            $this->discoverSensor(
                class: 'state',
                type: 'myapp_online_status',
                index: $statusIndex,
                oid: "app:myapp:{$statusIndex}",
                descr: "MyApp {$id} status",
                current: (int) ($instance['online'] ?? 0),
                navigation: 'tab=apps/app=myapp/',
            )->withStateTranslations('myapp_online_status', [
                StateTranslation::define('Online', 0, Severity::Ok),
                StateTranslation::define('Offline', 1, Severity::Error),
            ]);

            $this->discoverSensor(
                class: 'load',
                type: 'app',
                index: $loadIndex,
                oid: "app:myapp:{$loadIndex}",
                descr: "MyApp {$id} load",
                current: $instance['load'] ?? null,
                navigation: 'tab=apps/app=myapp/',
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
    }

    public function poll(): void
    {
        $payload = $this->fetchPayload('myapp', 1);
        if ($payload === null) {
            return;
        }

        $values = [];
        $totalLoad = 0;

        foreach ($payload['data']['instances'] ?? [] as $id => $instance) {
            $load = $instance['load'] ?? null;

            $values["{$id}_status"] = (int) ($instance['online'] ?? 0);
            $values["{$id}_load"] = $load;

            if (is_numeric($load)) {
                $totalLoad += (float) $load;
            }
        }

        $this->updateSensorValues($values, 'app:myapp:');

        update_application($this->app, 'ok', [
            'instances' => count($payload['data']['instances'] ?? []),
            'total_load' => $totalLoad,
        ]);
    }
}
```

## Registration

Register the handler in `resources/definitions/agent/unix.yaml`:

```yaml
myapp:
  handler: LibreNMS\Agent\Unix\MyApp\Common
```

The Unix dispatcher reads this definition and instantiates the handler.

## `discover()`

`discover()` runs periodically and should handle structure:

- fetch payload
- create sensors
- define state translations
- sync sensors to the database
- remove stale sensors
- save discovery metadata to `$app->data` when needed

Call `app()->forgetInstance('sensor-discovery')` before discovering sensors so buffered sensors from another handler do not bleed into this one.

## `poll()`

`poll()` runs every 5 minutes and should be small:

- fetch payload
- resolve current values
- update sensors
- write RRDs if needed
- call `update_application()`

Do not repeat full discovery logic in `poll()`.

## `discoverSensor()`

`discoverSensor()` wraps LibreNMS sensor discovery with named parameters.

| Parameter | Required | Description |
| --- | --- | --- |
| `class` | yes | Sensor class: `load`, `voltage`, `state`, `count`, etc. |
| `type` | yes | `'app'` for numeric app sensors, unique type for state sensors |
| `index` | yes | Stable `sensor_index` on this device |
| `oid` | yes | Stable identity, usually `app:<appname>:<index>` |
| `descr` | yes | Human-readable label |
| `current` | no | Current value at discovery time |
| `group` | no | Shared heading/group in the UI |
| `navigation` | no | URL fragment for sensor links |
| `lowLimit` | no | Low critical threshold |
| `lowWarnLimit` | no | Low warning threshold |
| `warnLimit` | no | High warning threshold |
| `highLimit` | no | High critical threshold |
| `divisor` | no | Divide raw values before storage |
| `multiplier` | no | Multiply raw values before storage |

## `withStateTranslations()`

Call immediately after `discoverSensor()` for state sensors. The first argument must match the sensor `type`.

```php
$this->discoverSensor(
    class: 'state',
    type: 'myapp_status',
    index: 'daemon_status',
    oid: 'app:myapp:daemon_status',
    descr: 'MyApp daemon status',
    current: 0,
)->withStateTranslations('myapp_status', [
    StateTranslation::define('Running', 0, Severity::Ok),
    StateTranslation::define('Stopped', 1, Severity::Error),
    StateTranslation::define('Unknown', 2, Severity::Unknown),
]);
```

## `syncSensors()`

Flushes discovered sensors to the database. Pass every sensor type owned by this handler.

```php
$this->syncSensors('myapp_status', 'app');
```

Numeric app sensors normally use type `'app'`. State sensors should use an app-specific type such as `myapp_status`.

## `deleteStaleAgentSensors()`

Removes sensors created by this handler that are no longer expected.

```php
$this->deleteStaleAgentSensors(
    oidPrefix: 'app:myapp:',
    knownTypes: ['myapp_status', 'app'],
    expectedOids: $expectedOids,
);
```

Call it after `syncSensors()`.

## `updateSensorValues()`

Bulk-updates current sensor values and writes sensor RRDs.

```php
$this->updateSensorValues([
    'daemon_status' => 0,
    'worker_load' => 42,
], 'app:myapp:');
```

Map keys are `sensor_index` values. Values should be raw readings before divisor/multiplier scaling.

Use `null` for missing or invalid values unless the app deliberately maps them to a numeric state.

## `putRrd()`

Use `putRrd()` for app-level RRDs that do not correspond to one sensor.

```php
use LibreNMS\RRD\RrdDefinition;

$this->putRrd('app', [
    'name' => 'myapp',
    'app_id' => $this->app->app_id,
    'rrd_name' => ['app', 'myapp', $this->app->app_id, 'traffic'],
    'rrd_def' => RrdDefinition::make()
        ->addDataset('read_bps', 'GAUGE', 0)
        ->addDataset('write_bps', 'GAUGE', 0),
], [
    'read_bps' => $readBps,
    'write_bps' => $writeBps,
]);
```

## Optional immediate poll after discovery

Some apps need a first poll immediately after discovery to create RRDs or populate first values. If so, call `$this->poll()` at the end of `discover()`.

Do not include this by default. Use it only when the app actually needs immediate initialization.

## Practical checklist

- Use stable sensor indexes.
- Prefix app sensor OIDs with `app:<appname>:`.
- Keep discovery and polling separate.
- Store dynamic discovery maps in `$app->data['discovery']`.
- Use `update_application()` for app status and summary metrics.
- Remove stale sensors explicitly.
- Keep `poll()` fast.
