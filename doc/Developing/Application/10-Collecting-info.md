---
title: 1. Collecting Info
description: How application pollers collect and map data from agent or JSON payloads.
tags:
  - developing
  - applications
---

# 1. Collecting Info

This chapter explains how application pollers should collect and map data from agent or JSON payloads.

Application payloads are not normal SNMP tables. They are often nested JSON structures where values are addressed by object paths, not by walked OID indexes. The recommended approach is therefore to discover structure separately from polling current values.

## Core rule

Discovery builds structure. Polling updates values.

```text
Discovery (~4x/day)            Polling (~5 min)
-------------------            ----------------
find entities                  fetch latest payload
create sensors                 resolve known values
store discovery map            update sensors/RRD/app metrics
remove stale sensors           avoid schema work
```

## Why normal SNMP discovery is not enough

SNMP discovery expects MIB-backed OIDs and indexes. Application payloads usually look more like this:

```json
{
  "data": {
    "arrays": {
      "md0": {
        "state": "clean",
        "devices": {
          "sda": { "state": "active" }
        }
      }
    }
  }
}
```

The sensor identity in LibreNMS must be stable, but the live value is read from a JSON path.

## Store identity and payload location separately

Use two different concepts:

| Concept | Example | Purpose |
| --- | --- | --- |
| Sensor OID | `app:mdadm:md0_sda_health` | Stable LibreNMS sensor identity |
| Payload path | `devices.sda.state` | Location of the current value in the payload |

Do not use `sensor_oid` as the payload path.

Bad:

```text
sensor_oid = devices.sda.state
```

Good:

```text
sensor_oid  = app:mdadm:md0_sda_health
value path  = devices.sda.state
```

## Recommended discovery map

Store the discovery map in `$app->data['discovery']`:

```text
discovery.<entity_collection>.<entity_id>.sensors
  <sensor_index> => <payload_path_relative_to_entity>
```

Example:

```text
discovery.arrays.md0.sensors
  md0_health      => state
  md0_operation   => sync.action
  md0_sda_health  => devices.sda.state
```

For UPS-style payloads:

```text
discovery.ups.ups1.sensors
  ups1_output_L1_voltage  => output.L1-N.voltage
  ups1_battery_runtime    => battery.runtime
```

## Generic discovery flow

1. Fetch payload.
2. Iterate entities in the payload.
3. Build stable sensor indexes.
4. Create sensors with `discoverSensor()`.
5. Build `$expectedOids`.
6. Save `$app->data['discovery']`.
7. Call `syncSensors()`.
8. Call `deleteStaleAgentSensors()`.

## Generic poll flow

1. Fetch payload.
2. Load `$app->data['discovery']`.
3. Iterate known sensor indexes and payload paths.
4. Resolve current values from the payload.
5. Convert state strings, units, or sentinel values.
6. Call `updateSensorValues()`.
7. Write app RRDs if needed.
8. Call `update_application()`.

When a value is missing or invalid, prefer `null` so RRD stores unknown (`U`) unless the app has a deliberate reason to coerce it.

## State sensors

State sensors are often easier to maintain with a separate mapping function:

```php
private function mapHealthState(string $state): int
{
    return match ($state) {
        'ok', 'clean', 'active' => 0,
        'degraded', 'syncing' => 1,
        'failed', 'missing' => 2,
        default => 3,
    };
}
```

Define the state translations during discovery and update only integer state values during polling.

## When a discovery map is unnecessary

A stored path map is useful for dynamic nested payloads. It may be overkill when:

- the payload has a fixed schema
- the app has only one instance
- sensor indexes can be built directly and safely in `poll()`

Even then, keep the split between discovery and polling clear.
