<?php

return [
    'all_devices' => 'All Devices',
    'attributes' => [
        'hostname' => 'Hostname',
        'features' => 'OS Features',
        'hardware' => 'Hardware',
        'icon' => 'Icon',
        'ip' => 'IP',
        'location' => 'Location',
        'os' => 'Device OS',
        'serial' => 'Serial',
        'sysDescr' => 'sysDescr',
        'sysName' => 'sysName',
        'sysObjectID' => 'sysObjectID',
        'version' => 'OS Version',
        'type' => 'Device type',
    ],

    'never_polled' => 'Never polled',
    'vm_host' => 'VM Host',
    'scheduled_maintenance' => 'Scheduled Maintenance',
    'delete_device' => 'Delete Device',
    'delete' => 'Delete :name',
    'confirm_delete' => 'Are you sure you want to delete device :name?',
    'deleted' => 'Deleted device :hostname.',
    'please_select' => 'Select',
    'warning_monitored' => 'Warning! This removes the device from monitoring.',
    'warning_data' => 'It also removes historical data about this device, such as:',
    'device_group' => 'Device Group',
    'show_filter' => 'Show Filter',
    'show_header' => 'Show Header',
    'os' => 'OS',
    'status' => 'Status',
    'status_up' => 'Up',
    'status_down' => 'Down',
    'device_type' => 'Device Type',
    'alerts_disabled' => 'Alerts Disabled',

    'edit' => [
        'delete_device' => 'Delete Device',
        'rediscover_title' => 'Schedule the device for immediate rediscovery by the poller',
        'rediscover' => 'Rediscover device',

        'hostname_title' => 'Change the hostname used for name resolution',
        'hostname_ip' => 'Hostname / IP',

        'display_title' => 'Display name for this device. Keep it short. Available placeholders: hostname, sysName, sysName_fallback, ip (for example ":sysName")',
        'display_name' => 'Display Name',
        'system_default' => 'System Default',

        'overwrite_ip_title' => 'Use this IP instead of resolved one for polling',
        'overwrite_ip' => 'Overwrite IP (do not use)',

        'description' => 'Description',
        'type' => 'Type',
        'static_groups' => 'Static Groups',

        'override_sysLocation' => 'Override sysLocation',
        'coordinates_title' => 'To set coordinates, include [latitude,longitude]',

        'override_sysContact' => 'Override sysContact',

        'depends_on' => 'This device depends on',
        'none' => 'None',

        'poller_group' => 'Poller Group',
        'poller_group_general' => 'General',
        'default_poller' => '(default poller)',

        'disable_polling_alerting' => 'Disable polling and alerting',
        'disable_alerting' => 'Disable alerting',

        'ignore_alert_tag' => 'Ignore alert tag',
        'ignore_alert_tag_title' => "Tag the device to ignore alerts. Alert checks still run.\nAlert rules can read the ignore tag.\nIf the ignore alert tag is on, the alert rule does not match the condition `devices.ignore = 0` or `macros.device = 1`.",

        'ignore_device_status' => 'Ignore Device Status',
        'ignore_device_status_title' => 'Tag device to ignore Status. It will always be shown as online.',

        'save' => 'Save',

        'size_on_disk' => 'Size on Disk',
        'rrd_files' => 'RRD files',
        'last_polled' => 'Last polled',
        'last_discovered' => 'Last discovered',

        'rediscover_error' => 'Failed to schedule this device for rediscovery',
    ],

    'oxidized' => [
        'connection_error' => 'Could not retrieve the device information from Oxidized',
    ],
];
