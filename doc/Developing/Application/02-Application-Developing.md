---
title: Developing Applications
description: Developer guide for creating LibreNMS applications using agent or JSON payloads.
tags:
  - developing
  - applications
  - snmp
---

# Developing Applications

This page is the entry point for LibreNMS application development. It explains which development path to use and links to the deeper chapters.

## Recommended path

New applications should use `LibreNMS\Agent\Application`.

```text
HOST SIDE                         LIBRENMS SIDE
+----------------+                +-----------------------------------+
| extend script  |  JSON payload  | Application handler               |
| or agent data  +--------------->| LibreNMS\Agent\Application       |
+----------------+                +----------------+------------------+
                                                   |
                         +-------------------------+-------------------------+
                         |                         |                         |
                         v                         v                         v
                 discovery/sensors          poll/app metrics              RRD/UI
```

Use the base class because it keeps the important responsibilities in one place:

- fetch and validate JSON payloads
- create and sync app-based sensors during discovery
- update current sensor values during polling
- write RRD data
- store app-level status and metrics
- save app metadata in `$app->data`

## When to use each chapter

| Need | Chapter |
| --- | --- |
| Understand why app payloads are different from SNMP tables | `10-Collecting-info.md` |
| Create sensors from application payload data | `11-App-Based-Sensors.md` |
| Store app-level status and numeric metrics | `12-Database-storing.md` |
| See a complete annotated handler | `13-Common-Functions-Example.md` |
| Keep values out of the default sensor overview | `14-Hiding-sensors.md` |
| Add a custom table or change RRD layout | `15-Migrations.md` |
| Add UI pages, tables, graph selectors, or tabs | `21-App-Pages.md` |
| Add a compact Device Overview panel | `23-Device-Overview-Panel.md` |
| Write host-side SNMP extend scripts | `30-Extension-Developing.md` |

## Minimal file set for a new app

A small app normally needs only these files:

| File | Purpose |
| --- | --- |
| `LibreNMS/Agent/Unix/{AppName}/Common.php` | Application handler |
| `resources/definitions/agent/unix.yaml` | Handler registration |
| `doc/Extensions/Applications/{AppName}.md` | User-facing app documentation |

Add these only when required:

| File | Add when |
| --- | --- |
| `includes/html/graphs/application/{app}_{metric}.inc.php` | The app writes RRDs and needs graphs |
| `includes/html/pages/device/apps/{app}.inc.php` | The app needs a Device Apps tab page |
| `includes/html/pages/device/overview/apps/{app}.inc.php` | The app needs a Device Overview panel |
| `resources/views/device/overview/apps/{app}.blade.php` | The overview panel renders Blade HTML |
| `database/migrations/...php` | The app needs a dedicated relational table |

## Naming conventions

| Item | Rule | Example |
| --- | --- | --- |
| App type | lowercase, usually kebab-case | `powerdns-recursor` |
| Handler namespace | StudlyCaps directory/class | `LibreNMS\Agent\Unix\MyApp\Common` |
| Registration key | matches app type | `myapp:` |
| Sensor OID prefix | `app:<appname>:` | `app:myapp:disk0_health` |
| Numeric sensor type | usually `app` | `type: 'app'` |
| State sensor type | app-specific and unique | `myapp_drive_health` |
| RRD name | include app id | `['app', 'myapp', $app->app_id, 'traffic']` |
| Graph file | `{app}_{metric}.inc.php` | `myapp_traffic.inc.php` |

## Data storage decision guide

| Requirement | Use |
| --- | --- |
| Current health/status visible on the app | `update_application()` |
| Numeric summary values not shown as sensors | `application_metrics` via `update_application()` |
| Thresholds, health page, and sensor alerting | app-based sensors |
| Time-series graph data | RRD |
| Cross-poll metadata or discovery map | `$app->data` |
| Queryable relational data | custom table + migration |

Do not create a migration just to store ordinary app status, metric values, or simple discovery metadata.

## JSON payload contract

Host-side JSON applications should return a stable object with this shape:

```json
{
  "version": 1,
  "error": 0,
  "errorString": "success",
  "data": {}
}
```

| Key | Required | Meaning |
| --- | --- | --- |
| `version` | yes | Payload schema version. Increment when the format changes. |
| `error` | yes | `0` for success, non-zero for failure. |
| `errorString` | yes | Human-readable result or error message. |
| `data` | yes | Application-specific payload. |

`fetchPayload()`/`json_app_get()` can decode optimized payloads generated.

## Discovery vs polling

Discovery should build structure. Polling should update values.

Discovery normally does this:

1. Fetch payload.
2. Create or update sensors.
3. Store a discovery map in `$app->data['discovery']`.
4. Remove stale sensors.

Polling normally does this:

1. Fetch payload.
2. Resolve current values using known indexes or stored paths.
3. Update sensors.
4. Write RRD data.
5. Call `update_application()`.

Do not rediscover the whole payload structure every 5 minutes unless the app is extremely small and static.

## Legacy procedural application pollers

Existing applications may still use `includes/polling/applications/{name}.inc.php` directly. Use that pattern when maintaining old code, but do not use it as the first choice for new JSON/agent apps.

For legacy pollers:

| File | Purpose |
| --- | --- |
| `includes/polling/applications/{name}.inc.php` | Procedural polling logic |
| `includes/html/graphs/application/{name}_{metric}.inc.php` | Graph definitions |
| `includes/html/pages/device/apps/{name}.inc.php` | Optional app page |

A legacy polling file commonly starts with:

```php
<?php

use LibreNMS\Exceptions\JsonAppException;

$name = 'myapp';
$min_version = 1;

try {
    $payload = json_app_get($device, $name, $min_version);
    $data = $payload['data'];
} catch (JsonAppException $e) {
    echo PHP_EOL . $name . ':' . $e->getCode() . ':' . $e->getMessage() . PHP_EOL;
    update_application($app, 'ERROR: ' . $e->getMessage(), [], (string) $e->getCode());
    return;
}
```

Prefer migrating substantial new work to an `Application` handler instead of expanding procedural includes.
