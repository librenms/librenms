<?php

namespace LibreNMS\Plugins;

use View;

abstract class Plugin
{
    protected static $menu_view = 'menu';
    protected static $settings_view = 'settings';

    final protected static function className()
    {
        return str_replace(__NAMESPACE__ . '\\', '', get_called_class());
    }

    final protected static function prefix()
    {
        View::addLocation(base_path('html/plugins'));

        return self::className() . '/resources/views/';
    }

    final public static function menu()
    {
        echo view(self::prefix() . self::$menu_view, self::menuData());
    }

    final public static function settings()
    {
        echo view(self::prefix() . self::$settings_view, self::settingsData());
    }

    protected static function menuData(): array
    {
        return [
            'title' => self::className(),
        ];
    }

    protected static function settingsData(): array
    {
        return [
            'title' => self::className(),
        ];
    }
}
