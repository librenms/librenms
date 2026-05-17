# 1.4 Hiding Sensors from the Device Overview

The device overview page displays every sensor of each standard class (load, voltage, state, etc.) unconditionally. There is no per-sensor field to suppress individual sensors from appearing there.

If you have a sensor whose value is useful for alerting or graphing but should not clutter the device overview, you have three options.

## Option 1: Define an application overview panel

Instead of relying on sensor rows in the default overview blocks, render the data you want in an app-specific overview panel.

Use the application panel pattern documented in [2.3 Device Overview App Panels](23-Device-Overview-Panel.md):

- PHP glue file: `includes/html/pages/device/overview/apps/{app_type}.inc.php`
- Blade template: `resources/views/device/overview/apps/{app_type}.blade.php`
- Dispatcher: `includes/html/pages/device/overview/app-overview-renderer.php`

This gives you full control over what appears in Device Overview for the app.

## Option 2: Store as an app metric instead

If the value does not need individual sensor graphs or alert thresholds, pass it to `update_application()` as a plain metric rather than discovering it as a sensor:

```php
update_application($app, 'ok', [
    'my_metric' => $value,
]);
```

The metric value is stored in `application_metrics` (with app state/status in `applications`) and never appears in the sensor UI.

## Option 3: Use a sensor class with no overview include

The device overview only renders sensor classes that have a matching file under `includes/html/pages/device/overview/sensors/`. If you use a class name that has no such file, the sensor appears on the Health tab but not on the overview.

!!! warning
    This is fragile. If a future overview include file is added for that class, the sensor will reappear. Prefer Option 2 for values you genuinely want out of the sensor UI.
