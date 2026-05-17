# 2.3 Device Overview App Panels

Application panels appear in the left column of the **Device Overview** page, below ports and transceivers. Each application that wants a panel ships two files: a PHP glue file that guards and passes data, and a Blade template that renders the HTML.

## File locations

| File | Purpose |
|------|---------|
| `includes/html/pages/device/overview/app-overview-renderer.php` | Dispatch loop - scans active apps and includes per-app PHP files |
| `includes/html/pages/device/overview/apps/{app_type}.inc.php` | Per-app glue: guard, data prep, `echo view(...)` |
| `resources/views/device/overview/apps/{app_type}.blade.php` | Per-app Blade template |

`app-overview-renderer.php` is already included by `overview.inc.php`. You only need to add your two files.

---

## Contract for the PHP glue file

The dispatcher sets `$app` (`App\Models\Application`) and `$device` (array) in scope before including your file. Your file must:

1. Guard against missing variables and wrong types.
2. Guard against empty data - render nothing if the app has no data to show.
3. Call `echo view('device.overview.apps.{app_type}', [...])` to render.
4. Unset any temporary variables it introduced to avoid polluting the outer scope.

```php
<?php

use App\Models\Application;

if (! isset($app, $device) || ! $app instanceof Application || ! is_array($device)) {
    return;
}

// load and guard
$_myData = MyApp\Data::forDevice($app, $device);
if ($_myData->isEmpty()) {
    return;
}

echo view('device.overview.apps.my_app', [
    'app'     => $app,
    'data'    => $_myData,
    'appLink' => \LibreNMS\Util\Url::generate([
        'page' => 'device', 'device' => $app->device_id,
        'tab'  => 'apps',   'app'    => 'my_app',
    ]),
]);

unset($_myData);
```

---

## Blade template structure

Use the `<x-panel>` component for the outer wrapper and any inner panels. The outer panel gets `class="device-overview panel-condensed"` to match the style of other overview sections (ports, transceivers).

```blade
<div class="row">
    <div class="col-md-12">
        <x-panel class="device-overview panel-condensed">
            <x-slot name="heading">
                <i class="fa fa-YOUR-ICON fa-lg icon-theme" aria-hidden="true"></i>
                <strong><a href="{{ $appLink }}">My App</a></strong>
            </x-slot>

            {{-- content goes here --}}

        </x-panel>
    </div>
</div>
```

### Full-width tables

Use `<x-slot name="table">` to place a table outside the `panel-body` div, so it stretches edge-to-edge like Bootstrap's `.table` inside a panel:

```blade
<x-panel>
    <x-slot name="table">
        <table class="table table-condensed table-hover tw:mb-0!">
            ...
        </table>
    </x-slot>
</x-panel>
```

### Inner panels (sub-sections)

Nest `<x-panel>` inside the outer panel body for sub-sections such as per-array or per-device breakdowns:

```blade
@foreach($items as $item)
    <x-panel>
        <x-slot name="heading">{{ $item->name }}</x-slot>
        <x-slot name="table">
            <table class="table table-condensed table-hover tw:mb-0!">
                ...
            </table>
        </x-slot>
    </x-panel>
@endforeach
```

### Status badges

The `<x-label>` component maps a `Severity` enum to Bootstrap label classes. When your data layer resolves to Bootstrap class strings (`'default'`, `'warning'`, `'danger'`) directly, use inline spans instead:

```blade
<span class="label label-{{ $entry['class'] }}" title="{{ $entry['info'] }}">
    {{ $entry['label'] }}
</span>
```

---

!!! note
    Keep panel rendering defensive: if required data is missing, return early and render nothing.

## mdadm reference implementation

- PHP glue: `includes/html/pages/device/overview/apps/mdadm.inc.php`
- Blade template: `resources/views/device/overview/apps/mdadm.blade.php`
- Data layer: `LibreNMS\Agent\Unix\Mdadm\HtmlData`

The mdadm panel renders an arrays summary table (health/operation badges, disk counts, size, errors) followed by per-array inner panels listing each member device.
