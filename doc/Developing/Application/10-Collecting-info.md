# 1. Collecting Info

## Introduction

This chapter covers application-side data collection patterns for discovery and polling in LibreNMS. Application pollers receive data via the LibreNMS agent or JSON app endpoints.

The recommended approach is to extend `LibreNMS\Agent\Application`, which provides consistent patterns for sensor discovery, polling, RRD writes, and event logging.

## Application discovery and polling with sensor paths

This guide describes a generic pattern for application modules that discover sensors and then poll current values from structured payload data.

Using this pattern, discovery carries most structure-building complexity, so the 5-minute poll path can stay small and focused instead of repeatedly running full discovery-style code.

It focuses on three concerns:

- how to separate discovery from poll updates
- how to store sensor identity (`sensor_index` / `sensor_oid`)
- how to store and use payload paths for current values

The concepts here apply whether you use the `LibreNMS\Agent\Application` base class (recommended - see [Creating App-Based Sensors](11-App-Based-Sensors.md)) or write procedural include files directly. The mdadm handler (`LibreNMS/Agent/Unix/Mdadm/Common.php`) is the reference implementation of this pattern using the base class.

!!! note
    This pattern is for agent/JSON application payloads, not normal SNMP table discovery.

## Why not normal SNMP discovery

This pattern is needed for application data collected from agent/JSON payloads, not SNMP tables.

- SNMP discovery expects OID-based walks and indexes from MIB-backed data
- application payloads are nested JSON structures, so values are resolved by JSON path, not by SNMP OID walk logic
- these sensors are typically created as app sensors (for example `sensor_type` `app`) and updated by the application poller path

In short, standard SNMP sensor discovery/polling patterns are optimized for SNMP sources, while application modules need a path-based mapping from discovered sensor index to payload location.

## Overview

A robust application poller usually has two phases:

1. Discovery phase
2. Poll/update phase

### Discovery phase

Discovery decides which sensors exist and ensures they are present in LibreNMS.

Typical outputs from discovery:

- sensor records in the database
- persistent app metadata in `Application->data['Discovery']`

### Poll/update phase

Poll/update reads current values from the latest payload and writes values for existing sensors.

Poll should not need to rediscover structure every cycle.

## Recommended storage model

Store sensor identity and payload location separately.

- Identity: `sensor_index` and `sensor_oid`
- Location: payload path string

A practical structure is:

```text
Discovery.<entity_collection>.<entity_id>.sensors
  <sensor_index> => <payload_path_relative_to_entity>
```

Where `<entity_collection>` depends on the app shape (for example `arrays` for mdadm, `ups_list` for UPS-style payloads).

Examples by app shape:

```text
md0_health                      => array.state
md0_operation                   => array.sync.action
md0_mismatch                    => array.mismatch_cnt
md0_sda_health                  => devices.sda.state

ups1_output_L1_voltage          => output.L1-N.voltage
ups1_output_L2_voltage          => output.L2-N.voltage
ups1_battery_runtime            => battery.runtime
ups2_input_bypass_voltage       => input.bypass.voltage
```

This format gives fast lookup in poll:

- key (`sensor_index`) matches DB sensor row
- value (`payload path`) resolves current value in payload

## `sensor_oid` and payload path are different

Use each for its own purpose:

- `sensor_oid` (for example `app:mdadm:<sensor_index>` or `app:nut:<sensor_index>`) identifies the LibreNMS sensor
- payload path (`array.sync.action`, `output.L1-N.voltage`) identifies where to read the live value

Do not use `sensor_oid` as the payload path.

## Generic poll flow

1. Load sensors for this entity from DB and key by `sensor_index`
2. Loop discovery map: `<index> => <path>`
3. Match DB sensor by index
4. Resolve value from payload using path
5. Apply per-class conversion if needed (for example mdadm state strings to state values, or UPS runtime seconds to minutes)
6. Write sensor and RRD update

When value is missing/invalid, writing `null` is valid (RRD stores unknown `U`).

## State sensors

State sensors are often easier to maintain with a dedicated mapping and a separate update loop.

Why separate state loop can be useful:

- explicit state key mapping
- state translation handling remains isolated
- avoids mixing numeric and state-specific rules

## Migration and cleanup recommendations

When evolving schema, normalize old data on load:

- remove legacy keys no longer used
- keep one canonical location for stale counters
- convert older sensor map/list formats into one current format

This avoids poll-time branching and keeps logic predictable.

## Path handling notes

- Store paths relative to the entity root (`payload['data'][<entity_id>]`), such as `array.state`
- For nested sections, store full nested path, such as `output.L1-N.voltage` or `input.bypass.voltage`
- Use one helper for path resolution (`a.b.c` traversal)

## Minimal write requirements

For `app('Datastore')->put($device, 'sensor', $tags, $fields)`:

- minimum practical tags for sensor RRD writes:
  - `rrd_name`
  - `rrd_def`
- common useful tags:
  - `sensor_class`, `sensor_type`, `sensor_descr`, `sensor_index`
- fields usually include:
  - `['sensor' => $value]`

## Related Documentation

- [1.1 Creating App-Based Sensors](11-App-Based-Sensors.md)
- [Sensor State Support](../Sensor-State-Support.md) - State sensors and translations
- [RRD](../RRD.md) - RRD graph creation
- [Application Notes](../Application-Notes.md) - App poller patterns
