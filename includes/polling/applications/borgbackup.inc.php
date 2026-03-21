<?php

use Illuminate\Support\Facades\Log;
use LibreNMS\Exceptions\JsonAppException;
use LibreNMS\RRD\RrdDefinition;

$name = 'borgbackup';
$verbose = 0;
$AAAA = 1;


//
// ###  Fetch BorgBackup data from the agent
//
// Retrieves JSON data from the BorgBackup agent using the json_app_get() function.
// The second parameter '1' indicates the expected API version.
// If fetching fails, the exception is caught and the application state is updated
// with the error code and message before returning early.
try {
    $returned = json_app_get($device, $name, 1);
} catch (JsonAppException $e) {
    echo PHP_EOL . $name . ':' . $e->getCode() . ':' . $e->getMessage() . PHP_EOL;

    update_application($app, $e->getCode() . ':' . $e->getMessage(), []); // Set empty metrics and error message

    return;
}

$metrics = [];

$data = $returned['data'];
Log::info('Fetched data, mode=' . ($data['mode'] ?? 'undef'));
//Log::info('Raw data: ' . json_encode($data));

//
// ###  Mode validation (sanity check)
//
// BorgBackup operates in two modes:
// - 'single': Single repository mode (totals are the repo data)
// - 'multi': Multiple repositories mode (totals are aggregates, repos array contains individual repos)
// This check ensures the data is valid before processing. If mode is missing or invalid,
// the function returns early with an error state.
if (! isset($data['mode']) || (strcmp((string) $data['mode'], 'single') !== 0 && strcmp((string) $data['mode'], 'multi') !== 0)) {
    echo '    ERROR: .data.mode is undef or not set to single or multi' . PHP_EOL;
    update_application($app, 'Error', $metrics);

    return;
}

Log::info('Mode validated: ' . $data['mode']);

$app_data = [
    'mode' => $data['mode'],
    'errored' => [],
    'repos' => [],
];

// RRD (Round Robin Database) definition for storing time-series metrics.
// Defines a single 'data' gauge dataset used to store numeric values over time.
$rrd_def = RrdDefinition::make()
    ->addDataset('data', 'GAUGE');

//
// ###  Single mode error handling
//
// In single mode, the totals array IS the repository data. If the totals indicate
// an error occurred, this section captures the error details from the 'single' repo
// entry. This handles the case where borgbackup reported an error but the specific
// error message needs to be extracted from a different location in the data structure.
if (strcmp((string) $data['mode'], 'single') == 0
    && $data['totals']['errored'] > 0) {
    Log::info('    Single mode error detected' );
    if (isset($data['repos']['single']['error'])
        && strcmp($data['repos']['single']['error'], '') !== 0) {
        $app_data['errored']['single'] = $data['repos']['single']['error'];
        echo '        -> Error: ' . $data['repos']['single']['error'] . PHP_EOL;
    } else {
        $app_data['errored']['single'] = 'Unknown error. .totals.errored > 0, but .repos.single.error is empty.';
        Log::info('        -> Unknown error (totals.errored > 0)');
    }
}

//
// ###  Process totals (metrics for both single and multi mode)
//
// Extracts and stores RRD metrics for aggregate totals across all repositories.
// These metrics include: error status, lock status, time since last backup,
// chunk counts, compressed/uncompressed sizes (total and unique).
// In single mode, these totals represent the single repository.
// In multi mode, these totals represent the sum across all repositories.
Log::info('    Processing totals...');
$total_vars = [
    'errored',
    'locked',
    'locked_for',
    'time_since_last_modified',
    'total_chunks',
    'total_csize',
    'total_size',
    'total_unique_chunks',
    'unique_csize',
    'unique_size',
];
foreach ($total_vars as $to_total) {
    $rrd_name = ['app', $name, $app->app_id, 'totals___' . $to_total];
    $value = $data['totals'][$to_total] ?? null;
    $fields = [
        'data' => is_numeric($value) ? $value : null,
    ];
    $tags = ['name' => $name, 'app_id' => $app->app_id, 'rrd_def' => $rrd_def, 'rrd_name' => $rrd_name];
    app('Datastore')->put($device, 'app', $tags, $fields);
    $metrics[$to_total] = $value;
    // Log::info('totals.' . $to_total . ' = ' . var_export($value, true));
}
Log::info('    Processed ' . count($total_vars) . ' total metrics, errored=' . ($data['totals']['errored'] ?? 0));

//
// ###  Process individual repositories (multi mode only)
//
// In multi mode, each repository has its own metrics stored separately.
// This section iterates through all repositories in the data['repos'] array,
// extracts per-repo metrics, and stores them in RRD format.
// Single mode skips this section since totals == repo data in that mode.
if (strcmp((string) $data['mode'], 'multi') == 0) {
    Log::info('    Multi mode, processing repos');

    $repo_vars = [
        'locked',
        'locked_for',
        'time_since_last_modified',
        'total_chunks',
        'total_csize',
        'total_size',
        'total_unique_chunks',
        'unique_csize',
        'unique_size',
    ];

    // Iterate through each repository in the data
    foreach ($data['repos'] as $repo => $repo_info) {
        // Determine repository name: numeric keys use name/repo/repo_name fields,
        // otherwise use the key itself as the name
        if (is_int($repo) || ctype_digit((string) $repo)) {
            $repo_name = $repo_info['name'] ?? $repo_info['repo'] ?? $repo_info['repo_name'] ?? (string) $repo;
        } else {
            $repo_name = (string) $repo;
        }

        Log::info('        -> Repository: ' . $repo_name);

        // Sanitize repository name for use in RRD names (replace special chars with underscores)
        $repo_key = preg_replace('/[^A-Za-z0-9_\-]/', '_', (string) $repo_name);

        // Check if repository has valid data (total_size > 0)
        $repo_total_size = $repo_info['total_size'] ?? 0;
        if ($repo_total_size == 0) {
            $repo_error = $repo_info['error'] ?? null;
            if ($repo_error && strcmp((string) $repo_error, '') !== 0) {
                Log::error('[' . $name . '] Repository "' . $repo_name . '" error: ' . $repo_error);
                $app_data['errored'][$repo] = trim((string) $repo_error);
            } else {
                Log::error('[' . $name . '] Repository "' . $repo_name . '" has total_size=0, skipping');
                $app_data['errored'][$repo] = 'total_size is 0 - repository may be empty or invalid';
            }
            continue;
        }

        // Check for repository-specific errors and record them
        $errored = 0;
        if (isset($repo_info['error']) && strcmp($repo_info['error'], '') !== 0) {
            $app_data['errored'][$repo] = $repo_info['error'];
            $errored = 1;
        } elseif (isset($repo_info['error']) && strcmp($repo_info['error'], '') == 0) {
            $app_data['errored'][$repo] = '.repos.' . $repo . '.error is set but blank';
            $errored = 1;
        }
        $rrd_name = ['app', $name, $app->app_id, 'repos___' . $repo_key . '___errored'];
        $fields = [
            'data' => $errored,
        ];
        $tags = ['name' => $name, 'app_id' => $app->app_id, 'rrd_def' => $rrd_def, 'rrd_name' => $rrd_name];
        app('Datastore')->put($device, 'app', $tags, $fields);

        // Store each metric variable for this repository in RRD format
        foreach ($repo_vars as $repo_var_key) {
            $rrd_name = ['app', $name, $app->app_id, 'repos___' . $repo_key . '___' . $repo_var_key];
            $value = $repo_info[$repo_var_key] ?? null;
            $fields = [
                'data' => is_numeric($value) ? $value : null,
            ];
            $tags = ['name' => $name, 'app_id' => $app->app_id, 'rrd_def' => $rrd_def, 'rrd_name' => $rrd_name];
            app('Datastore')->put($device, 'app', $tags, $fields);
            $metrics[$repo_key . '_' . $repo_var_key] = $value;
            // Log::info('repos.' . $repo_name . '.' . $repo_var_key . ' = ' . var_export($value, true));
        }

        // Build repository data structure for UI display (stores full repo info)
        $app_data['repos'][$repo_name] = [
            'error' => $repo_info['error'] ?? null,
            'errored' => $errored,
            'locked' => $repo_info['locked'] ?? null,
            'locked_for' => $repo_info['locked_for'] ?? null,
            'time_since_last_modified' => $repo_info['time_since_last_modified'] ?? null,
            'total_chunks' => $repo_info['total_chunks'] ?? null,
            'total_csize' => $repo_info['total_csize'] ?? null,
            'total_size' => $repo_info['total_size'] ?? null,
            'total_unique_chunks' => $repo_info['total_unique_chunks'] ?? null,
            'unique_csize' => $repo_info['unique_csize'] ?? null,
            'unique_size' => $repo_info['unique_size'] ?? null,
        ];
    }
    Log::info('        Processed:' . count($data['repos']) . ' repositories');
}

//
// ###  Finalize and update application state
//
// Saves the collected application data to the database and updates the
// application state with success status ('OK') and all collected metrics.
// This makes the data available for the web UI and graphing.
$app->data = $app_data;
echo 'RRD updating';
update_application($app, 'OK', $metrics);
Log::info('Done. Collected ' . count($metrics) . ' metrics');
// Log::info('All metrics: ' . json_encode($metrics));
