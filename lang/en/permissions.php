<?php

return [
    'alert' => [
        'title' => 'Alerts',
        'viewAll' => ['label' => 'View all alerts', 'description' => 'See alerts on every device.'],
        'view' => ['label' => 'View alerts', 'description' => 'See alerts on devices the user can access.'],
        'detail' => ['label' => 'View alert details', 'description' => 'See the full details of an alert.'],
        'update' => ['label' => 'Acknowledge alerts', 'description' => 'Acknowledge, unacknowledge and add notes to alerts.'],
        'delete' => ['label' => 'Delete alerts', 'description' => 'Remove alerts from the alert history.'],
    ],

    'alert-log' => [
        'title' => 'Alert Log',
        'viewAll' => ['label' => 'View the whole alert log', 'description' => 'See alert history for every device.'],
        'view' => ['label' => 'View the alert log', 'description' => 'See alert history for devices the user can access.'],
    ],

    'alert-rule' => [
        'title' => 'Alert Rules',
        'viewAll' => ['label' => 'View all alert rules', 'description' => 'See every alert rule.'],
        'view' => ['label' => 'View alert rules', 'description' => 'See alert rules for devices the user can access.'],
        'create' => ['label' => 'Create alert rules', 'description' => 'Add new alert rules.', 'risk' => 'Rules can contain raw SQL that runs on the database.'],
        'update' => ['label' => 'Edit alert rules', 'description' => 'Change existing alert rules.', 'risk' => 'Rules can contain raw SQL that runs on the database.'],
        'delete' => ['label' => 'Delete alert rules', 'description' => 'Remove alert rules.'],
    ],

    'alert-schedule' => [
        'title' => 'Alert Schedules',
        'view' => ['label' => 'View maintenance windows', 'description' => 'See scheduled maintenance.'],
        'create' => ['label' => 'Create maintenance windows', 'description' => 'Schedule maintenance to silence alerts.'],
        'update' => ['label' => 'Edit maintenance windows', 'description' => 'Change scheduled maintenance.'],
        'delete' => ['label' => 'Delete maintenance windows', 'description' => 'Remove scheduled maintenance.'],
    ],

    'alert-template' => [
        'title' => 'Alert Templates',
        'view' => ['label' => 'View alert templates', 'description' => 'See the templates used for alert messages.'],
        'create' => ['label' => 'Create alert templates', 'description' => 'Add new alert templates.', 'risk' => 'Templates are Blade code and can run PHP on the server.'],
        'update' => ['label' => 'Edit alert templates', 'description' => 'Change existing alert templates.', 'risk' => 'Templates are Blade code and can run PHP on the server.'],
        'delete' => ['label' => 'Delete alert templates', 'description' => 'Remove alert templates.'],
    ],

    'alert-transport' => [
        'title' => 'Alert Transports',
        'view' => ['label' => 'View alert transports', 'description' => 'See where alerts are sent, including stored credentials.'],
        'create' => ['label' => 'Create alert transports', 'description' => 'Add new places to send alerts.', 'risk' => 'Transports can run programs on the server and send data to any address.'],
        'update' => ['label' => 'Edit alert transports', 'description' => 'Change where alerts are sent.', 'risk' => 'Transports can run programs on the server and send data to any address.'],
        'delete' => ['label' => 'Delete alert transports', 'description' => 'Remove alert transports.'],
    ],

    'api' => [
        'title' => 'API',
        'access' => ['label' => 'Use the API', 'description' => 'Use API tokens. The API still checks the other permissions.'],
    ],

    'application' => [
        'title' => 'Applications',
        'viewAll' => ['label' => 'View all applications', 'description' => 'See applications on every device.'],
        'view' => ['label' => 'View applications', 'description' => 'See applications on devices the user can access.'],
        'update' => ['label' => 'Edit applications', 'description' => 'Change application data.'],
    ],

    'auth-log' => [
        'title' => 'Authentication Log',
        'view' => ['label' => 'View the authentication log', 'description' => 'See who logged in, from where and when.'],
    ],

    'bill' => [
        'title' => 'Bills',
        'viewAll' => ['label' => 'View all bills', 'description' => 'See every bill.'],
        'view' => ['label' => 'View bills', 'description' => 'See bills the user can access.'],
        'create' => ['label' => 'Create bills', 'description' => 'Add new bills.'],
        'update' => ['label' => 'Edit bills', 'description' => 'Change bill settings.'],
        'delete' => ['label' => 'Delete bills', 'description' => 'Remove bills and their history.'],
    ],

    'component' => [
        'title' => 'Components',
        'viewAll' => ['label' => 'View all components', 'description' => 'See components on every device.'],
        'view' => ['label' => 'View components', 'description' => 'See components on devices the user can access.'],
        'create' => ['label' => 'Create components', 'description' => 'Add new components.'],
        'update' => ['label' => 'Edit components', 'description' => 'Change component data.'],
        'delete' => ['label' => 'Delete components', 'description' => 'Remove components.'],
    ],

    'config-backup' => [
        'title' => 'Config Backups',
        'view' => ['label' => 'View device configs', 'description' => 'See backed up device configurations.', 'risk' => 'Configs often contain passwords and keys.'],
        'refresh' => ['label' => 'Refresh device configs', 'description' => 'Start a new config backup now.'],
    ],

    'custom-map' => [
        'title' => 'Custom Maps',
        'viewAll' => ['label' => 'View all maps', 'description' => 'See every custom map.'],
        'view' => ['label' => 'View maps', 'description' => 'See maps with devices the user can access.'],
        'create' => ['label' => 'Create maps', 'description' => 'Add new custom maps.'],
        'update' => ['label' => 'Edit maps', 'description' => 'Change custom maps.'],
        'delete' => ['label' => 'Delete maps', 'description' => 'Remove custom maps.'],
    ],

    'customoid' => [
        'title' => 'Custom OIDs',
        'viewAll' => ['label' => 'View all custom OIDs', 'description' => 'See custom OIDs on every device.'],
        'view' => ['label' => 'View custom OIDs', 'description' => 'See custom OIDs on devices the user can access.'],
        'create' => ['label' => 'Create custom OIDs', 'description' => 'Add new custom OIDs.'],
        'update' => ['label' => 'Edit custom OIDs', 'description' => 'Change custom OIDs.'],
        'delete' => ['label' => 'Delete custom OIDs', 'description' => 'Remove custom OIDs.'],
    ],

    'dashboard' => [
        'title' => 'Dashboards',
        'update' => ['label' => 'Edit shared dashboards', 'description' => 'Change dashboards other users shared for editing.'],
        'delete' => ['label' => 'Delete any dashboard', 'description' => 'Remove dashboards owned by other users.'],
        'copy' => ['label' => 'Copy dashboards', 'description' => 'Copy dashboards to other users.'],
    ],

    'device' => [
        'title' => 'Devices',
        'viewAll' => ['label' => 'View all devices', 'description' => 'See every device.'],
        'view' => ['label' => 'View devices', 'description' => 'See devices the user can access.'],
        'create' => ['label' => 'Add devices', 'description' => 'Add new devices.'],
        'update' => ['label' => 'Edit devices', 'description' => 'Change device settings.'],
        'delete' => ['label' => 'Delete devices', 'description' => 'Remove devices and all their data.'],
        'debug' => ['label' => 'Debug devices', 'description' => 'Run snmpwalk, polling and discovery on demand.', 'risk' => 'Debug output can show SNMP credentials.'],
        'updateNotes' => ['label' => 'Edit device notes', 'description' => 'Change the notes on a device.'],
    ],

    'device-group' => [
        'title' => 'Device Groups',
        'viewAll' => ['label' => 'View all device groups', 'description' => 'See every device group.'],
        'view' => ['label' => 'View device groups', 'description' => 'See groups with devices the user can access.'],
        'create' => ['label' => 'Create device groups', 'description' => 'Add new device groups.'],
        'update' => ['label' => 'Edit device groups', 'description' => 'Change device groups.'],
        'delete' => ['label' => 'Delete device groups', 'description' => 'Remove device groups.'],
    ],

    'diskio' => [
        'title' => 'Disk I/O',
        'viewAll' => ['label' => 'View all disk I/O', 'description' => 'See disk I/O on every device.'],
        'view' => ['label' => 'View disk I/O', 'description' => 'See disk I/O on devices the user can access.'],
    ],

    'eventlog' => [
        'title' => 'Event Log',
        'viewAll' => ['label' => 'View the whole event log', 'description' => 'See events for every device.'],
        'view' => ['label' => 'View the event log', 'description' => 'See events for devices the user can access.'],
    ],

    'inventory' => [
        'title' => 'Inventory',
        'viewAll' => ['label' => 'View all inventory', 'description' => 'See hardware inventory for every device.'],
        'view' => ['label' => 'View inventory', 'description' => 'See hardware inventory for devices the user can access.'],
        'purge' => ['label' => 'Purge inventory', 'description' => 'Remove inventory left over from deleted devices.'],
    ],

    'irc' => [
        'title' => 'IRC Bot',
        'join' => ['label' => 'Make the bot join', 'description' => 'Tell the IRC bot to join a channel.'],
        'quit' => ['label' => 'Make the bot quit', 'description' => 'Tell the IRC bot to disconnect.'],
        'reload' => ['label' => 'Reload the bot', 'description' => 'Tell the IRC bot to reload its settings.'],
    ],

    'link' => [
        'title' => 'Links',
        'viewAll' => ['label' => 'View all links', 'description' => 'See every neighbour link (CDP, LLDP, ...).'],
        'view' => ['label' => 'View links', 'description' => 'See links on devices the user can access.'],
    ],

    'location' => [
        'title' => 'Locations',
        'viewAll' => ['label' => 'View all locations', 'description' => 'See every location.'],
        'view' => ['label' => 'View locations', 'description' => 'See locations of devices the user can access.'],
        'create' => ['label' => 'Create locations', 'description' => 'Add new locations.'],
        'update' => ['label' => 'Edit locations', 'description' => 'Change locations and their coordinates.'],
        'delete' => ['label' => 'Delete locations', 'description' => 'Remove locations.'],
    ],

    'mempool' => [
        'title' => 'Memory',
        'viewAll' => ['label' => 'View all memory', 'description' => 'See memory usage on every device.'],
        'view' => ['label' => 'View memory', 'description' => 'See memory usage on devices the user can access.'],
        'update' => ['label' => 'Edit memory', 'description' => 'Change memory thresholds.'],
    ],

    'notification' => [
        'title' => 'Notifications',
        'create' => ['label' => 'Create notifications', 'description' => 'Post notifications for all users.'],
        'update' => ['label' => 'Edit notifications', 'description' => 'Change notifications.'],
        'delete' => ['label' => 'Delete notifications', 'description' => 'Remove notifications.'],
    ],

    'outage' => [
        'title' => 'Outages',
        'viewAll' => ['label' => 'View all outages', 'description' => 'See outage history for every device.'],
        'view' => ['label' => 'View outages', 'description' => 'See outage history for devices the user can access.'],
    ],

    'oxidized' => [
        'title' => 'Oxidized',
        'search' => ['label' => 'Search configs', 'description' => 'Search the text of Oxidized config backups.'],
    ],

    'peering-db' => [
        'title' => 'PeeringDB',
        'view' => ['label' => 'View PeeringDB', 'description' => 'See PeeringDB data.'],
    ],

    'plugin' => [
        'title' => 'Plugins',
        'admin' => ['label' => 'Manage plugins', 'description' => 'Turn plugins on or off and change their settings.', 'risk' => 'Plugins run PHP code on the server.'],
    ],

    'poller' => [
        'title' => 'Pollers',
        'view' => ['label' => 'View pollers', 'description' => 'See pollers and their status.'],
        'update' => ['label' => 'Edit pollers', 'description' => 'Change poller settings.'],
        'delete' => ['label' => 'Delete pollers', 'description' => 'Remove pollers.'],
    ],

    'poller-group' => [
        'title' => 'Poller Groups',
        'create' => ['label' => 'Create poller groups', 'description' => 'Add new poller groups.'],
        'update' => ['label' => 'Edit poller groups', 'description' => 'Change poller groups.'],
        'delete' => ['label' => 'Delete poller groups', 'description' => 'Remove poller groups.'],
    ],

    'port' => [
        'title' => 'Ports',
        'viewAll' => ['label' => 'View all ports', 'description' => 'See every port, with its addresses, VLANs and FDB.'],
        'view' => ['label' => 'View ports', 'description' => 'See ports the user can access, with their addresses, VLANs and FDB.'],
        'update' => ['label' => 'Edit ports', 'description' => 'Change port descriptions and settings.'],
        'delete' => ['label' => 'Delete ports', 'description' => 'Remove ports and all their data.'],
    ],

    'port-group' => [
        'title' => 'Port Groups',
        'viewAll' => ['label' => 'View all port groups', 'description' => 'See every port group.'],
        'view' => ['label' => 'View port groups', 'description' => 'See groups with ports the user can access.'],
        'create' => ['label' => 'Create port groups', 'description' => 'Add new port groups.'],
        'update' => ['label' => 'Edit port groups', 'description' => 'Change port groups.'],
        'delete' => ['label' => 'Delete port groups', 'description' => 'Remove port groups.'],
    ],

    'processor' => [
        'title' => 'Processors',
        'viewAll' => ['label' => 'View all processors', 'description' => 'See CPU usage on every device.'],
        'view' => ['label' => 'View processors', 'description' => 'See CPU usage on devices the user can access.'],
        'update' => ['label' => 'Edit processors', 'description' => 'Change processor thresholds.'],
    ],

    'reporting' => [
        'title' => 'Reporting',
        'update' => ['label' => 'Change usage reporting', 'description' => 'Turn anonymous usage reporting on or off.'],
    ],

    'role' => [
        'title' => 'Roles',
        'view' => ['label' => 'View roles', 'description' => 'See roles and their permissions.'],
        'create' => ['label' => 'Create roles', 'description' => 'Add new roles.', 'risk' => 'Can create a role with any permission.'],
        'update' => ['label' => 'Edit roles', 'description' => 'Change the permissions in a role.', 'risk' => 'Can give any role, including their own, any permission.'],
        'delete' => ['label' => 'Delete roles', 'description' => 'Remove roles.'],
    ],

    'routing' => [
        'title' => 'Routing',
        'viewAll' => ['label' => 'View all routing', 'description' => 'See BGP, OSPF, MPLS, VRFs and routes on every device.'],
        'view' => ['label' => 'View routing', 'description' => 'See BGP, OSPF, MPLS, VRFs and routes on devices the user can access.'],
        'update' => ['label' => 'Edit routing', 'description' => 'Change routing data, such as BGP peer descriptions.'],
    ],

    'secret' => [
        'title' => 'Secrets',
        'viewAll' => ['label' => 'View All Secrets', 'description' => 'View all secrets'],
        'view' => ['label' => 'View Secrets', 'description' => 'View secrets used by devices the user can access'],
        'create' => ['label' => 'Add Secrets', 'description' => 'Add new secrets'],
        'update' => ['label' => 'Edit Secrets', 'description' => 'Modify secrets'],
        'delete' => ['label' => 'Delete Secrets', 'description' => 'Remove unused secrets'],
        'unmask' => ['label' => 'Unmask Secrets', 'description' => 'View secret values such as passwords and communities', 'risk' => 'Reveals credentials in plain text.'],
    ],

    'sensor' => [
        'title' => 'Sensors',
        'viewAll' => ['label' => 'View all sensors', 'description' => 'See health sensors on every device.'],
        'view' => ['label' => 'View sensors', 'description' => 'See health sensors on devices the user can access.'],
        'update' => ['label' => 'Edit sensors', 'description' => 'Change sensor limits and alerting.'],
    ],

    'service' => [
        'title' => 'Services',
        'viewAll' => ['label' => 'View all services', 'description' => 'See every service check.'],
        'view' => ['label' => 'View services', 'description' => 'See service checks on devices the user can access.'],
        'create' => ['label' => 'Add services', 'description' => 'Add service checks to devices.', 'risk' => 'Service checks run commands on the poller.'],
        'update' => ['label' => 'Edit services', 'description' => 'Change service checks.', 'risk' => 'Service checks run commands on the poller.'],
        'delete' => ['label' => 'Delete services', 'description' => 'Remove service checks.'],
    ],

    'service-template' => [
        'title' => 'Service Templates',
        'view' => ['label' => 'View service templates', 'description' => 'See service templates.'],
        'create' => ['label' => 'Create service templates', 'description' => 'Add new service templates.', 'risk' => 'Service checks run commands on the poller.'],
        'update' => ['label' => 'Edit service templates', 'description' => 'Change service templates.', 'risk' => 'Service checks run commands on the poller.'],
        'delete' => ['label' => 'Delete service templates', 'description' => 'Remove service templates.'],
    ],

    'settings' => [
        'title' => 'Settings',
        'view' => ['label' => 'View settings', 'description' => 'See global settings.'],
        'update' => ['label' => 'Edit settings', 'description' => 'Change global settings.', 'risk' => 'Settings include program paths and credentials, so this is close to full control.'],
    ],

    'ssl-certificate' => [
        'title' => 'SSL Certificates',
        'view' => ['label' => 'View certificates', 'description' => 'See monitored SSL certificates.'],
        'create' => ['label' => 'Add certificates', 'description' => 'Start monitoring an SSL certificate.'],
        'update' => ['label' => 'Edit certificates', 'description' => 'Change monitored SSL certificates.'],
        'delete' => ['label' => 'Delete certificates', 'description' => 'Stop monitoring an SSL certificate.'],
    ],

    'storage' => [
        'title' => 'Storage',
        'viewAll' => ['label' => 'View all storage', 'description' => 'See disk usage on every device.'],
        'view' => ['label' => 'View storage', 'description' => 'See disk usage on devices the user can access.'],
        'update' => ['label' => 'Edit storage', 'description' => 'Change storage thresholds.'],
    ],

    'syslog' => [
        'title' => 'Syslog',
        'viewAll' => ['label' => 'View all syslog', 'description' => 'See syslog for every device.'],
        'view' => ['label' => 'View syslog', 'description' => 'See syslog for devices the user can access.'],
        'delete' => ['label' => 'Delete syslog', 'description' => 'Remove syslog history.'],
    ],

    'tools' => [
        'title' => 'Tools',
        'oui' => ['label' => 'MAC vendor lookup', 'description' => 'Look up the vendor of a MAC address.'],
        'ripe' => ['label' => 'RIPE lookup', 'description' => 'Look up IP and ASN details in the RIPE database.'],
    ],

    'user' => [
        'title' => 'Users',
        'view' => ['label' => 'View users', 'description' => 'See user accounts.'],
        'create' => ['label' => 'Create users', 'description' => 'Add new user accounts.', 'risk' => 'Can create accounts with any role.'],
        'update' => ['label' => 'Edit users', 'description' => 'Change user accounts and their roles.', 'risk' => 'Can give any user, including themselves, any role.'],
        'delete' => ['label' => 'Delete users', 'description' => 'Remove user accounts.'],
        'manage' => ['label' => 'Manage user access', 'description' => 'Choose which devices, ports and bills a user can access.', 'risk' => 'Can give any user, including themselves, access to everything.'],
        'updatePassword' => ['label' => 'Change passwords', 'description' => 'Change the password of other users.', 'risk' => 'Can take over other accounts.'],
    ],

    'vlan' => [
        'title' => 'VLANs',
        'viewAll' => ['label' => 'View all VLANs', 'description' => 'See VLANs on every device.'],
    ],

    'vminfo' => [
        'title' => 'Virtual Machines',
        'viewAll' => ['label' => 'View all virtual machines', 'description' => 'See virtual machines on every device.'],
        'view' => ['label' => 'View virtual machines', 'description' => 'See virtual machines on devices the user can access.'],
        'update' => ['label' => 'Edit virtual machines', 'description' => 'Change virtual machine data.'],
    ],

    'wireless-sensor' => [
        'title' => 'Wireless Sensors',
        'viewAll' => ['label' => 'View all wireless sensors', 'description' => 'See wireless sensors on every device.'],
        'view' => ['label' => 'View wireless sensors', 'description' => 'See wireless sensors on devices the user can access.'],
        'update' => ['label' => 'Edit wireless sensors', 'description' => 'Change wireless sensor limits.'],
        'delete' => ['label' => 'Delete wireless sensors', 'description' => 'Remove wireless sensors.'],
    ],

    'rbac' => [
        'title' => 'Roles & Permissions',
        'beta_warning_title' => 'Beta Feature',
        'beta_warning_message' => 'This is a beta feature. Permissions can be applied incorrectly. Report any problems that you find.',
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
        'view_all_permissions' => 'View All Permissions',
        'view_permissions' => 'View Permissions',
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
        'risky' => 'Risky',
    ],
    'permissions' => [
        'user_permissons' => ':name Permissions',
        'bill_access' => 'Bill Access (:count)',
        'device_access' => 'Device Access (:count)',
        'device_group_access' => 'Device Group Access (:count)',
        'port_access' => 'Port Access (:count)',
        'bill_all' => 'All Bills',
        'device_all' => 'All Devices',
        'device_group_all' => 'All Device Groups',
        'port_all' => 'All Ports',
        'none_configured' => 'None Configured',
    ],
];
