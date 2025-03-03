<?php

// The 'load' and 'active' states only have
// two layers of depth in the systemd data returned
// by the snmp script.  The 'sub' state has three
// layers.  If another state type is introduced
// with three layers it must be added to the list
// here.
$state_type_ternary_depth = ['sub'];

// Any new systemd state types MUST be added to this list.
$systemd_state_types = ['load', 'active', 'sub'];

// Associative array used by the systemd application to
// build graphs, rrd names and descriptions, and parse
// the systemd.py script results.  Currently "load",
// "active", and "sub" are valid systemd state types.
// Originally, this application used a shared RRD file
// for each systemd state type. However, LibreNMS does
// not support adding new metrics to existing RRDs.
// Therefore, existing metrics are now associated with
// the "shared" string and any NEW metrics/state statuses
// added to the associative array below MUST specify the
// "individual" moniker.  This will create a NEW RRD file
// for the new metric.  For example, the "start" sub
// service state status was added originally and the
// metric is stored in the following RRD:
//   app-systemd-132-sub_service.rrd
// However the "dead-before-auto-restart" sub service
// state status was added after the fact and a new RRD
// file name is stored with the new format:
//   app-systemd-132-sub_service-dead-before-auto-restart.rrd
//
$systemd_mapper = [
    'load' => [
        'stub' => 'shared',
        'loaded' => 'shared',
        'not-found' => 'shared',
        'bad-setting' => 'shared',
        'error' => 'shared',
        'merged' => 'shared',
        'masked' => 'shared',
        'total' => 'shared',
    ],
    'active' => [
        'active' => 'shared',
        'reloading' => 'shared',
        'inactive' => 'shared',
        'failed' => 'shared',
        'activating' => 'shared',
        'deactivating' => 'shared',
        'maintenance' => 'shared',
        'total' => 'shared',
    ],
    'sub_automount' => [
        'dead' => 'shared',
        'waiting' => 'shared',
        'running' => 'shared',
        'failed' => 'shared',
        'total' => 'shared',
    ],
    'sub_device' => [
        'dead' => 'shared',
        'tentative' => 'shared',
        'plugged' => 'shared',
        'total' => 'shared',
    ],
    'sub_freezer' => [
        'running' => 'shared',
        'freezing' => 'shared',
        'freezing-by-parent' => 'individual',
        'frozen' => 'shared',
        'frozen-by-parent' => 'individual',
        'thawing' => 'shared',
        'total' => 'shared',
    ],
    'sub_mount' => [
        'dead' => 'shared',
        'mounting' => 'shared',
        'mounting-done' => 'shared',
        'mounted' => 'shared',
        'remounting' => 'shared',
        'unmounting' => 'shared',
        'remounting-sigterm' => 'shared',
        'remounting-sigkill' => 'shared',
        'unmounting-sigterm' => 'shared',
        'unmounting-sigkill' => 'shared',
        'failed' => 'shared',
        'cleaning' => 'shared',
        'total' => 'shared',
    ],
    'sub_path' => [
        'dead' => 'shared',
        'waiting' => 'shared',
        'running' => 'shared',
        'failed' => 'shared',
        'total' => 'shared',
    ],
    'sub_scope' => [
        'dead' => 'shared',
        'start-chown' => 'shared',
        'running' => 'shared',
        'abandoned' => 'shared',
        'stop-sigterm' => 'shared',
        'stop-sigkill' => 'shared',
        'failed' => 'shared',
        'total' => 'shared',
    ],
    'sub_service' => [
        'dead' => 'shared',
        'condition' => 'shared',
        'start-pre' => 'shared',
        'start' => 'shared',
        'start-post' => 'shared',
        'running' => 'shared',
        'exited' => 'shared',
        'reload' => 'shared',
        'reload-signal' => 'individual',
        'reload-notify' => 'individual',
        'stop' => 'shared',
        'stop-watchdog' => 'shared',
        'stop-sigterm' => 'shared',
        'stop-sigkill' => 'shared',
        'stop-post' => 'shared',
        'final-watchdog' => 'shared',
        'final-sigterm' => 'shared',
        'final-sigkill' => 'shared',
        'failed' => 'shared',
        'dead-before-auto-restart' => 'individual',
        'failed-before-auto-restart' => 'individual',
        'dead-resources-pinned' => 'individual',
        'auto-restart' => 'shared',
        'auto-restart-queued' => 'individual',
        'cleaning' => 'shared',
        'total' => 'shared',
    ],
    'sub_slice' => [
        'dead' => 'shared',
        'active' => 'shared',
        'total' => 'shared',
    ],
    'sub_socket' => [
        'dead' => 'shared',
        'start-pre' => 'shared',
        'start-chown' => 'shared',
        'start-post' => 'shared',
        'listening' => 'shared',
        'running' => 'shared',
        'stop-pre' => 'shared',
        'stop-pre-sigterm' => 'shared',
        'stop-pre-sigkill' => 'shared',
        'stop-post' => 'shared',
        'final-sigterm' => 'shared',
        'final-sigkill' => 'shared',
        'failed' => 'shared',
        'cleaning' => 'shared',
        'total' => 'shared',
    ],
    'sub_swap' => [
        'dead' => 'shared',
        'activating' => 'shared',
        'activating-done' => 'shared',
        'active' => 'shared',
        'deactivating' => 'shared',
        'deactivating-sigterm' => 'shared',
        'deactivating-sigkill' => 'shared',
        'failed' => 'shared',
        'cleaning' => 'shared',
        'total' => 'shared',
    ],
    'sub_target' => [
        'dead' => 'shared',
        'active' => 'shared',
        'total' => 'shared',
    ],
    'sub_timer' => [
        'dead' => 'shared',
        'waiting' => 'shared',
        'running' => 'shared',
        'elapsed' => 'shared',
        'failed' => 'shared',
        'total' => 'shared',
    ],
];
