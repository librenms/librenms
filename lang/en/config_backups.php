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

    'messages' => [
        'unreachable' => ':provider is not reachable.',
        'unreachable_details' => ':provider is not reachable. Check the configured URL and that :provider is running.',
        'error' => ':provider returned an error.',
        'error_details' => ':provider returned an error. Check the configured API token.',
        'backup_not_found' => 'This backup could not be loaded from :provider.',
        'no_backups' => 'No configuration backups exist for this device in :provider yet.',
        'device_not_found' => 'This device could not be found in :provider. It is matched by hostname or IP address.',
        'binary_not_supported' => 'This is a binary backup and cannot be displayed. View it in :provider instead.',
        'request_failed' => 'The request failed. Please try again.',
    ],
];
