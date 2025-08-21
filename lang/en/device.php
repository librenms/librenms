<?php

return [
    'attributes' => [
        'features' => 'OS Features',
        'hardware' => 'Hardware',
        'icon' => 'Icon',
        'ip' => 'IP',
        'location' => 'Location',
        'os' => 'Device OS',
        'serial' => 'Serial',
        'sysName' => 'sysName',
        'version' => 'OS Version',
        'type' => 'Device type',
    ],

    'vm_host' => 'VM Host',
    'scheduled_maintenance' => 'Scheduled Maintenance',

    'edit' => [
        'delete_device' => 'Delete Device',
        'rediscover_title' => 'Schedule the device for immediate rediscovery by the poller',
        'rediscover' => 'Rediscover device',

        'hostname_title' => 'Change the hostname used for name resolution',
        'hostname_ip' => 'Hostname / IP',

        'display_title' => 'Display Name for this device.  Keep short. Available placeholders: hostname, sysName, sysName_fallback, ip (e.g. ":sysName")',
        'display_name' => 'Display Name',
        'system_default' => 'System Default',

        'overwrite_ip_title' => 'Use this IP instead of resolved one for polling',
        'overwrite_ip' => 'Overwrite IP (do not use)',

        'description' => 'Description',
        'type' => 'Type',

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
        'ignore_alert_tag_title' => "Tag device to ignore alerts. Alert checks will still run.\nHowever, ignore tag can be read in alert rules.\nIf `devices.ignore = 0` or `macros.device = 1` condition is is set and ignore alert tag is on, the alert rule won't match.",

        'ignore_device_status' => 'Ignore Device Status',
        'ignore_device_status_title' => 'Tag device to ignore Status. It will always be shown as online.',

        'save' => 'Save',

        'size_on_disk' => 'Size on Disk',
        'rrd_files' => 'RRD files',
        'last_polled' => 'Last polled',
        'last_discovered' => 'Last discovered',

        'rediscover_error' => 'An error occurred setting this device to be rediscovered',
    ],
];
