# 1.2 Database Storing: update_application() Metrics and Status

## Introduction

This chapter documents how `update_application()` stores application health and metric values in the database for application pollers.

`update_application()` stores the application's health state and any numeric metrics you want tracked without appearing in the sensor UI. It writes to `applications` (`app_state`, `app_status`) and `application_metrics` (one row per metric).

```php
update_application($app, $response, $metrics = [], $status = '')
```

| Parameter | Type | Description |
|---|---|---|
| `$app` | `Application` | The app model from the DB |
| `$response` | `string` | Controls `app_state` - see states below |
| `$metrics` | `array` | Numeric metrics to store (flat or grouped) |
| `$status` | `string` | Free-form status string for alerting rules |

!!! tip
    Use `update_application()` for app-level status and metrics that should not be individual device sensors.

### Response strings and resulting app_state

| `$response` value | `app_state` set |
|---|---|
| `'ok'` / any non-error string | `OK` |
| Starts with `'ERROR'` | `ERROR` |
| Starts with `'LEGACY'` | `LEGACY` |
| Starts with `'UNSUPPORTED'` | `UNSUPPORTED` |
| `''` / `false` | `UNKNOWN` |

A state change from the previous poll is logged as an Eventlog entry automatically.

### Variant 1 - Status only, no metrics

```php
update_application($app, 'ok');
```

### Variant 2 - Flat metrics

Keys become metric names directly in `application_metrics`:

```php
update_application($app, 'ok', [
    'arrays'          => 3,
    'arrays_syncing'  => 1,
    'degraded_arrays' => 1,
    'devices_total'   => 8,
]);
// stored as: arrays=3, arrays_syncing=1, degraded_arrays=1, devices_total=8
```

### Variant 3 - Grouped metrics

Two-level arrays are flattened with the group name prepended as `{group}_{key}`:

```php
update_application($app, 'ok', [
    'md0' => [
        'active_devices'     => 2,
        'spare_devices'      => 1,
        'failed_devices'     => 0,
        'working_devices'    => 3,
        'sync_completed_pct' => 100.0,
    ],
    'md1' => [
        'active_devices'     => 3,
        'spare_devices'      => 0,
        'failed_devices'     => 1,
        'working_devices'    => 2,
        'sync_completed_pct' => 42.7,
    ],
]);
// stored as: md0_active_devices=2, md0_spare_devices=1, md0_failed_devices=0,
//            md0_working_devices=3, md0_sync_completed_pct=100.0, ...
```

### Variant 4 - Special group 'none' (no prefix)

The group key `'none'` is the exception: its metrics are stored without any prefix:

```php
update_application($app, 'ok', [
    'none' => [
        'ups_count' => 3,
    ],
    'ups1' => [
        'output_L1_voltage' => 230.1,
        'output_L2_voltage' => 229.8,
        'battery_runtime'   => 3600,
    ],
]);
// stored as: ups_count=3, ups1_output_L1_voltage=230.1,
//            ups1_output_L2_voltage=229.8, ups1_battery_runtime=3600
```

### Variant 5 - Error state with status message

```php
update_application($app, 'ERROR: could not connect to daemon', [], 'unreachable');
// app_state  = ERROR
// app_status = 'unreachable'  (used in alert rules as %app_status%)
```

### Metric lifecycle

- **New metric** key -> inserted (`+` echoed)
- **Changed value** -> updated, previous value saved to `value_prev` (`U` echoed)
- **Unchanged value** -> no-op (`.` echoed)
- **Key missing** from this call but present in DB -> deleted (`-` echoed)

## Notes

Use this for app-level metrics that belong in application status/graphs rather than in the sensor UI. For per-sensor behavior and discovery patterns, use the app sensor chapters.
