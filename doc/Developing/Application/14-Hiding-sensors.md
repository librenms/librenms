---
title: 1.4 Hiding Sensors from the Device Overview
description: Options for keeping sensors out of the Device Overview without removing them entirely.
tags:
  - developing
  - applications
  - sensors
---

# 1.4 Hiding Sensors from the Device Overview

The Device Overview page renders standard sensor classes such as load, voltage, temperature, and state. There is no supported per-sensor flag that hides one individual sensor from the overview while keeping it as a normal sensor everywhere else.

If a value is useful but would clutter the overview, choose one of these options.

## Option 1: Render an application overview panel

Use an app-specific Device Overview panel when the value should be visible, but in an app-controlled layout.

Files:

| File | Purpose |
| --- | --- |
| `includes/html/pages/device/overview/apps/{app_type}.inc.php` | PHP glue file |
| `resources/views/device/overview/apps/{app_type}.blade.php` | Blade template |

See `23-Device-Overview-Panel.md`.

## Option 2: Store it as an app metric

If the value does not need sensor thresholds, Health tab behavior, or per-sensor graphs, store it with `update_application()` instead of discovering it as a sensor.

```php
update_application($app, 'ok', [
    'my_metric' => $value,
]);
```

This stores the value in `application_metrics` and keeps it out of the sensor UI.

This is usually the best choice for summary values.

## Option 3: Use a sensor class with no overview include

The overview page only renders sensor classes that have a matching include under:

```text
includes/html/pages/device/overview/sensors/
```

A sensor class without an overview include may still appear on the Health tab but not on Device Overview.

!!! warning
    This is fragile. Do not use it for new applications unless there is a specific compatibility reason. If a future overview include is added for that class, the sensor may reappear on Device Overview.

## Rule of thumb

| Need | Use |
| --- | --- |
| Alertable value with thresholds | sensor |
| App summary value | `update_application()` metric |
| Compact app-specific overview | overview panel |
| Temporary compatibility workaround | non-overview sensor class |
