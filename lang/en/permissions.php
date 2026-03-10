<?php

return [
    'device' => [
        'title' => 'Devices',
        'viewAny' => ['label' => 'View Devices',           'description' => 'View the list of all devices'],
        'view' => ['label' => 'View Device Details',    'description' => 'View device details, and graphs'],
        'create' => ['label' => 'Add Devices',            'description' => 'Add new devices to LibreNMS'],
        'update' => ['label' => 'Edit Devices',           'description' => 'Modify device settings and SNMP credentials'],
        'delete' => ['label' => 'Delete Devices',         'description' => 'Remove devices from LibreNMS'],
        'debug' => ['label' => 'Debug Devices',          'description' => 'Run snmpwalk and other debug commands on devices'],
        'showConfig' => ['label' => 'Show Device Config',     'description' => 'Show device configuration'],
        'updateNotes' => ['label' => 'Update Device Notes',    'description' => 'Update device notes'],
    ],

    'alert' => [
        'title' => 'Alerts',
        'viewAny' => ['label' => 'View Alerts',        'description' => 'View active and historical alerts'],
        'view' => ['label' => 'View Alert Details', 'description' => 'View detailed alert information'],
        'detail' => ['label' => 'View Alert Details', 'description' => 'View detailed alert information'],
        'update' => ['label' => 'Edit Alerts',        'description' => 'Acknowledge or modify alerts'],
        'delete' => ['label' => 'Delete Alerts',      'description' => 'Delete alert history'],
    ],

    'alert-rule' => [
        'title' => 'Alert Rules',
        'viewAny' => ['label' => 'View Alert Rules',   'description' => 'View alert rules'],
        'view' => ['label' => 'View Alert Rule',    'description' => 'View alert rule details'],
        'create' => ['label' => 'Create Alert Rules', 'description' => 'Create new alert rules'],
        'update' => ['label' => 'Edit Alert Rules',   'description' => 'Modify existing alert rules'],
        'delete' => ['label' => 'Delete Alert Rules', 'description' => 'Delete alert rules'],
    ],

    'alert-schedule' => [
        'title' => 'Alert Schedules',
        'viewAny' => ['label' => 'View Alert Schedules',   'description' => 'View alert schedules'],
        'view' => ['label' => 'View Alert Schedule',    'description' => 'View alert schedule details'],
        'create' => ['label' => 'Create Alert Schedules', 'description' => 'Create new alert schedules'],
        'update' => ['label' => 'Edit Alert Schedules',   'description' => 'Modify existing alert schedules'],
        'delete' => ['label' => 'Delete Alert Schedules', 'description' => 'Delete alert schedules'],
    ],

    'alert-template' => [
        'title' => 'Alert Templates',
        'view' => ['label' => 'View Alert Templates',   'description' => 'View alert templates'],
        'create' => ['label' => 'Create Alert Templates', 'description' => 'Create new alert templates'],
        'update' => ['label' => 'Edit Alert Templates',   'description' => 'Modify existing alert templates'],
        'delete' => ['label' => 'Delete Alert Templates', 'description' => 'Delete alert templates'],
    ],

    'alert-transport' => [
        'title' => 'Alert Transports',
        'view' => ['label' => 'View Alert Transports',   'description' => 'View alert transports'],
        'create' => ['label' => 'Create Alert Transports', 'description' => 'Create new alert transports'],
        'update' => ['label' => 'Edit Alert Transports',   'description' => 'Modify existing alert transports'],
        'delete' => ['label' => 'Delete Alert Transports', 'description' => 'Delete alert transports'],
    ],

    'service' => [
        'title' => 'Services',
        'view' => ['label' => 'View Services',   'description' => 'View services monitored on devices'],
        'create' => ['label' => 'Add Services',    'description' => 'Add new services to devices'],
        'update' => ['label' => 'Edit Services',   'description' => 'Modify service check settings'],
        'delete' => ['label' => 'Delete Services', 'description' => 'Remove services from devices'],
    ],

    'port' => [
        'title' => 'Ports',
        'viewAny' => ['label' => 'View Ports',        'description' => 'View the list of all ports'],
        'view' => ['label' => 'View Port Details', 'description' => 'View port details, graphs, and statistics'],
        'update' => ['label' => 'Edit Ports',        'description' => 'Modify port descriptions and settings'],
        'delete' => ['label' => 'Delete Ports',      'description' => 'Permanently delete ports and their data'],
    ],

    'custom-map' => [
        'title' => 'Maps',
        'viewAny' => ['label' => 'View Maps',   'description' => 'View network maps'],
        'view' => ['label' => 'View Map',    'description' => 'View network map details'],
        'create' => ['label' => 'Create Maps', 'description' => 'Create new network maps'],
        'update' => ['label' => 'Edit Maps',   'description' => 'Modify existing network maps'],
        'delete' => ['label' => 'Delete Maps', 'description' => 'Delete network maps'],
    ],

    'reports' => [
        'view' => ['label' => 'View Reports',   'description' => 'View availability and performance reports'],
        'create' => ['label' => 'Create Reports', 'description' => 'Create new reports'],
        'delete' => ['label' => 'Delete Reports', 'description' => 'Delete existing reports'],
    ],

    'oxidized' => [
        'view' => ['label' => 'View Oxidized',          'description' => 'View device configuration backups'],
        'resync' => ['label' => 'Resync Oxidized',        'description' => 'Trigger a configuration re-fetch for a device'],
        'diff' => ['label' => 'View Config Diffs',      'description' => 'View configuration change diffs between backups'],
    ],

    'user' => [
        'title' => 'Users',
        'viewAny' => ['label' => 'View Users',   'description' => 'View user accounts and their roles'],
        'view' => ['label' => 'View User',    'description' => 'View user account details'],
        'create' => ['label' => 'Create Users', 'description' => 'Create new user accounts'],
        'update' => ['label' => 'Edit Users',   'description' => 'Modify user accounts, roles, and permissions'],
        'delete' => ['label' => 'Delete Users', 'description' => 'Delete user accounts'],
    ],

    'settings' => [
        'view' => ['label' => 'View Settings', 'description' => 'View global LibreNMS settings'],
        'edit' => ['label' => 'Edit Settings', 'description' => 'Modify global LibreNMS settings'],
    ],

    'api' => [
        'access' => ['label' => 'API Access', 'description' => 'Access the LibreNMS REST API'],
    ],

    'rbac' => [
        'title' => 'Roles & Permissions',
        'manage_users' => 'Manage Users',
        'manage_roles' => 'Manage Roles',
        'add_role' => 'Add Role',
        'create_role' => 'Create Role',
        'create_new_role' => 'Create New Role',
        'edit_role' => 'Edit Role',
        'delete_role' => 'Delete Role',
        'role_name' => 'Role Name',
        'permissions' => 'Permissions',
        'actions' => 'Actions',
        'all_permissions' => 'All Permissions',
        'read_permissions' => 'All Read-Only Permissions',
        'no_permissions' => 'No permissions assigned',
        'confirm_delete' => 'Are you sure you want to delete this role?',
        'role_name_placeholder' => 'e.g., network-engineer',
        'search_permissions' => 'Search permissions...',
        'select_all' => 'Select All',
        'clear_all' => 'Clear All',
        'save_role' => 'Save Role',
        'update_role' => 'Update Role',
        'created' => 'Role :name created successfully',
        'updated' => 'Role :name updated successfully',
        'deleted' => 'Role :name deleted successfully',
        'role_name_regex' => 'Role names can only contain lowercase letters and hyphens (-).',
    ],
];
