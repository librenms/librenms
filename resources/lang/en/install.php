<?php
return [
    'title' => 'LibreNMS Install',
    'install' => 'Install',
    'steps' => [
        'checks' => 'Pre-Install Checks',
        'database' => 'Database',
        'migrate' => 'Build Database',
        'user' => 'Create User',
        'finish' => 'Finish Install',
    ],
    'checks' => [
        'title' => 'Pre-Install Checks',
        'php_required' => ':version required',
        'item' => 'Item',
        'status' => 'Status',
        'comment' => 'Comment',
    ],
    'database' => [
        'title' => 'Configure Database',
        'status' => 'Status',
        'test' => 'Test',
        'host' => 'Host',
        'port' => 'Port',
        'socket' => 'Unix-Socket',
        'username' => 'User',
        'password' => 'Password',
        'name' => 'Database Name',
        'socket_empty' => 'Leave empty if using Unix-Socket',
        'ip_empty' => 'Leave empty if using Host',
    ],
    'migrate' => [
        'title' => 'Build Database',
        'building' => 'Building Database structure...',
        'building_interrupt' => 'Do not close this page or interrupt the import!',
        'wait' => 'Please Wait...',
        'timeout' => 'HTTP request timed out, your database structure may be inconsistent.',
        'error' => 'Error encountered, check output for details.',
        'retry' => 'Retry',
    ],
    'user' => [
        'title' => 'Create Admin User',
        'username' => 'Username',
        'password' => 'Password',
        'email' => 'Email',
        'button' => 'Add User',
        'created' => 'User Created',
        'success' => 'Successfully created user',
        'failure' => 'Failed to create user'
    ],
    'finish' => [
        'title' => 'Finish Install'
    ]
];
