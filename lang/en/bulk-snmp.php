<?php

return [
    'title' => 'Bulk SNMP Credentials',
    'group_label' => 'Device Group',
    'device_count' => ':count device|:count devices',
    'description' => 'Update SNMP credentials for every device in this group. Apply this AFTER you have configured the new credentials on the devices themselves.',

    'sections' => [
        'snmp_version' => 'SNMP Version',
        'credentials' => 'Credentials',
        'options' => 'Options',
    ],

    'fields' => [
        'snmpver' => 'SNMP Version',
        'community' => 'Community String',
        'authlevel' => 'Security Level',
        'authname' => 'Auth Username',
        'authpass' => 'Auth Password',
        'authalgo' => 'Auth Algorithm',
        'cryptopass' => 'Crypto Password',
        'cryptoalgo' => 'Crypto Algorithm',
        'port' => 'SNMP Port',
        'transport' => 'Transport',
        'skip_down' => 'Skip devices that are currently down',
    ],

    'buttons' => [
        'open' => 'Bulk SNMP Edit',
        'cancel' => 'Cancel',
        'test' => 'Test Credentials',
        'apply' => 'Apply to :count Devices',
        'close' => 'Close',
    ],

    'feedback' => [
        'testing' => 'Testing credentials on :count devices...',
        'applying' => 'Applying credentials to :count devices...',
        'test_result' => ':passed of :total devices reachable',
        'apply_result' => ':success of :total devices updated successfully',
        'no_devices' => 'No devices in this group',
        'all_down' => 'All devices in this group are currently down',
        'confirm_apply' => 'You are about to update SNMP credentials for :count devices. Continue?',
        'devices_reachable' => 'devices reachable',
        'devices_updated' => 'devices updated',
        'working' => 'Working...',
    ],

    'validation' => [
        'password_min' => 'SNMPv3 passwords must be at least 8 characters.',
    ],

    'denied' => [
        'title' => 'Administrator access required',
        'message' => 'Bulk SNMP credential management is restricted to administrators.',
        'contact' => 'If you need to update SNMP credentials for a device group, please contact a LibreNMS administrator.',
        'back' => 'Back to dashboard',
    ],

    'eventlog' => [
        'updated' => 'Bulk SNMP update: fields [:fields] changed by :user',
    ],
];
