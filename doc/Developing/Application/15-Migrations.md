---
title: 1.5 Migrations
description: When and how to add database migrations for LibreNMS application development.
tags:
  - developing
  - applications
  - database
---

# 1.5 Migrations

Most applications do **not** need a database migration.

Use the existing LibreNMS storage mechanisms first:

| Need | Use |
| --- | --- |
| App state and status | `applications` via `update_application()` |
| Numeric app metrics | `application_metrics` via `update_application()` |
| Time-series graph data | RRD |
| Cross-poll metadata | `$app->data` |
| Sensor values and thresholds | sensors |
| Queryable relational data | custom table + migration |

Create a migration only when the app needs a dedicated table that cannot be expressed cleanly through the existing mechanisms.

## When a custom table is justified

A custom table may be appropriate when the app needs:

- queryable rows beyond current sensor values
- relationships between entities
- persistent inventory-like data
- data that must be joined from other LibreNMS UI/API code
- retention behavior that RRD cannot provide

Do not create a table just to store the latest status, summary counts, or simple discovery metadata.

## Migration skeleton

??? example "Migration skeleton"
    ```php
    <?php

    use Illuminate\Database\Migrations\Migration;
    use Illuminate\Database\Schema\Blueprint;
    use Illuminate\Support\Facades\Schema;

    return new class extends Migration
    {
        public function up(): void
        {
            Schema::create('app_specific_table', function (Blueprint $table) {
                $table->id();
                $table->unsignedInteger('device_id')->index();
                $table->unsignedInteger('app_id')->index();
                $table->string('key_column', 64);
                $table->double('value')->nullable();
                $table->timestamps();

                $table->unique(['app_id', 'key_column']);
                $table->foreign('device_id')
                    ->references('device_id')
                    ->on('devices')
                    ->onDelete('cascade');
            });
        }

        public function down(): void
        {
            Schema::dropIfExists('app_specific_table');
        }
    };
    ```

Save the file as:

```text
database/migrations/{YYYY}_{MM}_{DD}_{HH}_{II}_{SS}_{name}.php
```

Run locally with:

```bash
php artisan migrate
```

!!! warning
    If an app creates a dedicated table, it must also clean up rows when entities disappear or when the app is removed.

## RRD restructuring

As an app matures, you may need to change the RRD layout. For example, moving from many single-dataset RRDs to fewer multi-dataset RRDs.

Handle this in the poller and version it in `$app->data`.

```php
$appData = $this->getAppData();

if (($appData['rrd_version'] ?? 1) < 2) {
    // Start writing the new RRD layout.
    // Keep old RRDs untouched so historical data is not destroyed.
    $appData['rrd_version'] = 2;
    $this->saveAppData($appData);
}
```

Key points:

- Keep an `rrd_version` field in `$app->data`.
- Save `$app->data` before `update_application()`.
- Leave old RRD files in place unless immediate cleanup is required.
- Make graph files read the new RRD name.
- Optionally add temporary fallback reads for old RRD names.

## Deleting old RRDs

Do not delete old RRD files in the same cycle where the new layout first appears. Let the new RRD receive at least one update first.

If immediate cleanup is needed, use a separate flag:

```php
$appData = $this->getAppData();

if (($appData['delete_old_rrds'] ?? false) === true) {
    // Carefully unlink old RRD paths here.
    $appData['delete_old_rrds'] = false;
    $this->saveAppData($appData);
}
```
