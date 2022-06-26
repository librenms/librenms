<?php

return [
    'title' => 'Services',
    'add' => 'Add Service',
    'save' => 'Save Service',
    'cancel' => 'Cancel',
    'test' => 'Test Service',
    'added' => 'Service Created',
    'delete' => 'Delete Service',
    'edit' => 'Edit Service',
    'deleted' => 'Service :service has been deleted.',
    'not_deleted' => 'Service :service has NOT been deleted.',
    'this_device' => '<This Device>',
    'status' => 'Status',
    'view_basic' => 'Basic',
    'view_detailed' => 'Detailed',
    'view_graphs' => 'Graphs',
    'state_all' => 'All',
    'state_ok' => 'Ok',
    'state_warning' => 'Warning',
    'state_critical' => 'Critical',
    'state_unknown' => 'Unknown',
    'graph' => 'Data Set: :ds',
    'fields' => [
        'device_id' => 'Device',
        'service_changed' => 'Last Changed',
        'service_desc' => 'Description',
        'service_disabled' => 'Disable Polling and Alerting',
        'service_ignore' => 'Ignore Alert Tag',
        'service_ip' => 'Remote Host',
        'service_message' => 'Message',
        'service_name' => 'Name',
        'service_param' => 'Parameters',
        'service_type' => 'Type',
    ],
    'defaults' => [
        '-H' => [
            'description' => 'Remote Host',
            'default' => 'This Device',
        ],
    ],
    'check_params' => [
        'dns' => [
            '-H' => [
                'description' => 'Hostname',
                'default' => 'localhost',
            ],
            '-s' => [
                'description' => 'Server',
                'default' => 'This Device',
            ],
        ],
    ],
];
