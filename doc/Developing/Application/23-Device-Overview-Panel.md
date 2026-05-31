---
title: 2.3 Device Overview App Panels
description: How to add compact application status panels to the LibreNMS Device Overview page.
tags:
  - developing
  - applications
---

# 2.3 Device Overview App Panels

Application panels appear in the left column of the Device Overview page, below ports and transceivers.

Use an overview panel for compact app status. Do not do heavy work here; the overview page should be fast.

## File locations

| File | Purpose |
| --- | --- |
| `includes/html/pages/device/overview/app-overview-renderer.php` | Dispatcher that includes active app panels |
| `includes/html/pages/device/overview/apps/{app_type}.inc.php` | Per-app PHP glue file |
| `resources/views/device/overview/apps/{view_name}.blade.php` | Per-app Blade template |

The dispatcher is already included by the Device Overview page. An app normally adds only the PHP glue file and Blade template.

## Naming

App types often use kebab-case. Blade view names use underscores.

| App type | PHP file | Blade view |
| --- | --- | --- |
| `my-app` | `my-app.inc.php` | `device.overview.apps.my_app` |
| `btrfs` | `btrfs.inc.php` | `device.overview.apps.btrfs` |

## PHP glue contract

The dispatcher sets `$app` and `$device` before including the per-app file.

The glue file must:

1. Guard against missing or wrong variable types.
2. Load only lightweight/precomputed data.
3. Render nothing when there is no useful data.
4. Call `echo view(...)`.
5. Unset temporary variables to avoid leaking scope.

```php
<?php

use App\Models\Application;
use LibreNMS\Util\Url;

if (! isset($app, $device) || ! $app instanceof Application || ! is_array($device)) {
    return;
}

$_myData = $app->data['latest']['overview'] ?? [];
if ($_myData === []) {
    return;
}

echo view('device.overview.apps.my_app', [
    'app' => $app,
    'data' => $_myData,
    'appLink' => Url::generate([
        'page' => 'device',
        'device' => $app->device_id,
        'tab' => 'apps',
        'app' => 'my-app',
    ]),
]);

unset($_myData);
```

## Blade template

Use `<x-panel>` for the outer wrapper and apply `device-overview panel-condensed`.

```blade
<div class="row">
    <div class="col-md-12">
        <x-panel class="device-overview panel-condensed">
            <x-slot name="heading">
                <i class="fa fa-cube fa-lg icon-theme" aria-hidden="true"></i>
                <strong><a href="{{ $appLink }}">My App</a></strong>
            </x-slot>

            <dl class="tw:mb-0!">
                <dt>Status</dt>
                <dd>{{ $data['status'] ?? 'unknown' }}</dd>
            </dl>
        </x-panel>
    </div>
</div>
```

## Full-width tables

Use the panel `table` slot to make a table stretch edge-to-edge like other Bootstrap panel tables.

```blade
<x-panel class="device-overview panel-condensed">
    <x-slot name="heading">
        <strong><a href="{{ $appLink }}">My App</a></strong>
    </x-slot>

    <x-slot name="table">
        <table class="table table-condensed table-hover tw:mb-0!">
            <thead>
                <tr>
                    <th>Name</th>
                    <th>Status</th>
                </tr>
            </thead>
            <tbody>
                @foreach($data['items'] ?? [] as $item)
                    <tr>
                        <td>{{ $item['name'] }}</td>
                        <td>{{ $item['status'] }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </x-slot>
</x-panel>
```

## Data source guidance

Prefer data already prepared by polling:

- `$app->data['latest']`
- `application_metrics`
- latest sensor values
- small model queries scoped by `app_id` or `device_id`

Avoid:

- SNMP calls
- shell commands
- expensive RRD reads
- broad unscoped database queries
- parsing large payloads on page load

## Rules

- Render nothing for empty data.
- Keep the panel compact.
- Link the heading to the full app page.
- Escape output through Blade `{{ }}` unless HTML is deliberate.
- Use app pages for detail; use overview panels for summary.
