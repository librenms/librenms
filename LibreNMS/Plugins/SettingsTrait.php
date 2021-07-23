<?php

namespace LibreNMS\Plugins;

use App\Models\Port;

trait SettingsTrait
{
    public static function menu()
    {
        echo view(self::prefix() . self::authenticateSettingsHook(), self::menuData());
    }

    public static function settings()
    {
        echo view(self::prefix() . self::authenticateSettingsHook(), self::settingsData());
    }

    protected static function authenticateSettingsHook()
    {
        // returns menu or settings
        return debug_backtrace()[1]['function'];
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
