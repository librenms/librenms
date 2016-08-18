<?php

namespace LibreNMS;

class Plugins {

    private static $plugins = array();


    public static function start() {
        global $config;
        if (file_exists($config['plugin_dir'])) {
            // $plugin_files = scandir($config['plugin_dir']);
            $plugin_files = dbFetchRows("SELECT * FROM `plugins` WHERE `plugin_active` = '1'");
            foreach ($plugin_files as $plugins) {
                $plugin_info = pathinfo($config['plugin_dir'].'/'.$plugins['plugin_name'].'/'.$plugins['plugin_name'].'.php');
                if ($plugin_info['extension'] == 'php') {
                    if (is_file($config['plugin_dir'].'/'.$plugins['plugin_name'].'/'.$plugins['plugin_name'].'.php')) {
                        self::load($config['plugin_dir'].'/'.$plugins['plugin_name'].'/'.$plugins['plugin_name'].'.php', $plugin_info['filename']);
                    }
                }
            }

            return true;
        }
        else {
            return false;
        }

    }//end start()


    public static function load($file, $pluginName)
    {
        include $file;
        $pluginFullName = 'LibreNMS\\Plugins\\' . $pluginName;
        if (class_exists($pluginFullName)) {
            $plugin = new $pluginFullName;
        } elseif (class_exists($pluginName)) {
            $plugin = new $pluginName;
        } else {
            return null;
        }
        $hooks  = get_class_methods($plugin);

        foreach ($hooks as $hookName) {
            if ($hookName{0} != '_') {
                self::$plugins[$hookName][] = $pluginName;
            }
        }

        return $plugin;

    }//end load()


    public static function call($hook, $params=false) {
        if (count(self::$plugins[$hook]) != 0) {
            foreach (self::$plugins[$hook] as $name) {
                if (!is_array($params)) {
                    call_user_func(array($name, $hook));
                }
                else {
                    call_user_func_array(array($name, $hook), $params);
                }
            }
        }

    }//end call()
}//end class
