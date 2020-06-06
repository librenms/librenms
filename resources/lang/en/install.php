<?php
return [
    'title' => 'LibreNMS Install',
    'install' => 'Install',
    'stage' => 'Stage :stage of :stages complete',
    'checks' => [
        'title' => 'Pre-Install Checks',
        'php_module' => 'PHP Module: :module',
        'item' => 'Item',
        'status' => 'Status',
        'comment' => 'Comment',
    ],
    'database' => [
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
    'user' => [
        'username' => 'Username',
        'password' => 'Password',
        'email' => 'Email',
        'button' => 'Add User',
        'created' => 'User Created',
    ]
];
