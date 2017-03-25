#!/usr/bin/php
<?php

if (isset($argv[1]) && $argv[1] == '-d') {
    $debug = true;
    $config['auth_ad_debug'] = 1;
}

$init_modules = array('auth');
require realpath(__DIR__ . '/..') . '/includes/init.php';

if (function_exists('ad_bind') && isset($config['auth_ad_binduser']) && isset($config['auth_ad_bindpassword'])) {
    if (!ad_bind($ldap_connection, false)) {
        print_error('LDAP Bind failed for user ' . $config['auth_ad_binduser'] . '@' . $config['auth_ad_domain'] .
        '. Check $config[\'auth_ad_binduser\'] and $config[\'auth_ad_bindpassword\'] in your config.php');
    }
}


// if ldap like, check selinux
if ($config['auth_mechanism'] = 'ldap' || $config['auth_mechanism'] = "active_directory") {
    $enforce = shell_exec('getenforce 2>/dev/null');
    if (str_contains($enforce, 'Enforcing')) {
        // has selinux
        $output = shell_exec('getsebool httpd_can_connect_ldap');
        if ($output != "httpd_can_connect_ldap --> on\n") {
            print_error("You need to run: setsebool -P httpd_can_connect_ldap=1");
            exit;
        }
    }
}

$username = readline('Username: ');
echo 'Password: ';
`stty -echo`;
$password = trim(fgets(STDIN));
`stty echo`;
echo PHP_EOL;

echo "Authenticate user $username: \n";
if (authenticate($username, $password)) {
    print_message("AUTH SUCCESS\n");
} else {
    print_error("AUTH FAILURE\n");
}

$user_id = get_userid($username);

echo "User:\n";
if (function_exists('get_user')) {
    $user = get_user($user_id);

    unset($user['password']);
    unset($user['remember_token']);
    foreach ($user as $property => $value) {
        echo "  $property => $value\n";
    }
}

if (function_exists('get_group_list')) {
    echo 'Groups: ' . implode('; ', get_group_list()) . PHP_EOL;
}


unset($password);
