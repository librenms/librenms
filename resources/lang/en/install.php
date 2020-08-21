<?php
return [
    "checks" => [
        "comment" => "Comment",
        "item" => "Item",
        "php_required" => ":version or higher required",
        "status" => "Status",
        "title" => "Pre-Install Checks"
    ],
    "database" => [
        "credentials" => "Database Credentials",
        "host" => "Host",
        "ip_empty" => "Leave empty if using Host",
        "name" => "Database Name",
        "password" => "Password",
        "port" => "Port",
        "socket" => "Unix-Socket",
        "socket_empty" => "Leave empty if using Unix-Socket",
        "test" => "Check Credentials",
        "title" => "Configure Database",
        "username" => "User"
    ],
    "finish" => [
        "config_exists" => "config.php file exists",
        "config_not_required" => "This file is not required.  Here is the default.",
        "config_not_written" => "Could not write config.php",
        "config_written" => "config.php file written",
        "copied" => "Copied to clipboard",
        "env_manual" => "Manually update :file with the following content",
        "env_not_written" => "Could not write .env file",
        "env_written" => ".env file written",
        "manual_copy" => "Press Ctrl-C to copy",
        "not_finished" => "You have not quite finished yet!",
        "retry" => "Retry",
        "statistics" => "It would be great if you would consider contributing to our statistics, you can do this on the :about and check the box under Statistics.",
        "statistics_link" => "About LibreNMS Page",
        "thanks" => "Thank you for setting up LibreNMS.",
        "title" => "Finish Install",
        "validate" => "First, you need to :validate and fix any issues.",
        "validate_link" => "validate your install"
    ],
    "install" => "Install",
    "migrate" => [
        "building_interrupt" => "Do not close this page or interrupt the import!",
        "error" => "Error encountered, check output for details.",
        "migrate" => "Build Database",
        "retry" => "Retry",
        "timeout" => "HTTP request timed out, your database structure may be inconsistent.",
        "wait" => "Please Wait..."
    ],
    "steps" => [
        "checks" => "Pre-Install Checks",
        "database" => "Database",
        "finish" => "Finish Install",
        "migrate" => "Build Database",
        "user" => "Create User"
    ],
    "title" => "LibreNMS Install",
    "user" => [
        "button" => "Add User",
        "created" => "User Created",
        "email" => "Email",
        "failure" => "Failed to create user",
        "password" => "Password",
        "success" => "Successfully created user",
        "title" => "Create Admin User",
        "username" => "Username"
    ]
];
