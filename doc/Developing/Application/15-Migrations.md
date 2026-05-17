# Migrations

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