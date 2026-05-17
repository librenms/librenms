---
title: Developing Applications (LibreNMS Side)
description: Developer guideline for creating LibreNMS applications (discovery, polling, RRD, graphs, app pages).
tags:

- developing
- applications
- snmp

---

# Developing SNMP Extend Applications (LibreNMS Side)

This document covers what a developer must do **inside the LibreNMS repository** to add a new SNMP extend application (a LibreNMS "Application"). For the host/agent side of extension development, see [Developing SNMP Extensions (Developer Guideline)](30-Extension-Developing.md).

## Overview

An SNMP extend application consists of two halves:

```text
 HOST SIDE                       LIBRENMS SIDE
+-----------+                   +-------------------------------+
| Extend    |    snmpd          | Discovery (When flag is set)  |
| script    | +---------------> | (auto via walk)               |
|           |                   +-------------------------------+
+-----------+                               |
                                            v
                                +-------------------------+
                                | Polling                 |
                                | includes/polling/       |
                                | applications/{name}.inc |
                                +-------------------------+
                                            |
                                +---------------------+
                                |                     |
                                v                     v
                        +-------------+       +-------------+
                        | RRD files   |       | applications|
                        | (metrics)   |       | table       |
                        +-------------+       +-------------+
                                |                     |
                                +----------+----------+
                                            |
                                            v
                                    +-------------+
                                    | app_pages   |
                                    | (graphs)    |
                                    +-------------+
```

Your job as a LibreNMS developer is everything after the SNMP poll:

1. **Files you create** - polling file, graph files, app page, docs
2. **Discovery** - manually triggered by user when enabling app.
3. **Polling** - process JSON, write RRDs, update application state
4. **Graphing** - define RRD structure and graph file templates
5. **App Pages** - render graphs and content (from flat pages to multi-section hierarchies)

## Files You Create

| File | Purpose |
| --- | --- |
| `includes/polling/applications/{name}.inc.php` | Polling logic |
| `includes/html/graphs/application/{name}_{metric}.inc.php` | One per graph type |
| `doc/Extensions/Applications/{Name}.md` | User-facing documentation |

## Naming Conventions

| Aspect | Rule | Example |
| --- | --- | --- |
| Polling file | `{name}.inc.php` | `chronyd.inc.php` |
| App type | kebab-case, matches polling filename | `powerdns-recursor` |
| SNMP extend name | kebab-case (must match extend line on host) | `chronyd` |
| RRD name | `['app', $name, $app->app_id, ...]` | `['app', 'chronyd', 5]` |
| Graph file | `{name}_{metric}.inc.php` | `chronyd_time.inc.php` |
| Common graph include | `{name}-common.inc.php` | `smart-common.inc.php` |
| Metrics table | snake_case | `metric_name` |

Some SNMP extend names differ from the app type (defined in the name mapping table in `includes/discovery/applications.inc.php`). If your extend name differs from the app type, add it to the mapping.

## App Registration

To enable an app on a device, you must create a polling file at `includes/polling/applications/{name}.inc.php`. No additional registration is needed - LibreNMS automatically discovers available apps by scanning this directory.

When discovery runs (via `./lnms device:discover {device_id}`), it enables apps found via SNMP extend and sets `discovered=1` in the `applications` table.

The app then appears in the device edit page at `device/device={id}/tab=edit/section=apps` where users can enable/disable it.

### How apps appear in the device edit page

The file `includes/html/pages/device/edit/apps.inc.php` handles this:

1. **Scans for available apps**: Loops over `includes/polling/applications/*.inc.php` to build the list of available apps. The filename (without `.inc.php`) becomes the `app_type`.

2. **Queries enabled apps**: Looks up `applications` table for the device to find which apps are already enabled. Uses `discovered` column to indicate apps that are discovered and polled via SNMP extend.

### Database keys used in LibreNMS

| Key | Table column | Purpose |
| --- | --- | --- |
| `app_type` | `applications.app_type` | Identifies the app, matches polling filename |
| `device_id` | `applications.device_id` | Links app to a device |
| `discovered` | `applications.discovered` | `1` = enabled (manually or via discovery); `0` = disabled for UI |
| Soft delete | `applications.deleted_at` | Apps are disabled via soft delete, not hard delete |

## Discovery

Discovery is handled by `includes/discovery/applications.inc.php`:

1. **SNMP walk**: Walks `nsExtendStatus` on `NET-SNMP-EXTEND-MIB` to find all extend entries on the device
2. **Match against polling files**: Compares extend names against `includes/polling/applications/*.inc.php` (filename without `.inc.php` is the `app_type`)
3. **Name mapping**: Some apps have different SNMP extend names than their `app_type` (e.g., `mailq` extends as `postfix`). Mappings are in the discovery file.
4. **Enable apps**: If an extend is found and not already in the `applications` table, adds it with `discovered=1`. If the app was previously disabled (soft deleted), it restores it.
5. **Remove stale apps**: Removes apps that were previously discovered but are no longer present in SNMP extend. Sets `discovered=0` (soft delete via `deleted_at`).

Example output from `./lnms device:discover {device_id} -v`:

```text
Applications: SNMP['/usr/bin/snmpbulkwalk' '-v2c' '-c' 'COMMUNITY' '-OQUs' '-m' 'NET-SNMP-EXTEND-MIB' 'udp:HOSTNAME:161' 'nsExtendStatus']
nsExtendStatus."btrfs" = active
nsExtendStatus."smart" = active
nsExtendStatus."chronyd" = active
nsExtendStatus."proxmox" = active
nsExtendStatus."borgbackup" = active
```

The polling file must exist for discovery to work. The app name passed to `json_app_get()` must match the extend name configured on the host.

## JSON Contract

Your extend script must output JSON with these required keys:

```json
{
  "version": 1,
  "error": 0,
  "errorString": "success",
  "data": { ... }
}
```

| Key | Required | Description |
| --- | --- | --- |
| `version` | Yes | Integer >= 1. Increment when the output format changes. |
| `error` | Yes | `0` = success, non-zero = error (code is the value). |
| `errorString` | Yes | Human-readable message. |
| `data` | Yes | Your metric data (objects, arrays, nested - as needed). |

Compression is supported: pipe output through `lnms_return_optimizer` on the host to gzip + base64 encode it. `json_app_get()` auto-detects and decodes it.

### Legacy Support

If you need to support older script versions that output non-JSON, catch `JsonAppParsingFailedException` to detect malformed JSON and parse the raw output.

??? example "Legacy support"
    ```php
    try {
        $data = json_app_get($device, $name, $min_version)['data'];
    } catch (JsonAppParsingFailedException $e) {
        // Legacy script: parse raw text from $e->getOutput()
        $legacy = $e->getOutput();
        $data = parse_legacy_output($legacy);
    } catch (JsonAppException $e) {
        echo PHP_EOL . $name . ':' . $e->getCode() . ':' . $e->getMessage() . PHP_EOL;
        update_application($app, $e->getCode() . ':' . $e->getMessage(), []);
        return;
    }
    ```

## Polling File

Create `includes/polling/applications/{name}.inc.php`.

### Structure

??? example "Polling file structure"
    ```php
    <?php

    use App\Models\Eventlog;
    use LibreNMS\Enum\Severity;
    use LibreNMS\Exceptions\JsonAppException;
    use LibreNMS\RRD\RrdDefinition;

    $name = 'appname';

    try {
        $data = json_app_get[$device, $name, $min_version]('data');
    } catch (JsonAppException $e) {
        echo PHP_EOL . $name . ':' . $e->getCode() . ':' . $e->getMessage() . PHP_EOL;
        update_application($app, $e->getCode() . ':' . $e->getMessage(), []);
        return;
    }

    // ... process $data, write RRDs ...

    $metrics = [...];
    update_application($app, 'OK', $metrics);
    ```

### RRD Definition

`RrdDefinition` builds the dataset schema passed to `rrdtool create`. Chain `addDataset()` calls to define each metric:

```php
use LibreNMS\RRD\RrdDefinition;

$rrd_def = RrdDefinition::make()
    ->addDataset('metric_name', 'GAUGE', 0, 100)
    ->addDataset('counter',     'DERIVE', 0);
```

#### addDataset() parameters

```php
addDataset(
    string $name,               // DS name: [a-zA-Z0-9_], max 19 chars
    string $type,               // See dataset types below
    int|null $min = null,       // null → 'U' (undefined lower bound)
    int|null $max = null,       // null → 'U' (undefined upper bound)
    int|null $heartbeat = null, // null → global rrd.heartbeat config
    ?string $source_ds = null,  // Copy initial data from this DS name (migration)
    ?string $source_file = null // RRD file to copy from (migration; optional)
): RrdDefinition
```

Returns `$this` - all calls are fluent.

#### Dataset types

| Type | Description |
|---|---|
| `GAUGE` | Instantaneous value - use for most metrics (temperature, load, counts) |
| `DERIVE` | Rate of change per second - use for monotonic counters where wrap is not expected |
| `COUNTER` | Like `DERIVE` but handles 32-bit counter wraps automatically |
| `ABSOLUTE` | Value is divided by elapsed time - for counters reset on each read |
| `DCOUNTER` | 64-bit variant of `COUNTER` |
| `DDERIVE` | 64-bit variant of `DERIVE` |

Set explicit bounds where the domain is known (`GAUGE 0 100` for percentages, `DERIVE 0` to reject negative rates). Leave `$min`/`$max` as `null` when the range is open.

#### disableNameChecking()

By default the datastore validates that every field key matches a DS name in the definition. Call `disableNameChecking()` to skip that check and assign values by position instead:

```php
$rrd_def = RrdDefinition::make()
    ->addDataset('in',  'DERIVE', 0)
    ->addDataset('out', 'DERIVE', 0)
    ->disableNameChecking();
```

#### Data migration via source_ds / source_file

To seed a new DS from an existing RRD at creation time, pass the source DS name and file path. If the source file does not exist the parameter is silently ignored (no error):

```php
$rrd_def = RrdDefinition::make()
    ->addDataset('reads',  'DERIVE', 0, null, null, 'read',  $old_rrd_path)
    ->addDataset('writes', 'DERIVE', 0, null, null, 'write', $old_rrd_path);
```

This copies historical data from `$old_rrd_path` DS `read` into the new `reads` DS when the file is first created. For post-creation schema changes (adding a DS to an existing RRD) use `Rrd::addDatasets()` instead - see [Evolving RRD Schema](#evolving-rrd-schema).

!!! info "One RRD with many DS vs one RRD per instance"
    | Pattern | Pros | Cons |
    |---|---|---|
    | One RRD, many DS | Simpler, fewer files | Harder to drop or aggregate one metric later |
    | One RRD per instance | Clear per-instance scoping, easy to drop one | More files, slightly more storage overhead |
    | One RRD per instance, many DS | Groups related metrics, fewer files | Less granular than one DS per RRD |

### Writing RRD Data

```php
$rrd_name = ['app', $name, $app->app_id];
$rrd_def = RrdDefinition::make()
    ->addDataset('metric', 'GAUGE', 0, 100);

$fields = ['metric' => $data['metric']];
$tags = ['name' => $name, 'app_id' => $app->app_id, 'rrd_def' => $rrd_def, 'rrd_name' => $rrd_name];
app('Datastore')->put($device, 'app', $tags, $fields);
```

The resulting RRD file is: `{rrd_dir}/{hostname}/app-{name}-{app_id}.rrd`.

### Multiple RRDs Per Poll

If your app has multiple data groups (e.g., per-source, per-disk), use separate RRD name prefixes:

??? example "Multiple RRDs per poll"
    ```php
    // Main RRD
    $rrd_name = ['app', $name, $app->app_id];
    // Per-source RRD (Chrony pattern)
    foreach ($data['sources'] as $source) {
        $rrd_name = ['app', $name, $app->app_id, $source['source_name']];
        // ... write RRD
    }
    ```

Use `$app->data` to track cross-poll state (e.g., source list, disk list).

### Reading RRD Data

Use the `Rrd` facade to read values back from an existing RRD file. This is useful for overview panels or for carrying current rates into event logic.

**Latest per-second rates** (aligned to configured `rrd.step`):

```php
use App\Facades\Rrd;

$filename = Rrd::name($device['hostname'], ['app', $name, $app->app_id]);
$point = Rrd::getLastRates($filename, ['read_bps', 'write_bps']);

$readBps  = $point?->get('read_bps');   // float|null
$writeBps = $point?->get('write_bps');
```

**Last update timestamp and raw values**:

```php
$point = Rrd::lastUpdate($filename);
$timestamp = $point?->timestamp;        // int UNIX timestamp
$value     = $point?->get('metric');    // float|null
$datasets  = $point?->ds();             // ['metric', ...]
```

Both methods return a `TimeSeriesPoint` (or `null` if the file does not exist or has no data):

| Property / Method | Type | Description |
|---|---|---|
| `->timestamp` | `int` | UNIX timestamp of the data point |
| `->get(string $name)` | `int\|float\|null` | Value for a named dataset |
| `->ds()` | `array<string>` | All dataset names in the point |

### Evolving RRD Schema

To add new datasets to an existing RRD without recreating it, use the facade methods below. These are the recommended approach for schema evolution - existing data is preserved and the new DS starts collecting from the next poll.

**List what is already there:**

```php
use App\Facades\Rrd;

$filename = Rrd::name($device['hostname'], ['app', $name, $app->app_id]);
$existing = Rrd::listDatasets($filename);  // ['metric1', 'metric2']
```

**Add new datasets:**

```php
Rrd::addDatasets($filename, [
    ['name' => 'new_metric', 'type' => 'GAUGE',  'heartbeat' => 600, 'min' => 0, 'max' => 'U'],
    ['name' => 'new_rate',   'type' => 'DERIVE', 'heartbeat' => 600, 'min' => 0],
]);
```

Datasets that already exist are silently skipped. Valid types: `GAUGE`, `COUNTER`, `DERIVE`, `ABSOLUTE`. Dataset names must be 1–19 characters matching `[a-zA-Z0-9_]`.

**Config-keyed form** (name as array key):

```php
Rrd::addDatasetsFromConfig($filename, [
    'new_metric' => ['type' => 'GAUGE',  'heartbeat' => 600, 'min' => 0, 'max' => 100],
    'new_rate'   => ['type' => 'DERIVE', 'heartbeat' => 600, 'min' => 0],
]);
```

Use a version field in `$app->data` so the migration runs only once. See [Migrations](15-Migrations.md) for the full versioning pattern.

### Delta During Polling

Track changes between poll cycles using `$app->data` (JSON blob, cast to array automatically). This lets you detect added/removed instances and log state transitions:

??? example "Delta during polling"
    ```php
    // Load previous state
    $old_data = $app->data ?? [];

    // Save new state before update_application()
    $app->data = ['sources' => $source_list, 'other' => $value];

    // Detect changes
    $added = array_diff($current, $old_data['sources'] ?? []);
    if ($added) {
        Eventlog::log('Added: ' . implode(',', $added), $device['device_id'], 'application', Severity::Ok);
    }
    ```

### Eventlog

Log state transitions and important events:

```php
use App\Models\Eventlog;
use LibreNMS\Enum\Severity;

Eventlog::log($message, $device['device_id'], 'application', Severity::Error);
// Also available: Severity::Ok, Warning, Notice
```

## Database

### applications Table

| Column | Purpose |
| --- | --- |
| `app_id` | Primary key |
| `device_id` | Foreign key to device |
| `app_type` | App identifier, matches polling filename |
| `app_state` | `OK`, `ERROR`, `LEGACY`, `UNKNOWN` |
| `app_state_prev` | Previous state (for change detection) |
| `app_status` | Raw status string (often error message) |
| `discovered` | `1` if discovered and polled via SNMP extend |
| `data` | JSON blob for cross-poll state |
| `timestamp` | Last poll time |

Managed by `update_application()`. Do not write directly.

### application_metrics Table

| Column | Purpose |
| --- | --- |
| `app_id` | Foreign key |
| `metric` | Metric name |
| `value` | Current value |
| `value_prev` | Previous value |

Used for alerting on individual metrics. Upserted by `update_application()`.



## Graph Definitions

Create one graph file per graph type in `includes/html/graphs/application/`.

### Simple Graph

??? example "Simple graph"
    ```php
    <?php

    require 'includes/html/graphs/common.inc.php';

    $colours = 'mixed';
    $nototal = (($width < 224) ? 1 : 0);
    $unit_text = 'Unit';
    $rrd_filename = Rrd::name($device['hostname'], ['app', 'appname', $app->app_id]);
    $array = [
        'metric1' => ['descr' => 'Metric 1'],
        'metric2' => ['descr' => 'Metric 2'],
    ];

    $rrd_list = [];
    $i = 0;
    foreach ($array as $ds => $var) {
        $rrd_list[$i] = [
            'filename' => $rrd_filename,
            'descr' => $var['descr'],
            'ds' => $ds,
            'colour' => \App\Facades\LibrenmsConfig::get("graph_colours.$colours.$i"),
        ];
        $i++;
    }

    require 'includes/html/graphs/generic_multi_line.inc.php';
    ```

### Multi-Instance Graph (via $app->data)

For apps with dynamic instances (e.g., sources, disks), enumerate from `$app->data`:

??? example "Multi-instance graph"
    ```php
    <?php

    require 'includes/html/graphs/common.inc.php';

    $colours = 'mixed';
    $nototal = 1;
    $unit_text = 'Value';
    $name = 'appname';

    $disks = array_keys($app->data['disks'] ?? []);
    sort($disks);

    $rrd_list = [];
    $i = 0;
    foreach ($disks as $disk) {
        $rrd_filename = Rrd::name($device['hostname'], ['app', $name, $app->app_id, $disk]);
        if (Rrd::checkRrdExists($rrd_filename)) {
            $rrd_list[$i] = [
                'filename' => $rrd_filename,
                'descr' => $disk,
                'ds' => 'metric',
            ];
            $i++;
        }
    }

    require 'includes/html/graphs/generic_multi_line.inc.php';
    ```

### Template Graph Files

| Template | Use when |
| --- | --- |
| `generic_multi_line.inc.php` | Multiple lines, auto-scale |
| `generic_multi_line_exact_numbers.inc.php` | Fixed-scale lines |
| `generic_data.inc.php` | Single dataset, simple values |
| `generic_multi_simplex_seperated.inc.php` | Two opposing metrics (in/out) |

## Metadata for Documentation

Add a stable metadata block near the top of your user-facing doc page:

| Field | Value |
| --- | --- | 
| JSON contract version | 1 |
| App type | `{name}` |
| SNMP extend name | `{name}` |

Avoid hard-coding build dates, versions, or timestamps in documentation.
