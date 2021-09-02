<?php

use LibreNMS\Config;

$link_array = ['page' => 'plugin'];

if ($vars['view'] == 'admin') {
    include_once Config::get('install_dir') . '/includes/html/pages/plugin/admin.inc.php';
    $pagetitle[] = 'Plugins';
} else {
    $pagetitle[] = $vars['p'];
    $plugin = dbFetchRow("SELECT `plugin_name` FROM `plugins` WHERE `plugin_name` = ? AND `plugin_active`='1'", [$vars['p']]);
    if (! empty($plugin)) {
        $plugin_path = Config::get('plugin_dir') . '/' . $plugin['plugin_name'] . '/' . $plugin['plugin_name'] . '.inc.php';
        if (is_file($plugin_path)) {
            chdir(Config::get('install_dir') . '/html');
            include $plugin_path;
            chdir(Config::get('install_dir'));

            return;
        }
    }
    print_error('This plugin is either disabled or not available.');
}
