<?php

return [
    'title' => 'Config',
    'backups' => 'Backups',
    'configuration' => 'Configuration',
    'diff' => 'Diff',
    'show_diff' => 'Show Diff',
    'show_config' => 'Show Config',
    'default_provider' => 'the backup provider',

    'select_two_to_compare' => 'Select two backups to compare.',
    'select_two_hint' => 'Select two backups from the list to view their differences.',
    'valid_until' => 'Valid until',
    'old' => 'Old',
    'new' => 'New',
    'download' => 'Download',
    'copy' => 'Copy',
    'copied' => 'Copied!',
    'load_more' => 'Load more',
    'loading' => 'Loading...',
    'refresh' => 'Refresh Config',
    'refresh_unavailable' => 'This provider cannot queue a refresh.',

    'messages' => [
        'unreachable' => 'Could not reach :provider.',
        'unreachable_details' => 'Could not reach :provider. Check the configured URL and check that :provider runs.',
        'error' => ':provider returned an error.',
        'error_details' => ':provider returned an error. Check the configured API token.',
        'backup_not_found' => 'Could not load this backup from :provider.',
        'no_backups' => 'No configuration backups exist for this device in :provider.',
        'device_not_found' => 'Could not find this device in :provider. :provider matches devices by hostname or IP address.',
        'binary_not_supported' => 'This is a binary backup. It cannot be displayed. View it in :provider instead.',
        'request_failed' => 'The request failed. Try again.',
        'refresh_queued' => 'Queued a refresh in :provider for this device.',
        'refresh_failed' => 'Could not queue a refresh in :provider.',
    ],
];
