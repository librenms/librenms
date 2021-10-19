<?php

return [
    'database_connect' => [
        'title' => 'Error connecting to database',
    ],
    'database_inconsistent' => [
        'title' => 'Database inconsistent',
        'header' => 'Database inconsistencies found during a database error, please fix to continue.',
    ],
    'dusk_unsafe' => [
        'title' => 'It is unsafe to run Dusk in production',
        'message' => 'Run ":command" to remove Dusk or if you are a developer set the appropriate APP_ENV',
    ],
    'file_write_failed' => [
        'title' => 'Error: Could not write to file',
        'message' => 'Failed to write to file (:file).  Please check permissions and SELinux/AppArmor if applicable.',
    ],
    'ldap_missing' => [
        'title' => 'PHP LDAP support missing',
        'message' => 'PHP does not support LDAP, please install or enable the PHP LDAP extension',
    ],
    'maximum_execution_time_exceeded' => [
        'title' => 'Maximum execution time of :seconds second exceeded|Maximum execution time of :seconds seconds exceeded',
        'message' => 'Page load exceeded your maximum execution time configured in PHP.  Either increase max_execution_time in your php.ini or improve server hardware',
    ],
    'unserializable_route_cache' => [
        'title' => 'Error caused by PHP version mismatch',
        'message' => 'The version of PHP your web server is running (:web_version) does not match the CLI version (:cli_version)',
    ],
];
