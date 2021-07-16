<?php

namespace LibreNMS\Plugins;

use View;

abstract class Plugin
{
    public function __construct()
    {
        View::addLocation(base_path('html/plugins/' . self::className() . '/ressources/views'));
    }

    final protected static function viewPath()
    {
        return debug_backtrace()[1]['function'];
    }

    final protected static function className()
    {
        return str_replace(__NAMESPACE__ . '\\', '', get_called_class());
    }

    public function menu()
    {
        echo view(self::viewPath(), [
        'title' =>self::className(),
    ]);
    }

    public function settings()
    {
        echo view(self::viewPath(), [
        'title' => self::className(),
    ]);
    }
}
