<?php

namespace LibreNMS\Plugins;

class Example extends Plugin
{
    use DeviceTrait;
    use PortTrait;
    /* Default defined in abstract Plugin class  */
    public static function menuData(): array
    {
        return [
            'title' => self::className(),
        ];
    }

    /* Default defined in abstract Plugin class  */
    public static function settingsData(): array
    {
        return [
            'title' => self::className(),
        ];
    }
}
