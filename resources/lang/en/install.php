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
        'host' => 'Host',
        'port' => 'Port',
        'socket' => 'Unix-Socket',
        'user' => 'User',
        'password' => 'Password',
        'name' => 'Name',
        'socket_empty' => 'Leave empty if using Unix-Socket',
        'ip_empty' => 'Leave empty if using Host',
    ]
];
