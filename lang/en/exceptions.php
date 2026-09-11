<?php

return [
    'database_connect' => [
        'title' => 'Error connecting to database',
    ],
    'database_inconsistent' => [
        'title' => 'Database inconsistent',
        'header' => 'Database inconsistencies were found during a database error. Fix them to continue.',
    ],
    'dusk_unsafe' => [
        'title' => 'It is unsafe to run Dusk in production',
        'message' => 'Run ":command" to remove Dusk. If you are a developer, set the correct APP_ENV.',
    ],
    'file_write_failed' => [
        'title' => 'Error: Could not write to file',
        'message' => 'Failed to write to file (:file). Check the permissions and SELinux/AppArmor.',
    ],
    'host_exists' => [
        'hostname_exists' => 'Device :hostname already exists',
        'ip_exists' => 'Cannot add :hostname, already have device :existing with this IP :ip',
        'sysname_exists' => 'Already have device :hostname due to duplicate sysName: :sysname',
    ],
    'host_name_empty' => 'Hostname is empty',
    'invalid_auth_mechanism' => [
        'title' => 'Invalid authentication mechanism',
        'message' => 'No valid authentication mechanism is configured. Please check the auth_mechanism setting.',
    ],
    'host_unreachable' => [
        'unpingable' => 'Could not ping :hostname (:ip)',
        'unsnmpable' => 'Could not connect to :hostname. Check the SNMP details and SNMP reachability.',
        'unresolvable' => 'Hostname did not resolve to IP',
        'no_reply_community' => 'SNMP :version: No reply with community :credentials',
        'no_reply_credentials' => 'SNMP :version: No reply with credentials :credentials',
    ],
    'ldap_missing' => [
        'title' => 'PHP LDAP support missing',
        'message' => 'PHP does not support LDAP. Install or enable the PHP LDAP extension.',
    ],
    'maximum_execution_time_exceeded' => [
        'title' => 'Maximum execution time of :seconds second exceeded|Maximum execution time of :seconds seconds exceeded',
        'message' => 'The page load exceeded the maximum execution time configured in PHP. Increase max_execution_time in your php.ini or improve the server hardware.',
    ],
    'unserializable_route_cache' => [
        'title' => 'Error caused by PHP version mismatch',
        'message' => 'The version of PHP your web server is running (:web_version) does not match the CLI version (:cli_version)',
    ],
    'snmp_version_unsupported' => [
        'message' => 'Unsupported SNMP Version ":snmpver", must be v1, v2c, or v3',
    ],
];
