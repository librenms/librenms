---
title: Developing Applications (LibreNMS Side)
description: Developer guideline for creating LibreNMS applications (discovery, polling, RRD, graphs, app pages).
tags:

- developing
- applications
- snmp

---

# Developing SNMP Extend Applications (LibreNMS Side)

This document covers what a developer must do **inside the LibreNMS repository** to add a new SNMP extend application (a LibreNMS "Application"). For the host/agent side of extension development, see [Developing SNMP Extensions (Developer Guideline)](Extension-developing.md).

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

If you need to support older script versions that output non-JSON, catch `JsonAppParsingFailedException` to detect malformed JSON and parse the raw output:

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

Use `RrdDefinition` to define datasets and sensible min/max bounds:

```php
$rrd_def = RrdDefinition::make()
    ->addDataset('metric_name', 'GAUGE', 0, 100)
    ->addDataset('counter', 'DERIVE', 0);
```

Dataset types:

- `GAUGE` - instantaneous value (most common)
- `DERIVE` - rate of change (for counters)
- `COUNTER` - like DERIVE but handles counter wraps
- `DCOUNTER`, `DDERIVE` - 64-bit variants

!!! info "One RRD with many DS vs one RRD per instance"
    You can store multiple datasets in a **single RRD file** (e.g., one RRD per host with all metrics as separate DS). This is simpler but less flexible. Alternatively, split into **one RRD per source/disk** (e.g., Chrony's per-source RRDs or SMART's per-disk RRDs). The trade-off:
    | Pattern | Pros | Cons |
    |---|---|--- |
    | One RRD, many DS | Simpler, fewer files | Harder to aggregate or drop one metric later |
    | One RRD per instance | Easier to drop/rename per instance, clearer graph scoping | More RRD files, slightly more storage overhead |
    | One RRD per instance, many DS | Groups related metrics, reduces file count | Less granular than one DS per RRD |

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

## Migrations

Most applications do **not** need a migration. The `applications` and `application_metrics` tables handle everything for you.

Create a migration only if your app requires a **dedicated table** that cannot be expressed through RRDs or `$app->data` (for example, storing historical data beyond RRD retention, or storing relationships between entities that must survive across poll cycles and are not easily keyed to `device_id`).

Migration naming follows LibreNMS conventions:

??? example "Migration skeleton"
    ```php
    <?php

    use Illuminate\Database\Migrations\Migration;
    use Illuminate\Database\Schema\Blueprint;

    return new class extends Migration
    {
        public function up(): void
        {
            Schema::create('app_specific_table', function (Blueprint $table) {
                $table->id();
                $table->unsignedInteger('app_id');
                $table->string('key_column', 64);
                $table->double('value')->nullable();
                $table->unique(['app_id', 'key_column']);
            });
        }

        public function down(): void
        {
            Schema::dropIfExists('app_specific_table');
        }
    };
    ```

Save the file as `database/migrations/{YYYY}_{MM}_{DD}_{HH}_{II}_{SS}_{name}.php` with a timestamp that reflects when you create it. Use `php artisan migrate` to run it locally.

!!! warning
    If you add a dedicated table, you must also handle its discovery and cleanup (drop rows when the application is removed) in your polling file.

### RRD Restructuring

As your app matures, you may want to change how RRD data is stored. For example, moving from many small RRDs (one per instance, one DS each) to fewer larger RRDs (one per instance, multiple DS grouped together).

This is a natural migration path: during **development** you might use one RRD per instance with a single DS to keep things simple. When moving to **production**, grouping related metrics into one RRD per instance reduces file count and simplifies storage.

Handle the migration in the polling file so it runs automatically during the next poll cycle:

??? example "RRD restructuring: migrate"
    ```php
    $app_data = $app->data ?? [];

    // v1: one RRD per instance, single DS
    // v2: one RRD per instance, multiple DS grouped
    if (($app_data['rrd_version'] ?? 1) < 2) {
        foreach ($data['sources'] as $source) {
            $source_name = $source['source_name'];
            $old_rrd_path = Rrd::name($device['hostname'], ['app', $name, $app->app_id, $source_name]);

            if (file_exists($old_rrd_path)) {
                // keep old RRD data as-is; the new RRD will be written alongside it
                d_echo("RRD migration: $source_name v1 -> v2");
            }
        }
        $app_data['rrd_version'] = 2;
        $app->data = $app_data;
    }
    ```

Key points:

- Keep a version field in `$app->data` so the migration runs once
- Save `$app->data` **before** `update_application()` so it persists
- Existing v1 RRD files are left untouched; the new RRDs are written alongside them
- Graph files should read from the new RRD name; they can optionally fall back to the old name during the transition
- Historical data in the old RRDs is preserved (but not migrated into the new files)

!!! note "LibreNMS automatically cleans up old RRD files"
    LibreNMS automatically cleans up RRD files that are no longer referenced by any graph definition. If you stop writing to an old RRD path, it will eventually be removed. You only need to manually delete old RRDs if you want immediate cleanup.

If you need to delete old RRDs after the transition, do it carefully:

??? example "RRD restructuring: delete old RRDs"
    ```php
    if (($app_data['rrd_version'] ?? 1) < 2) {
        // ... migrate as above ...
        $app_data['rrd_version'] = 2;
        $app_data['delete_old_rrds'] = true;
        $app->data = $app_data;
    }

    if ($app->data['delete_old_rrds'] ?? false) {
        foreach ($data['sources'] as $source) {
            $old_rrd_path = Rrd::name($device['hostname'], ['app', $name, $app->app_id, $source['source_name']]);
            @unlink($old_rrd_path);
        }
        $app->data['delete_old_rrds'] = false;
    }
    ```

This way old RRDs are deleted in a **separate poll cycle**, after the new RRDs have at least one data point.

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

## App Pages (UI)

LibreNMS renders app pages from template files in `includes/html/pages/device/apps/`. The filename must match your app type.

### Page Structure

App pages can range from a single flat page to a multi-section hierarchy with tabs, format toggles, and per-instance views. Here are the common structures:

**Single page** (all content in one file):

```text
device/device=2/tab=apps/app=myapp
+-----------------------------------------------------+
| myapp                                              |
+-----------------------------------------------------+
| [graphs and content]                                |
+-----------------------------------------------------+
```

**Overview + Focused views** (main page + sub-pages via URL params):

```text
device/device=2/tab=apps/app=borgbackup
+-----------------------------------------------------+
| borgbackup                                          |
+-----------------------------------------------------+
| [overview graphs and summary]                        |
+-----------------------------------------------------+

device/device=2/tab=apps/app=borgbackup/borgrepo=Alfader
+-----------------------------------------------------+
| borgbackup / Alfader                                |
+-----------------------------------------------------+
| [focused graphs for this specific instance]          |
+-----------------------------------------------------+
```

**Multi-section with tabs, format toggles, and per-item views** (Ports-style):

```text
device/device=2/tab=ports
+-----------------------------------------------------+
| Ports                                              |
+-----------------------------------------------------+
| [Overview] | [ARP Table] | [IPv6 ND Table]          |  <- section tabs
|                                                     |
| View: [Basic] | [Detail]    Graphs: [Bits] | ...   |  <- format + graph toggle
|                                                     |
| +-----------------------------------------------+   |
| | Interface 1    | Status | In    | Out          |  |
| +-----------------------------------------------+   |
| | eth0           | up     | 1.2G  | 800M         |  |
    ...
+-----------------------------------------------------+

device/device=2/tab=ports/port=1
+-----------------------------------------------------+
| Ports / eth0                                        |
+-----------------------------------------------------+
| [Graphs] | [Real time] | [Eventlog] | [Notes]     |  <- port sub-tabs
|                                                     |
| [Bits] | [Packets] | [Errors]                      |  <- graph selector
|                                                     |
| +-----------------------------------------------+   |
| |           [~~~~~graph~~~~~]                    |  |
| +-----------------------------------------------+   |
+-----------------------------------------------------+
```

This structure combines:

- **Section tabs** (Overview, ARP Table) switch between different data sections
- **Format toggle** (Basic, Detail) changes the table layout
- **Graph selector** (Bits, Errors) switches which graph is shown
- **Per-item sub-tabs** (Graphs, Real time, Eventlog) appear when an item is selected

Use `$vars` to read URL parameters and switch rendering accordingly.

### App Overview

Each file receives these variables:

| Variable | Description |
| --- | --- |
| `$device` | Device array |
| `$app` | Application model (has `app_id`, `app_type`, `data`, etc.) |
| `$vars` | URL query parameters (used for instance selection) |
| `$graph_array` | Build this to pass to `print-graphrow.inc.php` |

All app pages live at `includes/html/pages/device/apps/{app_name}.inc.php`.

As a developer, you should sketch how your app page will look before writing code. Here is a minimal example rendered as a user sees it:

???+ info "Pattern 1 rendered (Flat Graphs)"
    ```text
    +-----------------------------------------------------+
    | [Device: router01]  Apps > appname                  |
    +-----------------------------------------------------+
    |                                                     |
    | +-----------------------------------------------+   |
    | | Metric 1                                      |   |
    | +-----------------------------------------------+   |
    | |                                               |   |
    | |           [~~~~~graph~~~~~]                   |   |
    | |                                               |   |
    | +-----------------------------------------------+   |
    |                                                     |
    | +-----------------------------------------------+   |
    | | Metric 2                                      |   |
    | +-----------------------------------------------+   |
    | |                                               |   |
    | |           [~~~~~graph~~~~~]                   |   |
    | |                                               |   |
    | +-----------------------------------------------+   |
    +-----------------------------------------------------+
    ```

Each panel wraps one graph. The title comes from the `$graphs` map key.


The option bar lists instances as clickable links. Selecting one swaps to instance graphs.

### Pattern 1: Flat Graphs

Simple apps with no per-instance breakdown just loop over a list of graphs.

??? example "Pattern 1: Flat Graphs"
    ```php
    <?php

    $graphs = [
        'appname_metric1' => 'Metric 1',
        'appname_metric2' => 'Metric 2',
    ];

    foreach ($graphs as $key => $text) {
        $graph_type = $key;
        $graph_array = [
            'height' => '100',
            'width' => '215',
            'to' => \App\Facades\LibrenmsConfig::get('time.now'),
            'id' => $app['app_id'],
            'type' => 'application_' . $key,
        ];

        echo '<div class="panel panel-default">
        <div class="panel-heading">
            <h3 class="panel-title">' . $text . '</h3>
        </div>
        <div class="panel-body">
        <div class="row">';
        include 'includes/html/print-graphrow.inc.php';
        echo '</div></div></div>';
    }
    ```

### Pattern 2: Tabbed Sections

Use `print_optionbar_start()/end()` to create a navigation bar between sections (e.g., system vs queries vs InnoDB).

???+ info "Pattern 2 rendered (Tabbed Sections)"
    ```text
    +-----------------------------------------------------+
    | [Device: router01]  Apps > mysql                    |
    +-----------------------------------------------------+
    | [System] | [Queries] | [InnoDB]                     |
    +-----------------------------------------------------+
    |                                                     |
    | +-----------------------------------------------+   |
    | | Query Duration                                |   |
    | +-----------------------------------------------+   |
    | |           [~~~~~graph~~~~~]                   |   |
    | +-----------------------------------------------+   |
    +-----------------------------------------------------+
    ```

Tabs switch which set of graphs is displayed. State is stored in `$vars['app_section']`.

??? example "Pattern 2: Tabbed Sections"
    ```php
    <?php

    print_optionbar_start();

    $app_sections = ['system' => 'System', 'queries' => 'Queries'];
    $sep = '';
    foreach ($app_sections as $section => $label) {
        echo $sep;
        $vars['app_section'] ??= $section; // default to first

        if ($vars['app_section'] == $section) {
            echo "<span class='pagemenu-selected'>";
        }
        echo generate_link($label, $vars, ['app_section' => $section]);
        if ($vars['app_section'] == $section) {
            echo '</span>';
        }
        $sep = ' | ';
    }

    print_optionbar_end();

    $graphs['system'] = ['app_metric1' => 'Metric 1'];
    $graphs['queries'] = ['app_metric2' => 'Metric 2'];

    foreach ($graphs[$vars['app_section']] as $key => $text) {
        $graph_type = $key;
        $graph_array = [
            'height' => '100',
            'width' => '215',
            'to' => \App\Facades\LibrenmsConfig::get('time.now'),
            'id' => $app['app_id'],
            'type' => 'application_' . $key,
        ];

        echo '<div class="panel panel-default">
        <div class="panel-heading">
            <h3 class="panel-title">' . $text . '</h3>
        </div>
        <div class="panel-body">
        <div class="row">';
        include 'includes/html/print-graphrow.inc.php';
        echo '</div></div></div>';
    }
    ```

### Pattern 3: Per-Instance Breakdown

Use an option bar to list instances (sources, disks, containers). Show aggregated overview graphs when no instance is selected, and instance-specific graphs when one is selected.

???+ info "Pattern 3 rendered (Per-Instance Breakdown)"
    ```
+---------------------------------------------------------+
    | [Device: router01]  Apps > chronyd                  |
    +-----------------------------------------------------+
    | Overview | Instances: NTP, PTP, GPS                 |
    +-----------------------------------------------------+
    |                                                     |
    | +-----------------------------------------------+   |
    | | Overview 1                                    |   |
    | +-----------------------------------------------+   |
    | |           [~~~~~graph~~~~~]                   |   |
    | +-----------------------------------------------+   |
    +-----------------------------------------------------+
    ```

??? example "Pattern 3: Per-Instance Breakdown"
    ```php
    <?php

    $link_array = [
        'page' => 'device',
        'device' => $device['device_id'],
        'tab' => 'apps',
        'app' => 'appname',
    ];

    print_optionbar_start();

    echo generate_link('Overview', $link_array);
    echo ' | Instances: ';

    $instances = $app->data['instances'] ?? [];
    sort($instances);
    foreach ($instances as $index => $instance) {
        $label = $vars['instance'] == $instance
            ? '<span class="pagemenu-selected">' . $instance . '</span>'
            : $instance;

        echo generate_link($label, $link_array, ['instance' => $instance]);
        if ($index < count($instances) - 1) {
            echo ', ';
        }
    }

    print_optionbar_end();

    if (! isset($vars['instance'])) {
        $graphs = [
            'appname_overview1' => 'Overview 1',
            'appname_overview2' => 'Overview 2',
        ];
    } else {
        $graphs = [
            'appname_instance1' => 'Instance Metric 1',
            'appname_instance2' => 'Instance Metric 2',
        ];
    }

    foreach ($graphs as $key => $text) {
        $graph_type = $key;
        $graph_array = [
            'height' => '100',
            'width' => '215',
            'to' => \App\Facades\LibrenmsConfig::get('time.now'),
            'id' => $app['app_id'],
            'type' => 'application_' . $key,
        ];

        if (isset($vars['instance'])) {
            $graph_array['instance'] = $vars['instance'];
        }

        echo '<div class="panel panel-default">
        <div class="panel-heading">
            <h3 class="panel-title">' . $text . '</h3>
        </div>
        <div class="panel-body">
        <div class="row">';
        include 'includes/html/print-graphrow.inc.php';
        echo '</div></div></div>';
    }
    ```

### Pattern 4: Mix of Text/Table + Graphs

Show instance details (name, serial, status) alongside graphs. Useful when you have metadata that does not fit in a graph.

???+ info "Pattern 4 rendered (Text + Graphs)"
    ```text
    +-----------------------------------------------------+
    | [Device: router01]  Apps > smart                    |
    +-----------------------------------------------------+
    | Overview | Instances: sda, sdb, sdc                 |
    +-----------------------------------------------------+
    |                                                     |
    | Model: Samsung SSD 870 QVO              [selected]  |
    | Serial: S5EWNX0N123456                              |
    | Vendor: Samsung                                     |
    +-----------------------------------------------------+
    |                                                     |
    | +-----------------------------------------------+   |
    | | Temperature                                    |  |
    | +-----------------------------------------------+   |
    | |           [~~~~~graph~~~~~]                    |  |
    | +-----------------------------------------------+   |
    |                                                     |
    | +-----------------------------------------------+   |
    | | Reallocated Sectors                           |   |
    | +-----------------------------------------------+   |
    | |           [~~~~~graph~~~~~]                    |  |
    | +-----------------------------------------------+   |
    +-----------------------------------------------------+
    ```

Instance metadata is printed as text labels above the graphs. Use when you have attributes like serial numbers, model names, or health status that graphs cannot show.

??? example "Pattern 4: Mix of Text/Table + Graphs"
    ```php
    <?php
    // ... option bar for instance selection (see Pattern 3) ...

    if (isset($vars['disk'])) {
        $currentDisk = $app->data['disks'][$vars['disk']] ?? [];

        print_optionbar_start();

        $diskFields = [
            'model' => 'Model',
            'serial' => 'Serial',
            'vendor' => 'Vendor',
        ];

        foreach ($diskFields as $field => $label) {
            if (isset($currentDisk[$field])) {
                echo "{$label}: {$currentDisk[$field]}<br>\n";
            }
        }

        print_optionbar_end();

        $graphs = [
            'app_metric1' => 'Metric 1',
            'app_metric2' => 'Metric 2',
        ];
    }
    // ... render graphs (see Pattern 1) ...
    ```

### Pattern 5: Instance Loop with Multiple Graphs

For apps where instances are iterated and each gets multiple graphs, loop over instances directly (like Ceph or Proxmox).

???+ info "Pattern 5 rendered (Instance Loop)"
    ```text
    +-----------------------------------------------------+
    | [Device: router01]  Apps > proxmox                 |
    +-----------------------------------------------------+
    |                                                     |
    | Instance: vm-100                                    |
    | +-----------------------------------------------+   |
    | | CPU                                           |   |
    | +-----------------------------------------------+   |
    | |           [~~~~~graph~~~~~]                   |   |
    | +-----------------------------------------------+   |
    | +-----------------------------------------------+   |
    | | Memory                                        |   |
    | +-----------------------------------------------+   |
    | |           [~~~~~graph~~~~~]                   |   |
    | +-----------------------------------------------+   |
    |                                                     |
    | Instance: vm-200                                    |
    | +-----------------------------------------------+   |
    | | CPU                                           |   |
    | +-----------------------------------------------+   |
    | |           [~~~~~graph~~~~~]                   |   |
    | +-----------------------------------------------+   |
    | +-----------------------------------------------+   |
    | | Memory                                        |   |
    | +-----------------------------------------------+   |
    | |           [~~~~~graph~~~~~]                   |   |
    | +-----------------------------------------------+   |
    +-----------------------------------------------------+
    ```

Each instance gets its own section with multiple graphs. No option bar needed; all instances render in one scroll.

??? example "Pattern 5: Instance Loop with Multiple Graphs"
    ```php
    <?php

    $instances = $app->data['instances'] ?? [];

    foreach ($instances as $instance) {
        $graphs = [
            'app_instance_metric1' => 'Metric 1',
            'app_instance_metric2' => 'Metric 2',
        ];

        echo '<h3>Instance: ' . htmlspecialchars($instance) . '</h3>';

        foreach ($graphs as $key => $text) {
            $graph_type = $key;
            $graph_array = [
                'height' => '100',
                'width' => '215',
                'to' => \App\Facades\LibrenmsConfig::get('time.now'),
                'id' => $app['app_id'],
                'type' => 'application_' . $key,
                'instance' => $instance,
            ];

            echo '<div class="panel panel-default">
            <div class="panel-heading">
                <h3 class="panel-title">' . $text . '</h3>
            </div>
            <div class="panel-body">
            <div class="row">';
            include 'includes/html/print-graphrow.inc.php';
            echo '</div></div></div>';
        }
    }
    ```

### Pattern 6: Format Toggle (Overview vs Detailed)

Use an option bar to switch between different list/table layouts. The Ports page uses this with a Basic view (few columns) and Detail view (many columns).

???+ info "Pattern 6 rendered (Format Toggle)"
    ```text
    +-----------------------------------------------------+
    | [Device: router01]  Apps > myapp                    |
    +-----------------------------------------------------+
    | View: [Basic] | [Detail]                            |
    +-----------------------------------------------------+
    |                                                     |
    |  [Basic]                                            |
    |                                                     |
    | +-----------------------------------------------+   |
    | | Interface 1        | Status                   |   |
    | +-----------------------------------------------+   |
    | | eth0               | up                       |   |
    | | eth1               | up                       |   |
    | +-----------------------------------------------+   |
    |                                                     |
    | [Detail view adds columns: Speed, In, Out, etc.]    |
    |                                                     |
    | +-----------------------------------------------+   |
    | | Interface 1        | Status |  In    |  Out   |   |
    | +-----------------------------------------------+   |
    | | eth0               | up     | 1  Gbps| 800 Mbps| |
    | | eth1               | up     | 50 Mbps| 300 Mbps| |
    | +-----------------------------------------------+   |
    +-----------------------------------------------------+
    ```

Each format renders a different table with more or fewer columns.

??? example "Pattern 6: Format Toggle (Overview vs Detailed)"
    ```php
    <?php

    $vars['format'] ??= 'basic';
    $formats = ['basic' => 'Basic', 'detail' => 'Detail'];

    print_optionbar_start();
    $sep = '';
    foreach ($formats as $format => $label) {
        echo $sep;
        if ($vars['format'] == $format) {
            echo '<span class="pagemenu-selected">';
        }
        echo generate_link($label, $vars, ['format' => $format]);
        if ($vars['format'] == $format) {
            echo '</span>';
        }
        $sep = ' | ';
    }
    print_optionbar_end();

    if ($vars['format'] == 'basic') {
        $columns = ['interface', 'status'];
    } else {
        $columns = ['interface', 'status', 'speed', 'in', 'out'];
    }

    // Render table with $columns
    ```

### Pattern 7: Graph Selector

Use an option bar to let users switch between different graph types (e.g., Bits vs Packets vs Errors). The Ports page uses this to show different traffic graphs.

???+ info "Pattern 7 rendered (Graph Selector)"
    ```text
    +-----------------------------------------------------+
    | [Device: router01]  Apps > myapp                   |
    +-----------------------------------------------------+
    | Graphs: [Bits] | [Packets] | [Errors]              |
    +-----------------------------------------------------+
    |                                                     |
    | +-----------------------------------------------+  |
    | | Bits/sec                                     |  |
    | +-----------------------------------------------+  |
    | |           [~~~~~graph~~~~~]                    |  |
    | +-----------------------------------------------+  |
    +-----------------------------------------------------+
    ```

State is stored in `$vars['graph']`. Combine with Pattern 3 (instance selection) or Pattern 6 (format toggle) as needed.

??? example "Pattern 7: Graph Selector"
    ```php
    <?php

    $vars['graph'] ??= 'bits';
    $graphs = ['bits' => 'Bits', 'upkts' => 'Packets', 'errors' => 'Errors'];

    print_optionbar_start();
    $sep = '';
    foreach ($graphs as $graph => $label) {
        echo $sep;
        if ($vars['graph'] == $graph) {
            echo '<span class="pagemenu-selected">';
        }
        echo generate_link($label, $vars, ['graph' => $graph]);
        if ($vars['graph'] == $graph) {
            echo '</span>';
        }
        $sep = ' | ';
    }
    print_optionbar_end();

    $graph_type = 'appname_' . $vars['graph'];
    $graph_array = [
        'height' => '100',
        'width' => '215',
        'to' => \App\Facades\LibrenmsConfig::get('time.now'),
        'id' => $app['app_id'],
        'type' => 'application_' . $graph_type,
    ];

    echo '<div class="panel panel-default">
    <div class="panel-heading">
        <h3 class="panel-title">' . $graphs[$vars['graph']] . '</h3>
    </div>
    <div class="panel-body">
    <div class="row">';
    include 'includes/html/print-graphrow.inc.php';
    echo '</div></div></div>';
    ```

## Example: Minimal App

### 1. Polling file (`includes/polling/applications/myapp.inc.php`)

??? example "Pattern: Polling file"
    ```php
    <?php

    use LibreNMS\Exceptions\JsonAppException;
    use LibreNMS\RRD\RrdDefinition;

    $name = 'myapp';

    try {
        $data = json_app_get[$device, $name, 1]('data');
    } catch (JsonAppException $e) {
        echo PHP_EOL . $name . ':' . $e->getCode() . ':' . $e->getMessage() . PHP_EOL;
        update_application($app, $e->getCode() . ':' . $e->getMessage(), []);
        return;
    }

    $rrd_name = ['app', $name, $app->app_id];
    $rrd_def = RrdDefinition::make()
        ->addDataset('metric_a', 'GAUGE', 0, 100)
        ->addDataset('metric_b', 'GAUGE', 0, 100);

    $fields = [
        'metric_a' => $data['metric_a'],
        'metric_b' => $data['metric_b'],
    ];

    $tags = ['name' => $name, 'app_id' => $app->app_id, 'rrd_def' => $rrd_def, 'rrd_name' => $rrd_name];
    app('Datastore')->put($device, 'app', $tags, $fields);

    update_application($app, 'OK', $fields);
    ```

### 2. Graph file (`includes/html/graphs/application/myapp_metrics.inc.php`)

??? example "Pattern: Graph file"
    ```php
    <?php

    require 'includes/html/graphs/common.inc.php';

    $colours = 'mixed';
    $nototal = 0;
    $unit_text = 'Value';
    $rrd_filename = Rrd::name($device['hostname'], ['app', 'myapp', $app->app_id]);
    $array = [
        'metric_a' => ['descr' => 'Metric A'],
        'metric_b' => ['descr' => 'Metric B'],
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

## Metadata for Documentation

Add a stable metadata block near the top of your user-facing doc page:

| Field | Value |
| --- | --- |
| Polling file | `includes/polling/applications/{name}.inc.php` |
| Graph files | `includes/html/graphs/application/{name}_*.inc.php` |
| JSON contract version | 1 |
| App type | `{name}` |
| SNMP extend name | `{name}` |

Avoid hard-coding build dates, versions, or timestamps in documentation.
