<?php

namespace LibreNMS\Plugins;

use View;

abstract class Plugin
{
    final protected static function className()
    {
        return str_replace(__NAMESPACE__ . '\\', '', get_called_class());
    }

    final protected static function prefix()
    {
        View::addLocation(base_path('html/plugins'));

        return self::className() . '/resources/views/';
    }
}
