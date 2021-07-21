<?php

use App\Models\Plugin;
use LibreNMS\Config;

$link_array = ['page' => 'plugin'];

if ($vars['view'] == 'admin') {
    include_once Config::get('install_dir') . '/includes/html/pages/plugin/admin.inc.php';
    $pagetitle[] = 'Plugins';
} else {
    $pagetitle[] = $vars['p'];
    $plugin = Plugin::where('plugin_active', 1)->where('plugin_name', $vars['p'])->select('plugin_name')->first();
    if (! empty($plugin)) {
        $plugin_path = Config::get('plugin_dir') . '/' . $plugin->plugin_name . '/' . $plugin->plugin_name . '.inc.php';
        if (is_file($plugin_path)) {
            chdir(Config::get('install_dir') . '/html');
            include $plugin_path;
            chdir(Config::get('install_dir'));

            return;
        } else {
            $class = '\\LibreNMS\\Plugins\\' . $plugin->plugin_name;
            $class::settings();

            return;
        }
    }
    print_error('This plugin is either disabled or not available.');
}
