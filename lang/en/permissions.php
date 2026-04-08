<?php

return [
    'device' => [
        'title' => 'Devices',
        'viewAll' => ['label' => 'View All Devices', 'description' => 'View all devices'],
        'view' => ['label' => 'View Device Details', 'description' => 'View devices the user can access'],
        'create' => ['label' => 'Add Devices', 'description' => 'Add new devices to LibreNMS'],
        'update' => ['label' => 'Edit Devices', 'description' => 'Modify device settings'],
        'delete' => ['label' => 'Delete Devices', 'description' => 'Remove devices from LibreNMS'],
        'debug' => ['label' => 'Debug Devices', 'description' => 'Run snmpwalk and other debug commands on devices'],
        'showConfig' => ['label' => 'Show Device Config', 'description' => 'Show device configuration'],
        'updateNotes' => ['label' => 'Update Device Notes', 'description' => 'Update device notes'],
    ],

    'alert' => [
        'title' => 'Alerts',
        'viewAll' => ['label' => 'View All Alerts', 'description' => 'View all alerts'],
        'view' => ['label' => 'View Alert Details', 'description' => 'View alerts for devices the user can access'],
        'detail' => ['label' => 'View Alert Details', 'description' => 'View detailed alert information'],
        'update' => ['label' => 'Edit Alerts', 'description' => 'Acknowledge or modify alerts'],
        'delete' => ['label' => 'Delete Alerts', 'description' => 'Delete alert history'],
    ],

    'alert-rule' => [
        'title' => 'Alert Rules',
        'viewAll' => ['label' => 'View All Alert Rules', 'description' => 'View all alert rules'],
        'view' => ['label' => 'View Alert Rule', 'description' => 'View alert rule details for devices the user can access'],
        'create' => ['label' => 'Create Alert Rules', 'description' => 'Create new alert rules'],
        'update' => ['label' => 'Edit Alert Rules', 'description' => 'Modify existing alert rules'],
        'delete' => ['label' => 'Delete Alert Rules', 'description' => 'Delete alert rules'],
    ],

    'alert-schedule' => [
        'title' => 'Alert Schedules',
        'view' => ['label' => 'View Alert Schedule', 'description' => 'View alert schedule details'],
        'create' => ['label' => 'Create Alert Schedules', 'description' => 'Create new alert schedules'],
        'update' => ['label' => 'Edit Alert Schedules', 'description' => 'Modify existing alert schedules'],
        'delete' => ['label' => 'Delete Alert Schedules', 'description' => 'Delete alert schedules'],
    ],

    'alert-template' => [
        'title' => 'Alert Templates',
        'view' => ['label' => 'View Alert Templates', 'description' => 'View alert templates'],
        'create' => ['label' => 'Create Alert Templates', 'description' => 'Create new alert templates'],
        'update' => ['label' => 'Edit Alert Templates', 'description' => 'Modify existing alert templates'],
        'delete' => ['label' => 'Delete Alert Templates', 'description' => 'Delete alert templates'],
    ],

    'alert-transport' => [
        'title' => 'Alert Transports',
        'view' => ['label' => 'View Alert Transports', 'description' => 'View alert transports'],
        'create' => ['label' => 'Create Alert Transports', 'description' => 'Create new alert transports'],
        'update' => ['label' => 'Edit Alert Transports', 'description' => 'Modify existing alert transports'],
        'delete' => ['label' => 'Delete Alert Transports', 'description' => 'Delete alert transports'],
    ],

    'api' => [
        'title' => 'API Access',
        'access' => ['label' => 'API Access', 'description' => 'Access the LibreNMS REST API'],
    ],

    'application' => [
        'title' => 'Applications',
        'update' => ['label' => 'Update Application', 'description' => 'Update application data'],
    ],

    'auth-log' => [
        'title' => 'Authentication Logs',
        'view' => ['label' => 'View Auth Logs', 'description' => 'View authentication logs'],
    ],

    'bill' => [
        'title' => 'Bills',
        'viewAll' => ['label' => 'View All Bills', 'description' => 'View all billing records'],
        'view' => ['label' => 'View Bill Details', 'description' => 'View billing details and graphs for bills the user can access'],
        'create' => ['label' => 'Create Bills', 'description' => 'Create new billing records'],
        'update' => ['label' => 'Edit Bills', 'description' => 'Modify billing settings'],
        'delete' => ['label' => 'Delete Bills', 'description' => 'Remove billing records'],
    ],

    'component' => [
        'title' => 'Components',
        'update' => ['label' => 'Update Component', 'description' => 'Update component data'],
    ],

    'custom-map' => [
        'title' => 'Maps',
        'viewAll' => ['label' => 'View All Maps', 'description' => 'View all network maps'],
        'view' => ['label' => 'View Map', 'description' => 'View network maps containing devices the user can access'],
        'create' => ['label' => 'Create Maps', 'description' => 'Create new network maps'],
        'update' => ['label' => 'Edit Maps', 'description' => 'Modify existing network maps'],
        'delete' => ['label' => 'Delete Maps', 'description' => 'Delete network maps'],
    ],

    'dashboard' => [
        'title' => 'Dashboards',
        'copy' => ['label' => 'Copy Dashboard', 'description' => 'Copy dashboards from other users'],
    ],

    'device-group' => [
        'title' => 'Device Groups',
        'viewAll' => ['label' => 'View All Device Groups', 'description' => 'View all device groups'],
        'view' => ['label' => 'View Device Group', 'description' => 'View device groups containing devices the user can access'],
        'create' => ['label' => 'Create Device Groups', 'description' => 'Create new device groups'],
        'update' => ['label' => 'Edit Device Groups', 'description' => 'Modify existing device groups'],
        'delete' => ['label' => 'Delete Device Groups', 'description' => 'Delete device groups'],
    ],

    'link' => [
        'title' => 'Links',
        'viewAll' => ['label' => 'View All Links', 'description' => 'View network link information'],
    ],

    'location' => [
        'title' => 'Locations',
        'viewAll' => ['label' => 'View All Locations', 'description' => 'View all locations'],
        'view' => ['label' => 'View Location', 'description' => 'View location related to devices the user can access'],
        'create' => ['label' => 'Create Locations', 'description' => 'Create new locations'],
        'update' => ['label' => 'Edit Locations', 'description' => 'Modify existing locations'],
        'delete' => ['label' => 'Delete Locations', 'description' => 'Delete locations'],
    ],

    'mempool' => [
        'title' => 'Memory Pools',
        'update' => ['label' => 'Update Memory Pool', 'description' => 'Update memory pool data'],
    ],

    'notification' => [
        'title' => 'Notifications',
        'create' => ['label' => 'Create Notifications', 'description' => 'Create new notifications'],
        'update' => ['label' => 'Edit Notifications', 'description' => 'Modify existing notifications'],
    ],

    'oxidized' => [
        'title' => 'Oxidized',
        'view' => ['label' => 'View Oxidized', 'description' => 'View device configuration backups'],
        'refresh' => ['label' => 'Refresh Oxidized', 'description' => 'Trigger a configuration re-fetch for a device'],
        'search' => ['label' => 'Search Oxidized', 'description' => 'Search through Oxidized configuration backups'],
    ],

    'peering-db' => [
        'title' => 'PeeringDB',
        'view' => ['label' => 'View PeeringDB', 'description' => 'View PeeringDB information'],
    ],

    'plugin' => [
        'title' => 'Plugins',
        'admin' => ['label' => 'Plugin Admin', 'description' => 'Manage plugin settings and status'],
    ],

    'poller' => [
        'title' => 'Pollers',
        'view' => ['label' => 'View Pollers', 'description' => 'View poller information and status'],
        'update' => ['label' => 'Edit Pollers', 'description' => 'Modify poller settings'],
        'delete' => ['label' => 'Delete Pollers', 'description' => 'Remove pollers from LibreNMS'],
    ],

    'poller-group' => [
        'title' => 'Poller Groups',
        'create' => ['label' => 'Create Poller Groups', 'description' => 'Create new poller groups'],
        'update' => ['label' => 'Edit Poller Groups', 'description' => 'Modify existing poller groups'],
        'delete' => ['label' => 'Delete Poller Groups', 'description' => 'Delete poller groups'],
    ],

    'port' => [
        'title' => 'Ports',
        'viewAll' => ['label' => 'View All Ports', 'description' => 'View all ports'],
        'view' => ['label' => 'View Port Details', 'description' => 'View ports of devices or ports the user can access'],
        'update' => ['label' => 'Edit Ports', 'description' => 'Modify port descriptions and settings'],
        'delete' => ['label' => 'Delete Ports', 'description' => 'Permanently delete ports and their data'],
    ],

    'port-group' => [
        'title' => 'Port Groups',
        'viewAll' => ['label' => 'View All Port Groups', 'description' => 'View all port groups'],
        'view' => ['label' => 'View Port Group', 'description' => 'View port groups containing ports the user can access'],
        'create' => ['label' => 'Create Port Groups', 'description' => 'Create new port groups'],
        'update' => ['label' => 'Edit Port Groups', 'description' => 'Modify existing port groups'],
        'delete' => ['label' => 'Delete Port Groups', 'description' => 'Delete port groups'],
    ],

    'processor' => [
        'title' => 'Processors',
        'viewAll' => ['label' => 'View All Processors', 'description' => 'View all processors'],
        'view' => ['label' => 'View Processor', 'description' => 'View processors for devices the user can access'],
        'update' => ['label' => 'Update Processor', 'description' => 'Update processor data'],
    ],

    'reporting' => [
        'title' => 'Reporting',
        'update' => ['label' => 'Update Reporting', 'description' => 'Update reporting settings'],
    ],

    'role' => [
        'title' => 'Roles',
        'update' => ['label' => 'Edit Roles', 'description' => 'Modify role permissions and settings'],
    ],

    'routing' => [
        'title' => 'Routing',
        'viewAll' => ['label' => 'View All Routing', 'description' => 'View all routing information'],
        'view' => ['label' => 'View Routing', 'description' => 'View specific routing details'],
        'update' => ['label' => 'Update Routing', 'description' => 'Update routing data'],
    ],

    'service' => [
        'title' => 'Services',
        'viewAll' => ['label' => 'View All Services', 'description' => 'View all services'],
        'view' => ['label' => 'View Services', 'description' => 'View service for devices the user can access'],
        'create' => ['label' => 'Add Services', 'description' => 'Add new services to devices'],
        'update' => ['label' => 'Edit Services', 'description' => 'Modify service check settings'],
        'delete' => ['label' => 'Delete Services', 'description' => 'Remove services from devices'],
    ],

    'service-template' => [
        'title' => 'Service Templates',
        'view' => ['label' => 'View Service Templates', 'description' => 'View service templates'],
        'create' => ['label' => 'Create Service Templates', 'description' => 'Create new service templates'],
        'update' => ['label' => 'Edit Service Templates', 'description' => 'Modify existing service templates'],
        'delete' => ['label' => 'Delete Service Templates', 'description' => 'Delete service templates'],
    ],

    'settings' => [
        'title' => 'Settings',
        'view' => ['label' => 'View Settings', 'description' => 'View global LibreNMS settings'],
        'update' => ['label' => 'Edit Settings', 'description' => 'Modify global LibreNMS settings'],
    ],

    'syslog' => [
        'title' => 'Syslog',
        'delete' => ['label' => 'Delete Syslog', 'description' => 'Delete syslog history'],
    ],

    'user' => [
        'title' => 'Users',
        'view' => ['label' => 'View User', 'description' => 'View user account details'],
        'create' => ['label' => 'Create Users', 'description' => 'Create new user accounts'],
        'update' => ['label' => 'Edit Users', 'description' => 'Modify user accounts, roles, and permissions'],
        'delete' => ['label' => 'Delete Users', 'description' => 'Delete user accounts'],
        'updatePassword' => ['label' => 'Update Password', 'description' => 'Update user password'],
    ],

    'vlan' => [
        'title' => 'VLANs',
        'viewAll' => ['label' => 'View All VLANs', 'description' => 'View all VLAN information'],
    ],

    'vminfo' => [
        'title' => 'Virtual Machines',
        'viewAll' => ['label' => 'View All Virtual Machines', 'description' => 'View all virtual machine information'],
        'view' => ['label' => 'View Virtual Machine', 'description' => 'View virtual machine details for devices the user can access'],
        'update' => ['label' => 'Update Virtual Machine', 'description' => 'Update virtual machine data'],
    ],

    'wireless-sensor' => [
        'title' => 'Wireless Sensors',
        'update' => ['label' => 'Update Wireless Sensor', 'description' => 'Update wireless sensor data'],
        'delete' => ['label' => 'Delete Wireless Sensor', 'description' => 'Delete wireless sensor data'],
    ],

    'customoid' => [
        'title' => 'Custom OIDs',
        'view' => ['label' => 'View Custom OIDs', 'description' => 'View custom OID data'],
        'create' => ['label' => 'Create Custom OIDs', 'description' => 'Create new custom OIDs'],
        'update' => ['label' => 'Edit Custom OIDs', 'description' => 'Modify existing custom OIDs'],
        'delete' => ['label' => 'Delete Custom OIDs', 'description' => 'Delete custom OIDs'],
    ],

    'rbac' => [
        'title' => 'Roles & Permissions',
        'beta_warning_title' => 'Beta Feature',
        'beta_warning_message' => 'This is a beta feature. Permissions might not be applied correctly yet. Please report any issues you encounter.',
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
