<?php

use LibreNMS\Exceptions\JsonAppException;
use LibreNMS\RRD\RrdDefinition;

$name = 'borgbackup';

try {
    $returned = json_app_get($device, $name, 1);
} catch (JsonAppException $e) {
    echo PHP_EOL . $name . ':' . $e->getCode() . ':' . $e->getMessage() . PHP_EOL;

    update_application($app, $e->getCode() . ':' . $e->getMessage(), []); // Set empty metrics and error message

    return;
}

$metrics = [];

$data = $returned['data'];

// a basic sanity check
if (! isset($data['mode']) && (strcmp($data['mode'], 'single') == 0 || strcmp($data['mode'], 'multi') == 0)) {
    d_echo('.data.mode is undef or not set to single or multi');
    update_application($app, 'Error', $metrics);

    return;
}

$app_data = [
    'mode' => $data['mode'],
    'errored' => [],
    'repos' => [],
];

$rrd_def = RrdDefinition::make()
    ->addDataset('data', 'GAUGE');

//
//
// single error handling
//
//
// single mode is just processing the totals, outside of error checking
if (strcmp($data['mode'], 'single') == 0
    && $data['totals']['errored'] > 0) {
    d_echo('Single mode and error set.');
    if (isset($data['repos']['single']['error'])
        && strcmp($data['repos']['single']['error'], '') !== 0) {
        $app_data['errored']['single'] = $data['repos']['single']['error'];
    } else {
        $app_data['errored']['single'] = 'Unknown error. .totals.errored > 0, but .repos.single.error is empty.';
    }
}

//
//
// totals
//
//
d_echo('Processing totals...');
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
    $fields = [
        'data' => $data['totals'][$to_total],
    ];
    $tags = ['name' => $name, 'app_id' => $app->app_id, 'rrd_def' => $rrd_def, 'rrd_name' => $rrd_name];
    data_update($device, 'app', $tags, $fields);
    $metrics[$to_total] = $data['totals'][$to_total];
}

//
//
// repos
//
//
// only process repos for multi as total and repos are the same for single
if (strcmp($data['mode'], 'multi') == 0) {
    d_echo('Multi mode and starting processing repos');

    if (isset($data['repos'][$repo]['error'])
        && strcmp($data['repos'][$repo]['error'], '') !== 0) {
        $app_data['errored'][$repo] = $data['repos'][$repo]['error'];
    } elseif (isset($data['repos'][$repo]['error'])
              && strcmp($data['repos'][$repo]['error'], '') == 0) {
        $app_data['errored'][$repo] = '';
    }

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

    // process each repo
    foreach ($data['repos'] as $repo => $repo_info) {
        d_echo('Processing repo "' . $repo . '"');

        // record error info for this repo if we have it
        $errored = 0;
        if (isset($data['repos'][$repo]['error'])
            && strcmp($data['repos'][$repo]['error'], '') !== 0) {
            $app_data['errored'][$repo] = $data['repos'][$repo]['error'];
            $errored = 1;
        } elseif (isset($data['repos'][$repo]['error'])
                  && strcmp($data['repos'][$repo]['error'], '') == 0) {
            $app_data['errored'][$repo] = '.repos.' . $repo . '.error is set but blank';
            $errored = 1;
        }
        $rrd_name = ['app', $name, $app->app_id, 'repos___' . $repo . '___errored'];
        $fields = [
            'data' => $errored,
        ];
        $tags = ['name' => $name, 'app_id' => $app->app_id, 'rrd_def' => $rrd_def, 'rrd_name' => $rrd_name];
        data_update($device, 'app', $tags, $fields);

        // process each variable for the repo
        foreach ($repo_vars as $repo_var_key) {
            $rrd_name = ['app', $name, $app->app_id, 'repos___' . $repo . '___' . $repo_var_key];
            $fields = [
                'data' => $data['repos'][$repo][$repo_var_key],
            ];
            $tags = ['name' => $name, 'app_id' => $app->app_id, 'rrd_def' => $rrd_def, 'rrd_name' => $rrd_name];
            data_update($device, 'app', $tags, $fields);
        }

        // add the current repo to the list of repos
        $app_data['repos'][] = $repo;
    }
}

$app->data = $app_data;
update_application($app, 'OK', $metrics);
