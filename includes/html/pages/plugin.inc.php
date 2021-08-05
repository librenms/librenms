<?php

use App\Models\Plugin;
use App\Plugins\Hooks\SettingsHook;
use LibreNMS\Config;

$link_array = ['page' => 'plugin'];

if ($vars['view'] == 'admin') {
    include_once Config::get('install_dir') . '/includes/html/pages/plugin/admin.inc.php';
    $pagetitle[] = 'Plugins';
} else {
    $pagetitle[] = $vars['p'];
    $plugin = Plugin::isActive()->where('plugin_name', $vars['p'])->value('plugin_name');
    if (! empty($plugin)) {
        $plugin_path = Config::get('plugin_dir') . '/' . $plugin . '/' . $plugin . '.inc.php';
        if (is_file($plugin_path)) {
            chdir(Config::get('install_dir') . '/html');
            include $plugin_path;
            chdir(Config::get('install_dir'));

            return;
        } elseif (\PluginManager::pluginEnabled($plugin)) {
            \PluginManager::call(SettingsHook::class, ['plugin' => $plugin])->each(function ($view) {
                echo $view;
            });

            return;
        }
    }
    print_error('This plugin is either disabled or not available.');
}
