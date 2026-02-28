<?php

return [
    'devices' => [
        'view'   => ['label' => 'View Devices',   'description' => 'View device list, details, and graphs'],
        'create' => ['label' => 'Add Devices',    'description' => 'Add new devices to LibreNMS'],
        'edit'   => ['label' => 'Edit Devices',   'description' => 'Modify device settings and SNMP credentials'],
        'delete' => ['label' => 'Delete Devices', 'description' => 'Remove devices from LibreNMS'],
        'purge'  => ['label' => 'Purge Devices',  'description' => 'Permanently purge a device and all associated data'],
    ],

    'alerts' => [
        'view'        => ['label' => 'View Alerts',        'description' => 'View active and historical alerts'],
        'create'      => ['label' => 'Create Alert Rules', 'description' => 'Create new alert rules and templates'],
        'edit'        => ['label' => 'Edit Alert Rules',   'description' => 'Modify existing alert rules and templates'],
        'delete'      => ['label' => 'Delete Alert Rules', 'description' => 'Delete alert rules and templates'],
        'acknowledge' => ['label' => 'Acknowledge Alerts', 'description' => 'Acknowledge active alerts'],
        'unmute'      => ['label' => 'Unmute Alerts',      'description' => 'Unmute alerts that have been silenced'],
    ],

    'services' => [
        'view'   => ['label' => 'View Services',   'description' => 'View services monitored on devices'],
        'create' => ['label' => 'Add Services',    'description' => 'Add new services to devices'],
        'edit'   => ['label' => 'Edit Services',   'description' => 'Modify service check settings'],
        'delete' => ['label' => 'Delete Services', 'description' => 'Remove services from devices'],
    ],

    'ports' => [
        'view'  => ['label' => 'View Ports',  'description' => 'View port details, graphs, and statistics'],
        'edit'  => ['label' => 'Edit Ports',  'description' => 'Modify port descriptions and settings'],
        'purge' => ['label' => 'Purge Ports', 'description' => 'Permanently purge deleted ports and their data'],
    ],

    'maps' => [
        'view'   => ['label' => 'View Maps',   'description' => 'View network maps'],
        'create' => ['label' => 'Create Maps', 'description' => 'Create new network maps'],
        'edit'   => ['label' => 'Edit Maps',   'description' => 'Modify existing network maps'],
        'delete' => ['label' => 'Delete Maps', 'description' => 'Delete network maps'],
    ],

    'reports' => [
        'view'   => ['label' => 'View Reports',   'description' => 'View availability and performance reports'],
        'create' => ['label' => 'Create Reports', 'description' => 'Create new reports'],
        'delete' => ['label' => 'Delete Reports', 'description' => 'Delete existing reports'],
    ],

    'oxidized' => [
        'view'   => ['label' => 'View Oxidized',          'description' => 'View device configuration backups'],
        'resync' => ['label' => 'Resync Oxidized',        'description' => 'Trigger a configuration re-fetch for a device'],
        'diff'   => ['label' => 'View Config Diffs',      'description' => 'View configuration change diffs between backups'],
    ],

    'users' => [
        'view'   => ['label' => 'View Users',   'description' => 'View user accounts and their roles'],
        'create' => ['label' => 'Create Users', 'description' => 'Create new user accounts'],
        'edit'   => ['label' => 'Edit Users',   'description' => 'Modify user accounts, roles, and permissions'],
        'delete' => ['label' => 'Delete Users', 'description' => 'Delete user accounts'],
    ],

    'settings' => [
        'view' => ['label' => 'View Settings', 'description' => 'View global LibreNMS settings'],
        'edit' => ['label' => 'Edit Settings', 'description' => 'Modify global LibreNMS settings'],
    ],

    'api' => [
        'access' => ['label' => 'API Access', 'description' => 'Access the LibreNMS REST API'],
    ],
];

