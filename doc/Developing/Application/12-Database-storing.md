---
title: 1.2 Database Storing
description: How update_application() stores application health, metrics, and status in LibreNMS.
tags:
  - developing
  - applications
  - database
---

# 1.2 Database Storing: `update_application()` Metrics and Status

`update_application()` stores application health and app-level metric values.

It writes to:

| Table | Purpose |
| --- | --- |
| `applications` | app state, status, and data |
| `application_metrics` | numeric app metrics, one row per key |

```php
update_application($app, $response, $metrics = [], $status = '')
```

| Parameter | Type | Description |
| --- | --- | --- |
| `$app` | `Application` | Application model |
| `$response` | `string` | Controls `app_state` |
| `$metrics` | `array` | Numeric metrics to store |
| `$status` | `string` | Free-form status usable in alert rules |

## When to use app metrics vs sensors

Use sensors when users need:

- threshold UI
- Health tab visibility
- per-sensor graphs
- sensor alerting semantics

Use `update_application()` metrics when the value is:

- app-level summary data
- useful for alerting but not useful as a sensor row
- shown in an app page or overview panel
- too noisy for the default sensor UI

## Response strings

| `$response` value | Stored `app_state` |
| --- | --- |
| `'ok'` or other non-error string | `OK` |
| starts with `'ERROR'` | `ERROR` |
| starts with `'LEGACY'` | `LEGACY` |
| starts with `'UNSUPPORTED'` | `UNSUPPORTED` |
| `''` or `false` | `UNKNOWN` |

A state change from the previous poll creates an Eventlog entry.

Use lowercase `'ok'` in new code for consistency.

## Status only

```php
update_application($app, 'ok');
```

## Flat metrics

```php
update_application($app, 'ok', [
    'arrays' => 3,
    'arrays_syncing' => 1,
    'degraded_arrays' => 1,
    'devices_total' => 8,
]);
```

Stored as:

```text
arrays=3
arrays_syncing=1
degraded_arrays=1
devices_total=8
```

## Grouped metrics

Two-level arrays are flattened as `{group}_{key}`.

```php
update_application($app, 'ok', [
    'md0' => [
        'active_devices' => 2,
        'failed_devices' => 0,
        'sync_completed_pct' => 100.0,
    ],
    'md1' => [
        'active_devices' => 3,
        'failed_devices' => 1,
        'sync_completed_pct' => 42.7,
    ],
]);
```

Stored as:

```text
md0_active_devices=2
md0_failed_devices=0
md0_sync_completed_pct=100.0
md1_active_devices=3
md1_failed_devices=1
md1_sync_completed_pct=42.7
```

## Special group `none`

The group key `none` is not prefixed.

```php
update_application($app, 'ok', [
    'none' => [
        'ups_count' => 3,
    ],
    'ups1' => [
        'battery_runtime' => 3600,
    ],
]);
```

Stored as:

```text
ups_count=3
ups1_battery_runtime=3600
```

## Error state with status

```php
update_application($app, 'ERROR: could not connect to daemon', [], 'unreachable');
```

Result:

```text
app_state  = ERROR
app_status = unreachable
```

## Metric lifecycle

| Condition | Result |
| --- | --- |
| New metric key | inserted |
| Existing key with changed value | updated; previous value stored |
| Existing key with same value | unchanged |
| Existing DB key missing from this poll | deleted |

Only pass metrics that are still valid. Missing keys are treated as removed.
